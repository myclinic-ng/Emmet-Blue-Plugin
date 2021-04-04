<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\TreatmentPlan;

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
 * class TreatmentPlan.
 *
 * TreatmentPlan Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 04/04/2021 15:50
 */
class TreatmentPlan
{
    /**
     * creates new TreatmentPlan
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $sData = $data;
        $items = $data["items"];
        $admissionId = $sData['admissionId'] ?? 'NULL';
        $loggedBy = $sData["loggedBy"] ?? 'NULL';
        $startdate = $sData["startDate"] ?? 'NULL';
        
        $_query = "";
        $_charts = [];

        foreach ($items as $key => $data) {
            $drug = $data['drug'] ?? 'NULL';
            $dose = $data["dose"] ?? 'NULL';
            $route = $data["route"] ?? 'NULL';
            $note = $data["note"] ?? 'NULL';
            $hourlyInterval = $data["hourlyInterval"] ?? 'NULL';
            $numberOfDays = $data["numberOfDays"] ?? 'NULL';

            // $_query = "($admissionId, '$drug', '$dose', '$route', $hourlyInterval, $numberOfDays, '$startdate', $loggedBy, '$note')";
            // $query = "INSERT INTO Nursing.AdmissionTreatmentPlan (PatientAdmissionID, Drug, Dose, Route, HourlyInterval, NumberOfDays, StartDate, LoggedBy, Note) VALUES ".$_query;

            try
            {
                $result = DBQueryFactory::insert('Nursing.AdmissionTreatmentPlan', [
                    'PatientAdmissionID'=>QB::wrapString($admissionId, "'"),
                    'Drug'=>QB::wrapString($drug, "'"),
                    'Dose'=>QB::wrapString($dose, "'"),
                    'Route'=>QB::wrapString($route, "'"),
                    'HourlyInterval'=>QB::wrapString($hourlyInterval, "'"),
                    'NumberOfDays'=>QB::wrapString($numberOfDays, "'"),
                    'StartDate'=>QB::wrapString($startdate, "'"),
                    'LoggedBy'=>QB::wrapString($loggedBy, "'"),
                    'Note'=>QB::wrapString($note, "'")
                ]);

                $planId = $result["lastInsertId"];
                $chart = [];
                $chart_tpl = [
                    "date"=>"", 
                    "planId"=>$planId,
                    "admissionId"=>$admissionId,
                    "loggedBy"=>$loggedBy,
                    "items"=>[
                        "drug"=>$drug,
                        "dose"=>$dose,
                        "route"=>$route,
                        "note"=>$note
                    ]
                ];

                $frequency = (24 / $hourlyInterval) * $numberOfDays;
                $new_time = $startdate;
                for ($i=0; $i < $frequency; $i++) {
                    $new_time = date("Y-m-d H:i:s", strtotime(sprintf("+%d hours", $hourlyInterval), strtotime($new_time)));
                    $chart_tpl["date"] = $new_time;
                    $chart[] = $chart_tpl;
                }

                $treatmentChart = \EmmetBlue\Plugins\Nursing\TreatmentChart\TreatmentChart::create($chart);

            }
            catch (\PDOException $e)
            {
                throw new SQLException(sprintf(
                    "Unable to process request, %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }

        return true;
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.AdmissionTreatmentPlan')
            ->where('PatientAdmissionID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY DateLogged DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["StaffDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["LoggedBy"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentPlan',
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
            ->from('Nursing.AdmissionTreatmentPlan')
            ->where('PatientAdmissionID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY DateLogged DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["NurseFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["LoggedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentPlan',
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

    
    public static function deleteTreatmentPlan(int $resourceId, array $data)
    {
        $note = $data["note"] ?? null;
        $staff = $data["staffId"];

        $query="UPDATE Nursing.AdmissionTreatmentPlan SET Deleted = 1, DeletedBy=$staff, DateDeleted=GETDATE()";
        if (!is_null($note)){
            $query .= ", Note = '$note'";
        }

        $query .= " WHERE TreatmentPlanID = $resourceId";
        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }    
}