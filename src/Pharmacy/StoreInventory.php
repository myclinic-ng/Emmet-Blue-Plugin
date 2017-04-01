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
 * class Inventory.
 *
 * store and Inventory properties Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StoreInventory
{
	public static function newStoreInventory(array $data)
	{
		return StoreInventory\StoreInventory::create($data);
	}

	public static function newStoreInventoryTags(array $data)
	{
		return StoreInventory\StoreInventory::createInventoryTags($data);
	}

	public static function viewStoreInventory(int $resourceId=0, array $data = [])
	{
		return StoreInventory\StoreInventory::view($resourceId, $data);
	}

	public static function viewByStore(int $resourceId=0, array $data = [])
	{
		return StoreInventory\StoreInventory::viewByStore($resourceId, $data);
	}

	public static function viewAvailableItemsByStore(int $resourceId=0, array $data = [])
	{
		return StoreInventory\StoreInventory::viewAvailableItemsByStore($resourceId, $data);
	}

	public static function deleteStoreInventory(int $resourceId)
	{
		return StoreInventory\storeInventory::delete($resourceId);
	}

	public static function editStoreInventory(int $resourceId, array $data)
    {
        $result = StoreInventory\StoreInventory::editStoreInventory($resourceId, $data);

        return $result;
    }
    public static function editStoreInventoryTags(int $resourceId, array $data)
    {
        $result = StoreInventory\StoreInventory::editStoreInventoryTags($resourceId, $data);

        return $result;
    }
    public static function deleteStoreInventoryTag(int $resourceId)
    {
        $result = StoreInventory\StoreInventory::deleteStoreInventoryTag($resourceId);

        return $result;
    }
}