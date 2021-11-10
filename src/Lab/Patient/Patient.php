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
        $investigations = $data["investigations"];
        $requestedBy = $data['requestedBy'] ?? null;
        $request = $data['request'] ?? null;
        $dateRequested = $data['dateRequested'] ?? null;
        $name = $fname." ".$lname;

        try
        {
            foreach ($investigations as $inv){
                $investigationTypeRequired = $inv['investigation'] ?? 'null';
                $investigationRequired = $inv['note'] ?? null;
                $requestedBy = $inv['requestedBy'] ?? null;
                $dateRequested = $inv['dateRequested'] ?? null;
                $request = $inv['request'] ?? null;

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
                    'RequestedBy'=>$requestedBy,
                    'RequestID'=>$request
                ]);

                $id = $result["lastInsertId"];

                try {
                    \EmmetBlue\Plugins\EmmetblueCloud\Lab::addFollowUp([
                        "patient"=>$patientID,
                        "labNumber"=>$id,
                        "staff"=>$requestedBy
                    ]);   
                }
                catch(\Exception $e){
                    
                }

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_SELECT,
                    'Lab',
                    'Patients',
                    (string)(serialize($result))
                );
            }
            
            return true;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Patient not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    
    public static function view(int $resourceId, array $data=[])
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('ROW_NUMBER() OVER (ORDER BY a.RegistrationDate DESC) AS RowNum, a.PatientLabNumber, a.PatientID, a.FullName, a.InvestigationRequired, a.RegistrationDate, a.RequestID, a.RequestedBy, a.DateRequested, a.Published, a.Unlocked, b.InvestigationTypeName, b.InvestigationTypeID, c.LabName, c.LabID')
            ->from('Lab.Patients a')
            ->innerJoin('Lab.InvestigationTypes b', 'a.InvestigationTypeRequired = b.InvestigationTypeID')
            ->innerJoin('Lab.Labs c', 'b.InvestigationTypeLab = c.LabID')
            ->where('a.Published = 0');
        if ($resourceId != 0){
            $selectBuilder->andWhere('a.RequestID ='.$resourceId);
        }
        else {
            $selectBuilder->andWhere('a.Unlocked = 1');
        }

        if (isset($data["patient"])){
            $selectBuilder->andWhere('a.PatientID = '.$data["patient"]);
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (a.PatientLabNumber LIKE '%$keyword%' OR a.FullName LIKE '%$keyword%' OR b.InvestigationTypeName LIKE '%$keyword%' OR c.LabName LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = (string) $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key=>$result){
                $patientView = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $result["PatientID"]);
                if (isset($patientView["_source"])){
                    $viewOperation[$key]["PatientInfo"] = $patientView["_source"];
                }
                else {
                    $viewOperation[$key]["PatientInfo"] = [];
                }
                $viewOperation[$key]["RequestedByDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'Patients',
                (string)$selectBuilder
            );

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $viewOperation = [
                    "data"=>$viewOperation,
                    "total"=>$total,
                    "filtered"=>$total
                ];
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