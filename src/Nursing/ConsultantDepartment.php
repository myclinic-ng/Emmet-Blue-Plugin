<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing;

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
 * class ConsultantDepartment.
 *
 * ConsultantDepartment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class ConsultantDepartment
{
	public static function newConsultantDepartment(array $data)
	{
		return ConsultantDepartment\ConsultantDepartment::create($data);
	}

	public static function viewConsultantDepartment(int $resourceId = 0)
	{
		return ConsultantDepartment\ConsultantDepartment::view($resourceId);
	}
	
	public static function editConsultantDepartment(int $resourceId, array $data)
	{
		return ConsultantDepartment\ConsultantDepartment::edit($resourceId, $data);
	}

	public static function deleteConsultantDepartment(int $resourceId)
	{
		return ConsultantDepartment\ConsultantDepartment::delete($resourceId);
	}
}