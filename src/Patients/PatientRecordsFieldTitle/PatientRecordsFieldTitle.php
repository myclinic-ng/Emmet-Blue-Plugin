<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientRecordsFieldTitle;

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
 * class PatientRecordsFieldTitle.
 *
 * PatientRecordsFieldTitle Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientRecordsFieldTitle
{
    /**
     * creats new Patient Records field titles
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $fieldTitleName = $data["fieldTitleName"] ?? null;
        $fieldTitleType = $data["fieldTitleType"] ?? null;
        $fieldTitleDescription = $data["fieldTitleDescription"] ?? null;
        
        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRecordsFieldTitle', [
                'FieldTitleName'=>(is_null($fieldTitleName)) ? 'NULL' : QB::wrapString($fieldTitleName, "'"),
                'FieldTitleType'=>(is_null($fieldTitleType)) ? 'NULL' : QB::wrapString($fieldTitleType, "'"),
                'FieldTitleDescription'=>(is_null($fieldTitleDescription)) ? 'NULL' : QB::wrapString($fieldTitleDescription, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRecordsFieldTitle',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient Records Field Title not created), %s",
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
            if (isset($data['FieldTitleName'])){
                $data['FieldTitleName'] = QB::wrapString($data['FieldTitleName'], "'");
            }
            if (isset($data['FieldTitleType'])){
                $data['FieldTitleType'] = QB::wrapString($data['FieldTitleType'], "'");
            }
            if (isset($data['FieldTitleDescription'])){
                $data['FieldTitleDescription'] = QB::wrapString($data['FieldTitleDescription'], "'");
            }

            $updateBuilder->table("Patients.PatientRecordsFieldTitle");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldTitleID = $resourceId");

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
            ->from('Patients.PatientRecordsFieldTitle');
        if ($resourceId != 0){
            $selectBuilder->where('FieldTitleID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRecordsFieldTitle',
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
     * delete field title type
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRecordsFieldTitle")
                ->where("FieldTitleID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientRecordsFieldTitle',
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