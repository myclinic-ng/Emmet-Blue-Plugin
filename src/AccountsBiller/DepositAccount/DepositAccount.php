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
class DepositAccount
{
	public static function create(array $data){
        $patient = $data["patient"] ?? null;
        $staff = $data["staff"] ?? null;

		$query = "INSERT INTO Accounts.PatientDepositsAccount(PatientID, CreatedBy) VALUES ($patient, $staff)";

		$result = DBConnectionFactory::getConnection()->exec($query);

		return $result;
	}

    public static function accountExists(int $resourceId){
        $query = "SELECT * FROM Accounts.PatientDepositsAccount WHERE PatientID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return (count($result) == 1);
    }

    public static function viewAccountInfo(int $resourceId) {
        $query = "SELECT * FROM Accounts.PatientDepositsAccount WHERE PatientID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$data){
            $result[$key]["StaffName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["CreatedBy"])["StaffFullName"];
        }

        return $result[0] ?? [];
    }

    public static function newTransaction(array $data){
        $patient = $data["patient"] ?? null;
        $staff = $data["staff"] ?? null;
        $amount = $data["amount"] ?? null;
        $comment = $data["comment"] ?? null;

        if (!self::accountExists((int) $patient)){
            self::create(["patient"=>$patient, "staff"=>$staff]);
        }

        $accountId = self::viewAccountInfo((int) $patient)["AccountID"];

        $query = "INSERT INTO Accounts.PatientDepositsAccountTransactions(AccountID, TransactionAmount, TransactionComment, StaffID) VALUES($accountId, '$amount', '$comment', $staff)";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;        
    }

    public static function _viewTransactions(int $resourceId){
        $query = "SELECT a.* FROM Accounts.PatientDepositsAccountTransactions a INNER JOIN Accounts.PatientDepositsAccount b ON a.AccountID = b.AccountID WHERE b.PatientID = $resourceId ORDER BY a.TransactionID DESC";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$data){
            $result[$key]["StaffName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["StaffID"])["StaffFullName"];
        }

        return $result;
    }

    public static function viewTransactions(array $data){
        $query = "
            SELECT 
                ROW_NUMBER() OVER (ORDER BY a.TransactionDate DESC) AS RowNum,
                a.*,
                c.PatientFullName,
                c.PatientPicture,
                d.PatientTypeName,
                d.CategoryName
            FROM Accounts.PatientDepositsAccountTransactions a
            INNER JOIN Accounts.PatientDepositsAccount b ON a.AccountID = b.AccountID
            INNER JOIN Patients.Patient c ON b.PatientID = c.PatientID 
            INNER JOIN Patients.PatientType d on c.PatientType = d.PatientTypeID
        ";

        switch($data["filtertype"]){
            case "patient":{
                $query .= " WHERE b.PatientID = '".$data["query"]."'";
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
                $query .= " AND (c.PatientFullName LIKE '%$keyword%' OR d.PatientTypeName LIKE '%$keyword%' OR d.CategoryName LIKE '%$keyword%')";
            }

            $_query = $query;
            $size = $data["size"] + $data["from"];
            $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$data){
                $result[$key]["StaffName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["StaffID"])["StaffFullName"];
            }

            $_result = [];
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
}