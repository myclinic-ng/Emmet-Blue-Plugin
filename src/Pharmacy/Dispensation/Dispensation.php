<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Dispensation;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Dispensation.
 *
 * Dispensation Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class Dispensation
{
    /**
     * @method create
     * creates ne store inventory and also creates store inventory tags
     * 
     */

    public static function create(array $data)
    {
       // $DispensedItem = $data['eligibleDispensories'] ?? null;
        $dispensedItem = $data['dispensedItem'] ?? null;
        $dispenseeType = $data['dispenseeType'] ?? null;
        $dispenseeTypeId = $data['dispenseeTypeId'] ?? null;
        $eligibleDispensory = $data['eligibleDispensory'] ?? null;
        $dispensingStore = $data['dispensingStore'] ?? null;

        try
        {
            //eligible query
            $eligibleResult = DBQueryFactory::insert('Pharmacy.EligibleDispensory', [

                'EligibleDispensory'=>QB::wrapString($eligibleDispensory, "'"),
                ]);
            
            $eligibleId = $eligibleResult['lastInsertId']; 

            // dispensee query
            $dispenseeResult = DBQueryFactory::insert('Pharmacy.Dispensee', [

                'DispenseeType'=>QB::wrapString($dispenseeType, "'"),
                'DispenseeTypeId'=>QB::wrapString($dispenseeTypeId, "'")
                ]);
            
            $dispenseeId = $eligibleResult['lastInsertId'];

            //dispensation query
             $dispensationResult = DBQueryFactory::insert('Pharmacy.Dispensation', [

                'DispensingStore'=>QB::wrapString($dispensingStore, "'"),
                'EligibleDispensory'=>QB::wrapString($eligibleDispensory, "'"),
                'DispenseeId'=>$dispenseeId
                ]);
            
            $dispensationId = $dispensationResult['lastInsertId'];
            //foreach dispensed item
            foreach ($dispensedItem as $datum){
                $dispensedItem[] = "($dispensationId, ".QB::wrapString($datum['itemId'], "'").",".QB::wrapString($datum['dispensedQuantity'], "'").")";
            }

            $query = "INSERT INTO Pharmacy.DispensedItems (DispensationId, ItemId, DispensedQuantity) 
                            VALUES ".implode(", ", $dispensedItems);
                           
            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            return ['lastInsertId'=>$id];
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a Eligible Dispensory
     */
    public static function editEligibleDispensory(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['EligibleDispensory'])){
                $data['EligibleDispensory'] = QB::wrapString($data['EligibleDispensory'], "'");
            }

            $updateBuilder->table("Pharmacy.EligibleDispensory");
            $updateBuilder->set($data);
            $updateBuilder->where("EligibleDispensoryID = $resourceId");

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
     * Modifies the content of a Dispensee
     */
    public static function editDispensee(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispenseeType'])){
                $data['DispenseeType'] = QB::wrapString($data['DispenseeType'], "'");
            }
            if (isset($data['DispenseeId'])){
                $data['DispenseeId'] = QB::wrapString($data['DispenseeId'], "'");
            }

            $updateBuilder->table("Pharmacy.Dispensee");
            $updateBuilder->set($data);
            $updateBuilder->where("DispenseeID = $resourceId");

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
     * Modifies the content of a Dispensation
     */
    public static function editDispensation(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensingStore'])){
                $data['DispensingStore'] = QB::wrapString($data['DispensingStore'], "'");
            }
            if (isset($data['EligibleDispensory'])){
                $data['EligibleDispensory'] = QB::wrapString($data['EligibleDispensory'], "'");
            }
            
            $updateBuilder->table("Pharmacy.EligibleDispensory");
            $updateBuilder->set($data);
            $updateBuilder->where("EligibleDispensoryID = $resourceId");

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
     * Modifies the content of a DispensedItems
     */
    public static function editDispenseditems(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensedQuantity'])){
                $data['DispensedQuantity'] = QB::wrapString($data['DispensedQuantity'], "'");
            }
            
            $updateBuilder->table("Pharmacy.DispensedItems");
            $updateBuilder->set($data);
            $updateBuilder->where("DispensedItemsID = $resourceId");

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
     * Returns store group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            //selecting the from eligible dispensory table
            $selectBuilder->from("Pharmacy.EligibleDispensory");

            if ($resourceId !== 0){
                $selectBuilder->where("ELigibleDispensoryID = $resourceId");
            }

            $eligibleDispeonsoryResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            //selecting from Dispensee table
            $selectBuilder->from("Pharmacy.Dispensee");

            if ($resourceId !== 0){
                $selectBuilder->where("DispenseeID = $resourceId");
            }

            $dispenseeResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            // selecting from dispensation table
            $selectBuilder->from("Pharmacy.Dispensation");

            if ($resourceId !== 0){
                $selectBuilder->where("DispensationID = $resourceId");
            }

            $dispensationResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);


            foreach ($dispensationResult as $key=>$item)
            {
                $id = $item["DispensationID"];
                $query = "SELECT * FROM Pharmacy.DispensedItems WHERE DispensedItemsID = $id";

                $queryResult = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

                $result[$key]["StoreInventoryProperties"] = $queryResult;
            }

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * deletes dispensedItems resource
     */

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.DispensedItems")
                ->where("DispensedItemID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
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