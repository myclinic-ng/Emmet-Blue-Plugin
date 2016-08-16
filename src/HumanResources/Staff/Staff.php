<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\Staff;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class NewStaff.
 *
 * NewStaff Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Staff
{
	/**
	 * Determines if a login data is valid
	 *
	 * @param array $data
	 */
    public static function create(array $data)
    {
        $uuid = substr(str_shuffle(MD5(microtime())), 0, 20);
        $username = $data['username'];
        $password = password_hash($data["password"], PASSWORD_DEFAULT);

        try
        {
        	$result = DBQueryFactory::insert('Staffs.Staff', [
                'StaffUUID'=>QB::wrapString($uuid, "'")
            ]);
            
            if ($result['lastInsertId'])
            {
                $id = $result['lastInsertId'];

                $result = DBQueryFactory::insert('Staffs.StaffPassword', [
                    'StaffID'=>$id,
                    'StaffUsername'=>QB::wrapString($username, "'"),
                    'PasswordHash'=>QB::wrapString($password, "'")
                ]);

                if ($result['lastInsertId'])
                {
                    (new Permission())->add('role', $uuid);

                    return $result;
                }
            }
            return false;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (new department group creation request), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function createWithDepartmentAndRole(array $data)
    {
        $staffData = $data["staff"];
        $result = self::create($staffData);
        if (isset($result['lastInsertId'])){
            $departmentData = $data["department"];
            $departmentData["staffId"] = $result["lastInsertId"];

            \EmmetBlue\Plugins\HumanResources\StaffDepartment::newStaffDepartent($departmentData);

            $roleData = $data["role"];
            $roleData["staffId"] = $result["lastInsertId"];

           \EmmetBlue\Plugins\HumanResources\StaffRole::newStaffRole($roleData);

           return $result;
        }
        return false;
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.Staff")
                ->where("StaffID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}