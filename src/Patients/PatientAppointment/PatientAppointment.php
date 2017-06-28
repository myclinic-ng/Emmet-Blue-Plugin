<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientAppointment;

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
 * class PatientAppointment.
 *
 * PatientAppointment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientAppointment
{
    /**
     * creats new Patient Records field titles
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $patient = $data["patient"] ?? null;
        $staff = $data["staff"] ?? null;
        $reason = $data["reason"] ?? null;
        $date = $data["date"] ?? null;
        
        try
        {
            $result = DBQueryFactory::insert('Patients.PatientAppointments', [
                'PatientID'=>$patient,
                'Staff'=>$staff,
                'AppointmentDate'=>QB::wrapString((string)$date, "'"),
                'AppointmentReason'=>(is_null($reason)) ? 'NULL' : QB::wrapString((string)$reason, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientAppointments',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (appointment not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

     /**
     * Modifies the content of a store
     */
    public static function edit(int $resourceId, array $data)
    {
       
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['AppointmentReason'])){
                $data['AppointmentReason'] = QB::wrapString($data['AppointmentReason'], "'");
            }
            if (isset($data['AppointmentDate'])){
                $data['AppointmentDate'] = QB::wrapString($data['AppointmentDate'], "'");
            }

            $updateBuilder->table("Patients.PatientAppointments");
            $updateBuilder->set($data);
            $updateBuilder->where("AppointmentID = $resourceId");

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
     * delete field title type
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientAppointments")
                ->where("AppointmentID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_DELETE,
                'Patients',
                'PatientAppointments',
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

    /**
     * view field title
     */
    public static function viewByPatient(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientAppointments')
            ->where('PatientID ='.$resourceId);
        try
        {
            $selectBuilder = $selectBuilder. " ORDER BY AppointmentDate ASC";
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key => $value) {
                $viewOperation[$key]["staffInfo"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["Staff"]);
                $viewOperation[$key]["staffInfo"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff::viewStaffRole((int) $value["Staff"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientAppointments',
                (string)$selectBuilder
            );

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

    public static function viewByStaff(array $data)
    {
        $resourceId = $data["staff"];
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientAppointments')
            ->where('Staff ='.$resourceId);
        if (isset($data["daterange"])){
            $min = QB::wrapString($data["sdate"], "'");
            $max = QB::wrapString($data["edate"], "'");

            $selectBuilder->andWhere('CAST(AppointmentDate AS DATE) BETWEEN CONVERT(date, '.$min.') AND CONVERT(date, '.$max.')');
        }
        else {
            $selectBuilder->andWhere('CAST(AppointmentDate AS DATE) >= CAST(GETDATE() AS DATE)');
        }
        try
        {
            $selectBuilder = $selectBuilder. " ORDER BY AppointmentDate ASC";
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key => $value) {
                $viewOperation[$key]["patientInfo"] = \EmmetBlue\Plugins\Patients\Patient::viewPatient((int) $value["PatientID"])["_source"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientAppointments',
                (string)$selectBuilder
            );

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