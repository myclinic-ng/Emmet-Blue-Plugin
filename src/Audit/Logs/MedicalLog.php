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
 * class MedicalLog.
 *
 * MedicalLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class MedicalLog {
	public static function view(array $data = []){
		$patient = $data["patient"];
		$start = $data["startdate"];
		$end = $data["enddate"];

		$query = "SELECT a.*, b.DiagnosisTitle FROM Consultancy.PatientDiagnosisLog a INNER JOIN Patients.PatientDiagnosis b ON a.DiagnosisID = b.DiagnosisID WHERE a.PatientID = $patient AND CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

		foreach ($result as $key=>$value){
			$result[$key]["StaffDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"]);
			$result[$key]["StaffDetails"]["StaffRole"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["StaffID"]);
			$result[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $value["PatientID"])["_source"];
		}

		return $result;
	}
}