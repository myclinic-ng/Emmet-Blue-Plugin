<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\TreatmentChart;

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
 * class TreatmentChart.
 *
 * TreatmentChart Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class TreatmentChart
{
    /**
     * creates new TreatmentChart
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $_query = [];
        foreach ($data as $sData) {
            $items = $sData["items"];
            $admissionId = $sData['admissionId'] ?? 'NULL';
            $loggedBy = $sData["loggedBy"] ?? 'NULL';
            $date = $sData["date"] ?? 'NULL';
            $planId = $sData["planId"] ?? null;

            $drug = $items['drug'] ?? null;
            $dose = $items["dose"] ?? null;
            $route = $items["route"] ?? null;
            $note = $items["note"] ?? null;

            $_query[] = "($admissionId, '$drug', $loggedBy, '$dose', '$route', '$note', '$date', $planId)";
        }

        $query = "INSERT INTO Nursing.AdmissionTreatmentChart (PatientAdmissionID, Drug, LoggedBy, Dose, Route, Note, Date, TreatmentPlanID) VALUES ".implode(",", $_query);

        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.AdmissionTreatmentChart')
            ->where('PatientAdmissionID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY Date DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["StaffDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["LoggedBy"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChart',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }


    public static function viewMostRecent(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('TOP 1 *')
            ->from('Nursing.AdmissionTreatmentChart')
            ->where('PatientAdmissionID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY Date DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["StaffFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["LoggedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChart',
                (string)$selectBuilder
            );

            return (isset($result[0])) ? $result[0] : $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    
    public static function deleteTreatmentChart(int $resourceId)
    {
        $query="UPDATE Nursing.AdmissionTreatmentChart SET Deleted = 1 WHERE TreatmentChartID = $resourceId";
        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }    
}