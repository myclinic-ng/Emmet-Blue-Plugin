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
 * 
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
                        FULL OUTER JOIN Pharmacy.StoreInventoryItems b ON a.ItemID = b.ItemID 
                        INNER JOIN Pharmacy.StoreInventory c ON b.Item = c.ItemID
                        WHERE b.ItemQuantity=0 AND b.StoreID=$resourceId
                        GROUP BY c.ItemID
                    ) b
                    ON a.ItemID = b.ItemID  ORDER BY b.TotalDisp DESC
            ";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * function stockValues
     * 
     * Gets the current cost value, sales value and profit margin of items in a store
     * 
     * @since 04/09/2021 16:14
     * 
     */
    public static function stockValues(int $resourceId) {
        $query = "
            SELECT 
                a.Item, a.ItemQuantity, b.Item as BillingTypeItemID, c.ItemCostPrice, c.DateCreated as CostPriceLastUpdate, d.BillingTypeItemPrice,
                a.ItemQuantity * c.ItemCostPrice as StockValueCost, a.ItemQuantity * d.BillingTypeItemPrice as StockValueSales,
                (a.ItemQuantity * d.BillingTypeItemPrice) - (a.ItemQuantity * c.ItemCostPrice) as ProfitMargin, e.BillingTypeItemName,
                b.ItemManufacturer, b.ItemBrand
            FROM Pharmacy.StoreInventoryItems a 
            INNER JOIN Pharmacy.StoreInventory b ON a.Item = b.ItemID
            FULL OUTER JOIN (
                SELECT a.ItemID, a.ItemCostPrice, a.DateCreated FROM Pharmacy.ItemPurchaseLog a INNER JOIN (
                    SELECT ItemID, MAX(DateCreated) as DateCreated FROM Pharmacy.ItemPurchaseLog 
                    GROUP BY ItemID
                ) b ON a.ItemID =  b.ItemID WHERE a.DateCreated = b.DateCreated
            ) c ON a.Item = c.ItemID
            INNER JOIN Accounts.GeneralDefaultPrices d ON b.Item = d.BillingTypeItem
            INNER JOIN Accounts.BillingTypeItems e ON e.BillingTypeItemID = d.BillingTypeItem
            WHERE a.StoreID=$resourceId ORDER BY a.Item
        ";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $totalProfit = 0;
        $totalCostValue = 0;
        $totalSalesValue = 0;
        $ratioToProfitSum = 0;
        if (count($result) > 0){
            foreach ($result as $item){
                $totalProfit += $item["ProfitMargin"];
                $totalCostValue += $item["StockValueCost"];
                $totalSalesValue += $item["StockValueSales"];
            }

            foreach($result as $key=>$item){
                if ($totalProfit > 0) { 
                    $ratioToProfit = ($item["ProfitMargin"] * 100) / $totalProfit; 
                    $result[$key]["RatioToProfit"] = round($ratioToProfit, 2, PHP_ROUND_HALF_UP);
                    $ratioToProfitSum += $ratioToProfit;
                }
            }

            $mostExpensiveItem = array_reduce($result, function ($a, $b) {
                return @$a['StockValueCost'] > $b['StockValueCost'] ? $a : $b ;
            });

            $mostValuableItem = array_reduce($result, function ($a, $b) {
                return @$a['StockValueSales'] > $b['StockValueSales'] ? $a : $b ;
            });

            $meanRatioToProfit = round($ratioToProfitSum / count($result), 2, PHP_ROUND_HALF_UP);


            return [
                "meta"=>[
                    "StockValueCost"=>$totalCostValue,
                    "StockValueSales"=>$totalSalesValue,
                    "ProfitMargin"=>$totalProfit,
                    "MostExpensiveItem"=>$mostExpensiveItem,
                    "MostValuableItem"=>$mostValuableItem,
                    "MeanRatioToProfit"=>$meanRatioToProfit
                ],
                "stockValues"=>$result
            ];
        }

        return ["meta"=>[], "stockValues"=>$result];
    }
}