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
 * class PatientHospitalHistory.
 *
 * PatientHospitalHistory Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class PatientHospitalHistory
{
	/**
	 * Creates a new PatientHospitalHistory group
	 *
	 * @param $_POST
	 */
    public static function newPatientHospitalHistory(array $data)
    {
        $result = PatientHospitalHistory\PatientHospitalHistory::create($data);

        return $result;
    }

    /**
     * Selects PatientHospitalHistory UUID(s)
     */
    public static function viewPatientHospitalHistory(int $resourceId=0)
    {
        $result = PatientHospitalHistory\PatientHospitalHistory::view($resourceId);

        return $result;
    }
    /**
     * edits PatientHospitalHistory
     */
    public static function editPatientHospitalHistory(int $resourceId=0)
    {
        $result = PatientHospitalHistory\PatientHospitalHistory::edit($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientHospitalHistory UUID
     */
    public static function deletePatientHospitalHistory(int $resourceId)
    {
    	$result = PatientHospitalHistory\PatientHospitalHistory::delete($resourceId);

    	return $result;
    }
}