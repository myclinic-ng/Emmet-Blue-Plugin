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
 * class Patient.
 *
 * Patient Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class Patient
{
	/**
	 * Creates a new Patient group
	 *
	 * @param $_POST
	 */
    public static function newPatientUUID($data)
    {
        $result = Patient\Patient::create($data);

        return $result;
    }

    /**
     * Selects Patient UUID(s)
     */
    public static function viewPatientUUID(int $resourceId=0)
    {
        $result = Patient\Patient::view($resourceId);

        return $result;
    }

    /**
     * Deletes a Patient UUID
     */
    public static function deletePatientUUID(int $resourceId)
    {
    	$result = Patient\Patient::delete($resourceId);

    	return $result;
    }
}