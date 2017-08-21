<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud\Receipt;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use EmmetBlue\Plugins\EmmetblueCloud\XHttpRequest as HTTPRequest;

/**
 * class Receipt.
 *
 * Receipt Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class Receipt {
	public static function upload(array $data){
		$patient = $data["patient"];
		$staff = $data["staff"] ?? null;
		$receipt = $data["receipt"];
		$description = $data["description"];

		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$result = $result[0];
			$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$_data = [
				"profile"=>$result["ProfileID"],
				"provider"=>$keyBunch["ProviderID"],
				"staffId"=>$staff,
				"description"=>$description,
				"receipt"=>$receipt
			];

			$url = HTTPRequest::$cloudUrl."/provider/receipt/upload";
			$response = HTTPRequest::httpPostRequest($url, $_data, $keyBunch);

			// \EmmetBlue\Plugins\EmmetblueCloud\MessagePacket::sendReceiptPacket($data);

			return $response;
		}

		return;
	}
}