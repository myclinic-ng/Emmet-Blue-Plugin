<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\Observation;

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
 * class Observation.
 *
 * Observation Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class Observation
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientId = $data['patientId'] ?? null;
        $observationType = $data['observationType'] ?? null;
        $observationTypeName = $data['observationTypeName'] ?? null;
        $observation = $data['observation'] ?? null;
        $staffId = $data['staffId'] ?? null;

        $observation = serialize($observation);
        $date = date("F jS, Y", strtotime(date('Y-m-d H:i:s')));

        try
        {
            $repoData = [
                "patient"=>$patientId,
                "name"=>$observationTypeName. " (Nursing observation, ".$date.")",
                "type"=>"observation",
                "creator"=>$staffId
            ];
            $repoId = \EmmetBlue\Plugins\Patients\PatientRepository\PatientRepository::create($repoData)["lastInsertId"];

            $repoItemData = [
                "repository"=>$repoId,
                "name"=>$observationTypeName." deduction",
                "category"=>"json",
                "json"=>unserialize($observation),
                "creator"=>$staffId
            ];

            \EmmetBlue\Plugins\Patients\RepositoryItem\RepositoryItem::create($repoItemData);

            $result = DBQueryFactory::insert('Nursing.Observations', [
                'PatientID'=>QB::wrapString((string)$patientId, "'"),
                'RepositoryID'=>$repoId,
                'ObservationType'=>$observationType,
                'Observation'=>QB::wrapString((string)$observation, "'"),
                'StaffID'=>QB::wrapString((string)$staffId, "'"),
            ]);

            $result["repoId"] = $repoId;
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Observations',
                (string)(serialize($result))
            );
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

    /**
     * view
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = "SELECT * FROM Nursing.Observations WHERE PatientID = $resourceId";
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Observations',
                (string)$selectBuilder
            );

            foreach ($viewOperation as $key => $value) {
                $viewOperation[$key]["Report"] = unserialize($value["Report"]);
            }

            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    /**
     * Modifies a resource
     */
    public static function edit(int $resourceId, array $data)
    {
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     if (isset($data['FullName'])){
        //         $data['FullName'] = QB::wrapString($data['FullName'], "'");
        //     }
        //     $updateBuilder->table("Nursing.Observations");
        //     $updateBuilder->set($data);
        //     $updateBuilder->where("ObservationNursingNumber = $resourceId");

        //     $result = (
        //             DBConnectionFactory::getConnection()
        //             ->query((string)$updateBuilder)
        //         );

        //     return $result;
        // }
        // catch (\PDOException $e)
        // {
        //     throw new SQLException(sprintf(
        //         "Unable to process update, %s",
        //         $e->getMessage()
        //     ), Constant::UNDEFINED);
        // }
    }

    /**
     * delete
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Nursing.Observations")
                ->where("ResultID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'Observations',
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