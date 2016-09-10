<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientType;

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
 * class PatientType.
 *
 * PatientType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientType
{
    /**
     * creats new Patient Records field titles
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $patientTypeName = $data["patientTypeName"] ?? null;
        $patientTypeCategory = $data["patientTypeCategory"] ?? null;
        $patientTypeDescription = $data["patientTypeDescription"] ?? null;
        
        try
        {
            $result = DBQueryFactory::insert('Patients.PatientType', [
                'CategoryName'=>(is_null($patientTypeCategory)) ? 'NULL' : QB::wrapString((string)$patientTypeCategory, "'"),
                'PatientTypeName'=>(is_null($patientTypeName)) ? 'NULL' : QB::wrapString((string)$patientTypeName, "'"),
                'PatientTypeDescription'=>(is_null($patientTypeDescription)) ? 'NULL' : QB::wrapString((string)$patientTypeDescription, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientType',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient type not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

     /**
     * Modifies the content of a store
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['PatientTypeName'])){
                $data['PatientTypeName'] = QB::wrapString($data['PatientTypeName'], "'");
            }
            if (isset($data['CategoryName'])){
                $data['CategoryName'] = QB::wrapString($data['CategoryName'], "'");
            }
            if (isset($data['PatientTypeDescription'])){
                $data['PatientTypeDescription'] = QB::wrapString($data['PatientTypeDescription'], "'");
            }

            $updateBuilder->table("Patients.PatientType");
            $updateBuilder->set($data);
            $updateBuilder->where("PatientTypeID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view field title
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientType');
        if ($resourceId != 0){
            $selectBuilder->where('PatientTypeID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientType',
                (string)$selectBuilder
            );

            return $viewOperation;        
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

    public static function viewByCategory(int $resourceId)
    {
        $query = "SELECT CategoryName FROM Patients.PatientTypeCategories WHERE CategoryID = $resourceId";
        $categoryName = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["CategoryName"];

        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientType');
        if ($resourceId != 0){
            $selectBuilder->where('CategoryName ='.$categoryName);
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientTypeCategories',
                (string)$selectBuilder
            );

            return $viewOperation;        
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
     * delete field title type
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientType")
                ->where("PatientTypeID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientType',
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