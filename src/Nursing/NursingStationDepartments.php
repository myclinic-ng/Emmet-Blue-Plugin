<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class NursingStationDepartments.
 *
 * NursingStationDepartments Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class NursingStationDepartments
{
	public static function newDepartment(array $data)
	{
		$department = $data["department"];
		$query = "INSERT INTO Nursing.NursingStationLoggingDepartments(Department) VALUES ($department)";

		return DBConnectionFactory::getConnection()->exec($query);	
	}

	public static function viewDepartments(){
		$query = "SELECT a.*, b.* FROM Nursing.NursingStationLoggingDepartments a INNER JOIN Staffs.Department b ON a.Department = b.DepartmentID";

		return DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function deleteDepartment(int $resourceId){
		$query = "DELETE FROM Nursing.NursingStationLoggingDepartments WHERE LogID = $resourceId";

		return DBConnectionFactory::getConnection()->exec($query);	
	}

	public static function isNursingStation(int $resourceId){
		$query = "SELECT * FROM Nursing.NursingStationLoggingDepartments WHERE Department = $resourceId";

		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

		if (isset($result[0])){
			return true;
		}

		return false;
	}

	public static function logPatientProcessing(array $data){
		$patient = $data["patient"];
		$nurse = $data["nurse"];
		$observation = $data["observation"] ?? null;
		$consultant = $data["consultant"] ?? 'null';
		$department = $data["department"];

		if (self::isNursingStation((int) $department)){
			$query = "INSERT INTO Nursing.PatientProcessLog(PatientID, Nurse, ObservationSummary, Consultant, Department) VALUES ($patient, $nurse, '$observation', $consultant, $department)";

			return DBConnectionFactory::getConnection()->exec($query);
		}
 	}
}