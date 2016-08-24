<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Store;

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
 * class Store.
 *
 * store and Inventory properties Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Store
{
	public static function newStore(array $data)
	{
		return Store\Store::create($data);
	}

	public static function viewStore(int $resourceId=0, array $data = [])
	{
		return Store\Store::view($resourceId, $data);
	}

	public static function deleteStore(int $resourceId)
	{
		return Store\Store::delete($resourceId);
	}

	public static function editStore(int $resourceId, array $data)
    {
        $result = Store\Store::edit($resourceId, $data);

        return $result;
    }
    public static function editStoreInventoryProperties(int $resourceId, array $data)
    {
        $result = Store\Store::edit($resourceId, $data);

        return $result;
    }
}