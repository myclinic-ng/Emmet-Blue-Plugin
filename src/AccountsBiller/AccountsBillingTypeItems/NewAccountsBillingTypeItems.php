<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems;

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
 * class NewAccountsBillingTypeItems.
 *
 * NewAccountsBillingTypeItems Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class NewAccountsBillingTypeItems
{
	/**
	 *method default
	 * manages the creation of new billing item resource
	 * @author Samuel Adeshina <samueladeshina73@gmail.com>
	 * @since v.0.0.1 05/07/2016 08:48pm
	*/
	public static function default(array $data)
	{
		$billingType = $data['billingType'] ?? 'NULL';
		$billingTypeItemName = $data['name'] ?? 'NULL';

		$packed = [
			'BillingType'=>($billingType !== 'NULL') ? QB::wrapString((string)$billingType, "'") : $billingType,
			'BillingTypeItemName'=>($billingTypeItemName !== 'NULL') ? QB::wrapString($billingTypeItemName, "'") : $billingTypeItemName
		];

		$result = DatabaseQueryFactory::insert('Accounts.BillingTypeItems', $packed);
		$billingTypeItem = $result["lastInsertId"];

		foreach($data["priceStructures"] as $priceStructure){
			$price = $priceStructure["price"];
			$patientTypes = $priceStructure["patientTypes"];
			$intervalBased = !empty($priceStructure["interval"]);
			$rateBased = isset($priceStructure["rate"]);
			$rateIdentifier = ($rateBased) ? $priceStructure["rate"] : null;

			foreach($patientTypes as $patientType){
				$queryValue[] = "(".$billingTypeItem.", '".$patientType."', '".$price."', ".(int)$rateBased.", '".$rateIdentifier."', ".(int)$intervalBased.")";
			}

			$query[] = "INSERT INTO Accounts.BillingTypeItemsPrices VALUES ".implode(", ", $queryValue);
		}

		$query = implode(";", $query);
		$result = DBConnectionFactory::getConnection()->exec($query);
		return $result;
	}
}