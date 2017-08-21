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

                $diffQty = (int)$q - (int)$quantityAdded;
                if ($diffQty >= 0){
                    $updateQuery .= "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $diffQty WHERE Item = $itemId AND StoreID = $store;";
                    $updateQuery .= "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $sumQty WHERE Item = $itemId AND StoreID = $recipient;";
                    $updateQuery .= "INSERT INTO Pharmacy.StoreTransferLog (TransferringStore, RecipientStore, ItemID, ItemQuantity, StaffID) VALUES ($store, $recipient, $itemId, $quantityAdded, $staffId);";
                }
                else { 
                    throw new \Exception("Invalid Transfer Request, the quantities of ".$item['itemName']." does not balance");
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
        $filters = $data["filtertype"] ?? null;
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.TransferDate) AS RowNum,
                    a.*, b.ItemBrand, b.ItemManufacturer, c.BillingType, c.BillingTypeItemName,
                    c.BillingTypeItemID, d.StoreName AS RecipientStoreName
                FROM Pharmacy.StoreTransferLog a 
                INNER JOIN Pharmacy.StoreInventory b ON a.ItemID = b.ItemID 
                INNER JOIN Accounts.BillingTypeItems c ON b.Item = c.BillingTypeItemID
                INNER JOIN Pharmacy.Store d ON a.RecipientStore = d.StoreID";

        if (!is_null($filters)){
            $sDate = QB::wrapString($data["startdate"], "'");
            $eDate = QB::wrapString($data["enddate"], "'");
            $selectBuilder .= " WHERE (CONVERT(date, a.TransferDate)) BETWEEN $sDate AND $eDate";


            switch($data["filtertype"]){
                case "transferstore":{
                    $selectBuilder .= " AND a.TransferringStore = ".$data["query"];
                    break;
                }
                case "recipientstore":{
                    $selectBuilder .= " AND a.RecipientStore = ".$data["query"];
                    break;
                }                
                case "filtercombo":{
                    $query = $data["query"];
                    foreach ($query as $key=>$value){
                        switch(strtolower($value["type"])){
                           case "transferstore":{
                                $selectBuilder .= " AND a.TransferringStore = ".$value["value"];
                                break;
                            }
                            case "recipientstore":{
                                $selectBuilder .= " AND a.RecipientStore = ".$value["value"];
                                break;
                            }
                        }
                    }
                    break;
                }
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
            $selectBuilder .= " AND a.TransferringStore = $resourceId";
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (c.BillingTypeItemName LIKE '%$keyword%' OR d.StoreName LIKE '%$keyword%')";
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