<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Biometrics;

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
 * class Fingerprint.
 *
 * Fingerprint Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 14/04/2018 3:12 PM
 */
class Fingerprint
{
    public static function enroll($name, $category, $files){
        $databaseConfigJson = file_get_contents(Constant::getGlobals()["config-dir"]["database-config"]);

        $databaseConfig = json_decode($databaseConfigJson);

        $server = $databaseConfig->server;
        $database = $databaseConfig->database;
        $username = $databaseConfig->username;
        $password = $databaseConfig->password;

        $table = "[Biometrics].[Humanity]";
        $exec = Constant::getGlobals()["finger-matcher-executable"];

        foreach ($files as $groupName=>$group){
            $file = implode(",", $group);

            $params = [
                QB::wrapString($exec, "\""),
                "enroll",
                QB::wrapString($file, "\""),
                QB::wrapString($name.".$groupName", "\""),
                QB::wrapString($category, "\""),
                QB::wrapString($server, "\""),
                QB::wrapString($username, "\""),
                QB::wrapString($password, "\""),
                QB::wrapString($database, "\""),
                QB::wrapString($table, "\"")
            ];

            $cmd = implode(" ", $params);

            $cmd = escapeshellcmd($cmd);
            $str = exec($cmd, $result);
        }

        try {
            if (isset($result[0])){
                $result = $result[0];
                $result = json_decode($result, true);

                return $result["result"] ?? false;
            } 
        }
        catch(\Exception $e){
        }

        return false;
    }

    public static function identify($category, $file){
        $databaseConfigJson = file_get_contents(Constant::getGlobals()["config-dir"]["database-config"]);

        $databaseConfig = json_decode($databaseConfigJson);

        $server = $databaseConfig->server;
        $database = $databaseConfig->database;
        $username = $databaseConfig->username;
        $password = $databaseConfig->password;

        $table = "[Biometrics].[Humanity]";
        $exec = Constant::getGlobals()["finger-matcher-executable"];
        $threshold = Constant::getGlobals()["finger-match-threshold"] ?? 20.0;

        $params = [
            QB::wrapString($exec, "\""),
            "identify",
            QB::wrapString($file, "\""),
            QB::wrapString($category, "\""),
            QB::wrapString($server, "\""),
            QB::wrapString($username, "\""),
            QB::wrapString($password, "\""),
            QB::wrapString($database, "\""),
            QB::wrapString($table, "\""),
            $threshold
        ];

        $cmd = implode(" ", $params);
        $cmd = escapeshellcmd($cmd);
        $str = exec($cmd, $result);

        try {
            if (isset($result[0])){
                $result = $result[0];
                $result = json_decode($result, true);

                return $result;
            } 
        }
        catch(\Exception $e){
        }

        return false;
    }
}