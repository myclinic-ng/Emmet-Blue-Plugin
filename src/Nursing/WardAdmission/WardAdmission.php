<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\WardAdmission;

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
 * class WardAdmission.
 *
 * WardAdmission Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class WardAdmission
{
    /**
     * creates new WardAdmission
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $admissionId = $data['admissionId'] ?? 'NULL';
        $bed = $data['bed'] ?? 'NULL';
        $processedBy = $data["processedBy"] ?? 'NULL';

        try
        {
            $result = DBQueryFactory::insert('Nursing.WardAdmission', [
                'PatientAdmissionID'=>$admissionId,
                'Bed'=>$bed,
                'AdmissionProcessedBy'=>$processedBy
            ]);

            DBConnectionFactory::getConnection()->exec(
                "UPDATE Consultancy.PatientAdmission SET ReceivedInWard = 1 WHERE PatientAdmissionID = $admissionId"
            );

            DBConnectionFactory::getConnection()->exec(
                "UPDATE Nursing.SectionBed SET BedStatus = 1 WHERE SectionBedID = $bed"
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'WardAdmission',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not admitted), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function viewAdmittedPatients(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.WardAdmission')
            ->where('DischargeStatus = 0');

        if ($resourceId != 0){
            $selectBuilder->andWhere('WardAdmissionID ='.$resourceId);
        }
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach($result as $key=>$value){
                $id = $value["PatientAdmissionID"];
                $data = \EmmetBlue\Plugins\Consultancy\PatientAdmission\PatientAdmission::viewAdmittedPatients(0, ['admissionId'=>(int)$id]);
                if (isset($data[0])){
                    $result[$key]["AdmissionInfo"] = $data[0];
                }
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'WardAdmission',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    
    public static function editWardAdmission(int $resourceId, array $data)
    {
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     $updateBuilder->table("Nursing.WardAdmission");
        //     $updateBuilder->set($data);
        //     $updateBuilder->where("ExamTypeID = $resourceId");

        //     $result = (
        //             DBConnectionFactory::getConnection()
        //             ->query((string)$updateBuilder)
        //         );
        //     //logging
        //     DatabaseLog::log(
        //         Session::get('USER_ID'),
        //         Constant::EVENT_SELECT,
        //         'Nursing',
        //         'WardAdmission',
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
}