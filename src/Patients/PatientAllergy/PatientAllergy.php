<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientAllergy;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class PatientAllergy.
 *
 * PatientAllergy Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientAllergy
{
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $title = $data["title"] ?? null;
        $severity = $data["severity"] ?? null;
        $type = $data["type"] ?? null;
        $description = $data["description"] ?? null;
        $triggers = $data["triggers"] ?? [];
        $symptoms = $data["symptoms"] ?? [];

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientAllergies', [
                'PatientID'=>$patient,
                'AllergyTitle'=>(is_null($title)) ? 'NULL' : QB::wrapString((string)$title, "'"),
                'AllergySeverity'=>(is_null($severity)) ? 'NULL' : QB::wrapString((string)$severity, "'"),
                'AllergyType'=>(is_null($type)) ? 'NULL' : QB::wrapString((string)$type, "'"),
                'AllergyDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'")
            ]);

            $id = $result["lastInsertId"];

            foreach ($triggers as $key=>$trigger){
                $triggers[$key] = "($id, '".$trigger."')";
            }

            foreach ($symptoms as $key => $symptom) {
                $symptoms[$key] = "($id, '".$symptom."')";
            }

            $triggerString = implode(", ", $triggers);
            $symptomString = implode(", ", $symptoms);
            $query = "INSERT INTO Patients.PatientAllergyTriggers VALUES $triggerString; INSERT INTO Patients.PatientAllergySymptoms VALUES $symptomString";

            $_result = DBConnectionFactory::getConnection()->exec($query);


            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientAllergies',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient event not registered), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId)
    {
        $query = "SELECT * FROM Patients.PatientAllergies WHERE PatientID = $resourceId";
        $results = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($results as $key => $result) {
           if (isset($result["AllergyID"])){
                $id = $result["AllergyID"];
                $query = "SELECT * FROM Patients.PatientAllergyTriggers WHERE AllergyID = $id";
                $results[$key]["triggers"] = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
                $query = "SELECT * FROM Patients.PatientAllergySymptoms WHERE AllergyID = $id";
                $results[$key]["symptoms"] = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        return $results;
    }
}