<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\PaymentReceipt;

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
 * class PaymentReceipt.
 *
 * PaymentReceipt Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PaymentReceipt
{
    public static function create(array $data)
    {
        $patientId = $data['patient'] ?? null;
        $report = $data['receipt'] ?? null;
        $transaction = $data['transaction'] ?? null;
        $printedBy = $data['staff'] ?? null;

        try
        {
            $trans = \EmmetBlue\Plugins\AccountsBiller\Transaction\Transaction::view((int)$transaction)[0];
            $strs = [];
            if (isset($trans["BillingTransactionCustomerName"])){
                $strs[] = "Received from: ".$trans["BillingTransactionCustomerName"];
            }
            if (isset($trans["BillingAmountPaid"])){
                $strs[] = "Amount: ".$trans["BillingAmountPaid"];
            }
            if (isset($trans["BillingPaymentMethod"])){
                $strs[] = "Method: ".$trans["BillingPaymentMethod"];
            }

            $repoData = [
                "patient"=>$patientId,
                "name"=>"Payment Receipt. #$transaction",
                "type"=>"payment_receipt",
                "creator"=>$printedBy,
                "description"=>implode(". ", $strs)
            ];

            $repoId = \EmmetBlue\Plugins\Patients\PatientRepository\PatientRepository::create($repoData)["lastInsertId"];

            $repoItemData = [
                "repository"=>$repoId,
                "name"=>"Receipt No. #$transaction",
                "category"=>"file",
                "file"=>serialize($report),
                "file_ext"=>"img",
                "creator"=>$printedBy
            ];

            \EmmetBlue\Plugins\Patients\RepositoryItem\RepositoryItem::create($repoItemData);

            $query = "INSERT INTO Accounts.PaymentReceipts (PatientID, AssociatedTransaction, RepositoryID, PrintedBy) VALUES ($patientId, $transaction, $repoId, $printedBy)";

            return DBConnectionFactory::getConnection()->exec($query);
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Receipt Repository not updated), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function edit(int $resourceId, array $data)
    {

    }

    public static function view(int $resourceId = 0, array $data = [])
    {

    }

    public static function delete(int $resourceId)
    {

    }
}