<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Patient;

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
 * class PatientHospitalHistory.
 *
 * PatientHospitalHistory Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class HospitalHistory
{
    /**
     * view patients UUID
     */
    public static function view(int $patientId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientHospitalHistory');
        $selectBuilder->where('PatientID ='.$patientId);
        try
        {
            $viewPatients = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientHospitalHistory',
                (string)serialize($selectBuilder)
            );

            return $viewPatients;    
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function lastVisit(int $patientId){
        $query = "SELECT TOP(1) * from Patients.PatientHospitalHistory WHERE PatientID = $patient ORDER BY HospitalHistoryID DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function new(int $patientId, array $hospitalHistory){
        $historyArray = [];
        if (count($hospitalHistory) >= 1 && is_array($hospitalHistory[0])){
            foreach ($hospitalHistory as $value){
                $dateAttended = isset($value["dateAttended"]) ? QB::wrapString($value["dateAttended"], "'") : 'NULL';
                $referredBy = isset($value["referredBy"]) ? QB::wrapString($value["referredBy"], "'") : 'NULL';
                $physician = isset($value["physician"]) ? QB::wrapString($value["physician"], "'") : 'NULL';
                $ward = isset($value["ward"]) ? QB::wrapString($value["ward"], "'") : 'NULL';
                $dateDischarged = isset($value["dateDischarged"]) ? QB::wrapString($value["dateDischarged"], "'") : 'NULL';
                $dischargedTo = isset($value["dischargedTo"]) ? QB::wrapString($value["dischargedTo"], "'") : 'NULL';
                $condition = isset($value["condition"]) ? QB::wrapString($value["condition"], "'") : 'NULL';

                $historyArray[] = "($patientId, $dateAttended, $referredBy, $physician, $ward, $dateDischarged, $dischargedTo, $condition)";
            }
        }
        else {
            $dateAttended = isset($hospitalHistory["dateAttended"]) ? QB::wrapString($hospitalHistory["dateAttended"], "'") : 'NULL';
            $referredBy = isset($hospitalHistory["referredBy"]) ? QB::wrapString($hospitalHistory["referredBy"], "'") : 'NULL';
            $physician = isset($hospitalHistory["physician"]) ? QB::wrapString($hospitalHistory["physician"], "'") : 'NULL';
            $ward = isset($hospitalHistory["ward"]) ? QB::wrapString($hospitalHistory["ward"], "'") : 'NULL';
            $dateDischarged = isset($hospitalHistory["dateDischarged"]) ? QB::wrapString($hospitalHistory["dateDischarged"], "'") : 'NULL';
            $dischargedTo = isset($hospitalHistory["dischargedTo"]) ? QB::wrapString($hospitalHistory["dischargedTo"], "'") : 'NULL';
            $condition = isset($hospitalHistory["condition"]) ? QB::wrapString($hospitalHistory["condition"], "'") : 'NULL';

            $historyArray[] = "($patientId, $dateAttended, $referredBy, $physician, $ward, $dateDischarged, $dischargedTo, $condition)";
        }

        $query = "INSERT INTO Patients.PatientHospitalHistory VALUES ".implode(", ", $historyArray);
        
        $queryResult = (
            DBConnectionFactory::getConnection()
            ->exec($query)
        );

        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_INSERT,
            'Patients',
            'PatientHospitalHistory',
            $query
        );

        return $queryResult;
    }
}