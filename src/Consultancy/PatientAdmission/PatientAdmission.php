<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\PatientAdmission;

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
 * class PatientAdmission.
 *
 * PatientAdmission Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientAdmission
{
    /**
     * creates new PatientAdmission
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $patient = $data['patient'] ?? 'NULL';
        $ward = $data['ward'] ?? 'NULL';
        $section = $data["section"] ?? 'NULL';
        $consultant = $data["consultant"] ?? 'NULL';
        $diagnosis = $data["diagnosis"] ?? 'NULL';

        try
        {
            $result = DBQueryFactory::insert('Consultancy.PatientAdmission', [
                'Patient'=>$patient,
                'Ward'=>$ward,
                'Section'=>$section,
                'Consultant'=>$consultant,
                'Diagnosis'=>$diagnosis,
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientAdmission',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not admission), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function viewAdmittedPatients(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.*, c.WardSectionName, d.WardName')
            ->from('Consultancy.PatientAdmission a')
            ->innerJoin('Patients.Patient b', 'a.Patient = b.PatientID')
            ->innerJoin('Nursing.WardSection c', 'a.Section = c.WardSectionID')
            ->innerJoin('Nursing.Ward d', 'a.Ward = d.WardID')
            ->where('a.DischargeStatus = 0');

        if ($resourceId != 0){
            $selectBuilder->andWhere('a.Ward ='.$resourceId);
        }

        if (isset($data["admissionId"])){
            $selectBuilder->andWhere('a.PatientAdmissionID = '.$data["admissionId"]);
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientAdmission',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request: %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    
    public static function editPatientAdmission(int $resourceId, array $data)
    {
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     $updateBuilder->table("Consultancy.PatientAdmission");
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
        //         'Consultancy',
        //         'PatientAdmission',
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