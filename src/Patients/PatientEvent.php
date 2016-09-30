<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
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
 * class PatientEvent.
 *
 * PatientEvent Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 30/09/2016
 */
class PatientEvent
{
	/**
	 * Creates a new Patient Event request id
	 *
	 * @param $_POST
	 */
    public static function newPatientEvent(array $data)
    {
        $result = PatientEvent\PatientEvent::create($data);

        return $result;
    }

    /**
     * Selects Patient Event request id
     */
    public static function viewPatientEvent(int $resourceId=0)
    {
        $result = PatientEvent\PatientEvent::view($resourceId);

        return $result;
    }

    /**
     * Deletes a Patient Event request id
     */
    public static function deletePatientEvent(int $resourceId)
    {
    	$result = PatientEvent\PatientEvent::delete($resourceId);

    	return $result;
    }
}