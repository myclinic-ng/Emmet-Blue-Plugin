<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\Transaction;

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
 * class BillingTransaction.
 *
 * BillingTransaction Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Transaction
{
    public static function create(array $data)
    {
        $metaId = $data['metaId'] ?? null;
        $customerId = $data['customerId'] ?? null;
        $paymentMethod = $data['paymentMethod'] ?? null;
        $amountPaid = $data['amountPaid'] ?? null;
        $amountBalance = $data['amountBalance'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Accounts.BillingTransaction', [
                'BillingTransactionMetaID'=>$metaId,
                'BillingTransactionDate'=>'GETDATE()',
                'BillingTransactionCustomerID'=>(is_null($customerId)) ? "NULL" : QB::wrapString($customerId, "'"),
                'BillingPaymentMethod'=>(is_null($paymentMethod)) ? "NULL" : QB::wrapString($paymentMethod, "'"),
                'BillingAmountPaid'=>(is_null($amountPaid)) ? "NULL" : QB::wrapString($amountPaid, "'"),
                'BillingAmountBalance'=>(is_null($amountBalance)) ? "NULL" : QB::wrapString($amountBalance, "'")
            ]);

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
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['BillingTransactionCustomerID'])){
                $data['BillingTransactionCustomerID'] = QB::wrapString($data['BillingTransactionCustomerID'], "'");
            }
            if (isset($data['BillingPaymentMethod'])){
                $data['BillingPaymentMethod'] = QB::wrapString($data['BillingPaymentMethod'], "'");
            }
            if (isset($data['BillingAmountPaid'])){
                $data['BillingAmountPaid'] = QB::wrapString($data['BillingAmountPaid'], "'");
            }
            if (isset($data['BillingAmountBalance'])){
                $data['BillingAmountBalance'] = QB::wrapString($data['BillingAmountBalance'], "'");
            }

            $updateBuilder->table("Accounts.BillingTransaction");
            $updateBuilder->set($data);
            $updateBuilder->where("BillingTransactionID = $resourceId");

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
            
            $selectBuilder->from("Accounts.BillingTransaction a");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingTransactionID = $resourceId");
            }

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

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
                ->from("Accounts.BillingTransaction")
                ->where("BillingTransactionID = $resourceId");
            
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