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

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class DepartmentGroup.
 *
 * DepartmentGroup Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class DepartmentGroup
{
	/**
	 * Creates a new department group
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newDepartmentGroup(array $data)
    {
        $result = DepartmentGroup\DepartmentGroup::create($data);

        return $result;
    }

    /**
     * Edits a department group
     */
    public static function editDepartmentGroup(int $resourceId, array $data)
    {
    	$result = DepartmentGroup\DepartmentGroup::edit($resourceId, $data);

    	return $result;
    }

    /**
     * Selects department group(s)
     */
    public static function viewDepartmentGroup(int $resourceId=0, array $data = [])
    {
    	$result = DepartmentGroup\DepartmentGroup::view($resourceId, $data);

    	return $result;
    }

    /**
     * Deletes a department group
     */
    public static function deleteDepartmentGroup(int $resourceId)
    {
    	$result = DepartmentGroup\DepartmentGroup::delete($resourceId);

    	return $result;
    }
}