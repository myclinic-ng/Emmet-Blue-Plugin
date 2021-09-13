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
 * class Statistics.
 *
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Statistics
{
	public static function daysSinceLastGlobalRestock(int $resourceId)
	{
		return Statistics\GlobalRestock::daysSinceLastRestock($resourceId);
	}

	public static function itemQuantityDuringLastRestock(int $resourceId)
	{
		return Statistics\GlobalRestock::itemQuantityDuringLastRestock($resourceId);
	}

	public static function totalItemCountInStore(int $resourceId)
	{
		return Statistics\Store::totalItemCount($resourceId);
	}

	public static function outOfStockItems(int $resourceId){
		return Statistics\Store::outOfStockItems($resourceId);
	}

	public static function stockValues(int $resourceId){
		return Statistics\Store::stockValues($resourceId);
	}

	public static function salesValues(array $data=[]){
		return Statistics\Store::salesValues($data);
	}
}