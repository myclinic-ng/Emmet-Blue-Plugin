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
 * class PatientDiagnosis.
 *
 * PatientDiagnosis Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class PatientDiagnosis
{
	/**
	 * Creates a new PatientDiagnosis group
	 *
	 * @param $_POST
	 */
    public static function newPatientDiagnosis(array $data)
    {
        $result = PatientDiagnosis\PatientDiagnosis::create($data);

        return $result;
    }

    /**
     * Selects PatientDiagnosis UUID(s)
     */
    public static function viewPatientDiagnosis(int $resourceId=0)
    {
        $result = PatientDiagnosis\PatientDiagnosis::view($resourceId);

        return $result;
    }
    /**
     * edits PatientDiagnosis
     */
    public static function editPatientDiagnosis(int $resourceId=0)
    {
        $result = PatientDiagnosis\PatientDiagnosis::edit($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientDiagnosis UUID
     */
    public static function deletePatientDiagnosis(int $resourceId)
    {
    	$result = PatientDiagnosis\PatientDiagnosis::delete($resourceId);

    	return $result;
    }
}