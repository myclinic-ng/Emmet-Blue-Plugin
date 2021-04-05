<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\TreatmentChartStatus;

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
 * class TreatmentChartStatus.
 *
 * TreatmentChartStatus Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 04/04/2021 12:19
 */
class TreatmentChartStatus
{
    /**
     * creates new TreatmentChartStatus
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $_query = [];
        foreach ($data as $_data){
            $sData = $_data;
            $chartId = $_data["treatmentChartId"];
            $status = $_data["status"] ?? 1;
            $note = $_data["note"] ?? null;
            $staff = $_data["staff"] ?? null;
            $associatedDate = $_data["associatedDate"];
            $associatedTime = $_data["associatedTime"] ?? null;

            $_query[] = "($chartId, $status, '$note', $staff, '$associatedDate')";
        }

        $query = "INSERT INTO Nursing.AdmissionTreatmentChartStatus (TreatmentChartID, Status, Note, StaffID, AssociatedDate) VALUES ".implode(", ", $_query);

        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChartStatus',
                (string)serialize($result)
            );

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

    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.AdmissionTreatmentChartStatus')
            ->where('TreatmentChartID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY DateLogged DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["NurseDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["StaffID"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChartStatus',
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
            ->from('Nursing.AdmissionTreatmentChartStatus')
            ->where('TreatmentChartID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder." ORDER BY Date DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["NurseFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["StaffID"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'AdmissionTreatmentChartStatus',
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
}