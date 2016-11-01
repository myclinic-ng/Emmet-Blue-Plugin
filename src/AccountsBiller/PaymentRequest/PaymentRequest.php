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
        $patient = $data['patient'] ?? null;
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
                'RequestPatientID'=>QB::wrapString((string)$patient, "'"),
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

            $updateBuilder->table("Accounts.BillingPaymentRequest");
            $updateBuilder->set($data);
            $updateBuilder->where("BillingPaymentRequestID = $resourceId");

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
            
            $selectBuilder->from("Accounts.BillingPaymentRequest a");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingPaymentRequestID = $resourceId");
            }


            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

           if (empty($data)){
                foreach ($result as $key=>$metaItem)
                {
                    $id = $metaItem["BillingPaymentRequestID"];
                    $patient = $metaItem["PatientID"];
                    $query = "SELECT * FROM Accounts.BillingTransactionItems WHERE BillingPaymentRequestID = $id";
                    $query2 = "SELECT FieldTitle, FieldValue FROM Patients.PatientRecordsFieldValue WHERE PatientID=$patient";

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->query($query)
                    )->fetchAll(\PDO::FETCH_ASSOC);

                    $queryResult2 = (
                        DBConnectionFactory::getConnection()
                        ->query($query2)
                    )->fetchAll(\PDO::FETCH_ASSOC);

                    $name = "";
                    foreach ($queryResult2 as $value){
                        if ($value["FieldTitle"] == 'Title'){
                            $name .= $value["FieldValue"];
                        }
                        else if ($value["FieldTitle"] == 'FirstName'){
                            $name .= " ".$value["FieldValue"];
                        }
                        else if ($value["FieldTitle"] == 'LastName'){
                            $name .= " ".$value["FieldValue"];
                        }
                    }

                    $result[$key]["BillingTransactionItems"] = $queryResult;
                    $result[$key]["PatientName"] = $name;
                }
           }

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
    public static function loadAllRequests(){
       $query = "SELECT a.*, b.Name, b.GroupID, c.PatientUUID, c.PatientFullName, c.PatientType, d.GroupName FROM Accounts.PaymentRequest a JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID";
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

    /** load payment billing Items and price for each request*/
    public static function loadPaymentRequestBillingItems($resourceId)
    {
        # code...
        $paymentRequestId = $resourceId;
        $query = "SELECT a.*, b.*, c.BillingTypeItemPrice FROM Accounts.PaymentRequestItems a JOIN Accounts.BillingTypeItems b ON b.BillingTypeItemID = a.ItemID JOIN Accounts.BillingTypeItemsPrices c ON c.BillingTypeItemsPricesID = b.BillingTypeItemID WHERE a.RequestID = $paymentRequestId" ;
            
        $result = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
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