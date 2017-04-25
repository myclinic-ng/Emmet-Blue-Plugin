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
 * class ObservationTypeField.
 *
 * ObservationTypeField Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class ObservationTypeField
{
	public static function newObservationTypeField(array $data)
	{
		return ObservationTypeField\ObservationTypeField::create($data);
	}

	public static function viewObservationTypeField(int $resourceId = 0)
	{
		return ObservationTypeField\ObservationTypeField::view($resourceId);
	}

	public static function viewTypes(int $resourceId = 0)
	{
		return ObservationTypeField\ObservationTypeField::viewTypes();
	}
	
	public static function editObservationTypeField(int $resourceId, array $data)
	{
		return ObservationTypeField\ObservationTypeField::edit($resourceId, $data);
	}

	public static function deleteObservationTypeField(int $resourceId)
	{
		return ObservationTypeField\ObservationTypeField::delete($resourceId);
	}
}