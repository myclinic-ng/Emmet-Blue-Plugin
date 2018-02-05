<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
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
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 06/09/2017 11:28
 */
class InventoryLabel
{
	public static function newLabel(array $data)
	{
		return InventoryLabel\InventoryLabel::create($data);
	}

	public static function newDispensation(array $data){
		return InventoryLabel\InventoryLabel::newDispensation($data);
	}

	public static function getLabelDetails(array $data){
		return InventoryLabel\InventoryLabel::getLabelDetails($data);
	}

	public static function getPrintableLabels(int $resourceId, array $data){
		return InventoryLabel\InventoryLabel::getPrintableLabels($resourceId, $data);
	}

	public static function printLabels(array $data){
		return InventoryLabel\InventoryLabel::printLabels($data);
	}
}