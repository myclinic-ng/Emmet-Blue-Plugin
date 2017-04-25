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
 * class Patient.
 *
 * Patient Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class Patient
{
	/**
	 * Creates a new Patient group
	 *
	 * @param $_POST
	 */
    public static function newPatient(array $data)
    {
        $result = Patient\Patient::create($data);

        return $result;
    }

    /**
     * Selects Patient UUID(s)
     */
    public static function viewPatient(int $resourceId=0)
    {
        $result = Patient\Patient::view($resourceId);

        return $result;
    }

    public static function viewByPatientType(int $resourceId=0)
    {
        $result = Patient\Patient::viewByPatientType($resourceId);

        return $result;
    }

    public static function viewHospitalHistory(int $patientId)
    {
        $result = Patient\HospitalHistory::view($patientId);

        return $result;
    }

    public static function lastVisit(int $patientId)
    {
        $result = Patient\HospitalHistory::lastVisit($patientId);

        return $result;
    }

    public static function search(array $data)
    {
        $result = Patient\Patient::search($data);

        return $result;
    }

    public static function updatePhoto(array $data)
    {
        $result = Patient\Patient::updatePhoto($data);

        return $result;
    }

    public static function getImage(array $data){
        $result = Patient\Patient::getImage($data);

        return $result;
    }

    public static function lockProfile(array $data)
    {
        $result = Patient\Patient::lockProfile($data);

        return $result;
    }

    public static function unlockProfile(array $data)
    {
        $result = Patient\Patient::unlockProfile($data);

        return $result;
    }

    /**
     * edits Patient
     */
    public static function editPatient(int $resourceId=0)
    {
        $result = Patient\Patient::edit($resourceId);

        return $result;
    }

    /**
     * edits Patient
     */
    public static function editPatientRecordsFieldValue(array $data)
    {
        $result = Patient\Patient::editPatientRecordsFieldValue($data);

        return $result;
    }

    /**
     * Deletes a Patient UUID
     */
    public static function deletePatient(int $resourceId)
    {
        $result = Patient\Patient::delete($resourceId);

        return $result;
    }

    public static function viewUnlockedProfiles(array $data = [])
    {
        $result = Patient\Patient::getUnlockedProfiles($data);

        return $result;
    }

    public static function retrieveLockStatus(int $resourceId)
    {
        $result = Patient\Patient::retrieveLockStatus($resourceId);

        return $result;
    }


    public static function changeType(array $data){
        return Patient\Patient::changeType($data);
    }

    public static function viewRecordFields(int $data){
        return Patient\Patient::viewRecordFields($data);
    }
}