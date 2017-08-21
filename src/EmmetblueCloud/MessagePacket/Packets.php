<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud\MessagePacket;

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
 * class Packets.
 *
 * Packets Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class Packets {
	private static function sendMessagePacket(array $data){
		$keyBunch = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

		try {
			\EmmetBlue\Plugins\EmmetblueCloud\Provider::publishStaff((int) $data["staffId"]);
		}
		catch(\Exception $e){

		}

		$url = HTTPRequest::$cloudUrl."/provider/message-packet/new-packet";

		$response = HTTPRequest::httpPostRequest($url, $data, $keyBunch);

		return $response;
	}

	public static function appointment(array $data){
		$staff = $data["staff"] ?? null;
		$patient = $data["patient"];
		$date = $data["date"];
		$reason = $data["reason"];

		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result[0])){
			$result = $result[0];
			$providerDetails = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();
			try {
				\EmmetBlue\Plugins\EmmetblueCloud\Provider::publishStaff((int) $staff);
			}
			catch(\Exception $e){

			}

			$data = [
				"profileId"=>$result["ProfileID"],
				"subject"=>"Appointment Reminder",
				"providerId"=>$providerDetails["ProviderID"],
				"staffId"=>$staff
			];

			$staffName = \EmmetBlue\Plugins\HumanResources\Staff::viewStaffProfile((int) $staff)[0]["StaffFullName"];
			$provider = $providerDetails["ProviderAlias"];

			$data["message"] = "You have an appointment with $staffName at $provider on $date. Appointment Message: $reason";

			$response = self::sendMessagePacket($data);

			return $response;
		}

		return;
	}

	public static function appointmentCancel(array $data){
		$staff = $data["Staff"] ?? null;
		$patient = $data["PatientID"];
		$date = $data["AppointmentDate"];

		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result[0])){
			$result = $result[0];
			$providerDetails = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();

			$data = [
				"profileId"=>$result["ProfileID"],
				"subject"=>"Appointment Cancellation",
				"providerId"=>$providerDetails["ProviderID"],
				"staffId"=>$staff
			];

			$staffName = \EmmetBlue\Plugins\HumanResources\Staff::viewStaffProfile((int) $staff)[0]["StaffFullName"];
			$provider = $providerDetails["ProviderAlias"];

			$data["message"] = "Your appointment with $staffName at $provider on $date has been cancelled.";

			$response = self::sendMessagePacket($data);

			return $response;
		}

		return;
	}

	public static function lab(array $data){
		$patient = $data["patient"];
		$investigation = $data["investigation"];
		$type = $data["type"];
		$staff = $data["staff"] ?? null;

		$query = "SELECT a.ProfileID FROM EmmetBlueCloud.LinkedProfiles a WHERE a.PatientID = $patient";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result[0])){
			$result = $result[0];
			$providerDetails = \EmmetBlue\Plugins\EmmetblueCloud\Provider::getDetails();
			$data = [
				"profileId"=>$result["ProfileID"],
				"providerId"=>$providerDetails["ProviderID"],
				"staffId"=>$staff
			];

			if ($type == 0){
				$data["subject"] = "Laboratory Investigation Notification";
				$data["message"] = "You have been registered for $investigation test. Please be patient while we carry out your investigation.";
			}
			else if ($type == 1){
				$data["subject"] = "Your Results Are Ready";
				$data["message"] = "Your $investigation investigation has been completed and the result sent to the requesting consultant.";
			}

			$response = self::sendMessagePacket($data);

			return $response;
		}

		return;
	}
}