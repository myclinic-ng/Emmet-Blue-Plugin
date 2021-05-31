<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientDiagnosis;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class DiagnosisData.
 *
 * DiagnosisData Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 30/05/2021 11:01
 */

class DiagnosisData
{
    private static $diagnosis;
    private static function loadDiagnosis(int $resourceId){
        if (!is_array(self::$diagnosis)){
           self::$diagnosis = PatientDiagnosis::viewById($resourceId);   
        }
    }

    private static function getDateAfter($patient, $resourceId, $date){
        $dateAfter = "SELECT TOP 1 DiagnosisDate FROM Patients.PatientDiagnosis WHERE PatientID=$patient AND DiagnosisID > $resourceId ORDER BY DiagnosisDate ASC";

        $dateAfter = DBConnectionFactory::getConnection()->query($dateAfter)->fetchAll(\PDO::FETCH_ASSOC);
        $dateAfter = isset($dateAfter[0]) ? $dateAfter[0]["DiagnosisDate"] : $date;
        $dateAfter = date("Y-m-d", strtotime($dateAfter));

        return $dateAfter;
    }
    public static function getInvestigationStatus(int $resourceId){
        self::loadDiagnosis($resourceId);
        $patient = self::$diagnosis["PatientID"];
        $date = self::$diagnosis["DiagnosisDate"];
        $labs = self::$diagnosis["Diagnosis"]["investigations"]["lab"];

        if (count($labs) < 1){
            return [];
        }

        $invs = [];
        foreach ($labs as $key => $value) {
            $invs[] = QB::wrapString($value["title"], "'");
        }

        $_invs = implode(", ", $invs);

        $dateAfter = self::getDateAfter($patient, $resourceId, $date);

        $date = date("Y-m-d", strtotime($date));

        $query = "SELECT a.InvestigationTypeRequired, a.PatientLabNumber, a.RegistrationDate, a.Published, b.InvestigationTypeName FROM Lab.Patients a 
                INNER JOIN Lab.InvestigationTypes b ON a.InvestigationTypeRequired = b.InvestigationTypeID
                INNER JOIN Lab.LabRequests c ON a.RequestID = c.RequestID
                WHERE b.InvestigationTypeName IN ($_invs) AND CONVERT(date, a.RegistrationDate) >= '$date'
                ";
        if ($dateAfter != $date){
            $query .= " AND CONVERT(date, a.RegistrationDate) < '$dateAfter'";
        }

        $query .= " ORDER BY a.RegistrationDate DESC";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $labReqs = [];
        foreach($result as $key => $value){
            if (!in_array($value["InvestigationTypeName"], $labReqs)){
                $labReqs[$value["InvestigationTypeName"]] = $value;
            }
        }

        return $labReqs;
    }

    public static function getObservations(int $resourceId){
        self::loadDiagnosis($resourceId);
        $patient = self::$diagnosis["PatientID"];
        $date = self::$diagnosis["DiagnosisDate"];

        $dateAfter = self::getDateAfter($patient, $resourceId, $date);
        $date = date("Y-m-d", strtotime($date));

        $query = "SELECT ObservationID, RepositoryID, ObservationDate FROM Nursing.Observations WHERE PatientID = $patient AND CONVERT(date, ObservationDate) >= '$date'";
        if ($dateAfter != $date){
            $query .= " AND CONVERT(date, ObservationDate) < '$dateAfter'";
        }

        $query .= " ORDER BY ObservationDate ASC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function getData(int $resourceId){
        return [
            "labs"=>self::getInvestigationStatus($resourceId),
            "observations"=>self::getObservations($resourceId)
        ];
    }
}