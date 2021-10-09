<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Financiers\Financier;

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

        $query = "INSERT INTO InsuranceClaims.Financiers (FinancierUID) VALUES ('$financierUid');";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static function newPatient(array $data){
        $financierId = $data["financier"];
        $patientData = $data["patientData"] ?? [];

        $patientResult = \EmmetBlue\Plugins\Patients\Patient\Patient::create($patientData);

        $patientId = $patientResult["lastInsertId"];
        $patientType = $data["patientData"]["patientType"];

        $query = "INSERT INTO InsuranceClaims.FinancierPatientTypeLinks (FinancierID, PatientTypeID) VALUES ($finacierId, $patientType)";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    } 

    public static function viewFinanciers(int $resourceId=0){
        $query = "SELECT * FROM InsuranceClaims.Financiers";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
}