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
 * class Registration.
 *
 * Registration Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Registration
{
	public static function totalByCategories(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];

		$query = "SELECT b.CategoryName, COUNT(a.PatientID) AS TotalPatientCount, 
					(SELECT COUNT(*) FROM Patients.PatientType WHERE CategoryName=b.CategoryName) AS TotalPatientTypes
					FROM Patients.Patient a
					INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID
					WHERE CONVERT(date, a.LastModified) BETWEEN '$sd' AND '$ed'
					GROUP BY b.CategoryName
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function totalByCategory(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];
		$category = $data["category"];

		$query = "SELECT b.PatientTypeName, COUNT(a.PatientType) AS TotalPatientCount FROM Patients.Patient a
					INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID
					WHERE b.CategoryName = '$category' AND CONVERT(date, a.LastModified) BETWEEN '$sd' AND '$ed'
					GROUP BY b.PatientTypeName
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		return $result;
	}

	public static function total(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];

		$query = "SELECT COUNT(a.PatientID) AS TotalPatientCount
					FROM Patients.Patient a
					INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID
					WHERE CONVERT(date, a.LastModified) BETWEEN '$sd' AND '$ed'
				";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];

		$q = "SELECT TOP 1 b.CategoryName, COUNT(a.PatientID) AS TotalPatientCount
				FROM Patients.Patient a
				INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID
				WHERE CONVERT(date, a.LastModified) BETWEEN '$sd' AND '$ed'
				GROUP BY b.CategoryName
				ORDER BY COUNT(a.PatientID) DESC";

		$result["max"] = DBConnectionFactory::getConnection()->query($q)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result["max"][0])){
			$result["max"] = $result["max"][0];
		}

		return $result;
	}


}