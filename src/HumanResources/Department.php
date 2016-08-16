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
 * class Department.
 *
 * Department Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Department
{
	/**
	 * Creates a new department group
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newDepartment(array $data)
    {
        $result = Department\Department::create($data);

        return $result;
    }

    /**
     * Edits a department group
     */
    public static function editDepartment(int $resourceId, array $data)
    {
    	$result = Department\Department::edit($resourceId, $data);

    	return $result;
    }

    /**
     * Selects department group(s)
     */
    public static function viewDepartment(int $resourceId=0, array $data = [])
    {
        $result = Department\Department::view($resourceId, $data);

        return $result;
    }

    public static function viewByGroup(int $resourceId, array $data = [])
    {
        $result = Department\Department::viewByGroup($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a department group
     */
    public static function deleteDepartment(int $resourceId)
    {
    	$result = Department\Department::delete($resourceId);

    	return $result;
    }
}