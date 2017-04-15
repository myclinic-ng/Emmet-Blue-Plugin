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
        $storeInventoryTags = $data['tags'] ?? null;
        $itemName = $data['item'] ?? null;
        $itemBrand = $data['brand'] ?? null;
        $itemManufacturer = $data['manufacturer'] ?? null;
        
        try
        {
            $result = DBQueryFactory::insert('Pharmacy.StoreInventory', [
                'Item'=>$itemName,
                'ItemBrand'=>is_null($itemBrand) ? "NULL": QB::wrapString($itemBrand, "'"),
                'itemManufacturer'=>is_null($itemManufacturer) ? "NULL": QB::wrapString($itemManufacturer, "'")
            ]);
            
            $id = $result['lastInsertId']; 

            self::createInventoryTags(["tags"=>$data["tags"], "item"=>$id]);

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

    public static function createInventoryTags(array $data){
        $storeInventoryTags = $data['tags'] ?? null;
        $id = $data["item"] ?? null;

        if (is_array($storeInventoryTags))
        {
            foreach ($storeInventoryTags as $datum){
                $inventoryTags[] = "($id, ".QB::wrapString($datum['title'], "'").",".QB::wrapString($datum['name'], "'").")";
            }

            if (isset($inventoryTags))
            {
                $query = "INSERT INTO Pharmacy.StoreInventoryTags (ItemID, TagTitle, TagName) 
                            VALUES ".implode(", ", $inventoryTags);
                               
                $result = (
                    DBConnectionFactory::getConnection()
                    ->exec($query)
                );

                return $result;
            }
        }

        return false;
    }

    public static function addStoreItems(array $data)
    {
        $items = $data['items'] ?? null;
        $store = $data['store'] ?? null;

        $queryV = [];
        foreach ($items as $key => $value) {
            $queryV[] = "($value, $store)";
        }

        $query = "INSERT INTO Pharmacy.StoreInventoryItems (Item, StoreID) VALUES ".implode(", ", $queryV);

        // die($query);
        
        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

            return $result;
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
            if (isset($data['Item'])){
                $data['Item'] = QB::wrapString($data['Item'], "'");
            }

            if (isset($data['ItemQuantity'])){
                $data['ItemQuantity'] = QB::wrapString($data['ItemQuantity'], "'");
            }

            if (isset($data['ItemBrand'])){
                $data['ItemBrand'] = QB::wrapString($data['ItemBrand'], "'");
            }

            if (isset($data['ItemManufacturer'])){
                $data['ItemManufacturer'] = QB::wrapString($data['ItemManufacturer'], "'");
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
        try
        {
            if (isset($data["paginate"])){
                $size = $data["size"];
                $from = $data["from"];
                $selectBuilder = "SELECT * FROM ( SELECT ROW_NUMBER() OVER ( ORDER BY a.Item ) AS RowNum, a.*, b.BillingTypeItemName FROM Pharmacy.StoreInventory a INNER JOIN Accounts.BillingTypeItems b ON a.Item = b.BillingTypeItemID ) AS RowConstrainedResult WHERE RowNum >= $from AND RowNum < $size ORDER BY RowNum";
            }
            else {
                $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();
                $selectBuilder->columns("a.*, b.BillingTypeItemName");
                
                $selectBuilder->from("Pharmacy.StoreInventory a")->innerJoin("Accounts.BillingTypeItems b", "a.Item = b.BillingTypeItemID");

                if ($resourceId !== 0){
                    $selectBuilder->where("ItemID = $resourceId");
                }
            }

            // die($selectBuilder);

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$storeItem)
            {
                $id = $storeItem["ItemID"];
                $query = "SELECT TagID, TagTitle, TagName FROM Pharmacy.StoreInventoryTags WHERE ItemID = $id";

                $queryResult = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

                $result[$key]["Tags"] = $queryResult;
            }

            if (isset($data["paginate"])){
                $total = DBConnectionFactory::getConnection()->query(
                    "SELECT count(*) as count FROM Pharmacy.StoreInventory a INNER JOIN Accounts.BillingTypeItems b ON a.Item = b.BillingTypeItemID"
                )->fetchAll(\PDO::FETCH_ASSOC)[0]["count"];

                $result = [
                    "data"=>$result,
                    "filtered"=>count($result),
                    "total"=>$total
                ];
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

    public static function viewByStore(int $resourceId=0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("a.*, c.*, b.BillingTypeItemName");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Pharmacy.StoreInventoryItems a")->innerJoin("Pharmacy.StoreInventory c", "a.Item = c.ItemID")->innerJoin("Accounts.BillingTypeItems b", "c.Item = b.BillingTypeItemID")->where("a.StoreID = $resourceId");

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$storeItem)
            {
                $id = $storeItem["ItemID"];
                $query = "SELECT TagID, TagTitle, TagName FROM Pharmacy.StoreInventoryTags WHERE ItemID = $id";

                $queryResult = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

                $result[$key]["Tags"] = $queryResult;
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

    public static function viewAvailableItemsByStore(int $resourceId=0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            $selectBuilder->columns("ROW_NUMBER() OVER (ORDER BY b.BillingTypeItemID) AS RowNum, a.StoreID, a.ItemQuantity, c.ItemID, c.Item, c.ItemBrand, c.ItemManufacturer, b.BillingTypeItemName");
            
            $selectBuilder->from("Pharmacy.StoreInventoryItems a")->innerJoin("Pharmacy.StoreInventory c", "a.Item = c.ItemID")->innerJoin("Accounts.BillingTypeItems b", "c.Item = b.BillingTypeItemID")->where("a.StoreID = $resourceId AND a.ItemQuantity > 0");

            if (isset($data["paginate"])){
                if (isset($data["keywordsearch"])){
                    $keyword = $data["keywordsearch"];
                    $selectBuilder .= " AND (c.ItemBrand LIKE '%$keyword%' OR c.ItemManufacturer LIKE '%$keyword%' OR b.BillingTypeItemName LIKE '%$keyword%')";
                }
                $size = $data["from"] + $data["size"];
                $_query = (string) $selectBuilder;
                $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
            }

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$storeItem)
            {
                $id = $storeItem["ItemID"];
                $query = "SELECT TagID, TagTitle, TagName FROM Pharmacy.StoreInventoryTags WHERE ItemID = $id";

                $queryResult = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

                $result[$key]["Tags"] = $queryResult;
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $result = [
                    "data"=>$result,
                    "total"=>$total,
                    "filtered"=>$total
                ];
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

    public static function deleteStoreInventoryTag(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.StoreInventoryTags")
                ->where("TagID = $resourceId");

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