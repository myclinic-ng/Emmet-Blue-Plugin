<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina samueladeshina73@gmail.com
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Logs.
 *
 * Logs Controller
 *
 * @author Samuel Adeshina samueladeshina73@gmail.com
 * @since v0.0.1 20/08/2016 03:29AM
 */
class Logs
{
    public static function getDiagnoses(int $resourceId, array $data)
    {
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.DateLogged DESC) AS RowNum, a.*, b.DiagnosisTitle, c.PatientFullName
                            FROM Consultancy.PatientDiagnosisLog a
                            INNER JOIN Patients.PatientDiagnosis b ON a.DiagnosisID = b.DiagnosisID
                            INNER JOIN Patients.Patient c ON a.PatientID = c.PatientID
                            WHERE StaffID=$resourceId
                        ";
        if (isset($data['startdate'])){
            $selectBuilder .= " AND CONVERT(date, a.DateLogged) BETWEEN '".$data["startdate"]."' AND '".$data["enddate"]."'"; 
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (c.PatientFullName LIKE '%$keyword%' OR b.DiagnosisTitle LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = (string) $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $result["PatientID"])["_source"];
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $viewOperation = [
                    "data"=>$viewOperation,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }

            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }
}