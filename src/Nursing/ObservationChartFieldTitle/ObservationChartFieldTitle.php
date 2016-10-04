<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ObservationChartFieldTitle;

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
 * class ObservationChartFieldTitleType.
 *
 * ObservationChartFieldTitleTypeController
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 25/08/2016 13:35
 */
class ObservationChartFieldTitle
{
    /**
     * creats new Observation Chart Field Title
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $fieldTitleName = $data['fieldTitleName'] ?? null;
        $fieldTitleType = $data['fieldTitleType'] ?? null;
        $fieldTitleDescription = $data['fieldTitleDescription'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Nursing.ObservationChartFieldTitle', [
                'FieldTitleName'=>QB::wrapString($fieldTitleName, "'"),
                'FieldTitleType'=>QB::wrapString($fieldTitleType, "'"),
                'FieldTitleDescription'=>QB::wrapString($fieldTitleDescription, "'"),
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldTitle',
                (string)(serialize($result))
            );
            $id = $result['lastInsertId'];
            return $id;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Nursng Observation Chart Field Title not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Observation Chart Field Title data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.ObservationChartFieldTitle');
        if ($resourceId != 0){
            $selectBuilder->where('FieldTitleID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldTitle',
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
     * Modifies the content of a Observation Chart Field Title Type
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
            $updateBuilder->table("Nursing.ObservationChartFieldTitle");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldTitleID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldTitle',
                (string)(serialize($updateBuilder))
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
     * delete Observation Chart Field Title Type
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Nursing.ObservationChartFieldTitle")
                ->where("FieldTitleID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldTitle',
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