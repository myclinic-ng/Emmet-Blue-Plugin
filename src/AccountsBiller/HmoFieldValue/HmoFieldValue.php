<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\HmoFieldValue;

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
 * class NewHmoFieldValue.
 *
 * NewHmoFieldValue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoFieldValue
{
    CONST PATIENTHMO_ARCHIVE_DIR = "bin\\data\\records\\archives\\hmodatabase\\";

    protected static $hmoFolders = [];

    protected static function base64ToJpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb"); 

       if (is_string($base64_string)){
            $data = explode(',', $base64_string);

            fwrite($ifp, base64_decode($data[1])); 
            fclose($ifp);
       } 

        return $output_file; 
    }

    protected static function createHmoFolders(string $hmoUuid)
    {
        $hmoDir = self::PATIENTHMO_ARCHIVE_DIR.$hmoUuid;
        if (is_dir($hmoDir) || !mkdir($hmoDir)){
            return false;
        }

        self::$hmoFolders = [
            "hmo" => $hmoDir
        ];

        return true;
    }

    protected static function uploadPhotoAndDocuments($documents){
        if (!isset(self::$hmoFolders["hmo"]) || is_null(self::$hmoFolders["hmo"])){
            return false;
        }
        self::base64ToJpeg($documents, self::$hmoFolders["hmo"].DIRECTORY_SEPARATOR."documents.jpg");
        return true;
    }

    public static function create(array $data)
    {
        if (true != false){
            $patientId = $data["patientId"];
            $documents = $data["documents"] ?? null;

            unset(
                $data["patientId"],
                $data["documents"]
            );
            
            try
            {
                $result = DBQueryFactory::insert('Accounts.PatientHmoProfile', [
                    'PatientID'=>$patientId,
                    'PatientIdentificationDocument'=> QB::wrapString(self::PATIENTHMO_ARCHIVE_DIR.$patientId.DIRECTORY_SEPARATOR."documents.jpg", "'")
                ]);

                if ($result){
                    $id = $result['lastInsertId'];

                    $values = [];
                    foreach ($data as $key=>$value){
                        $values[] = "($id, ".QB::wrapString((string)ucfirst($key), "'").", ".QB::wrapString((string)$value, "'").")";
                    }

                    $query = "INSERT INTO Accounts.PatientHmoFieldValues (ProfileID, FieldTitle, FieldValue) VALUES ".implode(", ", $values);
                    // die($query);

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->exec($query)
                    );
                    
                    if ($queryResult){
                        //upload documents now
                        if(!self::createHmoFolders((string)$patientId)){
                            self::delete((int)$patientId);
                        }
                        else {
                            if (!self::uploadPhotoAndDocuments($documents)){
                                self::delete((int)$patientId);
                            }
                        }
                    }
                    else {
                        self::delete((int)$patientId);
                    }

                    DatabaseLog::log(
                        Session::get('USER_ID'),
                        Constant::EVENT_INSERT,
                        'Accounts',
                        'PatientHmoFieldValues',
                        $query
                    );                
                }

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_INSERT,
                    'Accounts',
                    'PatientHmoProfile',
                    (string)(serialize($result))
                );
                
                
                return $result;
            }
            catch (\PDOException $e)
            {
                self::delete((int)$patientId);
                throw new SQLException(sprintf(
                    "Unable to process request (hmo profile not created), %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }

        throw new \Exception("Required data not set");
    }

    public static function delete(int $resourceId)
    {
        try
        {
	        if (is_dir(self::PATIENTHMO_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$resourceId)){
	        	$oldPath = self::PATIENTHMO_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$resourceId;
	        	$newPath = self::PATIENTHMO_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$resourceId."-deleted-".date("mdy")."-".time();
	        	rename("$oldPath", "$newPath");
	            //unlink(self::PATIENTHMO_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$resourceId);
	        }

        	$deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();
            $deleteBuilder
                ->from("Accounts.PatientHmoProfile")
                ->where("PatientID = $resourceId");
            
            $result = (
                DBConnectionFactory::getConnection()
                ->exec((string)$deleteBuilder)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_DELETE,
                'Accounts',
                'PatientHmoProfile',
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

    public static function viewProfile(int $resourceId = 0){
        if ($resourceId == 0){
            $query = "Accounts.GetPatientHmoProfile";
        }
        else {
            $query = "Accounts.GetPatientHmoProfile $resourceId";
        }
        try {
        	$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        }
        catch (\PDOException $e){
        	$result = [];
        }
        // foreach ($result as $key=>$value){
        //     $id = (int) $value["PatientID"];

        //     $result[$key]["PatientInformation"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view($id)["_source"];

        // }

        return $result;
    }

    public static function viewProfileByUuid(int $resourceId, array $data){
        $uuid = $data["uuid"];
        $query = "SELECT PatientID FROM Patients.Patient WHERE PatientUUID = '$uuid'";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            $resourceId = $result[0]["PatientID"];

            return self::viewProfile((int) $resourceId); 
        }

        throw new SQLException(sprintf(
            "Profile not found"
        ), Constant::UNDEFINED);
    }

}