<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud\PatientProfile;

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
 * class PatientProfile.
 *
 * PatientProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class PatientProfile {
	private static function prepareCloudAccount(array $data){
		$patient = $data["patient"];
		$profile = $data["profile"];

		$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

		$patientInfo  = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $patient);
		if ($patientInfo["found"]){
			$patientInfo = $patientInfo["_source"];
			$patientInfo["resourceId"] = $profile;

			$path = $patientInfo["patientpicture"];
			$type = pathinfo($path, PATHINFO_EXTENSION);
			$data = file_get_contents($path);
			$patientInfo["patientpicture"] = 'data:image/' . $type . ';base64,' . base64_encode($data);

			$url = HTTPRequest::$cloudUrl."/provider/user-profile/upload-data";
			$response = HTTPRequest::httpPostRequest($url, $patientInfo, $keyBunch);

			return $response;
		}

		return false;
	}

	public static function newLink(array $data){
		$patient = $data["patient"];
		$accountId = $data["accountId"];
		$staff = $data["staff"];

		$query = "SELECT PatientFullName FROM Patients.Patient WHERE PatientID = $patient";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		
		if (isset($result[0])){
			$alias = $result[0]["PatientFullName"];

			$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$url = HTTPRequest::$cloudUrl."/provider/user-profile/link?resourceId=$accountId&userProviderId=$patient&alias=$alias&provider=".$keyBunch["ProviderID"];

			$response = HTTPRequest::httpRequest($url, $keyBunch);

			$profileId = $response->profile_id ?? null;

			if (!is_null($profileId)){
				self::prepareCloudAccount(["profile"=>$profileId, "patient"=>$patient]);
				$query = "INSERT INTO EmmetBlueCloud.LinkedProfiles (ProfileID, AccountID, PatientID, LinkedBy) VALUES ('$profileId', '$accountId', $patient, $staff)";

				return DBConnectionFactory::getConnection()->exec($query);
			}
		}

		return false;
	}

	public static function isLinked(int $resourceId){
		$query = "SELECT 1 FROM EmmetBlueCloud.LinkedProfiles WHERE PatientID = $resourceId";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return count($result) > 0;
	}

	public static function retrieveAccountPublicInfo(array $data){
		$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();
		switch($data["method"]){
			case "username":
				$url = HTTPRequest::$cloudUrl."/provider/user-profile/retrieve-user-account-details-by-username?username=".$data["value"];	
				break;
			case "email":
				$url = HTTPRequest::$cloudUrl."/provider/user-profile/retrieve-user-account-details-by-email?email=".$data["value"];	
				break;
			case "phone":
				$url = HTTPRequest::$cloudUrl."/provider/user-profile/retrieve-user-account-details-by-phone?phone=".$data["value"];	
				break;
			default:
				$url = HTTPRequest::$cloudUrl."/provider/user-profile/retrieve-user-account-details?resourceId=".$data["value"];	
		}
		
		$response = HTTPRequest::httpRequest($url, $keyBunch);

		return $response;
	}
}