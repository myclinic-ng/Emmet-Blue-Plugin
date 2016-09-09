<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\Ward;

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
 * class Ward.
 *
 * Ward Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 25/08/2016 13:35
 */
class Ward
{
    /**
     * creates new Ward resource
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $wardName = $data['wardName'] ?? null;
        $wardDescription = $data['wardDescription'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Nursing.Ward', [
                'WardName'=>QB::wrapString($wardName, "'"),
                'WardDescription'=>QB::wrapString($wardDescription, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Ward',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Nursng ward not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Wards data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.Ward');
        if ($resourceId != 0){
            $selectBuilder->where('WardID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Ward',
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
     * Modifies a Ward resource
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Nursing.Ward");
            $updateBuilder->set($data);
            $updateBuilder->where("WardID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Ward',
                (string)(serialize($result))
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
     * delete a ward resource
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Nursing.Ward")
                ->where("WardID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Ward',
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