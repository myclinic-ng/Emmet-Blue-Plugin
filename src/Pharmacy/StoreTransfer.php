<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
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
 * class RestockHistory.
 *
 * store and RestockHistory properties Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StoreRestockHistory
{
	public static function newStoreRestockHistory(array $data)
	{
		return StoreRestockHistory\StoreRestockHistory::create($data);
	}

	public static function viewStoreRestockHistory(int $resourceId=0, array $data = [])
	{
		return StoreRestockHistory\StoreRestockHistory::view($resourceId, $data);
	}

	public static function viewByStore(int $resourceId=0, array $data = [])
	{
		return StoreRestockHistory\StoreRestockHistory::viewByStore($resourceId, $data);
	}

	public static function deleteStoreRestockHistory(int $resourceId)
	{
		return StoreRestockHistory\storeRestockHistory::delete($resourceId);
	}

	public static function editStoreRestockHistory(int $resourceId, array $data)
    {
        $result = StoreRestockHistory\StoreRestockHistory::editStoreRestockHistory($resourceId, $data);

        return $result;
    }
    public static function editStoreRestockHistoryTags(int $resourceId, array $data)
    {
        $result = StoreRestockHistory\StoreRestockHistory::editStoreRestockHistoryTags($resourceId, $data);

        return $result;
    }
}