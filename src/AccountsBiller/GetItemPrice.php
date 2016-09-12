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
		return $data;
		$item = $data["item"] ?? null;
		$quantity = $data["quantity"] ?? null;

		$query = "SELECT PatientType FROM Patients.Patient WHERE PatientID = $patient";
		$patientType = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC)[0]["PatientType"];

		$query = "SELECT * FROM Accounts.BillingTypeItemsPrices WHERE BillingTypeItem = $item AND PatientType = $patientType";
		$result = (DBConnectionFactory::getConnection()->query($query))->fetchall(\PDO::FETCH_ASSOC)[0];

		return $result;
	}
}