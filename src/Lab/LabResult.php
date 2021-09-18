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
 * class LabResult.
 *
 * LabResult Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class LabResult
{
	public static function newLabResult(array $data)
	{
		return LabResult\LabResult::create($data);
	}
	
	public static function createWithRequestId(array $data)
	{
		return LabResult\LabResult::createWithRequestId($data);
	}

	public static function viewLabResult(int $resourceId = 0)
	{
		return LabResult\LabResult::view($resourceId);
	}

	public static function getResults(array $data)
	{
		return LabResult\LabResult::getResults($data);
	}

	public static function editLabResult(int $resourceId, array $data)
	{
		return LabResult\LabResult::edit($resourceId, $data);
	}

	public static function deleteLabResult(int $resourceId)
	{
		return LabResult\LabResult::delete($resourceId);
	}

	public static function viewByRepositoryId(int $resourceId = 0)
	{
		return LabResult\LabResult::viewByRepositoryId($resourceId);
	}
}