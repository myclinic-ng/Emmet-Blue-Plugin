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

		$query = "SELECT ROW_NUMBER() OVER (ORDER BY a.DateLogged) AS RowNum, a.*, b.Status, b.StatusNote, b.StaffID FROM Patients.PatientProfileUnlockLog a LEFT OUTER JOIN FinancialAuditing.UnlockLogStatus b ON a.LogID = b.LogID WHERE CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";

		if (isset($data["paginate"])){
	        $size = $data["from"] + $data["size"];
	        $_query = "SELECT COUNT(*) as Count FROM Patients.PatientProfileUnlockLog a LEFT OUTER JOIN FinancialAuditing.UnlockLogStatus b ON a.LogID = b.LogID WHERE CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";
	        $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";	
		}

		$result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

		foreach ($result as $key=>$value){
			$result[$key]["StaffFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["Staff"])["StaffFullName"];
			$result[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $value["PatientID"])["_source"];
			if (!is_null($value["StaffID"])){
				$staff = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"]);
				$result[$key]["StatusStaffFullName"] = $staff["StaffFullName"];
				$result[$key]["StatusStaffPicture"] = $staff["StaffPicture"];
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
			$query = "INSERT INTO FinancialAuditing.UnlockLogStatus (LogID, Status, StatusNote, StaffID) VALUES ($resourceId, $status, '$note', $staff)";
			$result = DBConnectionFactory::getConnection()->exec($query);
		}
		catch(\PDOException $e){
			$query = "UPDATE FinancialAuditing.UnlockLogStatus SET Status = $status, StatusNote = '$note', StaffID = $staff WHERE LogID = $resourceId";

			$result = DBConnectionFactory::getConnection()->exec($query);
		}

		return $result;
	}
}