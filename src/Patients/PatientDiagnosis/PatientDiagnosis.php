<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientDiagnosis;

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
 * class PatientDiagnosis.
 *
 * PatientDiagnosis Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientDiagnosis
{
    /**
     * creats new patient id and generates a unique user id (UUID)
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $codeNumber = $data["codeNumber"] ?? null;
        $diagnosisType = $data["diagnosisType"] ?? "diagnosis";
        $diagnosisTitle = $data["diagnosisTitle"] ?? null;
        $diagnosis = $data["diagnosis"] ?? null;
        $diagnosisBy = $data["diagnosisBy"] ?? null;
        $staffId = $data["staff"] ?? null;
        $diagnosisDate = $data["diagnosisDate"] ?? null;
        $diagnosisId = $data["diagnosisId"] ?? null;

        $diagnosis = serialize($diagnosis);

        try
        {
            $insertData = [
                'PatientID'=>$patient,
                'CodeNumber'=>(is_null($codeNumber)) ? 'NULL' : QB::wrapString($codeNumber, "'"),
                'DiagnosisType'=>QB::wrapString($diagnosisType, "'"),
                'Diagnosis'=>(is_null($diagnosis)) ? 'NULL' : QB::wrapString($diagnosis, "'"),
                'DiagnosisTitle'=>(is_null($diagnosisTitle)) ? 'NULL' : QB::wrapString($diagnosisTitle, "'"),
                'DiagnosisBy'=>(is_null($diagnosisBy)) ? 'NULL' : QB::wrapString($diagnosisBy, "'")
            ];

            if (!is_null($diagnosisDate)){
                $insertData["DiagnosisDate"] = QB::wrapString((new \DateTime($diagnosisDate))->format('Y-m-d\TH:i:s'), "'");
            }

            if (!is_null($diagnosisId)){
                $insertData["DiagnosisID"] = $diagnosisId;
            }

            $insertKeys = implode(",",array_keys($insertData));
            $insertVals = implode(",",array_values($insertData));

            $query = "INSERT INTO Patients.PatientDiagnosis ($insertKeys) VALUES($insertVals);";

            if (!is_null($diagnosisId)){
                $query = "SET IDENTITY_INSERT Patients.PatientDiagnosis ON;".$query."SET IDENTITY_INSERT Patients.PatientDiagnosis OFF;";
            }

            $connection = DBConnectionFactory::getConnection();
            $result = $connection->prepare($query)->execute();

            $result = [$result, "lastInsertId"=>$connection->lastInsertId()];

            \EmmetBlue\Plugins\Consultancy\DiagnosisLog::newDiagnosisLog([
                "patient"=>$patient,
                "staff"=>$staffId,
                "diagnosis"=>$result["lastInsertId"]
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientDiagnosis',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a field title type
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Patients.PatientDiagnosis");
            $updateBuilder->set($data);
            $updateBuilder->where("DiagnosisID = $resourceId");

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

    /**
     * view patients UUID
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientDiagnosis')
            ->where('PatientID ='.$resourceId);
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientDiagnosis',
                (string)serialize($selectBuilder)
            );

            foreach ($result as $key => $value) {
                $result[$key]["StaffFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullNameFromUUID(["uuid"=>$value["DiagnosisBy"]])["StaffFullName"];
                $result[$key]["Diagnosis"] = unserialize($value["Diagnosis"]);
            }

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

    public static function viewById(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientDiagnosis')
            ->where('DiagnosisID ='.$resourceId);
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientDiagnosis',
                (string)serialize($selectBuilder)
            );

            foreach ($result as $key => $value) {
                $result[$key]["Diagnosis"] = unserialize($value["Diagnosis"]);
            }

            return $result[0];

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

    public static function getDiagnosisDateGroup(int $resourceId){
        $query = "SELECT *, DateName( month , DateAdd( month , a.MonthDate , -1 ) ) as MonthName
                FROM (SELECT MONTH(DiagnosisDate) as MonthDate, YEAR(DiagnosisDate) as YearDate 
                FROM Patients.PatientDiagnosis a WHERE PatientID =$resourceId GROUP BY Month(DiagnosisDate), Year(DiagnosisDate)) a";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);


        return $result;

    }

    public static function viewDiagnosisInDateGroups(int $resourceId, array $data){
        $month = $data["month"];
        $year = $data["year"];

        if ($month == 12){ //if month is december
            $dateMax = "$year-$month-31";
        }
        else {
            $month2 = $month + 1;
            $dateMax = "$year-$month2-1";
        }
        $query = "SELECT * FROM Patients.PatientDiagnosis WHERE PatientID=$resourceId AND
                DiagnosisDate >= CONVERT(DATE, '$year-$month-1') AND DiagnosisDate < CONVERT(DATE, '$dateMax')";

        try
        {
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientDiagnosis',
                (string)serialize($query)
            );

            foreach ($result as $key => $value) {
                $result[$key]["StaffFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullNameFromUUID(["uuid"=>$value["DiagnosisBy"]])["StaffFullName"];
                $result[$key]["Diagnosis"] = unserialize($value["Diagnosis"]);
            }

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

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientDiagnosis")
                ->where("DiagnosisID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientDiagnosis',
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