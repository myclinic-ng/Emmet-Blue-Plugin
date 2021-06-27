<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud;

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
use EmmetBlue\Core\Factory\HTTPRequestFactory as HTTPRequest;

/**
 * class PatientProfile.
 *
 * PatientProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class XHttpRequest {
	public static $cloudUrls = ["https://emmetblue.ng/endpoints/cloud/v1", "http://localhost:6543/v1"]; 
	public static $cloudUrl = Constant::getGlobals()["cloud-url"] ?? "http://api.emmetblue.ng:7002/v1";

	public static function httpRequest($url, $keyBunch){
		$request = HTTPRequest::get($url, [
			'X-Authorization'=>$keyBunch["ProviderID"].",".$keyBunch["ProviderSecretToken"]
		]);

		$response = json_decode($request->body);

		if ($response->errorStatus || !$response->contentData){
			throw new \Exception(!is_null($response->errorMessage) ? $response->errorMessage : "An error occurred");
		}

		return $response->contentData;
	}

	public static function httpPostRequest($url, $data, $keyBunch){
		$request = HTTPRequest::post($url, $data, [
			'X-Authorization'=>$keyBunch["ProviderID"].",".$keyBunch["ProviderSecretToken"]
		]);

		$response = json_decode($request->body);

		if ($response->errorStatus || !$response->contentData){
			throw new \Exception(!is_null($response->errorMessage) ? $response->errorMessage : "An error occurred");
		}

		return $response->contentData;
	}
}