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
 * class ObservationTypeFieldDirtyValue.
 *
 * ObservationTypeFieldDirtyValue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class ObservationTypeFieldDirtyValue
{
	public static function newObservationTypeFieldDirtyValue(array $data)
	{
		return ObservationTypeFieldDirtyValue\ObservationTypeFieldDirtyValue::create($data);
	}

	public static function viewObservationTypeFieldDirtyValue(int $resourceId = 0)
	{
		return ObservationTypeFieldDirtyValue\ObservationTypeFieldDirtyValue::view($resourceId);
	}
	
	public static function editObservationTypeFieldDirtyValue(int $resourceId, array $data)
	{
		return ObservationTypeFieldDirtyValue\ObservationTypeFieldDirtyValue::edit($resourceId, $data);
	}

	public static function deleteObservationTypeFieldDirtyValue(int $resourceId)
	{
		return ObservationTypeFieldDirtyValue\ObservationTypeFieldDirtyValue::delete($resourceId);
	}

	public static function containsDirt(array $data){
		return ObservationTypeFieldDirtyValue\ObservationTypeFieldDirtyValue::containsDirt($data);
	}
}