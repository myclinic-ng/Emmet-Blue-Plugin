<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Store;

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
 * class Store.
 *
 * Stores and store inventory properies Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class Store
{
    /**
     * @method create
     * creates ne store and also creates store inventory properties
     * 
     */

    public static function create(array $data)
    {
        $storeInventoryProperties = $data['storeInventoryProperties'] ?? null;
        $storeName = $data['storeName'] ?? null;
        $storeDescription = $data['storeDescription'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Pharmacy.Store', [
                'StoreName'=>QB::wrapString($storeName, "'"),
                'StoreDescription'=>QB::wrapString($storeDescription, "'"),
                ]);
            
            $id = $result['lastInsertId']; 

            $storeInventoryProperties = [];
            foreach ($storeInventoryProperties as $datum){
                $inventoryProperties[] = "($id, ".QB::wrapString($datum['name'], "'").")";
            }

            $query = "INSERT INTO Pharmacy.storeInventoryProperties (StoreID, PropertyName) 
                            VALUES ".implode(", ", $inventoryProperties);

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
    public static function editStore(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['StoreName'])){
                $data['StoreName'] = QB::wrapString($data['StoreName'], "'");
            }
            if (isset($data['StoreDescription'])){
                $data['StoreDescription'] = QB::wrapString($data['StoreDescription'], "'");
            }

            $updateBuilder->table("Pharmacy.Store");
            $updateBuilder->set($data);
            $updateBuilder->where("StoreID = $resourceId");

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
    public static function editStoreInventoryProperties(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['StoreName'])){
                $data['StoreName'] = QB::wrapString($data['StoreName'], "'");
            }
            if (isset($data['PropertyName'])){
                $data['PropertyName'] = QB::wrapString($data['PropertyName'], "'");
            }

            $updateBuilder->table("Pharmacy.StoreInventoryProperties");
            $updateBuilder->set($data);
            $updateBuilder->where("StoreInventoryPropertiesID = $resourceId");

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
            
            $selectBuilder->from("Pharmacy.Store");

            if ($resourceId !== 0){
                $selectBuilder->where("StoreID = $resourceId");
            }

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$storeItem)
            {
                $id = $storeItem["StoreID"];
                $query = "SELECT * FROM Pharmacy.StoreInventoryProperties WHERE StoreID = $id";

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
                ->from("Pharmacy.Store")
                ->where("StoreID = $resourceId");
            
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