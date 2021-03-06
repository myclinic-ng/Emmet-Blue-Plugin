<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\Role;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class NewRole.
 *
 * NewRole Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Role
{

    private static function convertToCamelString(string $string){
        $string = explode(" ", $string);
        $sKey = strtolower($string[0]);
        unset($string[0]);
        foreach ($string as $key=>$value){
            $string[$key] = ucfirst(strtolower($value));
        }
        $string = $sKey.implode("", $string);

        return $string;
    }

    /**
     * Determines if a login data is valid
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $name = $data['name'];
        $department = $data['department'];
        $description = $data["description"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Staffs.Role', [
                'Name'=>QB::wrapString($name, "'"),
                'DepartmentID'=>$department,
                'Description'=>(is_null($description)) ? "NULL" : QB::wrapString($description, "'")
            ]);

            if ($result){
                $query = "SELECT Name FROM Staffs.Department WHERE DepartmentID = $department";
                $department = (DBConnectionFactory::getConnection()->query($query))->fetchAll(\PDO::FETCH_ASSOC)[0]["Name"];
                $department = self::convertToCamelString($department); //str_replace(" ", "", strtolower($department));
                $role = self::convertToCamelString($name); //str_replace(" ", "", strtolower($name));
                $aclRole = $department."_".$role;

                return \EmmetBlue\Plugins\Permission\ManagePermissions::addRole($aclRole);
            }
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (new department group creation request), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $data['Name'] = QB::wrapString($data['Name'], "'");
            if (isset($data['Description'])){
                $data['Description'] = QB::wrapString($data['Description'], "'");
            }
            $updateBuilder->table("Staffs.Role");
            $updateBuilder->set($data);
            $updateBuilder->where("RoleID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Staffs.Role");

            if ($resourceId !== 0){
                $selectBuilder->where("RoleID = $resourceId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function viewByDepartment(int $departmentId, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Staffs.Role");

            if ($departmentId !== 0){
                $selectBuilder->where("DepartmentID = $departmentId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function delete(int $resourceId)
    {
        $query = "SELECT a.Name as RoleName, b.Name as DepartmentName FROM Staffs.Role a INNER JOIN Staffs.Department b ON a.DepartmentID = b.DepartmentID WHERE a.RoleID = $resourceId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];

        $role = self::convertToCamelString($result["RoleName"]);
        $department = self::convertToCamelString($result["DepartmentName"]);
        $aclRole = $department."_".$role;

        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.Role")
                ->where("RoleID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$deleteBuilder)
                );

            return \EmmetBlue\Plugins\Permission\ManagePermissions::removeRole($aclRole);
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