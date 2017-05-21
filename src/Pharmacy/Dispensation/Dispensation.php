<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Dispensation;

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
 * class Dispensation.
 *
 * Dispensation Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class Dispensation
{

    public static function create(array $data)
    {
        $dispensedItems = $data['dispensedItems'] ?? null;
        $dispensee = QB::wrapString((string)$data['dispensee'], "'") ?? null;
        $eligibleDispensory = $data['eligibleDispensory'] ?? 'null';
        $dispensingStore = $data['dispensingStore'] ?? 'null';
        $patient = $data['patient'] ?? 'null';
        $request = $data["request"] ?? 'null';

        try
        {

            $dispensationResult = DBQueryFactory::insert('Pharmacy.Dispensation', [
                'DispensingStore'=>$dispensingStore,
                'EligibleDispensory'=>$eligibleDispensory,
                'DispenseeID'=>$dispensee,
                'Patient'=>$patient,
                'RequestID'=>$request
            ]);
            
            $dispensationId = $dispensationResult['lastInsertId'];
            $updatesQ = [];
            foreach ($dispensedItems as $datum){
                $dispensedItem[] = "($dispensationId, ".$datum['itemID'].",".$datum['quantity'].")";
                $q = DBConnectionFactory::getConnection()->query("SELECT ItemQuantity as Q FROM Pharmacy.StoreInventoryItems WHERE Item = ".$datum["itemID"]." AND StoreID = ".$dispensingStore)->fetchAll(\PDO::FETCH_ASSOC)[0]["Q"];
                $newQ = (int) $q - (int) $datum["quantity"];
                $updatesQ[] = "UPDATE Pharmacy.StoreInventoryItems SET ItemQuantity = $newQ WHERE Item = ".$datum["itemID"]." AND StoreID = ".$dispensingStore; 
            }

            $query = "INSERT INTO Pharmacy.DispensedItems (DispensationID, ItemID, DispensedQuantity) VALUES ".implode(", ", $dispensedItem);

            $query .= "; ".implode(";", $updatesQ);

            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

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
     * Modifies the content of a Dispensation
     */
    public static function editDispensation(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensingStore'])){
                $data['DispensingStore'] = QB::wrapString($data['DispensingStore'], "'");
            }
            if (isset($data['EligibleDispensory'])){
                $data['EligibleDispensory'] = QB::wrapString($data['EligibleDispensory'], "'");
            }
            
            $updateBuilder->table("Pharmacy.EligibleDispensory");
            $updateBuilder->set($data);
            $updateBuilder->where("EligibleDispensoryID = $resourceId");

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
     * Modifies the content of a DispensedItems
     */
    public static function editDispenseditems(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensedQuantity'])){
                $data['DispensedQuantity'] = QB::wrapString($data['DispensedQuantity'], "'");
            }
            
            $updateBuilder->table("Pharmacy.DispensedItems");
            $updateBuilder->set($data);
            $updateBuilder->where("DispensedItemsID = $resourceId");

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

    public static function view(int $resourceId = 0, array $data = [])
    {
        $filters = $data["filtertype"] ?? null;
        try
        {
            $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY DispensationDate) AS RowNum, 
                                a.*, b.StoreName, c.EligibleDispensory as Dispensory, d.PatientUUID, 
                                d.PatientFullName, d.PatientPicture, e.Acknowledged, e.AcknowledgedBy  
                                FROM Pharmacy.Dispensation a 
                                JOIN Pharmacy.Store b ON a.DispensingStore = b.StoreID 
                                JOIN Pharmacy.EligibleDispensory c ON a.EligibleDispensory = c.EligibleDispensoryID 
                                JOIN Patients.Patient d ON a.Patient = d.PatientID
                                JOIN Patients.PatientType f ON d.PatientType = f.PatientTypeID
                                LEFT OUTER JOIN Pharmacy.PrescriptionRequests e ON a.RequestID = e.RequestID";

            if (is_null($filters)){
                $selectBuilder .= " WHERE e.Acknowledged = -1";
            }
            else {
                switch($data["filtertype"]){
                    case "patient":{
                        $selectBuilder .= " WHERE a.Patient = ".$data["query"];
                        break;
                    }
                    case "date":{
                        $sDate = QB::wrapString($data["startdate"], "'");
                        $eDate = QB::wrapString($data["enddate"], "'");
                        $selectBuilder .= " WHERE (CONVERT(date, a.DispensationDate) BETWEEN $sDate AND $eDate OR CONVERT(date, e.RequestDate) BETWEEN $sDate AND $eDate)";
                        break;
                    }
                    case "status":{
                        $selectBuilder .= " WHERE e.Acknowledged = ".$data["query"];
                        break;
                    }
                    case "staff":{
                        $selectBuilder .= " WHERE e.AcknowledgedBy = ".$data["query"];
                        break;
                    }
                    case "patienttype":{
                        $selectBuilder .= " WHERE f.CategoryName = '".$data["query"]."'";
                        break;
                    }
                    case "requestedby":{
                        $selectBuilder .= " WHERE e.RequestedBy = ".$data["query"];
                        break;
                    }
                }

                unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);

                if (isset($data["constantstatus"]) && $data["constantstatus"] != ""){
                   $selectBuilder .= " AND e.Acknowledged = ".$data["constantstatus"];
                   unset($data["constantstatus"]);
                }
            }

            if ($resourceId !== 0){
                $selectBuilder .= " AND a.DispensationID = $resourceId";
            }

            if (isset($data["paginate"])){
                if (isset($data["keywordsearch"])){
                    $keyword = $data["keywordsearch"];
                    $selectBuilder .= " AND (d.PatientFullName LIKE '%$keyword%' OR d.PatientUUID LIKE '%$keyword%')";
                }
                $size = $data["from"] + $data["size"];
                $_query = $selectBuilder;
                $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
            }

            $dispensationResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dispensationResult as $key => $value) {
                $itemQ = "SELECT c.*, b.BillingTypeItemName, a.ItemBrand, a.ItemManufacturer FROM Pharmacy.DispensedItems c INNER JOIN Pharmacy.StoreInventory a ON c.ItemID = a.ItemID INNER JOIN Accounts.BillingTypeItems b ON a.Item = b.BillingTypeItemID WHERE c.DispensationID = ".$value["DispensationID"];
                $items = DBConnectionFactory::getConnection()->query($itemQ)->fetchAll(\PDO::FETCH_ASSOC);

                $dispensationResult[$key]["items"] = $items;
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $dispensationResult = [
                    "data"=>$dispensationResult,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }

            return $dispensationResult;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function retract(int $resourceId, array $data){
        $staff = $data["staff"];
        $comment = $data["comment"] ?? "N/A";
        $dispensation = DBConnectionFactory::getConnection()->query(
            "SELECT * FROM Pharmacy.Dispensation a JOIN Pharmacy.PrescriptionRequests b ON a.RequestID = b.RequestID WHERE a.RequestID = $resourceId AND b.Acknowledged = -1"
        )->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($dispensation[0])){
            $dispensation = $dispensation[0];

            $query = "SELECT DispensedItemsID, ItemID, DispensedQuantity FROM Pharmacy.DispensedItems WHERE DispensationID = ".$dispensation["DispensationID"];
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $items = [];
            $note = "Item Retraction. ID: ".$dispensation["DispensationID"]." Comment: ".$comment;

            foreach ($result as $key => $value) {
                $qA = $value["DispensedQuantity"];
                $items[] = [
                    "item"=>$value["ItemID"],
                    "quantityAdded"=>$qA
                ];
            }

            $restockData = [
                "items"=>$items,
                "comment"=>$note,
                "staffId"=>$staff,
                "storeId"=>$dispensation["DispensingStore"]
            ];

            if (\EmmetBlue\Plugins\Pharmacy\StoreRestockHistory\StoreRestockHistory::create($restockData)){
                $query = "UPDATE Pharmacy.PrescriptionRequests SET Acknowledged = 2, AcknowledgedBy = $staff WHERE RequestID = $resourceId";

                return DBConnectionFactory::getConnection()->exec($query);
            }
        }
        else {
            throw new \Exception("Invalid Retraction Request, Please try again with valid data");
        }
    }

    /**
     * deletes dispensedItems resource
     */

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.DispensedItems")
                ->where("DispensedItemID = $resourceId");
            
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