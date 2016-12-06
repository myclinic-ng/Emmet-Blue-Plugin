<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\StoreRestockHistory;

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
 * class StoreRestockHistory.
 *
 * store inventory and inventory tags Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class StoreRestockHistory
{
    /**
     * @method create
     * creates ne store inventory and also creates store inventory tags
     * 
     */

    public static function create(array $data)
    {
        $items = $data["items"];
        $comment = $data['comment'] ?? null;
        $staffId = $data['staffId'] ?? null;
        $globalRestock = $data['globalRestock'] ?? false;
        $store = $data["storeId"] ?? null;

        $values = [];
        $updateQuery = "";
        $totalQty = 0;

        try
        {
            foreach ($items as $item){
                $itemId = $item['item'] ?? null;
                $quantityBefore = $item['quantityBefore'] ?? null;
                $quantityAdded = $item['quantityAdded'] ?? null;

                $totalQty += (int) $quantityAdded;
                $sumQty = $quantityAdded + $quantityBefore;

                $updateQuery .= "UPDATE Pharmacy.StoreInventory SET ItemQuantity = $sumQty WHERE ItemID = $itemId;";
                $values[] = "($itemId, $quantityBefore, $quantityAdded, '$comment', $staffId)";
            }

            $query = "INSERT INTO Pharmacy.StoreRestockHistory (ItemID, QuantityBefore, QuantityAdded, Comment, StaffID) VALUES ".implode(",", $values);
            $query .= $updateQuery;

            if ($globalRestock){
                $query .= "; INSERT INTO Pharmacy.GlobalRestockLog (ItemQuantity, Comment, StaffID, StoreID) VALUES ($totalQty, '$comment', $staffId, $store)";
            }
            
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
            
            $selectBuilder->from("Pharmacy.StoreRestockHistory a")->innerJoin("Pharmacy.StoreInventory b", "a.ItemID = b.ItemID");
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