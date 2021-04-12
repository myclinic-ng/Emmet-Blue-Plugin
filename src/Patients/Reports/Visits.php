<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Reports;

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

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class Visits.
 *
 * Visits Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Visits
{
	public static function totalByCategories(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];

		$query = "SELECT c.CategoryName, COUNT(DISTINCT a.PatientID) as TotalPatientCount FROM Patients.PatientProfileUnlockLog a 
					INNER JOIN Patients.Patient b ON a.PatientID = b.PatientID
					INNER JOIN Patients.PatientType c ON b.PatientType = c.PatientTypeID
					WHERE CONVERT(date, a.DateLogged) BETWEEN '$sd' AND '$ed'
					GROUP BY c.CategoryName
					ORDER BY COUNT(DISTINCT a.PatientID) DESC
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function totalByCategory(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];
		$category = $data["category"];

		$query = "SELECT c.PatientTypeName, COUNT(DISTINCT a.PatientID) as TotalPatientCount FROM Patients.PatientProfileUnlockLog a 
					INNER JOIN Patients.Patient b ON a.PatientID = b.PatientID
					INNER JOIN Patients.PatientType c ON b.PatientType = c.PatientTypeID
					WHERE c.CategoryName = '$category' AND CONVERT(date, a.DateLogged) BETWEEN '$sd' AND '$ed'
					GROUP BY c.PatientTypeName
					ORDER BY COUNT(DISTINCT a.PatientID) DESC
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function total(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];

		$query = "SELECT COUNT(DISTINCT a.PatientID) as Count FROM Patients.PatientProfileUnlockLog a WHERE CONVERT(date, a.DateLogged) BETWEEN '$sd' AND '$ed'";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];

		$q = "SELECT TOP 1 c.CategoryName, COUNT(DISTINCT a.PatientID) as TotalPatientCount FROM Patients.PatientProfileUnlockLog a 
				INNER JOIN Patients.Patient b ON a.PatientID = b.PatientID
				INNER JOIN Patients.PatientType c ON b.PatientType = c.PatientTypeID
				WHERE CONVERT(date, a.DateLogged) BETWEEN '$sd' AND '$ed'
				GROUP BY c.CategoryName
				ORDER BY COUNT(DISTINCT a.PatientID) DESC";

		$result["max"] = DBConnectionFactory::getConnection()->query($q)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result["max"][0])){
			$result["max"] = $result["max"][0];
		}

		return $result;
	}
}