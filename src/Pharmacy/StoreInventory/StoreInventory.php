<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\StoreInventory;

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
 * class StoreInventory.
 *
 * store inventory and inventory tags Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class StoreInventory
{
    /**
     * @method create
     * creates ne store inventory and also creates store inventory tags
     * 
     */

    public static function create(array $data)
    {
        $storeInventoryTags = $data['storeInventoryTags'] ?? null;
        $storeId = $data['storeId'] ?? null;
        $itemName = $data['itemName'] ?? null;
        $itemQuantity = $data['itemQuantity'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Pharmacy.StoreInventory', [
                'StoreID'=>$storeId,
                'ItemName'=>QB::wrapString($itemName, "'"),
                'ItemQuantity'=>QB::wrapString($itemQuantity, "'"),
                ]);
            
            $id = $result['lastInsertId']; 

            foreach ($storeInventoryTags as $datum){
                $inventoryTags[] = "($id, ".QB::wrapString($datum['tagTitle'], "'").",".QB::wrapString($datum['tagName'], "'").")";
            }

            $query = "INSERT INTO Pharmacy.StoreInventoryTags (ItemID, TagTitle, TagName) 
                            VALUES ".implode(", ", $inventoryTags);
                           
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
     * Modifies the content of a store
     */
    public static function editStoreInventory(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['ItemName'])){
                $data['ItemName'] = QB::wrapString($data['ItemName'], "'");
            }
            if (isset($data['ItemQuantity'])){
                $data['ItemQuantity'] = QB::wrapString($data['ItemQuantity'], "'");
            }

            $updateBuilder->table("Pharmacy.StoreInventory");
            $updateBuilder->set($data);
            $updateBuilder->where("ItemID = $resourceId");

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
     * Modifies the content of a store Inventory Properties
     */
    public static function editStoreInventoryTags(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['TagTitle'])){
                $data['TagTitle'] = QB::wrapString($data['TagTitle'], "'");
            }
            if (isset($data['TagName'])){
                $data['TagName'] = QB::wrapString($data['TagName'], "'");
            }

            $updateBuilder->table("Pharmacy.StoreInventoryTags");
            $updateBuilder->set($data);
            $updateBuilder->where("TagID = $resourceId");

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
            
            $selectBuilder->from("Pharmacy.StoreInventory");

            if ($resourceId !== 0){
                $selectBuilder->where("ItemID = $resourceId");
            }

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$storeItem)
            {
                $id = $storeItem["ItemID"];
                $query = "SELECT * FROM Pharmacy.StoreInventoryTags WHERE TagID = $id";

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
     * deletes store resource
     */

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.StoreInventory")
                ->where("ItemID = $resourceId");
            
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