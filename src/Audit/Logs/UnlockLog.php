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
 * class UnlockLog.
 *
 * UnlockLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class UnlockLog {
	public static function view(array $data = []){
		$start = $data["startdate"];
		$end = $data["enddate"];

		$query = "SELECT ROW_NUMBER() OVER (ORDER BY a.DateLogged) AS RowNum, a.*, b.Status, b.StatusNote, b.StaffID FROM Patients.PatientProfileUnlockLog a LEFT OUTER JOIN FinancialAuditing.UnlockLogStatus b ON a.LogID = b.LogID INNER JOIN Patients.Patient c ON a.PatientID = c.PatientID INNER JOIN  Patients.PatientType d ON c.PatientType = d.PatientTypeID WHERE CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";

		if (isset($data["paginate"])){
			 if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $query .= " AND (c.PatientFullName LIKE '%$keyword%' OR d.PatientTypeName LIKE '%$keyword%' OR d.CategoryName LIKE '%$keyword%')";
            }
	        $size = $data["from"] + $data["size"];
	        $_query = "SELECT COUNT(*) as Count FROM Patients.PatientProfileUnlockLog a LEFT OUTER JOIN FinancialAuditing.UnlockLogStatus b ON a.LogID = b.LogID WHERE CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";
	        $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";	
		}

		$result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

		$retrievedStaffs = [];
		foreach ($result as $key=>$value){
			if (!isset($retrievedStaffs[$value["Staff"]])){
				$retrievedStaffs[$value["Staff"]] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["Staff"]);
			}
			$result[$key]["StaffFullName"] = $retrievedStaffs[$value["Staff"]]["StaffFullName"];
			$result[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $value["PatientID"]);
			if (isset($result[$key]["PatientInfo"]["_source"])){
				$result[$key]["PatientInfo"] = $result[$key]["PatientInfo"]["_source"];
			}

			if (!is_null($value["StaffID"])){
				if (!isset($retrievedStaffs[$value["StaffID"]])){
					$retrievedStaffs[$value["StaffID"]] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"]);
				}
				$result[$key]["StatusStaffFullName"] = $retrievedStaffs[$value["StaffID"]]["StaffFullName"];
				$result[$key]["StatusStaffPicture"] = $retrievedStaffs[$value["StaffID"]]["StaffPicture"];
			}
		}

		if (isset($data["paginate"])){
            $total = DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC)[0]["Count"];
            // $filtered = count($_result) + 1;
            $result = [
                "data"=>$result,
                "total"=>$total,
                "filtered"=>$total
            ];
        }

		return $result;
	}

	public static function setStatus(int $resourceId, array $data){
		$status = $data["status"];
		$note = $data["statusNote"] ?? null;
		$staff = $data["staff"] ?? null;

		try {
			$query = "SELECT COUNT(*) as total FROM FinancialAuditing.UnlockLogStatus WHERE LogID = $resourceId;";
			$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["total"];
			if ($result == 1){
				$query = "UPDATE FinancialAuditing.UnlockLogStatus SET Status = $status, StatusNote = '$note', StaffID = $staff WHERE LogID = $resourceId";
			}
			else {
				$query = "INSERT INTO FinancialAuditing.UnlockLogStatus (LogID, Status, StatusNote, StaffID) VALUES ($resourceId, $status, '$note', $staff);";
			}

			$result = DBConnectionFactory::getConnection()->exec($query);
		}
		catch(\PDOException $e){
			return $e->getMessage();
		}

		return $result;
	}
}