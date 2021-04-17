<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\MedicalSummary;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class MedicalSummaryFields.
 *
 * MedicalSummaryFields Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Field
{
    /**
     * creats new Patient Records field titles
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $fieldTitle = $data["fieldTitle"] ?? null;
        $fieldDescription = $data["fieldDescription"] ?? null;
        
        try
        {
            $result = DBQueryFactory::insert('Patients.MedicalSummaryFields', [
                'FieldTitle'=>(is_null($fieldTitle)) ? 'NULL' : QB::wrapString($fieldTitle, "'"),
                'FieldDescription'=>(is_null($fieldDescription)) ? 'NULL' : QB::wrapString($fieldDescription, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'MedicalSummaryFields',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Patient Medical Records Field Title not created), %s",
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
            if (isset($data['FieldTitle'])){
                $data['FieldTitle'] = QB::wrapString($data['FieldTitle'], "'");
            }
            if (isset($data['FieldDescription'])){
                $data['FieldDescription'] = QB::wrapString($data['FieldDescription'], "'");
            }

            $updateBuilder->table("Patients.MedicalSummaryFields");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldID = $resourceId");

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
            ->from('Patients.MedicalSummaryFields');
        if ($resourceId != 0){
            $selectBuilder->where('FieldID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            // DatabaseLog::log(
            //     Session::get('USER_ID'),
            //     Constant::EVENT_SELECT,
            //     'Patients',
            //     'MedicalSummaryFields',
            //     (string)$selectBuilder
            // );

            return $viewOperation;

            // if(count($viewOperation) > 0)
            // {
            //     return $viewOperation;
            // }
            // else
            // {
            //     return null;
            // }           
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
                ->from("Patients.MedicalSummaryFields")
                ->where("FieldID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'MedicalSummaryFields',
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