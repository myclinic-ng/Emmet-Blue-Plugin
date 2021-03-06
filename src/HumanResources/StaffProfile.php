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
 * class StaffProfile.
 *
 * StaffProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StaffProfile
{
	/**
	 * Creates a new staff
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newStaffProfile(array $data)
    {
        $result = StaffProfile\StaffProfile::create($data);

        return $result;
    }

    public static function viewStaffProfile(int $resourceId=0, array $data = [])
    {
        $result = StaffProfile\StaffProfile::view($resourceId, $data);

        return $result;
    }

    public static function editStaffProfile(int $resourceId=0, array $data = [])
    {
        $result = StaffProfile\StaffProfile::edit($resourceId, $data);

        return $result;
    }

    public static function viewAllStaffs(int $resourceId=0)
    {
        $result = StaffProfile\StaffProfile::viewAllStaffs();

        return $result;
    }

    public static function viewStaffFullName(int $id){
        $result = StaffProfile\StaffProfile::viewStaffFullName($id);

        return $result;
    }

    public static function enrollFingerprint($data = []){
        $result = StaffProfile\StaffProfile::enrollFingerprint($data);

        return $result;
    }

    public static function identifyFingerprint($data = []){
        $result = StaffProfile\StaffProfile::identifyFingerprint($data);

        return $result;
    }
}