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
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use FileUpload;

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

    public static function uploadRepoItems($patientUuid, $repoNumber, $files)
    {
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoNumber;

        $pathResolver = new FileUpload\PathResolver\Simple($repoDir);
        $fileSystem = new FileUpload\FileSystem\Simple();
        $fileUpload = new FileUpload\FileUpload($files, $_SERVER);

        $fileUpload->setPathResolver($pathResolver);
        $fileUpload->setFileSystem($fileSystem);

        list($files, $headers) = $fileUpload->processAll();

        print_r($headers);
        die();
    }

    public static function create(array $data)
    {
        $repository = $data["repository"] ?? 'null';
        $number = substr(str_shuffle(MD5(microtime())), 0, 20);
        $name = $data["name"] ?? null;
        $description = $data["description"] ?? null;
        $category = $data["category"] ?? null;
        $creator = $data["creator"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRepositoryItems', [
                'RepositoryID'=>$repository,
                'RepositoryItemNumber'=>QB::wrapString($number, "'"),
                'RepositoryItemName'=>(is_null($name)) ? 'NULL' : QB::wrapString((string)$name, "'"),
                'RepositoryItemDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'"),
                'RepositoryItemCategory'=>(is_null($category)) ? 'NULL' : QB::wrapString((string)$category, "'"),
                'RepositoryItemCreator'=>(is_null($creator)) ? 'NULL' : $creator
            ]);

            if ($result){
                switch (strtolower($category))
                {
                    case "media":
                    {
                        $query = "SELECT a.RepositoryNumber, b.PatientUUID FROM Patients.PatientRepository a JOIN Patients.Patient b ON a.PatientID = b.PatientID WHERE a.RepositoryID = $repository";
                        $uuids = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0];
                        $ruuid = $uuids["RepositoryNumber"];
                        $puuid = $uuids["PatientUUID"];

                        if (!self::uploadRepoItems($puuid, $ruuid, $_FILES)){
                            self::delete($result["lastInsertId"]);
                        }
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
    public static function delete(int $resourceId)
    {
        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = $resourceId";
        $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["PatientUUID"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        $query = "SELECT RepositoryNumber FROM Patients.PatientRepositoryItems WHERE RepositoryID = $resourceId";
        $ruuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["RepositoryNumber"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_dir(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid)){
            unlink(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid);
        }

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRepositoryItems")
                ->where("RepositoryNumber = $resourceId");
            
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