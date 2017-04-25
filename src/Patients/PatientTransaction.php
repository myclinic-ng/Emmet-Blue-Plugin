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
 * class PatientTransaction.
 *
 * PatientTransaction Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 14:2004
 */
class PatientTransaction
{
	/**
	 * Creates a new PatientTransaction request Id
	 *
	 * @param array $data
	 */
    public static function newPatientTransaction(array $data)
    {
        $result = PatientTransaction\PatientTransaction::create($data);

        return $result;
    }

    /**
     * Selects Patienttransaction Id request
     */
    public static function viewPatientTransaction(int $resourceId=0)
    {
        $result = Patient\Patient::view($resourceId);

        return $result;
    }

    /**
     * Deletes a Patient transaction Id request
     */
    public static function deletePatientTransaction(int $resourceId)
    {
    	$result = Patient\Patient::delete($resourceId);

    	return $result;
    }
}