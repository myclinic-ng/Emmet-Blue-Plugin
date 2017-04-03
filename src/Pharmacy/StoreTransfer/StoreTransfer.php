<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\StoreTransfer;

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
 * class StoreTransfer.
 *
 * store inventory and inventory tags Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class StoreTransfer
{
    /**
     * @method create
     * creates ne store inventory and also creates store inventory tags
     * 
     */

    public static function create(array $data)
    {
        $items = $data["items"];
        $staffId = $data['staffId'] ?? null;
        $store = $data["storeId"] ?? null;
        $recipient = $data["receivingStore"] ?? null;

        if ($store == $recipient){
            throw new \Exception("Invalid Transfer Request");
        }

        $query = "";

        $values = [];
        $updateQuery = "";
        $totalQty = 0;

        try
        {
            foreach ($items as $item){
                $itemId = $item['item'] ?? null;
                $quantityBefore = $q = (int) DBConnectionFactory::getConnection()->query("SELECT ItemQuantity FROM Pharmacy.StoreInventoryItems WHERE Item = $itemId AND StoreID = $recipient")->fetchall(\PDO::FETCH_ASSOC)[0]["ItemQuantity"];
                $quantityAdded = $item['quantityAdded'] ?? null;

                $totalQty += (int) $quantityAdded;
                $sumQty = $quantityAdded + $quantityBefore;

                $q = (int) DBConnectionFactory::getConnection()->query("SELECT ItemQuantity FROM Pharmacy.StoreInventoryItems WHERE Item = $itemId AND StoreID = $store")->fetchall(\PDO::FETCH_ASSOC)[0]["ItemQuantity"];


                $diffQty = $q - $quantityAdded;

                if ($diffQty >= 0){
                    $updateQuery .= "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $diffQty WHERE Item = $itemId AND StoreID = $store";
                    $updateQuery .= "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $sumQty WHERE Item = $itemId AND StoreID = $recipient;";
                }
                else { 
                    throw new \Exception("Invalid Transfer Request");
                }
            }

            $query .= $updateQuery;
            
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
            
            $selectBuilder->from("Pharmacy.StoreTransfer a")->innerJoin("Pharmacy.StoreInventory b", "a.ItemID = b.ItemID");
            $selectBuilder->innerJoin('Accounts.BillingTypeItems c', 'b.Item = c.BillingTypeItemID');

            if ($resourceId !== 0){
                $selectBuilder->where("a.ItemID = $resourceId");
            }

            // die($selectBuilder);
            
            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

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
}