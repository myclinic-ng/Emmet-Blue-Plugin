<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\TransactionMeta;

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
 * class BillingTransactionMeta.
 *
 * BillingTransactionMeta Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class TransactionMeta
{
    private static function generateTransactionNumber()
    {
        $string = date(DATE_RFC2822);
        $date = new \DateTime($string);

        return $date->format('YmdHis');  
    }

    public static function create(array $data)
    {
        $patient = $data['patient'] ?? null;
        $type = $data['type'] ?? null;
        $createdBy = $data['createdBy'] ?? null;
        $items = $data['items'] ?? null;
        $status = $data['status'] ?? null;
        $amount = $data['amount'] ?? null;
        $transactionNumber = self::generateTransactionNumber();

        try
        {
            $result = DBQueryFactory::insert('Accounts.BillingTransactionMeta', [
                'BillingTransactionNumber'=>QB::wrapString($transactionNumber, "'"),
                'PatientID'=>$patient,
                'BillingType'=>QB::wrapString((string)$type, "'"),
                'CreatedByUUID'=>(is_null($createdBy)) ? "NULL" : QB::wrapString($createdBy, "'"),
                'DateCreated'=>'GETDATE()',
                'BilledAmountTotal'=>(is_null($amount)) ? "NULL" : QB::wrapString((string)$amount, "'"),
                'BillingTransactionStatus'=>(is_null($status)) ? "NULL" : QB::wrapString((string)$status, "'")
            ]);
            
            $id = $result['lastInsertId']; 

            $itemNames = [];
            foreach ($items as $datum){
                $itemNames[] = "($id, ".QB::wrapString((string)$datum['itemName'], "'").", ".QB::wrapString((string)$datum['itemQuantity'], "'").", ".QB::wrapString((string)$datum['itemPrice'], "'").")";
            }

            $query = "INSERT INTO Accounts.BillingTransactionItems (BillingTransactionMetaID, BillingTransactionItemName, BillingTransactionItemQuantity, BillingTransactionItemPrice) VALUES ".implode(", ", $itemNames);

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

            $updateBuilder->table("Accounts.BillingTransactionMeta");
            $updateBuilder->set($data);
            $updateBuilder->where("BillingTransactionMetaID = $resourceId");

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
            
            $selectBuilder->from("Accounts.BillingTransactionMeta a");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingTransactionMetaID = $resourceId");
            }


            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

           if (empty($data)){
                foreach ($result as $key=>$metaItem)
                {
                    $id = $metaItem["BillingTransactionMetaID"];
                    $patient = $metaItem["PatientID"];
                    $query = "SELECT * FROM Accounts.BillingTransactionItems WHERE BillingTransactionMetaID = $id";
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
                    foreach ($queryResult2 as $key => $value){
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

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.BillingTransactionMeta")
                ->where("BillingTransactionMetaID = $resourceId");
            
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