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

    public static function discharge(array $data){
        $admissionId = $data["admissionId"] ?? null;
        $dischargedBy = $data["dischargedBy"] ?? null;
        $dischargeNote = $data['dischargeNote'] ?? null;
        $staff = $data['staff'] ?? null;

        $query = "SELECT * FROM Nursing.ServicesRendered WHERE PatientAdmissionID = $admissionId";

        $paymentRequest = [];
        $items = [];

        $res = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($res)){
            foreach ($res as $key => $value) {
                $items[] = ["item"=>$value["BillingTypeItem"], "quantity"=>$value["BillingTypeItemQuantity"]];
            }

            $paymentRequest["items"] = $items;
            $paymentRequest["requestBy"] = $staff;

            $paymentRequest["patient"] = DBConnectionFactory::getConnection()->query("SELECT Patient FROM Consultancy.PatientAdmission WHERE PatientAdmissionID = $admissionId")->fetchAll(\PDO::FETCH_ASSOC)[0]["Patient"];

            $makeBillersHappy = \EmmetBlue\Plugins\AccountsBiller\PaymentRequest\PaymentRequest::create($paymentRequest);
        }

        $bed = DBConnectionFactory::getConnection()->query("SELECT Bed FROM Nursing.WardAdmission WHERE PatientAdmissionID = $admissionId")->fetchAll(\PDO::FETCH_ASSOC)[0]["Bed"];
        DBConnectionFactory::getConnection()->exec("UPDATE Nursing.SectionBed SET BedStatus = 0 WHERE SectionBedID = $bed");

        $result = DBQueryFactory::insert('Consultancy.PatientDischargeInformation', [
            'PatientAdmissionID'=>$admissionId,
            'DischargedBy'=>$dischargedBy,
            'DischargeNote'=>QB::wrapString((string)$dischargeNote, "'")
        ]);

        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_SELECT,
            'Consultancy',
            'PatientAdmission',
            (string)serialize($result)
        );

        // DBConnectionFactory::getConnection()->exec("UPDATE Consultancy.PatientAdmission SET DischargeStatus = -1 WHERE PatientAdmissionID = $admissionId");

        return $result;
    }

    public static function clearForDischarge(int $resourceId){
        $query = "UPDATE Consultancy.PatientAdmission SET DischargeStatus = 1 WHERE PatientAdmissionID = $resourceId";
        return DBConnectionFactory::getConnection()->exec($query);
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
            ->innerJoin('Patients.Patient b', 'a.Patient = b.PatientID');
        $selectBuilder .= " LEFT OUTER JOIN Nursing.WardSection c ON a.Section = c.WardSectionID";
        $selectBuilder .= " LEFT OUTER JOIN Nursing.Ward d ON a.Ward = d.WardID";

        if (!(isset($data["ignore"]) && $data["ignore"] == true)){
            $selectBuilder .= " WHERE a.DischargeStatus = 0";
        }

        if ($resourceId != 0){
            $selectBuilder .= " WHERE a.Ward = $resourceId";
        }

        if (isset($data["admissionId"])){
            $selectBuilder .= " WHERE a.PatientAdmissionID = ".$data["admissionId"];
        }

        if (isset($data["patientId"])){
            $selectBuilder .= " WHERE a.Patient = ".$data["patientId"];
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$value){
                $admissionId = $value["PatientAdmissionID"];

                $result[$key]["ConsultantDetail"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["Consultant"]);
                $result[$key]["ConsultantDetail"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["Consultant"])["Name"];
                $wardDetailsString = "SELECT * FROM Nursing.WardAdmission WHERE PatientAdmissionID = $admissionId";
                $WardDetails = DBConnectionFactory::getConnection()->query($wardDetailsString)->fetchAll(\PDO::FETCH_ASSOC);
                if (isset($WardDetails[0])){
                    $result[$key]["WardDetails"] = $WardDetails[0];
                }
                else {
                    $result[$key]["WardDetails"] = [
                        "WardAdmissionID"=>null,
                        "Bed"=>null,
                        "AdmissionProcessedBy"=>null
                    ];
                }
            }

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

    public static function viewDischargedPatients(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.*, c.WardSectionName, d.WardName, e.DischargedBy, e.DischargeNote, e.DischargeDate, f.WardAdmissionID')
            ->from('Consultancy.PatientAdmission a')
            ->innerJoin('Patients.Patient b', 'a.Patient = b.PatientID')
            ->innerJoin('Nursing.WardSection c', 'a.Section = c.WardSectionID')
            ->innerJoin('Nursing.Ward d', 'a.Ward = d.WardID')
            ->innerJoin('Nursing.WardAdmission f', 'a.PatientAdmissionID = f.PatientAdmissionID')
            ->innerJoin('Consultancy.PatientDischargeInformation e', 'a.PatientAdmissionID = e.PatientAdmissionID')
            ->where('a.DischargeStatus IN (1, -1)');

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

    public static function viewReceivedPatients(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.*, c.WardSectionName, d.WardName, e.DischargedBy, e.DischargeNote, e.DischargeDate')
            ->from('Consultancy.PatientAdmission a')
            ->innerJoin('Patients.Patient b', 'a.Patient = b.PatientID')
            ->innerJoin('Nursing.WardSection c', 'a.Section = c.WardSectionID')
            ->innerJoin('Nursing.Ward d', 'a.Ward = d.WardID')
            ->innerJoin('Consultancy.PatientDischargeInformation e', 'a.PatientAdmissionID = e.PatientAdmissionID')
            ->where('a.ReceivedInWard = 1 AND (a.DischargeStatus = 0 OR a.DischargeStatus = -1)');

        if ($resourceId != 0){
            $selectBuilder->andWhere('a.Ward ='.$resourceId);
        }

        if (isset($data["admissionId"])){
            $selectBuilder->andWhere('a.PatientAdmissionID = '.$data["admissionId"]);
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$value){
                $admissionId = $value["PatientAdmissionID"];

                $wardDetailsString = "SELECT * FROM Nursing.WardAdmission WHERE PatientAdmissionID = $admissionId";
                $WardDetails = DBConnectionFactory::getConnection()->query($wardDetailsString)->fetchAll(\PDO::FETCH_ASSOC);
                if (isset($WardDetails[0])){
                    $result[$key]["WardDetails"] = $WardDetails[0];
                }
                else {
                    $result[$key]["WardDetails"] = [
                        "WardAdmissionID"=>null,
                        "Bed"=>null,
                        "AdmissionProcessedBy"=>null
                    ];
                }
            }

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