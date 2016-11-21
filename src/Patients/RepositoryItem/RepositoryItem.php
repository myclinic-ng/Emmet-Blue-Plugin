<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\RepositoryItem;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UnallowedOperationException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use FileUpload;
use EmmetBlue\Core\CustomFileNameGenerator as CFNG;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class RepositoryItem.
 *
 * RepositoryItem Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class RepositoryItem
{
    CONST PATIENT_ARCHIVE_DIR = "bin\\data\\records\\archives\\patient\\";
    protected static $allowedExtensions = [
        "image"=>["jpg", "png"],
        "text"=>["txt"],
        "pdf"=>["pdf"]
    ];

    public static function uploadRepoItems($patientUuid, $repoNumber, $files, $name)
    {
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoNumber;

        $validator = new FileUpload\Validator\MimeTypeValidator(['image/png', 'image/jpg']);
        $pathResolver = new FileUpload\PathResolver\Simple($repoDir);
        $fileSystem = new FileUpload\FileSystem\Simple();
        $filenamegenerator = new CFNG($name);

        $fileUpload = new FileUpload\FileUpload($files, $_SERVER);

        $fileUpload->setPathResolver($pathResolver);
        $fileUpload->setFileSystem($fileSystem);
        // $fileUpload->addValidator($validator);
        $fileUpload->setFileNameGenerator($filenamegenerator);

        list($files, $headers) = $fileUpload->processAll();

        return true;
    }

    public static function createRepoFile($patientUuid, $repoNumber, $file, $name)
    {
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoNumber;

        $handle = fopen($repoDir. DIRECTORY_SEPARATOR . $name, 'w');
        fwrite($handle, $file);
        fclose($handle);

        return true;
    }

    public static function create(array $data)
    {
        $repository = $data["repository"] ?? 'null';
        $number = substr(str_shuffle(MD5(microtime())), 0, 20);
        $name = $data["name"] ?? null;
        $description = $data["description"] ?? null;
        $category = $data["category"] ?? null;
        $creator = $data["creator"] ?? null;

        if (isset($_FILES["documents"])){
            $fileNameArray = explode(".", $_FILES["documents"]["name"]);
            $ext = $fileNameArray[count($fileNameArray) - 1];
        }
        else if(isset($data["json"])){
            $ext = "json";
        }

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRepositoryItems', [
                'RepositoryID'=>$repository,
                'RepositoryItemNumber'=>QB::wrapString($number.".".$ext, "'"),
                'RepositoryItemName'=>(is_null($name)) ? 'NULL' : QB::wrapString((string)$name, "'"),
                'RepositoryItemDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'"),
                'RepositoryItemCategory'=>(is_null($category)) ? 'NULL' : QB::wrapString((string)$category, "'"),
                'RepositoryItemCreator'=>(is_null($creator)) ? 'NULL' : $creator
            ]);

            if ($result){
                $query = "SELECT a.RepositoryNumber, b.PatientUUID FROM Patients.PatientRepository a JOIN Patients.Patient b ON a.PatientID = b.PatientID WHERE a.RepositoryID = $repository";
                $uuids = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0];
                $ruuid = $uuids["RepositoryNumber"];
                $puuid = $uuids["PatientUUID"];

                switch (strtolower($category))
                {
                    case "image":
                    case "pdf":
                    case "text":
                    {
                        if (in_array(strtolower($ext), self::$allowedExtensions[strtolower($category)])){
                           if (!self::uploadRepoItems($puuid, $ruuid, $_FILES["documents"], $number)){
                                self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            }
                        }
                        else {
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            throw new UnallowedOperationException(sprintf(
                                "Unallowed file type detected. .%s files are not allowed",
                                $ext
                            ), Constant::UNDEFINED);
                        }
                        break;
                    }
                    case "json":
                    {
                        $json = $data["json"] ?? null;

                        if (!self::createRepoFile($puuid, $ruuid, serialize($json), $number.".".$ext)){
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                        }
                        break;
                    }
                }
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'RepositoryItem',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    /**
     * view patients UUID
     */
    public static function view(int $resourceId)
    {
        
    }
    /**
     * delete patient
     */
    public static function delete(int $resourceId, string $uuid, string $file)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        $query = "SELECT b.RepositoryNumber FROM Patients.PatientRepositoryItems a INNER JOIN Patients.PatientRepository b ON a.RepositoryID = b.RepositoryID WHERE RepositoryItemID = $resourceId";
        $ruuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["RepositoryNumber"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_file(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid.DIRECTORY_SEPARATOR.$file)){
            unlink(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid.DIRECTORY_SEPARATOR.$file);
        }

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRepositoryItems")
                ->where("RepositoryItemID = $resourceId");
            
            $result = (
                DBConnectionFactory::getConnection()
                ->exec((string)$deleteBuilder)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'RepositoryItem',
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
}