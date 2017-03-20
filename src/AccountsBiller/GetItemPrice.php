<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class GetItemPrice.
 *
 * GetItemPrice Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class GetItemPrice
{
	/**
	 * Multi Level (or multi-tier) Price Determination.
	 *
	 * Highest Level: ITEM'S SPECIFIC PRICE
	 * Intermediate Level: ITEM'S CATEGORY's PRICE. f.ex: Multishield patient gets the price for all public hmo incase it's own price structure is not explicitly registered
	 * Lowest Level: ITEM ASSUMES THE GENERAL PRICE. 
	 */
	public static function calculate(int $patient, array $data){
		$item = $data["item"] ?? null;
		$quantity = $data["quantity"] ?? null;

		$query = "SELECT PatientType FROM Patients.Patient WHERE PatientID = $patient";
		$patientType = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC)[0]["PatientType"];
		if (is_null($patientType)){
			throw new \Exception("Patient's Category has no associated price structure for the specified item");
		}

		$query = "SELECT * FROM Accounts.BillingTypeItemsPrices WHERE BillingTypeItem = $item AND PatientType = $patientType";
		$result = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC);
		if (empty($result)){
			$q = "SELECT BillingTypeItemPrice FROM Accounts.PatientTypeCategoriesDefaultPrices WHERE CategoryID = (SELECT b.CategoryID FROM Patients.PatientType a INNER JOIN Patients.PatientTypeCategories b On a.CategoryName = b.CategoryName WHERE a.PatientTypeID = $patientType) AND BillingTypeItem = $item";
			$price = DBConnectionFactory::getConnection()->query($q)->fetchall(\PDO::FETCH_ASSOC);

			if (isset($price[0]["BillingTypeItemPrice"])){
				$result[0]["BillingTypeItemPrice"] = $price[0]["BillingTypeItemPrice"];
			}
			else {
				$q = "SELECT BillingTypeItemPrice FROM Accounts.GeneralDefaultPrices WHERE BillingTypeItem = $item";
				$price = DBConnectionFactory::getConnection()->query($q)->fetchall(\PDO::FETCH_ASSOC);
				if (isset($price[0]["BillingTypeItemPrice"])){
					$result[0]["BillingTypeItemPrice"] = $price[0]["BillingTypeItemPrice"];
				}
				else {
					$result[0]["BillingTypeItemPrice"] = 0;
				}
			}
		}

		$result = $result[0];
		$price = $result["BillingTypeItemPrice"];
		if (isset($result["IntervalBased"]) && $result["IntervalBased"]){
			$query = "SELECT * FROM Accounts.BillingTypeItemsInterval WHERE BillingTypeItemID = $item";
			$results = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC);
			if (empty($result)){
				throw new \Exception("Interval definition not found.");
			}
			$totalPrice = 0;
			foreach ($results as $_result) {
				$interval = $_result["Interval"];
				$type = $_result["IntervalIncrementType"];
				$increment = $_result["IntervalIncrement"];

				$totalPrice += self::calculateIntervalBasedPrice((int)$price, (int)$quantity, (int)$interval, $type, (int)$increment);
				$quantity -= $interval;
				if ($quantity <= 0){
					break;
				}
			}
		}
		else {
			$totalPrice = self::calculateNonIntervalBasedPrice((int)$price, (int)$quantity);
		}

		return (isset($result["RateIdentifier"]) && !is_null($result["RateIdentifier"])) ? ["rateIdentifier"=>strtolower($result["RateIdentifier"]), "totalPrice"=>$totalPrice] : ["totalPrice"=>$totalPrice];
	}

	private static function calculateNonIntervalBasedPrice(int $price, int $quantity){
		return $price * $quantity;
	}

	private static function calculateIntervalBasedPrice(int $price, int $quantity, int $interval, string $type, int $increment){
		$totalPrice = 0;
		switch (strtolower($type)){
			case "additive":{
				if ($quantity > $interval){
					$dividend = round($quantity / $interval);
					$modulus = $quantity % $interval;
					for ($i = 0; $i < $dividend; $i++){
						$totalPrice += ($price + $increment) * $interval;
					}
					if ($modulus > 0){
						$totalPrice += ($price + $increment) * $modulus;
					}
				}
				else {
					$totalPrice += ($price + $increment) * $quantity;
				}
				break;
			}

			case "multiplicative":{
				if ($quantity > $interval){
					$dividend = round($quantity / $interval);
					$modulus = $quantity % $interval;
					for($i = 0; $i < $dividend; $i++){
						$totalPrice += ($price * $increment) * $interval;
					}
					if ($modulus > 0){
						$totalPrice += ($price * $increment) * $modulus;
					}
				}
				else {
					$totalPrice += ($price * $increment) * $quantity;
				}
				break;
			}

			case "custom":{
				$counter = $quantity - $interval;
				if ($counter > 0){
					$totalPrice += ($price + $increment) * $interval;
				}
				else {
					$totalPrice += ($price + $increment) * $quantity;
				}
				break;
			}
		}

		return $totalPrice;
	}

	public static function applyPaymentRule(int $patient, array $data){
		$amount = $data["amounts"];
		$items = $data["items"];

		$itemsIm = implode(" OR BillingTypeItem=", $items);

		$query = "SELECT PatientType FROM Patients.Patient WHERE PatientID = $patient";
		$patientType = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC)[0]["PatientType"];
		if (is_null($patientType)){
			throw new \Exception("Patient's Category has no associated price structure for the specified item");
		}

		$query = "SELECT * FROM Accounts.BillPaymentRules WHERE PatientType = $patientType AND (BillingTypeItem = $itemsIm)";
		$rules = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC);

		// die($query);

		$result = [];
		foreach ($items as $key => $value) {
			foreach ($rules as $rule) {
				if ($rule["BillingTypeItem"] == $value){
					switch($rule["RuleType"])
					{
						case "%":
						{
							$ruleValue = (int)$rule["RuleValue"];
							$amnt = $ruleValue * $amount[$value] / 100;
							$bal = $amount[$value] - $amnt;

							$result[$value] = [
								"amount"=>$amnt,
								"balance"=>$bal
							];

							break;
						}
						case "+":
						{
							$ruleValue = (int)$rule["RuleValue"];
							$amnt = $ruleValue + $amount[$value];
							$bal = $amount[$value] - $amnt;

							$result[$value] = [
								"amount"=>$amnt,
								"balance"=>$bal
							];

							break;
						}
						case "*":
						{
							$ruleValue = (int)$rule["RuleValue"];
							$amnt = $ruleValue * $amount[$value];
							$bal = $amount[$value] - $amnt;

							$result[$value] = [
								"amount"=>$amnt,
								"balance"=>$bal
							];

							break;
						}
						case "-":
						{
							$ruleValue = (int)$rule["RuleValue"];
							$amnt = $amount[$value] - $ruleValue;
							$bal = $amount[$value] - $amnt;

							$result[$value] = [
								"amount"=>$amnt,
								"balance"=>$bal
							];

							break;
						}
					}

					continue;
				}
			}

			if (!isset($result[$value])){
				$result[$value] = [
					"amount"=>$amount[$value],
					"balance"=>0
				];
			}
		}

		$meta = ["amount"=>0, "balance"=>0];
		foreach ($result as $value){
			$meta["amount"] += $value["amount"];
			$meta["balance"] += $value["balance"];
		}

		$result["_meta"] = $meta;

		return $result;
	}
}