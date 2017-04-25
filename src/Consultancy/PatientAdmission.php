<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
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
 * class PatientAdmission.
 *
 * PatientAdmission Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class PatientAdmission
{
    public static function newPatientAdmission(array $data)
    {
        $result = PatientAdmission\PatientAdmission::create($data);

        return $result;
    }

    public static function viewAdmittedPatients(int $resourceId=0, array $data = [])
    {
        $result = PatientAdmission\PatientAdmission::viewAdmittedPatients($resourceId, $data);

        return $result;
    }

    public static function discharge(array $data)
    {
        $result = PatientAdmission\PatientAdmission::discharge($data);

        return $result;
    }

    public static function clearForDischarge(int $resourceId)
    {
        $result = PatientAdmission\PatientAdmission::clearForDischarge($resourceId);

        return $result;
    }

    public static function viewDischargedPatients(int $resourceId=0, array $data = [])
    {
        $result = PatientAdmission\PatientAdmission::viewDischargedPatients($resourceId, $data);

        return $result;
    }

    public static function viewReceivedPatients(int $resourceId=0, array $data = [])
    {
        $result = PatientAdmission\PatientAdmission::viewReceivedPatients($resourceId, $data);

        return $result;
    }

    public static function editPatientAdmission(int $resourceId=0, array $data)
    {
        $result = PatientAdmission\PatientAdmission::editPatientAdmission($resourceId, $data);

        return $result;
    }

    public static function deletePatientAdmission(int $resourceId)
    {
        $result = PatientAdmission\PatientAdmission::delete($resourceId);

        return $result;
    }
}