<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class BillingHmoSalesVerification.
 *
 * BillingHmoSalesVerification Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoSalesVerification
{
	public static function newHmoSalesVerification(array $data)
	{
		return HmoSalesVerification\HmoSalesVerification::create($data);
	}

	public static function viewHmoSalesVerification(int $resourceId=0, array $data = [])
	{
		return HmoSalesVerification\HmoSalesVerification::view($resourceId, $data);
	}

	public static function getStatus(array $data)
	{
		return HmoSalesVerification\HmoSalesVerification::getStatus($data);
	}

	public static function loadUnprocessedRequests(int $resourceId)
	{
		return HmoSalesVerification\HmoSalesVerification::loadUnprocessedRequests($resourceId);
	}

	public static function loadAllRequests()
	{
		return HmoSalesVerification\HmoSalesVerification::loadAllRequests();
	}

	public static function verifyRequest(array $data)
	{
		return HmoSalesVerification\HmoSalesVerification::verifyRequest($data);
	}
}