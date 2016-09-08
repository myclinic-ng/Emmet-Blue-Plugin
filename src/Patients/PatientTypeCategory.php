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
 * class PatientTypeCategory.
 *
 * PatientTypeCategory Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class PatientTypeCategory
{
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newPatientTypeCategory(array $data)
    {
        $result = PatientTypeCategory\PatientTypeCategory::create($data);

        return $result;
    }

    /**
     * edits PatientTypeCategory
     */
    public static function editPatientTypeCategory(int $resourceId=0)
    {
        $result = PatientTypeCategory\PatientTypeCategory::edit($resourceId);

        return $result;
    }

    /**
     * Selects PatientTypeCategory
     */
    public static function viewPatientTypeCategory(int $resourceId=0)
    {
        $result = PatientTypeCategory\PatientTypeCategory::view($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientTypeCategory
     */
    public static function deletePatientTypeCategory(int $resourceId)
    {
    	$result = PatientTypeCategory\PatientTypeCategory::delete($resourceId);

    	return $result;
    }
}