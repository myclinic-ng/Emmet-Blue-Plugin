<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud\Lab;

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
 * class FollowUp.
 *
 * FollowUp Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class FollowUp {
	public static function register(array $data){
		$patient = $data["patient"];
		$labNo = $data["labNumber"];
		$query = "SELECT a.ProfileID, b.PatientLabNumber, b.DateRequested, c.InvestigationTypeName, d.StaffFullName, d.StaffID
					FROM EmmetBlueCloud.LinkedProfiles a
					INNER JOIN Lab.Patients b ON a.PatientID  = b.PatientID
					INNER JOIN Lab.InvestigationTypes c ON c.InvestigationTypeID = b.InvestigationTypeRequired
					INNER JOIN Staffs.StaffProfile d ON b.RequestedBy = d.StaffID
					WHERE a.PatientID = $patient AND b.PatientLabNumber = $labNo";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$result = $result[0];

			try {
				\EmmetBlue\Plugins\EmmetblueCloud\Provider::publishStaff((int) $result["StaffID"]);
			}
			catch(\Exception $e){

			}

			$data = [
				"profile"=>$result["ProfileID"],
				"labNumber"=>$result["PatientLabNumber"],
				"investigation"=>$result["InvestigationTypeName"],
				"requestedBy"=>$result["StaffFullName"],
				"staffId"=>$result["StaffID"],
				"dateRequested"=>$result["DateRequested"]
			];

			$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$url = HTTPRequest::$cloudUrl."/provider/lab-followup/register";
			$response = HTTPRequest::httpPostRequest($url, $data, $keyBunch);

			\EmmetBlue\Plugins\EmmetblueCloud\MessagePacket::sendLabPacket([
				"patient"=>$patient,
				"investigation"=>$result["InvestigationTypeName"],
				"type"=>0
			]);

			return $response;
		}

		return;
	}

	public static function publish(array $data){
		$patient = $data["patient"];
		$labNo = $data["labNumber"];
		$staff = $data["staff"] ?? null;
		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$result = $result[0];
			$data = [
				"profile"=>$result["ProfileID"],
				"labNumber"=>$labNo
			];

			$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$url = HTTPRequest::$cloudUrl."/provider/lab-followup/publish";
			$response = HTTPRequest::httpPostRequest($url, $data, $keyBunch);

			\EmmetBlue\Plugins\EmmetblueCloud\MessagePacket::sendLabPacket([
				"patient"=>$patient,
				"investigation"=>"Registered",
				"type"=>1,
				"staff"=>$staff
			]);

			return $response;
		}

		return;
	}
}