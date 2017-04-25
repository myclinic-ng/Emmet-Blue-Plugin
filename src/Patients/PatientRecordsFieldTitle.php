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
 * class PatientRecordsFieldTitle.
 *
 * PatientRecordsFieldTitle Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class PatientRecordsFieldTitle
{
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newPatientRecordsFieldTitle(array $data)
    {
        $result = PatientRecordsFieldTitle\PatientRecordsFieldTitle::create($data);

        return $result;
    }

    /**
     * edits PatientRecordsFieldTitle
     */
    public static function editPatientRecordsFieldTitle(int $resourceId=0)
    {
        $result = PatientRecordsFieldTitle\PatientRecordsFieldTitle::edit($resourceId);

        return $result;
    }

    /**
     * Selects PatientRecordsFieldTitle
     */
    public static function viewPatientRecordsFieldTitle(int $resourceId=0)
    {
        $result = PatientRecordsFieldTitle\PatientRecordsFieldTitle::view($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientRecordsFieldTitle
     */
    public static function deletePatientRecordsFieldTitle(int $resourceId)
    {
    	$result = PatientRecordsFieldTitle\PatientRecordsFieldTitle::delete($resourceId);

    	return $result;
    }
}