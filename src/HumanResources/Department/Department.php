<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\Department;

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
 * class NewDepartment.
 *
 * NewDepartment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Department
{
    /**
     * Determines if a login data is valid
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $groupId = $data['groupId'];
        $name = $data['name'];

        try
        {
            $result = DBQueryFactory::insert('Staffs.Department', [
                'Name'=>QB::wrapString($name, "'"),
                'GroupID'=>$groupId
            ]);

            if ($result['lastInsertId'])
            {
                (new Permission())->add('role', $name);
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
            $data["Name"] = QB::wrapString($data["Name"], "'");
            $updateBuilder->table("Staffs.Department");
            $updateBuilder->set($data);
            $updateBuilder->where("DepartmentID = $resourceId");

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
            
            $selectBuilder->from("Staffs.Department a")->innerJoin("Staffs.DepartmentGroup b", "a.GroupID = b.DepartmentGroupID");

            if ($resourceId !== 0){
                $selectBuilder->where("a.DepartmentID = $resourceId");
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

    public static function viewByGroup(int $group, array $data = [])
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
            
            $selectBuilder->from("Staffs.Department a")->innerJoin("Staffs.DepartmentGroup b", "a.GroupID = b.DepartmentGroupID");

            if ($resourceId !== 0){
                $selectBuilder->where("a.GroupID = $group");
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

    public static function newRootUrl(array $data){
        $department = $data["department"];
        $url = $data["url"];

        $query = "INSERT INTO Staffs.DepartmentRootUrl VALUES ($department, '$url');";
        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static function viewRootUrl(int $department){
        if ($department == 0){
            $query = "SELECT * FROM Staffs.DepartmentRootUrl a INNER JOIN Staffs.Department b ON a.DepartmentID = b.DepartmentID";
        }
        else {
            $query = "SELECT * FROM Staffs.DepartmentRootUrl a INNER JOIN Staffs.Department b ON a.DepartmentID = b.DepartmentID WHERE a.DepartmentID = $department";
        }

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.Department")
                ->where("DepartmentID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$deleteBuilder)
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