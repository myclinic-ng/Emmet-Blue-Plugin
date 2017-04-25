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
 * class InvestigationTypeField.
 *
 * InvestigationTypeField Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class InvestigationTypeField
{
	public static function newInvestigationTypeField(array $data)
	{
		return InvestigationTypeField\InvestigationTypeField::create($data);
	}

	public static function newDefaultValue(array $data)
	{
		return InvestigationTypeField\InvestigationTypeField::newDefaultValue($data);
	}

	public static function viewInvestigationTypeField(int $resourceId = 0)
	{
		return InvestigationTypeField\InvestigationTypeField::view($resourceId);
	}

	public static function viewFieldTypes(int $resourceId = 0)
	{
		return InvestigationTypeField\InvestigationTypeField::viewFieldTypes($resourceId);
	}

	public static function viewDefaultValues(int $resourceId = 0)
	{
		return InvestigationTypeField\InvestigationTypeField::viewDefaultValues($resourceId);
	}

	public static function editInvestigationTypeField(int $resourceId, array $data)
	{
		return InvestigationTypeField\InvestigationTypeField::edit($resourceId, $data);
	}

	public static function deleteInvestigationTypeField(int $resourceId)
	{
		return InvestigationTypeField\InvestigationTypeField::delete($resourceId);
	}

	public static function deleteDefaultValue(int $data)
	{
		return InvestigationTypeField\InvestigationTypeField::deleteDefaultValue($data);
	}
}