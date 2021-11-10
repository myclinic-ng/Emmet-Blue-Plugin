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
 * class LabRequest.
 *
 * LabRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class LabRequest
{
	public static function newLabRequest(array $data)
	{
		return LabRequest\LabRequest::create($data);
	}

	public static function newExternalLabRequest(array $data)
	{
		return LabRequest\LabRequest::newExternalLabRequest($data);
	}

	public static function viewLabRequest(int $resourceId = 0, array $data = [])
	{
		return LabRequest\LabRequest::view($resourceId, $data);
		
	}

	public static function viewByPatient(int $resourceId = 0, array $data)
	{
		return LabRequest\LabRequest::viewByPatient($resourceId, $data);
	}

	public static function editLabRequest(int $resourceId, array $data)
	{
		return LabRequest\LabRequest::edit($resourceId, $data);
	}

	public static function deleteLabRequest(int $resourceId)
	{
		return LabRequest\LabRequest::delete($resourceId);
	}

	public static function closeRequest(array $data)
	{
		return LabRequest\LabRequest::closeRequest($data);
	}

	public static function closeMultipleRequests(array $data)
	{
		return LabRequest\LabRequest::closeMultipleRequests($data);
	}
}