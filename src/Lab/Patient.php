<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab;

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
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Patient
{
	public static function newPatient(array $data)
	{
		return Patient\Patient::create($data);
	}

	public static function viewPatient(int $resourceId = 0, array $data=[])
	{
		return Patient\Patient::view($resourceId, $data);
	}
	public static function editPatient(int $resourceId, array $data)
	{
		return Patient\Patient::edit($resourceId, $data);
	}

	public static function deletePatient(int $resourceId)
	{
		return Patient\Patient::delete($resourceId);
	}
}