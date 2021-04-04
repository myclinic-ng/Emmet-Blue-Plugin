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
            $drug = $data['drug'] ?? null;
            $dose = $data["dose"] ?? null;
            $route = $data["route"] ?? null;
            $note = $data["note"] ?? null;
            $hourlyInterval = $data["hourlyInterval"] ?? null;
            $numberOfDays = $data["numberOfDays"] ?? null;

            $_query = "($admissionId, '$drug', '$dose', '$route', $hourlyInterval, $numberOfDays, '$startdate', $loggedBy, '$note')";
            $query = "INSERT INTO Nursing.AdmissionTreatmentPlan (PatientAdmissionID, Drug, Dose, Route, HourlyInterval, NumberOfDays, StartDate, LoggedBy, Note) VALUES ".$_query;
            try
            {
                $result = DBConnectionFactory::getConnection()->exec($query);

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_SELECT,
                    'Nursing',
                    'AdmissionTreatmentPlan',
                    (string)serialize($result)
                );

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
                    $new_time = date("Y-m-d H:i:s", strtotime(sprintf("+%d hours", $hourlyInterval)));
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
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY Date DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["NurseDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Nurse"]);
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
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY Date DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["NurseFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Nurse"])["StaffFullName"];
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

    
    public static function deleteTreatmentPlan(int $resourceId)
    {
        $query="UPDATE Nursing.AdmissionTreatmentPlan SET Deleted = 1 WHERE TreatmentPlanID = $resourceId";
        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }    
}