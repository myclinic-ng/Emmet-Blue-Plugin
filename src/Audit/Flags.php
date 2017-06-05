<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Audit;

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

/**
 * class Flags.
 *
 * Flags Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class Flags {
	public static function viewByPatient(int $resourceId){
		$query = "SELECT a.StatusNote, b.LogID, a.StaffID FROM FinancialAuditing.UnlockLogStatus a 
					INNER JOIN Patients.PatientProfileUnlockLog b ON a.LogID = b.LogID
					WHERE a.Status = -1 AND b.PatientID=$resourceId
					ORDER BY PatientID DESC
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function flagPatient(int $resourceId, array $data){
		$note = [$data["note"]];
		$staff = $data["staff"];

		$query = "SELECT TOP 1 a.StatusNote, a.Status, b.* FROM FinancialAuditing.UnlockLogStatus a 
					INNER JOIN Patients.PatientProfileUnlockLog b ON a.LogID = b.LogID
					WHERE b.PatientID=$resourceId
					ORDER BY b.DateLogged DESC
					";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$logId = $result[0]["LogID"];
			if (!empty($result[0]["StatusNote"]) && $result[0]["StatusNote"] !== ""){
				$note[] = $result[0]["StatusNote"];
			}

			$_note = implode(", ", $note);

			$action = \EmmetBlue\Plugins\Audit\Logs\UnlockLog::setStatus((int) $logId, [
				"status"=>-1,
				"statusNote"=> $_note,
				"staff"=> $staff
			]);

			return $action;
		}

	}
}