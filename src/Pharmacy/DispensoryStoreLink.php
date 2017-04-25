<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
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
 * class DispensoryStoreLink.
 *
 * DispensoryStoreLink Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class DispensoryStoreLink
{
	public static function newDispensoryStoreLink(array $data)
	{
		return DispensoryStoreLink\DispensoryStoreLink::create($data);
	}

	public static function viewDispensoryStoreLink(int $resourceId=0, array $data = [])
	{
		return DispensoryStoreLink\DispensoryStoreLink::view($resourceId, $data);
	}

	public static function viewByDispensory(int $resourceId=0, array $data = [])
	{
		return DispensoryStoreLink\DispensoryStoreLink::viewByDispensory($resourceId, $data);
	}

	public static function deleteDispensoryStoreLink(int $resourceId)
	{
		return DispensoryStoreLink\DispensoryStoreLink::delete($resourceId);
	}

	public static function editDispensoryStoreLink(int $resourceId, array $data)
    {
        $result = DispensoryStoreLink\DispensoryStoreLink::edit($resourceId, $data);

        return $result;
    }
}