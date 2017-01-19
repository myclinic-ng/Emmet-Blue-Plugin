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
        $query = "SELECT * FROM Accounts.PaymentRequest a JOIN (SELECT b.DepartmentID FROM Staffs.Staff a JOIN Staffs.StaffDepartment b ON a.StaffID = b.StaffID WHERE StaffUUID = '$staff') b ON a.RequestDepartment = b.DepartmentID";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /*loading all request for Account Department*/
    public static function loadAllRequests($data){
        $query = "SELECT a.*, b.Name, b.GroupID, c.PatientUUID, c.PatientFullName, c.PatientType, d.GroupName, e.CategoryName as PatientCategoryName, e.PatientTypeName FROM Accounts.PaymentRequest a JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID JOIN Patients.PatientType e ON c.PatientType = e.PatientTypeID";

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
        }

        unset($data["filtertype"], $data["query"]);

        if (!empty($data)){
            $string = implode(" OR ", $data);

            $query .= " AND (".$string.")";
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

            foreach ($viewPaymentRequestOperation as $key => $value) {
               if ($value["AttachedInvoice"] != ""){
                    $viewPaymentRequestOperation[$key]["AttachedInvoiceNumber"] = \EmmetBlue\Plugins\AccountsBiller\TransactionMeta\TransactionMeta::view((int) $value["AttachedInvoice"], ["a.BillingTransactionNumber"])[0]["BillingTransactionNumber"];
               }
            }
            
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

    public static function analyseRequests($data){
        $dataToCrunch = self::loadAllRequests($data);
        $analysis = [];
        $totals = [];
        foreach ($dataToCrunch as $key => $value)
        {
            $billingItem = self::loadPaymentRequestBillingItems($value["PaymentRequestID"]);

            $totals[] = $billingItem[0]["totalPrice"];
        }


        $analysis["Net Total"] = array_sum($totals);

        return $analysis;
    } 

    /** load payment billing Items and price for each request*/
    public static function loadPaymentRequestBillingItems($resourceId)
    {
        # code...
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