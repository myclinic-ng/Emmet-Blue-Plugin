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
                $quantityBefore = /*$item['quantityBefore'] ??*/ null; //<- Disallow sending this value by the user
                $quantityAdded = $item['quantityAdded'] ?? null;

                if (is_null($quantityBefore)){
                    $q = "SELECT ItemQuantity FROM Pharmacy.StoreInventoryItems WHERE Item = $itemId AND StoreID = $store";
                    $r = DBConnectionFactory::getConnection()->query($q)->fetchAll(\PDO::FETCH_ASSOC);
                    if (isset($r[0])){
                        $quantityBefore = (int) $r[0]["ItemQuantity"];
                    }
                }

                $totalQty += (int) $quantityAdded;
                $sumQty = $quantityAdded + $quantityBefore;

                $updateQuery .= "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $sumQty WHERE Item = $itemId AND StoreID = $store";
                $values[] = "($itemId, $quantityBefore, $quantityAdded, '$comment', $staffId)";
            }
            
            if (!empty($values)){
                $query = "INSERT INTO Pharmacy.StoreRestockHistory (ItemID, QuantityBefore, QuantityAdded, Comment, StaffID) VALUES ".implode(",", $values);
                $query .= $updateQuery;

                if ($globalRestock){
                    $query .= "; INSERT INTO Pharmacy.GlobalRestockLog (ItemQuantity, Comment, StaffID, StoreID) VALUES ($totalQty, '$comment', $staffId, $store)";
                }
                
                $result = DBConnectionFactory::getConnection()->exec($query);

                return $result;  
            }
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId = 0, array $data = [])
    {
        $filters = $data["filtertype"] ?? null;
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.RestockDate) AS RowNum,
                    a.*, b.ItemBrand, b.ItemManufacturer, c.BillingType, c.BillingTypeItemName,
                    c.BillingTypeItemID
                FROM Pharmacy.StoreRestockHistory a 
                LEFT JOIN Pharmacy.StoreInventory b ON a.ItemID = b.ItemID 
                INNER JOIN Accounts.BillingTypeItems c ON b.Item = c.BillingTypeItemID";

        if (!is_null($filters)){
            $sDate = QB::wrapString($data["startdate"], "'");
            $eDate = QB::wrapString($data["enddate"], "'");
            $selectBuilder .= " WHERE (CONVERT(date, a.RestockDate)) BETWEEN $sDate AND $eDate";


            switch($data["filtertype"]){
                default:{

                }
            }

            unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);

            if (isset($data["constantstatus"]) && $data["constantstatus"] != ""){
               unset($data["constantstatus"]);
            }

            unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);
        }

        if ($resourceId !== 0){
            $selectBuilder .= " AND a.HistoryID = $resourceId";
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (c.BillingTypeItemName LIKE '%$keyword%' OR a.Comment LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        $result = (
            DBConnectionFactory::getConnection()
            ->query((string)$selectBuilder)
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key]["staffInfo"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"]);
            $result[$key]["staffInfo"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["StaffID"])["Name"];
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
}