<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\StaffProfile;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class NewStaffProfile.
 *
 * NewStaffProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StaffProfile
{
    CONST STAFF_ARCHIVE_DIR = "bin\\data\\records\\archives\\staff\\";

    protected static $staffFolders = [];

    protected static function base64ToJpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb"); 

       if (is_string($base64_string)){
            $data = explode(',', $base64_string);

            fwrite($ifp, base64_decode($data[1])); 
            fclose($ifp);
       } 

        return $output_file; 
    }

    protected static function createStaffFolders(string $staffUuid)
    {
        /**
         * Create 'profile' and 'repositories' folders inside a folder named
         * '$staffUuid' which will also be created inside the STAFF_ARCHIVE_DIR
         * directory.
         */
        $staffDir = self::STAFF_ARCHIVE_DIR.$staffUuid;
        $profileDir = $staffDir.DIRECTORY_SEPARATOR.'profile';
        if (!mkdir($staffDir)){
            return false;
        }
        if (!mkdir($profileDir)){
            unlink($staffDir);
            return false;
        }

        self::$staffFolders = [
            "staff" => $staffDir,
            "profile" => $profileDir
        ];

        return true;
    }

    protected static function uploadPhotoAndDocuments($passport, $documents){
        if (!isset(self::$staffFolders["profile"]) || is_null(self::$staffFolders["profile"])){
            return false;
        }

        // $handler = fopen(self::$staffFolders["profile"].DIRECTORY_SEPARATOR."photo.img", "w");
        // fwrite($handler, (!is_null($passport)) ? $passport : "");
        // fclose($handler);
        // $handler = fopen(self::$staffFolders["profile"].DIRECTORY_SEPARATOR."documents.img", "w");
        // fwrite($handler, (!is_null($documents)) ? $documents : "");
        // fclose($handler);

        self::base64ToJpeg($passport, self::$staffFolders["profile"].DIRECTORY_SEPARATOR."photo.jpg");
        self::base64ToJpeg($documents, self::$staffFolders["profile"].DIRECTORY_SEPARATOR."documents.jpg");
        return true;
    }

    public static function create(array $data)
    {
        if (isset($data["staffName"])){
            $staffId = $data["staffId"];
            $fullName = $data["staffName"];
            $passport = $data["staffPassport"] ?? null;
            $documents = $data["documents"] ?? null;
            
            $query = "SELECT StaffUUID FROM Staffs.Staff WHERE StaffID = $staffId";
            $staffUuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["StaffUUID"];

            unset(
                $data["staffPassport"],
                $data["documents"],
                $data["staffName"],
                $data["staffId"]
            );
            
            try
            {
                $result = DBQueryFactory::insert('Staffs.StaffProfile', [
                    'StaffID'=>$staffId,
                    'StaffFullName'=>(is_null($fullName)) ? 'NULL' : QB::wrapString((string)$fullName, "'"),
                    'StaffPicture'=> QB::wrapString(self::STAFF_ARCHIVE_DIR.$staffUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."photo.jpg", "'"),
                    'StaffIdentificationDocument'=> QB::wrapString(self::STAFF_ARCHIVE_DIR.$staffUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."documents.jpg", "'")
                ]);

                if ($result){
                    $id = $result['lastInsertId'];

                    $values = [];
                    foreach ($data as $key=>$value){
                        $values[] = "($staffId, ".QB::wrapString((string)ucfirst($key), "'").", ".QB::wrapString((string)$value, "'").")";
                    }

                    $values[] = "($staffId, 'StaffProfile', '$id')";

                    $query = "INSERT INTO Staffs.StaffRecordsFieldValue (StaffID, FieldTitle, FieldValue) VALUES ".implode(", ", $values);

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->exec($query)
                    );
                    
                    if ($queryResult){
                        //upload documents now
                        if(!self::createStaffFolders($staffUuid)){
                            self::delete((int)$staffId);
                        }
                        else {
                            if (!self::uploadPhotoAndDocuments($passport, $documents)){
                                self::delete((int)$staffId);
                            }
                        }
                    }
                    else {
                        self::delete((int)$staffId);
                    }

                    DatabaseLog::log(
                        Session::get('USER_ID'),
                        Constant::EVENT_INSERT,
                        'Staffs',
                        'StaffRecordsFieldValue',
                        $query
                    );                
                }

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_INSERT,
                    'Staffs',
                    'Staff',
                    (string)(serialize($result))
                );
                
                
                return $result;
            }
            catch (\PDOException $e)
            {
                self::delete((int)$id);
                throw new SQLException(sprintf(
                    "Unable to process request (staff profile not created), %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }

        throw new \Exception("Required data not set");
    }

    public static function delete(int $resourceId)
    {
        $query = "SELECT StaffUUID FROM Staffs.Staff WHERE StaffID = $resourceId";
        $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["StaffUUID"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_dir(self::STAFF_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid)){
            unlink(self::STAFF_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid);
        }

        try
        {
            $deleteBuilder
                ->from("Staffs.Staff")
                ->where("StaffID = $resourceId");
            
            $result = (
                DBConnectionFactory::getConnection()
                ->exec((string)$deleteBuilder)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Staffs',
                'Staff',
                (string)$deleteBuilder
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function viewAllStaffs(){
        $query = "Staffs.GetStaffBasicProfile";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $usernames = DBConnectionFactory::getConnection()->query("SELECT StaffID, StaffUsername, LoggedIn FROM Staffs.StaffPassword")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$value){
            $id = $value["StaffID"];

            $index = array_search($id, array_column($usernames, 'StaffID'));
            $result[$key]["StaffUsername"] = $usernames[$index]["StaffUsername"];
            $result[$key]["isLoggedIn"] = $usernames[$index]["LoggedIn"];
        }

        return $result;
    }

    public static function viewStaffFullName(int $id){
        $query = "SELECT StaffID, StaffFullName, StaffPicture FROM Staffs.StaffProfile WHERE StaffID = $id";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            return $result[0];
        }

        return ["StaffID"=>$id, "StaffFullName"=>null];
    }

    public static function viewStaffFullNameFromUUID(array $data){
        $query = "SELECT b.StaffID, b.StaffFullName, b.StaffPicture FROM Staffs.Staff a INNER JOIN Staffs.StaffProfile b ON a.StaffID = b.StaffID WHERE a.StaffUUID = '".$data["uuid"]."'";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            return $result[0];
        }

        return ["StaffID"=>$id, "StaffFullName"=>null];
    }
}