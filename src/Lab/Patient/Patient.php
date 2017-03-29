<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab\Patient;

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
 * class Patient.
 *
 * Patient Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class Patient
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientID = $data['patientID'] ?? 'null';
        $fname = $data['firstName'] ?? null;
        $lname = $data['lastName'] ?? null;
        $dateOfBirth = $data['dateOfBirth'] ?? null;
        $gender = $data['gender'] ?? null;
        $address = $data['address'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $clinic = $data['clinic'] ?? null;
        $clinicalDiagnosis = $data['clinicalDiagnosis'] ?? null;
        $investigationTypeRequired = $data['investigationTypeRequired'] ?? 'null';
        $investigationRequired = $data['investigationRequired'] ?? null;
        $requestedBy = $data['requestedBy'] ?? null;
        $dateRequested = $data['dateRequested'] ?? null;

        $name = $fname." ".$lname;

        try
        {
            $result = DBQueryFactory::insert('Lab.Patients', [
                'PatientID'=>$patientID,
                'FullName'=>QB::wrapString((string)$name, "'"),
                'DateOfBirth'=>QB::wrapString((string)$dateOfBirth, "'"),
                'Gender'=>QB::wrapString((string)$gender, "'"),
                'Address'=>QB::wrapString((string)$address, "'"),
                'PhoneNumber'=>QB::wrapString((string)$phoneNumber, "'"),
                'Clinic'=>QB::wrapString((string)$clinic, "'"),
                'ClinicalDiagnosis'=>QB::wrapString((string)$clinicalDiagnosis, "'"),
                'InvestigationTypeRequired'=>$investigationTypeRequired,
                'InvestigationRequired'=>QB::wrapString((string)$investigationRequired, "'"),
                'DateRequested'=>QB::wrapString((string)$dateRequested, "'"),
                'RequestedBy'=>$requestedBy
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'Patients',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Patient not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Wards data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.InvestigationTypeName, b.InvestigationTypeID, c.LabName, c.LabID')
            ->from('Lab.Patients a')
            ->innerJoin('Lab.InvestigationTypes b', 'a.InvestigationTypeRequired = b.InvestigationTypeID')
            ->innerJoin('Lab.Labs c', 'b.InvestigationTypeLab = c.LabID')
            ->where('a.Published = 0');
        if ($resourceId != 0){
            $selectBuilder->andWhere('a.PatientLabNumber ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'Patients',
                (string)$selectBuilder
            );

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
     * Modifies a Ward resource
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['FullName'])){
                $data['FullName'] = QB::wrapString($data['FullName'], "'");
            }
            $updateBuilder->table("Lab.Patients");
            $updateBuilder->set($data);
            $updateBuilder->where("PatientLabNumber = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
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

    /**
     * delete a ward resource
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Lab.Patients")
                ->where("PatientLabNumber = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patient',
                'Patients',
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