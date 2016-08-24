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
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFacto
use EmmetBlue\Core\Session\Session;ry;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class PatientRepository.
 *
 * PatientRepository Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class PatientRepository
{
	/**
	 * Creates a new PatientRepository group
	 *
	 * @param $_POST
	 */
    public static function newPatientRepository(array $data)
    {
        $result = PatientRepository\PatientRepository::create($data);

        return $result;
    }

    /**
     * Selects PatientRepository UUID(s)
     */
    public static function viewPatientRepository(int $resourceId=0)
    {
        $result = PatientRepository\PatientRepository::view($resourceId);

        return $result;
    }

    /**
     * Deletes a PatientRepository UUID
     */
    public static function deletePatientRepository(int $resourceId)
    {
    	$result = PatientRepository\PatientRepository::delete($resourceId);

    	return $result;
    }
}