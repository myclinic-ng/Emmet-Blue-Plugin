<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\BillPaymentRule;

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
 * class BillPaymentRule.
 *
 * BillPaymentRule Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class BillPaymentRule
{
	public static function create(array $data){
		$queryValue = [];
        $ruleType = $data["ruleType"] ?? null;
        $ruleValue = $data["ruleValue"] ?? null;
		foreach ($data["patientTypes"] as $datum){
			$patientType = $datum;
            if ($patientType == null){
                continue;
            }

            foreach ($data["billingTypes"] as $key => $value) {
                if (is_null($value)){
                    continue;
                }
                $queryValue[] = "($patientType, $value, '$ruleType', $ruleValue)";
            }
		}

		$query = "INSERT INTO Accounts.BillPaymentRules(PatientType, BillingTypeItem, RuleType, RuleValue) VALUES ". implode(",", $queryValue);

        // die($query);

		$result = DBConnectionFactory::getConnection()->exec($query);

		return $result;
	}

	public static function edit(int $resourceId, array $data){
		$updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['RuleType'])){
                $data['RuleType'] = QB::wrapString($data['RuleType'], "'");
            }

            $updateBuilder->table("Accounts.BillPaymentRules");
            $updateBuilder->set($data);
            $updateBuilder->where("RuleID = $resourceId");

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

    public static function view(int $resourceId = 0, array $data = []){
        $query = "SELECT a.*, b.PatientTypeName, c.BillingTypeItemName FROM Accounts.BillPaymentRules a JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID JOIN Accounts.BillingTypeItems c ON a.BillingTypeItem = c.BillingTypeItemID JOIN Patients.PatientTypeCategories d ON b.CategoryName = d.CategoryName WHERE c.BillingType = $resourceId";

        if (isset($data["patientcategory"]) && $data["patientcategory"] != ""){
            $query .= " AND d.CategoryID = ".$data["patientcategory"];
        }

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

	public static function delete(int $resourceId){
		$deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.BillPaymentRules")
                ->where("RuleID = $resourceId");
            
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