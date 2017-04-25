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

                $result2 = DBQueryFactory::insert('Staffs.StaffPassword', [
                    'StaffID'=>$id,
                    'StaffUsername'=>QB::wrapString($username, "'"),
                    'PasswordHash'=>QB::wrapString($password, "'")
                ]);

                if ($result2['lastInsertId'])
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
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function updatePassword(array $data)
    {
        $id = $data['staff'];
        $password = password_hash($data["password"], PASSWORD_DEFAULT);

        try
        {
            $query = "UPDATE Staffs.StaffPassword SET PasswordHash = '$password', ModifiedDate = GETDATE() WHERE StaffID = $id";
            
            $result = DBConnectionFactory::getConnection()->exec($query);
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
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

            \EmmetBlue\Plugins\HumanResources\StaffDepartment::newStaffDepartment($departmentData);

            $roleData = $data["role"];
            $roleData["staffId"] = $result["lastInsertId"];

           \EmmetBlue\Plugins\HumanResources\StaffRole::newStaffRole($roleData);

           return $result;
        }

        return false;
    }

    /* view staff profile */
    public static function viewStaffWithDepartmentAndRole(string $id)
    {
        $staffUUID = QB::wrapString($id,"'");

        try
        {            
    
            $query = "SELECT a.AccountActivated, a.StaffUUID, b.* FROM Staffs.Staff a INNER JOIN (SELECT b.DepartmentID, b.Name, a.StaffID FROM Staffs.StaffDepartment a INNER JOIN Staffs.Department b ON a.DepartmentID=b.DepartmentID) b ON a.StaffID = b.StaffID WHERE a.StaffUUID = $staffUUID";

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
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

    public static function viewStaffRole(int $id)
    {
        try
        {
            $query = "SELECT b.Name FROM Staffs.StaffRole a INNER JOIN Staffs.Role b ON a.RoleID = b.RoleID WHERE a.StaffID = $id";

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);

            if (isset($result[0])){
                $result = $result[0];
            }

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

    public static function viewStaffProfile(int $id){
        $query = "SELECT * FROM Staffs.StaffProfile WHERE StaffID = $id";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function getStaffID(array $data){
        $username = $data["name"];

        $query = "SELECT StaffID FROM Staffs.StaffPassword WHERE StaffUsername = '$username'";
        $result = (
            DBConnectionFactory::getConnection()
            ->query((string)$query)
        )->fetchAll(\PDO::FETCH_ASSOC);

        return $result[0] ?? [];
    }

     /* view staff profile */
    public static function viewStaffRootUrl(int $id)
    {
        try
        {            
    
            $query = "SELECT * FROM Staffs.DepartmentRootUrl a INNER JOIN Staffs.StaffDepartment b ON a.DepartmentID = b.DepartmentID WHERE b.StaffID = $id";

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);

            if (isset($result[0])){
                $result = $result[0];
            }
            else {
                throw new \Exception("Root Url Not Found");
            }

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

    /*view staff based on dept*/
    public static function viewDepartmentStaff(int $resourceId){
       /* $query = "SELECT a.StaffUsername, c.Name,d.GroupName,e.* FROM Staffs.StaffPassword a JOIN Staffs.StaffDepartment b ON a.StaffID=b.StaffID JOIN Staffs.Department c ON c.DepartmentID=b.DepartmentID JOIN Staffs.DepartmentGroup d ON d.DepartmentGroupID=c.GroupID JOIN Staffs.Staff e ON a.StaffID=e.StaffID WHERE b.DepartmentID=9"*/
       try
        {            
    
            $query = "SELECT a.StaffUsername, c.Name,d.GroupName,e.* FROM Staffs.StaffPassword a JOIN Staffs.StaffDepartment b ON a.StaffID=b.StaffID JOIN Staffs.Department c ON c.DepartmentID=b.DepartmentID JOIN Staffs.DepartmentGroup d ON d.DepartmentGroupID=c.GroupID JOIN Staffs.Staff e ON a.StaffID=e.StaffID WHERE d.DepartmentGroupID=$resourceId";

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
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

    public static function viewStaffsWithNoProfile(){
        $query = "SELECT a.StaffID, a.StaffUsername FROM Staffs.StaffPassword a LEFT JOIN Staffs.StaffProfile b ON a.StaffID = b.StaffID WHERE B.StaffID IS NULL";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
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