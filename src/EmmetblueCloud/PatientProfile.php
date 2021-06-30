<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud;

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
 * class PatientProfile.
 *
 * PatientProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class PatientProfile
{
	public static function newLink(array $data)
    {
        $result = PatientProfile\PatientProfile::newLink($data);

        return $result;
    }

    public static function isLinked(int $data)
    {
        $result = PatientProfile\PatientProfile::isLinked($data);

        return $result;
    }

    public static function retrieveAccountPublicInfo(array $data){
    	return PatientProfile\PatientProfile::retrieveAccountPublicInfo($data);
    }
    
    public static function newRegistration(array $data){
        $result = PatientProfile\PatientProfile::newRegistration($data);

        if ($result) {
            $publicInfo = self::retrieveAccountPublicInfo([
                "method"=>"email",
                "value"=>$data["email"]
            ]);

            $result = self::newLink([
                "patient"=>$data["patient"],
                "accountId"=>$publicInfo->user_id,
                "staff"=>$data["staff"]
            ]);
        }

        return $result;
    }
}