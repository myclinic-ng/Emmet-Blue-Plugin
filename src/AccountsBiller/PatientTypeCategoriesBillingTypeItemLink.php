<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
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
 * class AccountsPatientTypeCategoriesBillingTypeItemLink.
 *
 * AccountsPatientTypeCategoriesBillingTypeItemLink Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 31/10/2021 18:21
 */
class PatientTypeCategoriesBillingTypeItemLink
{
	public static function newLink(array $data)
	{
		return PatientTypeCategoriesBillingTypeItemLink\PatientTypeCategoriesBillingTypeItemLink::create($data);
	}

	public static function viewByCategory(int $resourceId=0, array $data = [])
	{
		return PatientTypeCategoriesBillingTypeItemLink\PatientTypeCategoriesBillingTypeItemLink::viewByCategory($resourceId, $data);
	}

	public static function delete(int $resourceId=0)
	{
		return PatientTypeCategoriesBillingTypeItemLink\PatientTypeCategoriesBillingTypeItemLink::delete($resourceId);
	}
}