<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Statistics;

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
 * class Statistics.
 *
 * Statisticss and store inventory properies Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class Store
{
    public static function totalItemCount(int $resourceId)
    {
        $query = "SELECT SUM(ItemQuantity) as Count FROM Pharmacy.StoreInventoryItems WHERE StoreID = $resourceId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            $count = $result[0]["Count"];

            return (is_null($count)) ? 0 : $count;
        }

        return 0;
    }

    public static function outOfStockItems(int $resourceId)
    {
        $query = "
                    SELECT a.* FROM (
                        SELECT a.ItemID, a.ItemBrand, a.ItemManufacturer, b.BillingTypeItemName FROM Pharmacy.StoreInventory a 
                        INNER JOIN Accounts.BillingTypeItems b ON a.Item = b.BillingTypeItemID
                    ) a 
                    INNER JOIN (
                        SELECT c.ItemID, COUNT(c.ItemID) AS TotalDisp FROM Pharmacy.DispensedItems a 
                        INNER JOIN Pharmacy.StoreInventoryItems b ON a.ItemID = b.ItemID 
                        INNER JOIN Pharmacy.StoreInventory c ON b.Item = c.ItemID
                        WHERE b.ItemQuantity=0 AND b.StoreID=$resourceId
                        GROUP BY c.ItemID
                    ) b
                    ON a.ItemID = b.ItemID  ORDER BY b.TotalDisp DESC
            ";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
}