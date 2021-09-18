<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Patient;

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
 * class LinkedExternalPatient .
 *
 * LinkedExternalPatient Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 18/09/2021 09:15
 */
class LinkedExternalPatient
{
    public static function create(array $data)
    {
        $localPatientId = $data['localPatientId'];
        $externalPatientId = $data['externalPatientId'];
        $businessId = $data['businessId'];

        try
        {
            $insertData = [
                "LocalPatientID"=>$localPatientId,
                "ExternalPatientID"=>$externalPatientId,
                "ExternalBusinessID"=>$businessId
            ];

            $result = DBQueryFactory::insert('Patients.LinkedExternalPatients', $insertData);
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Observation not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function getLocalId(array $data){
        $externalPatientId = $data["externalPatientId"];
        $businessId = $data["businessId"];

        $query = "SELECT * FROM Patients.LinkedExternalPatients WHERE ExternalPatientID = $externalPatientId AND ExternalBusinessID = $businessId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            return $result[0];
        }

        return false;
    }
}