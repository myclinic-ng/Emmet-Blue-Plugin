<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources;

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
 * class Role.
 *
 * Role Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Role
{
	/**
	 * Creates a new role
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newRole(array $data)
    {
        $result = Role\Role::create($data);

        return $result;
    }

    /**
     * Edits a role
     */
    public static function editRole(int $resourceId, array $data)
    {
    	$result = Role\Role::edit($resourceId, $data);

    	return $result;
    }

    /**
     * Selects role(s)
     */
    public static function viewRole(int $resourceId=0, array $data = [])
    {
        $result = Role\Role::view($resourceId, $data);

        return $result;
    }

    public static function viewByDepartment(int $resourceId, array $data = [])
    {
        $result = Role\Role::viewByDepartment($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a role
     */
    public static function deleteRole(int $resourceId)
    {
    	$result = Role\Role::delete($resourceId);

    	return $result;
    }
}