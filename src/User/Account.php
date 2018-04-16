<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\User;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session as CoreSession;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Account.
 *
 * Account Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Account
{
    /**
     * Logs a User In
     *
     * @param string $username
     * @param string $password
     */
    public static function login($data)
    {
        $username = $data["username"];
        $password = $data["password"];

        if (Account\Login::isLoginDataValid($username, $password))
        {
            $info = self::getAccountInfo(self::getUserID($username));
            $id = self::getUserID($username);

            \EmmetBlue\Plugins\User\Session::activate((int) $id);

            return ["status"=>true, "uuid"=>$info['StaffUUID'], "accountActivated"=>$info["AccountActivated"], "id"=>$id];
        }

        return ["status"=>false];
    }

    public static function loginWithFingerprint($data){
        $staff = \EmmetBlue\Plugins\HumanResources\StaffProfile::identifyFingerprint($data);

        if (isset($staff["StaffID"])){
            $id = $staff["StaffID"];
            $info = self::getAccountInfo((int) $id);

            \EmmetBlue\Plugins\User\Session::activate((int) $id);

            return ["status"=>true, "uuid"=>$info['StaffUUID'], "accountActivated"=>$info["AccountActivated"], "id"=>$id];
        }

        return ["status"=>false];
    }

    public static function getSwitchData($data){
        $id = $data["staff"] ?? null;
        $department = $data["department"] ?? null;

        $query = "SELECT COUNT(*) as count FROM Staffs.StaffSecondaryDepartments WHERE StaffID = $id AND DepartmentID = $department";
        $count = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["count"];

        if ($count == 1){
            try
            {            
        
                $query = "SELECT * FROM Staffs.DepartmentRootUrl a INNER JOIN Staffs.StaffSecondaryDepartments b ON a.DepartmentID = b.DepartmentID WHERE b.StaffID = $id AND b.DepartmentID = $department";

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
        else {
            throw new \Exception("Access Denied!");
        }
    }

     /**
     * Gets the ID of a user from the db
     *
     * @param string $username
     */
    public static function getUserID(string $username)
    {
        $selectBuilder = (new Builder("QueryBuilder","Select"))->getBuilder();

        try
        {
            $selectBuilder
                ->columns(
                    "StaffID"
                )
                ->from(
                    "Staffs.StaffPassword"
                )
                ->where(
                    "StaffUsername = ".
                    QB::wrapString($username, "'")
                );

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_SELECT, 'Staffs', 'StaffPassword', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return (int)$result[0]['StaffID'];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }
    }

    public static function getAccountInfo(int $staffId)
    {
        $selectBuilder = (new Builder("QueryBuilder","Select"))->getBuilder();

        try
        {
            $selectBuilder
                ->columns(
                    "StaffUUID",
                    "AccountActivated"
                )
                ->from(
                    "Staffs.Staff"
                )
                ->where(
                    "StaffID = ".
                    $staffId
                );

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_SELECT, 'Staffs', 'Staff', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return $result[0];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }   
    }

    public static function getStaffRoleAndDepartment(string $staffUuid)
    {
        try
        {
            $selectBuilder = "SELECT a.RoleName, b.Name as DepartmentName FROM (
                                SELECT a.StaffUUID, b.Name as RoleName, b.DepartmentID FROM Staffs.Staff a INNER JOIN (Select a.StaffID, b.* From Staffs.StaffRole a
                                INNER JOIN Staffs.Role b ON a.RoleID = b.RoleID) b on a.StaffID = b.StaffID
                            ) a
                            INNER JOIN Staffs.Department b ON a.DepartmentID = b.DepartmentID WHERE a.StaffUUID = '$staffUuid'";

             $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(CoreSession::get('USER_ID'), Constant::EVENT_SELECT, 'Staffs', 'Staff', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return $result[0];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)CoreSession::get('USER_ID')
             );
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "A database related error has occurred"
            ), Constant::UNDEFINED);
        }   
    }
}