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
        $sData = $data;
        $items = $data["items"];
        $time = $sData["associatedTime"];
        $admissionId = $sData['admissionId'] ?? 'NULL';
        $nurse = $sData["nurse"] ?? 'NULL';
        $date = $sData["time"] ?? 'NULL';
        
        $_query = [];

        foreach ($items as $key => $data) {
            $drug = $data['drug'] ?? null;
            $dose = $data["dose"] ?? null;
            $route = $data["route"] ?? null;
            $note = $data["note"] ?? null;

            $_query[] = "($admissionId, '$drug', $nurse, '$dose', '$route', '$note', '$time', '$date')";
        }

        $query = "INSERT INTO Nursing.AdmissionTreatmentChart (PatientAdmissionID, Drug, Nurse, Dose, Route, Note, Time, Date) VALUES ".implode(", ", $_query);

        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChart',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (chart not saved), %s",
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
                $result[$i]["NurseDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Nurse"]);
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
                $result[$i]["NurseFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Nurse"])["StaffFullName"];
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