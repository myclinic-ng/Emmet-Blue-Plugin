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
 * class AdmissionBillingItems.
 *
 * AdmissionBillingItems Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AdmissionBillingItems
{
	public static function newAdmissionBillingItems(array $data)
	{
		return AdmissionBillingItems\AdmissionBillingItems::create($data);
	}

	public static function viewAdmissionBillingItems(int $resourceId = 0)
	{
		return AdmissionBillingItems\AdmissionBillingItems::view($resourceId);
	}
	
	public static function editAdmissionBillingItems(int $resourceId, array $data)
	{
		return AdmissionBillingItems\AdmissionBillingItems::edit($resourceId, $data);
	}

	public static function deleteAdmissionBillingItems(int $resourceId)
	{
		return AdmissionBillingItems\AdmissionBillingItems::delete($resourceId);
	}
}