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
 * class PatientType.
 *
 * PatientType Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class PatientType
{
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newPatientType(array $data)
    {
        $result = PatientType\PatientType::create($data);

        return $result;
    }

    /**
     * edits PatientType
     */
    public static function editPatientType(int $resourceId=0)
    {
        $result = PatientType\PatientType::edit($resourceId);

        return $result;
    }

    /**
     * Selects PatientType
     */
    public static function viewPatientType(int $resourceId=0)
    {
        $result = PatientType\PatientType::view($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientType
     */
    public static function deletePatientType(int $resourceId)
    {
    	$result = PatientType\PatientType::delete($resourceId);

    	return $result;
    }
}