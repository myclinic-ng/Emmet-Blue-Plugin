<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientHospitalHistory;

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
class PatientHospitalHistory
{
    /**
     * creats new patient id and generates a unique user id (UUID)
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $dateAttended = $data["dateAttended"] ?? null;
        $referredBy = $data["referredBy"] ?? null;
        $physician = $data["physician"] ?? null;
        $ward = $data["ward"] ?? null;
        $dateDischarged = $data["dateDischarged"] ?? null;
        $dischargedTo = $data["dischargedTo"] ?? null;
        $condition = $data["condition"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientHospitalHistory', [
                'PatientID'=>$patient,
                'DateAttended'=>(is_null($dateAttended)) ? 'NULL' : QB::wrapString($dateAttended, "'"),
                'ReferredBy'=>(is_null($referredBy)) ? 'NULL' : QB::wrapString($referredBy, "'"),
                'Physician'=>(is_null($physician)) ? 'NULL' : QB::wrapString($physician, "'"),
                'Ward'=>(is_null($ward)) ? 'NULL' : QB::wrapString($ward, "'"),
                'DateDischarged'=>(is_null($dateDischarged)) ? 'NULL' : QB::wrapString($dateDischarged, "'"),
                'DischargedTo'=>(is_null($dischargedTo)) ? 'NULL' : QB::wrapString($dischargedTo, "'"),
                'Condition'=>(is_null($condition)) ? 'NULL' : QB::wrapString($condition, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientHospitalHistory',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a field title type
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Patients.PatientHospitalHistory");
            $updateBuilder->set($data);
            $updateBuilder->where("HospitalHistoryID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view patients UUID
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientHospitalHistory');
        if ($resourceId != 0){
            $selectBuilder->where('PatientHospitalHistoryID ='.$resourceId);
        }
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientHospitalHistory',
                (string)serialize($selectBuilder)
            );

            return $result;

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

    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientHospitalHistory")
                ->where("HospitalHistoryID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientHospitalHistory',
                (string)$deleteBuilder
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}