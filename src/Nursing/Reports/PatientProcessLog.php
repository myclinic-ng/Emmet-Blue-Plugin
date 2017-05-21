<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\Reports;

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
 * class PatientProcessLog.
 *
 * PatientProcessLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PatientProcessLog
{
	public static function view(array $data){
		$sd = $data["startdate"];
		$ed = $data["enddate"];

		$query = "SELECT ROW_NUMBER() OVER (ORDER BY DateLogged DESC) AS RowNum, a.*, b.PatientFullName, c.StaffFullName, c.StaffPicture FROM Nursing.PatientProcessLog a INNER JOIN Patients.Patient b ON a.PatientID = b.PatientID INNER JOIN Staffs.StaffProfile c ON a.Nurse = c.StaffID WHERE CONVERT(date, a.DateLogged) BETWEEN '$sd' AND '$ed'";

		if (isset($data["filtertype"])){
			switch(strtolower($data["filtertype"])){
				case "department":{
					$query .= " AND Department = ".$data["query"];
					break;
				}
				case "staff":{
					$query .= " AND Nurse = ".$data["query"];
					break;
				}
				case "observation":{
					$query .= " AND ObservationSummary = '".$data["query"]."'";
					break;
				}
			}
		}

		if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $query .= " AND (a.ObservationSummary LIKE '%$keyword%' OR b.PatientFullName LIKE '%$keyword%' OR c.StaffFullName LIKE '%$keyword%')";
            }

            $_query = $query;
            $size = $data["size"] + $data["from"];
            $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        $result = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);
        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_SELECT,
            'Nursing',
            'PatientProcessLog',
            (string)$query
        );

        foreach ($result as $key=>$value){
        	$result[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $value["PatientID"])["_source"];
        }

        if (isset($data["paginate"])){
            $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
            // $filtered = count($_result) + 1;
            $result = [
                "data"=>$result,
                "total"=>$total,
                "filtered"=>$total
            ];
        }

        return $result;	
	}
}