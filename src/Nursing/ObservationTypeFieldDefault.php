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
 * class ObservationTypeFieldDefault.
 *
 * ObservationTypeFieldDefault Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class ObservationTypeFieldDefault
{
	public static function newObservationTypeFieldDefault(array $data)
	{
		return ObservationTypeFieldDefault\ObservationTypeFieldDefault::create($data);
	}

	public static function viewObservationTypeFieldDefault(int $resourceId = 0)
	{
		return ObservationTypeFieldDefault\ObservationTypeFieldDefault::view($resourceId);
	}
	
	public static function editObservationTypeFieldDefault(int $resourceId, array $data)
	{
		return ObservationTypeFieldDefault\ObservationTypeFieldDefault::edit($resourceId, $data);
	}

	public static function deleteObservationTypeFieldDefault(int $resourceId)
	{
		return ObservationTypeFieldDefault\ObservationTypeFieldDefault::delete($resourceId);
	}
}