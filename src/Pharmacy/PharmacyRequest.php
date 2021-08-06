<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy;

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
 * class PharmacyRequest.
 *
 * PharmacyRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PharmacyRequest
{
	public static function newPharmacyRequest(array $data)
	{
		return PharmacyRequest\PharmacyRequest::create($data);
	}

	public static function viewPharmacyRequest(int $resourceId = 0, array $data=[])
	{
		return PharmacyRequest\PharmacyRequest::view($resourceId, $data);
	}
	public static function editPharmacyRequest(int $resourceId, array $data)
	{
		return PharmacyRequest\PharmacyRequest::edit($resourceId, $data);
	}

	public static function deletePharmacyRequest(int $resourceId)
	{
		return PharmacyRequest\PharmacyRequest::delete($resourceId);
	}

	public static function close(int $resourceId, array $data=[])
	{
		return PharmacyRequest\PharmacyRequest::close($resourceId, $data);
	}

	public static function declineRequest(int $resourceId, array $data=[])
	{
		return PharmacyRequest\PharmacyRequest::declineRequest($resourceId,$data);
	}

	public static function smartify(array $data)
	{
		return PharmacyRequest\PharmacyRequest::smartify($data);
	}
}