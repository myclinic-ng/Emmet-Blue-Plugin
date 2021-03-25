<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\PaymentRequest;

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
 * class BillingPaymentRequest.
 *
 * BillingPaymentRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PaymentRequest
{
    private static function generateRequestNumber()
    {
        $string = date(DATE_RFC2822);
        $date = new \DateTime($string);

        return $date->format('YmdHis');  
    }

    public static function create(array $data)
    {
        $patient = $data['patient'] ?? 'null';
        $requestBy = $data['requestBy'] ?? null;
        $items = $data['items'] ?? null;
        $requestNumber = self::generateRequestNumber();

        $query = "SELECT b.DepartmentID from Staffs.Staff a JOIN Staffs.StaffDepartment b ON a.StaffID = b.StaffID WHERE a.StaffUUID = '$requestBy'";
        $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$query)
            )->fetchAll(\PDO::FETCH_ASSOC);
        $requestDepartment = $result[0]["DepartmentID"];

        $patientCategory = DBConnectionFactory::getConnection()->query(
            "SELECT c.CategoryID FROM Patients.Patient a INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID 
            INNER JOIN Patients.PatientTypeCategories c ON b.CategoryName = c.CategoryName
            WHERE a.PatientID=$patient"
        )->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($patientCategory[0])){
            $patientCategory = $patientCategory[0]["CategoryID"];
        }
        else {
            $patientCategory = 0;
        }

        try
        {
            $result = DBQueryFactory::insert('Accounts.PaymentRequest', [
                'PaymentRequestUUID'=>QB::wrapString($requestNumber, "'"),
                'RequestPatientID'=>$patient,
                'RequestBy'=>QB::wrapString((string)$requestBy, "'"),
                'RequestDepartment'=>$requestDepartment
            ]);
            
            $id = $result['lastInsertId'];

            $itemNames = [];
            foreach ($items as $datum){
                $itemNames[] = "($id, ".$datum['item'].", ".$datum['quantity'].")";
            }

            $appends = \EmmetBlue\Plugins\AccountsBiller\BillPaymentRule::viewAppendItems((int) $patientCategory);

            foreach ($appends as $value){
                $itemNames[] = "($id, ".$value['BillingTypeItem'].", 1)";
            }

            $query = "INSERT INTO Accounts.PaymentRequestItems (RequestID, ItemID, ItemQuantity) VALUES ".implode(", ", $itemNames);

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
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['BillingTransactionStatus'])){
                $data['BillingTransactionStatus'] = QB::wrapString($data['BillingTransactionStatus'], "'");
            }
            if (isset($data['BilledAmountTotal'])){
                $data['BilledAmountTotal'] = QB::wrapString($data['BilledAmountTotal'], "'");
            }
            if (isset($data['BillingType'])){
                $data['BillingType'] = QB::wrapString($data['BillingType'], "'");
            }

            $updateBuilder->table("Accounts.PaymentRequest");
            $updateBuilder->set($data);
            $updateBuilder->where("PaymentRequestID = $resourceId");

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
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function viewUnprocessed(int $resourceId = 0, array $data = [])
    {
        $query = "SELECT a.*, b.Name, b.GroupID, c.PatientUUID, c.PatientFullName, c.PatientType, d.GroupName, e.CategoryName as PatientCategoryName, e.PatientTypeName FROM Accounts.PaymentRequest a JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID JOIN Patients.PatientType e ON c.PatientType = e.PatientTypeID WHERE a.RequestFulfillmentStatus NOT IN (1)";
        try
        {
            $viewPaymentRequestOperation = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Accounts',
                'PaymentRequest',
                (string)$query
            );
            
            return $viewPaymentRequestOperation;  
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

    public static function viewProcessed(int $resourceId = 0, array $data = [])
    {
        $query = "SELECT a.*, b.Name, b.GroupID, c.PatientUUID, c.PatientFullName, c.PatientType, d.GroupName, e.CategoryName as PatientCategoryName, e.PatientTypeName FROM Accounts.PaymentRequest a JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID JOIN Patients.PatientType e ON c.PatientType = e.PatientTypeID WHERE a.RequestFulfillmentStatus NOT IN (0, -1)";
        try
        {
            $viewPaymentRequestOperation = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Accounts',
                'PaymentRequest',
                (string)$query
            );
            
            return $viewPaymentRequestOperation;  
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

    public static function getStatus(int $resourceId = 0, array $data = [])
    {
        $requestNumber = $data["requestNumber"];
        $query = "SELECT RequestFulfillmentStatus AS Status FROM Accounts.PaymentRequest WHERE PaymentRequestUUID = '$requestNumber'";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public static function loadRequests(int $resourceId = 0, array $data = [])
    {
        $staff = $data["staff"];
        $query = "SELECT DISTINCT * FROM Accounts.PaymentRequest a JOIN (SELECT b.DepartmentID FROM Staffs.Staff a JOIN Staffs.StaffDepartment b ON a.StaffID = b.StaffID WHERE StaffUUID = '$staff') b ON a.RequestDepartment = b.DepartmentID";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public static function loadAllRequests($data){
        $query = 
                "
                    SELECT 
                        ROW_NUMBER() OVER (ORDER BY a.RequestDate DESC) AS RowNum,
                        a.*, 
                        b.Name, 
                        b.GroupID, 
                        c.PatientUUID, 
                        c.PatientFullName, 
                        c.PatientType,
                        c.PatientPicture, 
                        d.GroupName, 
                        e.CategoryName AS PatientCategoryName, 
                        e.PatientTypeName, 
                        f.BillingAmountPaid,
                        f.BillingPaymentMethod  
                    FROM Accounts.PaymentRequest a 
                    JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID 
                    JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID 
                    JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID 
                    JOIN Patients.PatientType e ON c.PatientType = e.PatientTypeID 
                    LEFT OUTER JOIN Accounts.BillingTransaction f ON f.BillingTransactionMetaID = a.AttachedInvoice
                ";

        switch($data["filtertype"]){
            case "patient":{
                $query .= " WHERE c.PatientUUID = '".$data["query"]."'";
                break;
            }
            case "date":{
                $sDate = QB::wrapString($data["startdate"], "'");
                $eDate = QB::wrapString($data["enddate"], "'");
                $query .= " WHERE CONVERT(date, a.RequestDate) BETWEEN $sDate AND $eDate";
                break;
            }
            case "department":{
                $query .= " WHERE a.RequestDepartment = ".$data["query"];
                break;
            }
            case "status":{
                $query .= " WHERE a.RequestFulfillmentStatus = ".$data["query"];
                break;
            }
            case "staff":{
                $query .= " WHERE f.StaffID = ".$data["query"];
                break;
            }
            case "patienttype":{
                $query .= " WHERE e.CategoryName = '".$data["query"]."'";
                break;
            }
            case "paymentmethod":{
                $query .= " WHERE f.BillingPaymentMethod = '".$data["query"]."'";
                break;
            }
            case "invoice":{
                $query .= " WHERE a.AttachedInvoice = ".$data["query"];
                break;
            }
        }

        unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);

        if (isset($data["constantstatus"]) && $data["constantstatus"] != ""){
           $query .= " AND a.RequestFulfillmentStatus = ".$data["constantstatus"];
           unset($data["constantstatus"]);
        }

        if (!empty($data)){
            $_filters = ["status"=>[], "department"=>[], "date"=>[]];
            if (isset($data["_status"]) && $data["_status"] != ""){
                $data["_status"] = explode(",", str_replace(" ", "", $data["_status"]));

                foreach ($data["_status"] as $value) {
                    $_filters["status"][] = "a.RequestFulfillmentStatus=".$value;
                }
            }

            if (isset($data["_date"]) && $data["_date"] != ""){
                $data["_date"] = explode(",", str_replace(" ", "", $data["_date"]));

                foreach ($data["_date"] as $value) {
                    $_filters["date"][] = "CAST(a.RequestDate AS DATE) ='".$value."'";
                }
            }

            if (isset($data["_department"]) && $data["_department"] != ""){
                $data["_department"] = explode(",", str_replace(" ", "", $data["_department"]));

                foreach ($data["_department"] as $value) {
                    $_filters["department"][] = "a.RequestDepartment =".$value;
                }
            }

            if (isset($data["_patienttype"]) && $data["_patienttype"] != ""){
                $data["_patienttype"] = explode(",", str_replace(" ", "", $data["_patienttype"]));

                foreach ($data["_patienttype"] as $value) {
                    $_filters["patienttype"][] = "e.PatientTypeID =".$value;
                }
            }

            if (isset($data["_paymentmethod"]) && $data["_paymentmethod"] != ""){
                $data["_paymentmethod"] = explode(",", str_replace(" ", "", $data["_paymentmethod"]));

                foreach ($data["_paymentmethod"] as $value) {
                    $_filters["paymentmethod"][] = "f.BillingPaymentMethod ='".$value."'";
                }
            }

            $string[] = empty($_filters["status"]) ? "1=1" : "(".implode(" OR ", $_filters["status"]).")";
            $string[] = empty($_filters["date"]) ? "1=1" : "(".implode(" OR ", $_filters["date"]).")";
            $string[] = empty($_filters["department"]) ? "1=1" : "(".implode(" OR ", $_filters["department"]).")";
            $string[] = empty($_filters["patienttype"]) ? "1=1" : "(".implode(" OR ", $_filters["patienttype"]).")";
            $string[] = empty($_filters["paymentmethod"]) ? "1=1" : "(".implode(" OR ", $_filters["paymentmethod"]).")";

            $string = implode(" AND ", $string);

            if ($string != ""){
                $query .= " AND (".$string.")";
            }
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $query .= " AND (c.PatientFullName LIKE '%$keyword%' OR c.PatientType LIKE '%$keyword%' OR b.Name LIKE '%$keyword%')";
            }

            $_query = $query;
            $size = $data["size"] + $data["from"];
            $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }
        // die($query);
        try
        {
            $viewPaymentRequestOperation = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Accounts',
                'PaymentRequest',
                (string)$query
            );

            $result = [];
            foreach ($viewPaymentRequestOperation as $value) {
                $key = $value["PaymentRequestID"];
                if (!isset($result[$key])){
                    $result[$key] = $value; 
                }
                else {
                    $result[$key]["BillingAmountPaid"] += $value["BillingAmountPaid"];
                }

                if ($value["AttachedInvoice"] != ""){
                    $result[$key]["AttachedInvoiceNumber"] = \EmmetBlue\Plugins\AccountsBiller\TransactionMeta\TransactionMeta::getTransactionNumber((int) $value["AttachedInvoice"])["BillingTransactionNumber"];
                }

                $result[$key]["RequestByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullNameFromUUID(["uuid"=>$result[$key]["RequestBy"]])["StaffFullName"];
            }
                
            $_result = [];
            foreach ($result as $value){
                $_result[] = $value;
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $_result = [
                    "data"=>$_result,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }

            return $_result;  
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

    public static function analyseRequests($data){
        $dataToCrunch = self::loadAllRequests($data);
        $analysis = ["summary"=>[], "breakdown"=>[], "paymentMethodBreakdown"=>[]];
        $finalData = [
            "totals"=>[],
            "totalExPrice"=>[],
            "totalMoReceived"=>[]
        ];
        $receivedMoneyBreakdown = [];
        foreach ($dataToCrunch as $key => $data)
        {
            $billingItem = self::loadPaymentRequestBillingItems($data["PaymentRequestID"]);
            $sums = [
                "sum"=>0,
                "sumExPrice"=>0
            ];
            foreach ($billingItem as $value) {
                if (!isset($analysis["breakdown"][$value["ItemID"]])){
                    $analysis["breakdown"][$value["ItemID"]] = ["name"=>$value["BillingTypeItemName"],  "value"=>0, "qty"=>0, "expectedPrice"=>0];
                }

                $analysis["breakdown"][$value["ItemID"]]["value"] += $value["totalPrice"];
                $payRuleData = [
                    "amounts"=>[
                        $value["ItemID"]=>$value["totalPrice"]
                    ],
                    "items"=>[$value["ItemID"]]
                ];
                $exPrice = \EmmetBlue\Plugins\AccountsBiller\GetItemPrice::applyPaymentRule((int) $value["PatientID"], $payRuleData)["_meta"]["amount"];
                $analysis["breakdown"][$value["ItemID"]]["expectedPrice"] += $exPrice;
                $analysis["breakdown"][$value["ItemID"]]["balPrice"] = $analysis["breakdown"][$value["ItemID"]]["value"] - $analysis["breakdown"][$value["ItemID"]]["expectedPrice"];
                $analysis["breakdown"][$value["ItemID"]]["qty"] += 1;
                $sums["sum"] += $value["totalPrice"];
                $sums["sumExPrice"] += $exPrice;
            }

            //$analysis["breakdown"][] = $billingItem;
            $finalData["totals"][] = $sums["sum"];
            $finalData["totalExPrice"][] = $sums["sumExPrice"];
            $finalData["totalMoReceived"][] = $data["BillingAmountPaid"];

            if (!isset($receivedMoneyBreakdown[$data["BillingPaymentMethod"]])){
                $receivedMoneyBreakdown[$value["BillingPaymentMethod"]] = 0;
            }

            $receivedMoneyBreakdown[$value["BillingPaymentMethod"]] += (int)$value["BillingAmountPaid"];
        }


        $analysis["summary"]["Net Total"] = ["value"=>array_sum($finalData["totals"]), "type"=>"netTotal"];
        $analysis["summary"]["Total With Payment Rules Applied"] = ["value"=>array_sum($finalData["totalExPrice"]), "type"=>"netTotal"];
        $analysis["summary"]["Total Money Received"] = ["value"=>array_sum($finalData["totalMoReceived"]), "type"=>"netTotal"];
        $analysis["summary"]["Total Money Unaccounted For"] = ["value"=>
            ($analysis["summary"]["Total Money Received"]["value"] - $analysis["summary"]["Total With Payment Rules Applied"]["value"]), 
            "type"=>"netTotal"
        ];
        $analysis["summary"]["Total Money Accrued By HMOS/Companies"] = ["value"=>
            ($analysis["summary"]["Net Total"]["value"] - $analysis["summary"]["Total With Payment Rules Applied"]["value"]), 
            "type"=>"netTotal"
        ];

        $analysis["paymentMethodBreakdown"] = $receivedMoneyBreakdown;
        // $analysis["Net Total Accumulated Over"] = ["value"=>"10 days", "type"=>""];

        return $analysis;
    } 

    /** load payment billing Items and price for each request*/
    public static function loadPaymentRequestBillingItems($resourceId)
    {
        $paymentRequestId = $resourceId;
        $query = "SELECT a.*, b.BillingTypeItemName, c.RequestPatientID as PatientID FROM Accounts.PaymentRequestItems a JOIN Accounts.BillingTypeItems b On a.ItemID = b.BillingTypeItemID JOIN Accounts.PaymentRequest c ON a.RequestID = c.PaymentRequestID WHERE a.RequestID = $paymentRequestId" ;
            
        $res = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $key=>$result){
            $price = \EmmetBlue\Plugins\AccountsBiller\GetItemPrice::calculate((int)$result["PatientID"], [
                "item"=>(int)$result["ItemID"],
                "quantity"=>(int)$result["ItemQuantity"]
            ]);
            
            $res[$key] = array_merge($res[$key], $price);
        }

        return $res;
    }

    /*make payment for each request*/
    public static function makePayment($resourceId, $data){
        $status['RequestFulfillmentStatus'] = $data['status'];
        $status['RequestFulfilledBy'] = QB::wrapString($data['staffUUID'], "'");
        $status['RequestFulFilledDate'] = QB::wrapString($data['fulfilledDate'], "'");
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Accounts.PaymentRequest");
            $updateBuilder->set($status);
            $updateBuilder->where("PaymentRequestID = $resourceId");

            $bodyResult = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.PaymentRequest")
                ->where("PaymentRequestID = $resourceId");
            
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