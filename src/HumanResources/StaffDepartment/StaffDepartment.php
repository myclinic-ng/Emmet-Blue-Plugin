<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\StaffDepartment;

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
 * class NewStaffDepartment.
 *
 * NewStaffDepartment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StaffDepartment
{
	/**
	 * Determines if a login data is valid
	 *
	 * @param array $data
	 */
    public static function create(array $data)
    {
        $staff = $data['staffId'];
        $department = $data['departmentId'];

        try
        {
            $result = DBQueryFactory::insert('Staffs.StaffDepartment', [
                'StaffID'=>$staff,
                'DepartmentID'=>$department
            ]);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (department assignment unsuccessful), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function assignSecondary(array $data)
    {
        $staff = $data['staffId'];
        $department = $data['departmentId'];

        try
        {
            $result = DBQueryFactory::insert('Staffs.StaffSecondaryDepartments', [
                'StaffID'=>$staff,
                'DepartmentID'=>$department
            ]);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (secondary department assignment unsuccessful), %s",
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
            $updateBuilder->table("Staffs.StaffDepartment");
            $updateBuilder->set($data);
            $updateBuilder->where("StaffDepartmentID = $resourceId");

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
            
            $selectBuilder->from("Staffs.StaffDepartment");

            if ($resourceId !== 0){
                $selectBuilder->where("StaffDepartmentID = $resourceId");
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
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.StaffDepartment")
                ->where("StaffDepartmentID = $resourceId");
            
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