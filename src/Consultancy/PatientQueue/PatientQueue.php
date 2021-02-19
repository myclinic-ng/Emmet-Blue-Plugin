<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\PatientQueue;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
/**
 * class PatientQueue.
 *
 * PatientQueue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientQueue
{
    /**
     * creats new PatientQueue
     * 
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data['patient'] ?? null;
        $consultant = $data['consultant'] ?? null;

        $query = "SELECT * FROM Consultancy.PatientQueue WHERE Patient=$patient AND Consultant=$consultant;";
        $_result = (DBConnectionFactory::getConnection()->query($query))->fetchAll(\PDO::FETCH_ASSOC);

        if (count($_result) > 0){
            return true;
        }

        try
        {
            $result = DBQueryFactory::insert('Consultancy.PatientQueue', [
                'Patient'=>$patient,
                'Consultant'=>$consultant
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientQueue',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not registered to queue), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Consultancy.PatientQueue')
            ->where('Consultant ='.$resourceId. " ORDER BY QueueDate DESC");
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientQueue',
                (string)$selectBuilder
            );

            foreach ($viewOperation as $key=>$value){
                if (isset(\EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $value["Patient"])["_source"])){
                    $viewOperation[$key]["patientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $value["Patient"])["_source"];
                }
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

    
    public static function editPatientQueue(int $resourceId, array $data)
    {
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     $updateBuilder->table("Consultancy.PatientQueue");
        //     $updateBuilder->set($data);
        //     $updateBuilder->where("QueueID = $resourceId");

        //     $result = (
        //             DBConnectionFactory::getConnection()
        //             ->query((string)$updateBuilder)
        //         );
        //     //logging
        //     DatabaseLog::log(
        //         Session::get('USER_ID'),
        //         Constant::EVENT_SELECT,
        //         'Consultancy',
        //         'PatientQueue',
        //         (string)(serialize($result))
        //     );

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

    
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Consultancy.PatientQueue")
                ->where("QueueID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientQueue',
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