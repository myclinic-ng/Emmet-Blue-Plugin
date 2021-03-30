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
 * class PatientMedicalHighlight.
 *
 * PatientMedicalHighlight Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 30/03/2021 19:53
 */
class PatientMedicalHighlight
{
	/**
	 * Creates a new PatientMedicalHighlight group
	 *
	 * @param $_POST
	 */
    public static function newPatientMedicalHighlight(array $data)
    {
        $result = PatientMedicalHighlight\PatientMedicalHighlight::create($data);

        return $result;
    }

    /**
     * Selects PatientMedicalHighlight UUID(s)
     */
    public static function viewPatientMedicalHighlight(int $resourceId=0)
    {
        $result = PatientMedicalHighlight\PatientMedicalHighlight::view($resourceId);

        return $result;
    }
    /**
     * edits PatientMedicalHighlight
     */
    public static function editPatientMedicalHighlight(int $resourceId=0)
    {
        $result = PatientMedicalHighlight\PatientMedicalHighlight::edit($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientMedicalHighlight UUID
     */
    public static function deletePatientMedicalHighlight(int $resourceId)
    {
    	$result = PatientMedicalHighlight\PatientMedicalHighlight::delete($resourceId);

    	return $result;
    }
}