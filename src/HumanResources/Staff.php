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
 * class Staff.
 *
 * Staff Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Staff
{
    /**
     * Creates a new staff
     *
     * @param array $data Dept. Group Data
     */
    public static function newStaff(array $data)
    {
        $result = Staff\Staff::create($data);

        return $result;
    }

    public static function newStaffWithDepartmentAndRole(array $data)
    {
        $result = Staff\Staff::createWithDepartmentAndRole($data);

        return $result;
    }
    /*view file profile*/
    public static function viewStaffWithDepartmentAndRole(array $data)
    {
        $id = $data["uuid"];
        $result = Staff\Staff::viewStaffWithDepartmentAndRole($id);

        return $result;
    }
    /**
     * Deletes a staff
     */
    public static function deleteStaff(int $resourceId)
    {
        $result = Staff\Staff::delete($resourceId);

        return $result;
    }
}