<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud\Appointment;

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
 * class Appointment.
 *
 * Appointment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class Appointment {
	public static function publish(array $data){
		$patient = $data["patient"];
		$staff = $data["staff"] ?? null;
		$reason = $data["reason"];
		$date = $data["date"];
		$appointmentId = $data["appointmentId"];

		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$result = $result[0];
			$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$_data = [
				"profile"=>$result["ProfileID"],
				"provider"=>$keyBunch["ProviderID"],
				"staffId"=>$staff,
				"appointmentReason"=>$reason,
				"appointmentDate"=>$date,
				"appointmentId"=>$appointmentId
			];

			$url = HTTPRequest::$cloudUrl."/provider/appointment/create";
			$response = HTTPRequest::httpPostRequest($url, $_data, $keyBunch);

			\EmmetBlue\Plugins\EmmetblueCloud\MessagePacket::sendAppointmentPacket($data);

			return $response;
		}

		return;
	}

	public static function cancelAppointment(int $resourceId, array $appointmentData=[]){
		$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();
		$data = [
			"appointmentId"=>$resourceId,
			"provider"=>$keyBunch["ProviderID"]
		];

		$url = HTTPRequest::$cloudUrl."/provider/appointment/delete";
		$response = HTTPRequest::httpPostRequest($url, $data, $keyBunch);

		if (!empty($data)){
			\EmmetBlue\Plugins\EmmetblueCloud\MessagePacket::sendAppointmentCancelPacket($appointmentData);
		}

	}
}