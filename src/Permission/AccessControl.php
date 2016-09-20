<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Permission;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class AccessControl.
 *
 * AccessControl Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccessControl
{
	private static function parseCamelString($string){
		return preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $string);
	}

	private static function convertToCamelString($string){
		$string = explode(" ", $string);
    	$sKey = strtolower($string[0]);
    	unset($string[0]);
    	foreach ($string as $key=>$value){
    		$string[$key] = ucfirst(strtolower($value));
    	}
    	$string = $sKey.implode("", $string);

    	return $string;
	}

    public static function viewResources(){
        $permissions = (new Permission())->getResources();

        $groupedPermissions = [];
        foreach ($permissions as $permission)
        {
        	$strings = explode("_", $permission);
        	foreach ($strings as $key=>$value){
        		$strings[$key] = ucfirst(self::parseCamelString($value));
        	}
        	$key = $strings[0];
        	unset($strings[0]);
        	$string = implode(" ", $strings);
        	$groupedPermissions[$key][] = $string;
        }

        return $groupedPermissions;
    }

    public static function viewPermissions(int $resourceId = 0, array $data)
    {
    	$department  = $data["department"] ?? null;
    	$role = $data["role"] ?? null;

    	$department = self::convertToCamelString($department);
    	$role = self::convertToCamelString($role);

    	$aclRole = $department."_".$role;

        $registry = (new Permission())->getAllPermissions($aclRole);

    	return $registry;
    }
}