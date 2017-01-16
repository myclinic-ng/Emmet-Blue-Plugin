<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientRepository;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class PatientRepository.
 *
 * PatientRepository Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientRepository
{
    CONST PATIENT_ARCHIVE_DIR = "bin\\data\\records\\archives\\patient\\";

    protected static $folders = [];

    protected static function createRepoFolders(string $patientUuid, string $repoUuid)
    {
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoUuid;
        if (!mkdir($repoDir)){
            return false;
        }

        self::$folders = [
            "repo" => $repoDir
        ];

        return true;
    }

    public static function create(array $data)
    {
        $patient = $data["patient"] ?? 'null';
        $number = substr(str_shuffle(MD5(microtime())), 0, 20);
        $name = $data["name"] ?? null;
        $description = $data["description"] ?? null;
        $creator = $data["creator"] ?? null;
        $type = $data["type"] ?? null;

        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = $patient";
        $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["PatientUUID"];

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRepository', [
                'PatientID'=>$patient,
                'RepositoryNumber'=>QB::wrapString($number, "'"),
                'RepositoryName'=>(is_null($name)) ? 'NULL' : QB::wrapString((string)$name, "'"),
                'RepositoryDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'"),
                'RepositoryCreator'=>(is_null($creator)) ? 'NULL' : $creator,
                'RepositoryType'=>(is_null($type)) ? 'NULL' : QB::wrapString((string)$type, "'")
            ]);

            if ($result && !self::createRepoFolders($uuid, $number)){
                self::delete((int)$result["lastInsertId"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRepository',
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
    public static function view(int $resourceId) {
        $query = "SELECT * FROM Patients.PatientRepository WHERE RepositoryID = $resourceId ORDER BY RepositoryID DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            $result = $result[0];
            $id = $result["PatientID"];
            $query = "SELECT * FROM Patients.PatientRepositoryItems WHERE RepositoryID = $resourceId";
            $resultItems = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = $id";
            $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["PatientUUID"];
            $query = "SELECT RepositoryNumber FROM Patients.PatientRepository WHERE RepositoryID = $resourceId";
            $ruuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["RepositoryNumber"];
            $repoLoc = self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid.DIRECTORY_SEPARATOR;

            $result["RepositoryUrl"] = $repoLoc;
            $result["items"] = $resultItems;
        }       

        return $result;
    }

    public static function viewMostRecentJsonByPatient(int $patient) {
        $query = "BEGIN
                    DECLARE @repo AS INT
                    SELECT @repo = RepositoryID FROM Patients.PatientRepository WHERE PatientID = $patient ORDER BY RepositoryCreationDate ASC
                    SELECT * FROM Patients.PatientRepositoryItems WHERE RepositoryID = @repo AND RepositoryItemCategory = 'json'
                END";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];

        $result['ReposityDetails'] = self::view((int)$result['RepositoryID']);

        $result['RepositoryItemContent'] = unserialize(file_get_contents($result['ReposityDetails']['RepositoryUrl'].$result['RepositoryItemNumber']));

        return $result;
    }

    public static function viewByPatient(int $resourceId) {
        $query = "SELECT * FROM Patients.PatientRepository WHERE PatientID = $resourceId ORDER BY RepositoryID DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = $resourceId";
        $uuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["PatientUUID"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        $query = "SELECT RepositoryNumber FROM Patients.PatientRepository WHERE RepositoryID = $resourceId";
        $ruuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["RepositoryNumber"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_dir(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid)){
            unlink(self::PATIENT_ARCHIVE_DIR.DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid);
        }

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRepository")
                ->where("RepositoryNumber = $resourceId");
            
            $result = (
                DBConnectionFactory::getConnection()
                ->exec((string)$deleteBuilder)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRepository',
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