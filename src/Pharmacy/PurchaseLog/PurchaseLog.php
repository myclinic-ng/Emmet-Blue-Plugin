<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\PurchaseLog;

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
 * class PurchaseLog.
 *
 * PurchaseLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 29/12/2017 5:09 PM
 */
class PurchaseLog
{
    public static function create(array $data)
    {
        $items = $data["items"] ?? [];
        $invoiceNo = $data['invoiceNumber'] ?? null;
        $itemVendor = $data['itemVendor'] ?? null;
        $itemPurchaseDate = $data['itemPurchaseDate'] ?? null;
        $itemBuyee = $data['itemBuyee'] ?? null;
        $staff = $data["staff"] ?? 'NULL';

        $restockData = ["items"=>[], "staffId"=>$staff, "storeId"=>1, "globalRestock"=>true, "comment"=> "Purchase log invoice number $invoiceNo"];
        foreach ($items as $item){
            $itemId = $item['item'] ?? null;
            $itemQty = $item['itemQty'] ?? null;
            $itemPrice = $item['itemCostPrice'] ?? null;
            $values[] = "($itemId, '$invoiceNo', $itemQty, $itemPrice, $itemVendor, '$itemPurchaseDate', '$itemBuyee', $staff)";
            
            $restockData["items"][] = ["item"=>$itemId, "quantityAdded"=>$itemQty];
        }

        $query = "INSERT INTO Pharmacy.ItemPurchaseLog (ItemID, InvoiceNumber, ItemQuantity, ItemCostPrice, ItemVendor, ItemPurchaseDate, ItemBuyee, CreatedBy) VALUES ". implode(", ", $values);
        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

            $restock = \EmmetBlue\Plugins\Pharmacy\StoreRestockHistory\StoreRestockHistory::create($restockData);

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

    public static function view(int $resourceId = 0, array $data=[])
    {
        $filters = $data["filtertype"] ?? null;
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.DateCreated DESC) AS RowNum, a.*, e.BillingTypeItemName, c.ItemBrand, ItemManufacturer, d.*
                         FROM Pharmacy.ItemPurchaseLog a
                         INNER JOIN FinancialAccounts.CorporateVendors d ON a.ItemVendor = d.VendorID
                         INNER JOIN Pharmacy.StoreInventory c ON a.ItemID = c.ItemID
                         INNER JOIN Accounts.BillingTypeItems e ON c.Item = e.BillingTypeItemID";

        if (!is_null($filters)){
            $sDate = QB::wrapString($data["startdate"], "'");
            $eDate = QB::wrapString($data["enddate"], "'");
            $selectBuilder .= " WHERE (CONVERT(date, a.DateCreated)) BETWEEN $sDate AND $eDate";


            switch($data["filtertype"]){
                case "invoicenumber":{
                    $selectBuilder .= " AND a.InvoiceNumber = '".$data["query"]."'";
                    break;
                }
                case "item":{
                    $selectBuilder .= " AND a.ItemID = ".$data["query"];
                    break;
                }                
                case "filtercombo":{
                    $query = $data["query"];
                    foreach ($query as $key=>$value){
                        switch(strtolower($value["type"])){
                           case "invoicenumber":{
                                $selectBuilder .= " AND a.InvoiceNumber = '".$value["value"]."'";
                                break;
                            }
                            case "item":{
                                $selectBuilder .= " AND a.ItemID = ".$value["value"];
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
            $selectBuilder .= " WHERE a.LogID = $resourceId";
        } 

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (d.VendorName LIKE '%$keyword%' OR d.VendorAddress LIKE '%$keyword%' OR d.VendorDescription LIKE '%$keyword%' OR e.BillingTypeItemName LIKE '%$keyword%' OR c.ItemBrand LIKE '%$keyword%' OR c.ItemManufacturer LIKE '%$keyword%' OR a.ItemBuyee LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        // die($selectBuilder);

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key => $value) {
                $viewOperation[$key]["staffInfo"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["CreatedBy"]);
                $viewOperation[$key]["staffInfo"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["CreatedBy"]);
                if (isset($viewOperation[$key]["staffInfo"]["Role"]["Name"])){
                    $viewOperation[$key]["staffInfo"]["Role"] = $viewOperation[$key]["staffInfo"]["Role"]["Name"];
                }
            }
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Pharmacy',
                'ItemPurchaseLog',
                (string)$selectBuilder
            );

            if ($resourceId !== 0 && isset($viewOperation[0])){
                $viewOperation = $viewOperation[0];
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $viewOperation = [
                    "data"=>$viewOperation,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }
            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function registerNewItem(array $data){
        // create billing type item
        $billingData = $data["billing"];
        $result = \EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems\NewAccountsBillingTypeItems::default($billingData);
        $billingItemId = $result["billingTypeItemId"];

        // create general default price
        $priceData = $data["price"];
        $priceData = ["price"=>$priceData, "billingTypeItem"=>$billingItemId];
        $result = \EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems\NewAccountsBillingTypeItems::newGeneralPrice($priceData);

        // add to store inventory
        $storeData = $data["inventory"];
        $storeData["item"] = $billingItemId;

        $result = \EmmetBlue\Plugins\Pharmacy\StoreInventory\StoreInventory::create($storeData);

        return $result;
    }
}