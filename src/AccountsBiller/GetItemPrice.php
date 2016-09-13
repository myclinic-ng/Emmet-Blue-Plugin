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
			throw new \Exception("Price structure does not exist for the specified item");
		}

		$result = $result[0];
		$price = $result["BillingTypeItemPrice"];
		if ($result["IntervalBased"]){
			$query = "SELECT * FROM Accounts.BillingTypeItemsInterval WHERE BillingTypeItemID = $item";
			$results = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC);
			if (empty($result)){
				throw new \Exception("Interval definition not found.");
			}
			$totalPrice = 0;
			foreach ($results as $result) {
				$interval = $result["Interval"];
				$type = $result["IntervalIncrementType"];
				$increment = $result["IntervalIncrement"];

				$totalPrice += self::calculateIntervalBasedPrice((int)$price, (int)$quantity, (int)$interval, $type, (int)$increment);
			}
		}
		else {
			$totalPrice = self::calculateNonIntervalBasedPrice((int)$price, (int)$quantity);
		}

		return $totalPrice;
	}

	private static function calculateNonIntervalBasedPrice(int $price, int $quantity){
		return $price * $quantity;
	}

	private static function calculateIntervalBasedPrice(int $price, int $quantity, int $interval, string $type, int $increment){
		$totalPrice = $price;
		switch (strtolower($type)){
			case "additive":{
				if ($quantity > $interval){
					$dividend = $quantity / $interval;
					$modulus = $quantity % $interval;
					for ($i = 0; $i < $dividend; $i++){
						$totalPrice += ($totalPrice + $increment) * $interval;
					}
					if ($modulus > 0){
						$totalPrice += ($totalPrice + $increment) * $modulus;
					}
				}
				break;
			}

			case "multiplicative":{
				if ($quantity > $interval){
					$dividend = $quantity / $interval;
					$modulus = $quantity % $interval;
					for($i = 0; $i < $dividend; $i++){
						$totalPrice += ($totalPrice * $increment) * $interval;
					}
					if ($modulus > 0){
						$totalPrice += ($totalPrice * $increment) * $modulus;
					}
				}
				break;
			}

			case "custom":{
				$counter = $quantity - $interval;
				if ($counter > 0){
					$totalPrice += ($totalPrice + $increment) * $counter;
				}
				break;
			}
		}

		return $totalPrice;
	}
}