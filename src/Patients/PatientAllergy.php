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
 * class PatientAllergy.
 *
 * PatientAllergy Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 30/09/2016
 */
class PatientAllergy
{
	/**
	 * Creates a new Patient Allergy request id
	 *
	 * @param $_POST
	 */
    public static function newPatientAllergy(array $data)
    {
        $result = PatientAllergy\PatientAllergy::create($data);

        return $result;
    }

    /**
     * Selects Patient Allergy request id
     */
    public static function viewPatientAllergy(int $resourceId)
    {
        $result = PatientAllergy\PatientAllergy::view($resourceId);

        return $result;
    }

}