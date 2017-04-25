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
 * class InvestigationType.
 *
 * InvestigationType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class InvestigationType
{
	public static function newInvestigationType(array $data)
	{
		return InvestigationType\InvestigationType::create($data);
	}

	public static function viewInvestigationType(int $resourceId = 0)
	{
		return InvestigationType\InvestigationType::view($resourceId);
	}

	public static function viewByLab(int $resourceId = 0)
	{
		return InvestigationType\InvestigationType::viewByLab($resourceId);
	}
	public static function editInvestigationType(int $resourceId, array $data)
	{
		return InvestigationType\InvestigationType::edit($resourceId, $data);
	}

	public static function deleteInvestigationType(int $resourceId)
	{
		return InvestigationType\InvestigationType::delete($resourceId);
	}
}