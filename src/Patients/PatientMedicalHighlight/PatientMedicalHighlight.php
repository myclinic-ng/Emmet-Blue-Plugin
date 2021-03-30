<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientMedicalHighlight;

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
 * class PatientMedicalHighlight.
 *
 * PatientMedicalHighlight Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 30/03/2021 19:53
 */
class PatientMedicalHighlight
{
    /**
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $title = $data["title"] ?? null;
        $message = $data["message"] ?? null;
        $staff = $data["staff"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientMedicalHighlights', [
                'PatientID'=>$patient,
                'HighlightTitle'=>(is_null($title)) ? 'NULL' : QB::wrapString($title, "'"),
                'HighlightMessage'=>(is_null($message)) ? 'NULL' : QB::wrapString($message, "'"),
                'CreatedBy'=>(is_null($staff)) ? 'NULL' : QB::wrapString($staff, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientMedicalHighlights',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (highlight not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Patients.PatientMedicalHighlights");
            $updateBuilder->set($data);
            $updateBuilder->where("MedicalHighlightID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId)
    {
        try
        {
            $selectBuilder = "SELECT * FROM Patients.PatientMedicalHighlights WHERE PatientID = $resourceId ORDER BY HighlightDate DESC;";
            $result = (DBConnectionFactory::getConnection()->query($selectBuilder)->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientMedicalHighlights',
                (string)serialize($selectBuilder)
            );

            return $result;

        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientMedicalHighlight")
                ->where("MedicalHighlightID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientMedicalHighlight',
                (string)$deleteBuilder
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}