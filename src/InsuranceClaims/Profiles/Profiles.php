<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\InsuranceClaims\Profiles;

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
 * class Profiles.
 *
 * Profiles Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 31/10/2021 11:15
 */
class Profiles
{
    public static function setPrimaryAccount(array $data)
    {
        $plan = $data["PlanId"];
        $patient = $data["PatientId"];

        $query = "SELECT * FROM InsuranceClaims.PrimaryAccounts WHERE PatientTypeID = $plan";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) == 0){
            $query = "INSERT INTO InsuranceClaims.PrimaryAccounts (PatientTypeID, PatientID) VALUES ($plan, $patient)";
        }
        else {
            $query = "UPDATE InsuranceClaims.PrimaryAccounts SET PatientID = $patient WHERE PatientTypeID = $plan";
        }

        $result = DBConnectionFactory::getConnection()->exec($query);

    	return $result;
    }

    public static function getPrimaryAccount(int $planId){
        $query = "SELECT * FROM InsuranceClaims.PrimaryAccounts WHERE PatientTypeID = $planId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $result = $result[0] ?? [];

        return $result;
    }
}