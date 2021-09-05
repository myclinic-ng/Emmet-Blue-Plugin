<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\DepositAccount;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DatabaseQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class DepositAccount.
 *
 * DepositAccount Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/20/2017 7:49 PM
 */
class PatientTypeDepositAccount
{
    public static function create(array $data){
        $patientType = $data["patientType"] ?? null;
        $staff = $data["staff"] ?? null;

        $query = "INSERT INTO Accounts.PatientTypeDepositsAccount(PatientTypeID, CreatedBy) VALUES ($patientType, $staff)";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static function accountExists(int $resourceId){
        $query = "SELECT * FROM Accounts.PatientTypeDepositsAccount WHERE PatientTypeID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return (count($result) == 1);
    }

    public static function viewAccountInfo(int $resourceId) {
        $query = "SELECT * FROM Accounts.PatientTypeDepositsAccount WHERE PatientTypeID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$data){
            $result[$key]["StaffName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["CreatedBy"])["StaffFullName"];
        }

        return $result[0] ?? [];
    }

    public static function newTransaction(array $data){
        $patientType = $data["patientType"] ?? null;
        $staff = $data["staff"] ?? null;
        $amount = $data["amount"] ?? null;
        $comment = $data["comment"] ?? null;

        if (!self::accountExists((int) $patientType)){
            self::create(["patientType"=>$patientType, "staff"=>$staff]);
        }

        $accountId = self::viewAccountInfo((int) $patientType)["AccountID"];

        $query = "INSERT INTO Accounts.PatientTypeDepositsAccountTransactions(AccountID, TransactionAmount, TransactionComment, StaffID) VALUES($accountId, '$amount', '$comment', $staff)";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;        
    }

    public static function viewTransactions(array $data){
        $query = "
            SELECT 
                ROW_NUMBER() OVER (ORDER BY a.TransactionDate DESC) AS RowNum,
                a.*,
                d.PatientTypeName,
                d.CategoryName
            FROM Accounts.PatientTypeDepositsAccountTransactions a
            INNER JOIN Accounts.PatientTypeDepositsAccount b ON a.AccountID = b.AccountID
            INNER JOIN Patients.PatientTypeID d on d.PatientType = b.PatientTypeID
        ";

        switch($data["filtertype"]){
            case "patientType":{
                $query .= " WHERE c.PatientTypeID = '".$data["query"]."'";
                break;
            }
            case "date":{
                $sDate = QB::wrapString($data["startdate"], "'");
                $eDate = QB::wrapString($data["enddate"], "'");
                $query .= " WHERE (CONVERT(date, a.TransactionDate) BETWEEN $sDate AND $eDate)";
                break;
            }
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $query .= " AND (d.PatientTypeName LIKE '%$keyword%' OR d.CategoryName LIKE '%$keyword%')";
            }

            $_query = $query;
            $size = $data["size"] + $data["from"];
            $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY TransactionDate DESC";
        }

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$_data){
                $result[$key]["StaffName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $_data["StaffID"])["StaffFullName"];
            }

            $_result = $result;
            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                $_result = [
                    "data"=>$result,
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

    public static function getCreditTransactionsTotal(array $data){
        $sDate = QB::wrapString($data["startdate"], "'");
        $eDate = QB::wrapString($data["enddate"], "'");
        $query = "
            SELECT SUM(TransactionAmount) as totalCredit FROM Accounts.PatientTypeDepositsAccountTransactions WHERE TransactionAmount > 0 AND (CONVERT(date, TransactionDate) BETWEEN $sDate AND $eDate);
        ";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result[0] ?? ["totalCredit"=>0];
    }
}