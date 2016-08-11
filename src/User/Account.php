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
use EmmetBlue\Core\Session\Session;
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

            return ["status"=>true, "uuid"=>$info['StaffUUID'], "accountActivated"=>$info["AccountActivated"]];
        }

        return ["status"=>false];
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

            DatabaseLog::log(Session::get('USER_ID'), Constant::EVENT_SELECT, 'Staffs', 'StaffPassword', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return (int)$result[0]['StaffID'];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)Session::get('USER_ID')
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

            DatabaseLog::log(Session::get('USER_ID'), Constant::EVENT_SELECT, 'Staffs', 'Staff', (string)$selectBuilder);
             if (count($result) == 1)
             {
                return $result[0];
             }

             throw new UndefinedValueException(
                sprintf(
                    "User with ID: %s not found",
                    $username
                 ),
                (int)Session::get('USER_ID')
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