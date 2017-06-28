<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients;

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
 * class PatientAppointment.
 *
 * PatientAppointment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class PatientAppointment
{
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newPatientAppointment(array $data)
    {
        $result = PatientAppointment\PatientAppointment::create($data);

        return $result;
    }

    /**
     * edits PatientAppointment
     */
    public static function editPatientAppointment(int $resourceId=0, array $data)
    {
        $result = PatientAppointment\PatientAppointment::edit($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a PatientAppointment
     */
    public static function deletePatientAppointment(int $resourceId)
    {
        $result = PatientAppointment\PatientAppointment::delete($resourceId);

        return $result;
    }

    /**
     * Selects PatientAppointment
     */
    public static function viewPatientAppointment(int $patientId)
    {
        $result = PatientAppointment\PatientAppointment::viewByPatient($patientId);

        return $result;
    }

    /**
     * Selects PatientAppointment
     */
    public static function viewByStaff(array $data)
    {
        $result = PatientAppointment\PatientAppointment::viewByStaff($data);

        return $result;
    }
}