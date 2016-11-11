<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\AllergyTrigger;

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
 * class AllergyTrigger.
 *
 * AllergyTrigger Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 25/08/2016 13:35
 */
class AllergyTrigger
{
    /**
     * creats new AllergyTrigger
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $allergy = $data['allergy'] ?? null;
        $triggerName = $data['name'] ?? null;
        $triggerDescription = $data['description'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Consultancy.AllergyTriggers', [
                'Allergy'=>$allergy,
                'TriggerName'=>QB::wrapString($triggerName, "'"),
                'TriggerDescription'=>QB::wrapString($triggerDescription, "'"),
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'AllergyTriggers',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (allergy trigger not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Trigger data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.AllergyName')
            ->from('Consultancy.AllergyTriggers a')
            ->innerJoin('Consultancy.Allergies b', "a.TriggerID = b.AllergyID");
        if ($resourceId != 0){
            $selectBuilder->where('TriggerID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'AllergyTriggers',
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

    public static function viewByAllergy(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.AllergyName')
            ->from('Consultancy.AllergyTriggers a')
            ->innerJoin('Consultancy.Allergies b', "a.TriggerID = b.AllergyID")
            ->where('Allergy ='.$resourceId);

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'AllergyTriggers',
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
     * Modifies the content of a AllergyTrigger
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Consultancy.AllergyTriggers");
            $updateBuilder->set($data);
            $updateBuilder->where("TriggerID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'AllergyTriggers',
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
     * delete AllergyTrigger
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Consultancy.AllergyTriggers")
                ->where("TriggerID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'AllergyTriggers',
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