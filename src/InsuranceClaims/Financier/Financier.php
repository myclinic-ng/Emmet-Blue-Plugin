<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\InsuranceClaims\Financier;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class Financier.
 *
 * Financier Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 09/10/2021 13:01
 */
class Financier
{
    public static function newFinancier(array $data)
    {
        $financierUid = $data["financierUid"];
        $type = $data["financierType"];

        $query = "INSERT INTO InsuranceClaims.Financiers (FinancierUID, FinancierType) VALUES ('$financierUid', '$financierType');";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static function newInsuranceId(array $data){
        $financierId = $data["financier"];
        $planId = $data["planId"];
        $planDescription = $data["planDescription"] ?? null;

        $query = "SELECT * FROM InsuranceClaims.Financiers WHERE FinancierID = $financierId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (count($result) > 0){
            $financierUid = $result[0]["FinancierUID"];
            $timestamp = microtime(true);

            $typeName = $financierUid." ".$timestamp;

            $typeName = crc32($typeName);

            $type = \EmmetBlue\Plugins\Patients\PatientType\PatientType::create([
                "patientTypeName"=>$typeName,
                "patientTypeCategory"=>$planId,
                "patientTypeDescription"=>$planDescription
            ]);

            $patientTypeId = $type["lastInsertId"];

            $query = "INSERT INTO InsuranceClaims.FinancierPatientTypeLinks (FinancierID, PatientTypeID) VALUES ($financierId, $patientTypeId)";

            $result = DBConnectionFactory::getConnection()->exec($query);

            $result = ["result"=>$result, "planName"=>$typeName];
        }

        return $result;
    } 

    public static function viewFinancierTypes(){
        $query = "SELECT * FROM InsuranceClaims.FinancierTypes";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function viewFinanciers(int $resourceId=0){
        $query = "SELECT * FROM InsuranceClaims.Financiers";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function viewInsuranceId(int $resourceId=0){
        $query = "SELECT a.LinkID, b.*, c.* FROM InsuranceClaims.FinancierPatientTypeLinks a INNER JOIN InsuranceClaims.Financiers b ON a.FinancierID = b.FinancierID INNER JOIN Patients.PatientType c ON a.PatientTypeID = c.PatientTypeID";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
}