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
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientRepository
{
    /**
     * creats new patient id and generates a unique user id (UUID)
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $number = substr(str_shuffle(MD5(microtime())), 0, 40);
        $name = $data["name"] ?? null;
        $description = $data["description"] ?? null;
        $location = "bin/records/patient-repositories";
        if (!empty($_FILES)) {
            return $_FILES;
            foreach ($_FILES["name"] as $key=>$files)
            {
                $tempFile = $files['file']['tmp_name'][$key];    
                $url = $location.DIRECTORY_SEPARATOR.$number.DIRECTORY_SEPARATOR;
                $ext = explode(".", $files['file']['name'][$key])[1];
                $targetFile =  $url. $key.".".$ext;
                return $targetFile;
                move_uploaded_file($tempFile,$targetFile);

                return $targetFile;
            } 

        }
        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRepository', [
                'PatientID'=>$patient,
                'RepositoryItemNumber'=>QB::wrapString($number, "'"),
                'RepositoryItemName'=>(is_null($name)) ? 'NULL' : QB::wrapString($name, "'"),
                'RepositoryItemDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString($description, "'"),
                'RepositoryItemUrl'=>(is_null($url)) ? 'NULL' : QB::wrapString($url, "'")
            ]);

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
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientRepository');
        if ($resourceId != 0){
            $selectBuilder->where('PatientRepositoryUUID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRepository',
                (string)$selectBuilder
            );

            if(count($viewOperation) > 0)
            {
                return $viewOperation;
            }
            else
            {
                return null;
            }           
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }
    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRepository")
                ->where("PatientRepositoryID = $resourceId");
            
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