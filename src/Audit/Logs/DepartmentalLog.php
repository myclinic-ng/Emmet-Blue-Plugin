<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Audit\Logs;

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
 * class DepartmentalLog.
 *
 * DepartmentalLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class DepartmentalLog {
	public static function viewPharmacyLog(array $data = []){
		$patient = $data["patient"];
		$start = $data["startdate"];
		$end = $data["enddate"];

		$query = "SELECT * FROM Pharmacy.PrescriptionRequests a WHERE a.PatientID = $patient AND CONVERT(date, a.RequestDate) BETWEEN '$start' AND '$end'";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

		foreach ($result as $key=>$value){
			$result[$key]["RequestedByDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int)$value["RequestedBy"]);
			$result[$key]["RequestedByDetails"]["StaffRole"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["RequestedBy"]);

			$result[$key]["AcknowledgedByDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int)$value["AcknowledgedBy"]);
			$result[$key]["AcknowledgedByDetails"]["StaffRole"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["RequestedBy"]);
			
			$result[$key]["Request"] = unserialize(base64_decode($value["Request"]));
		}

		return $result;
	}
}