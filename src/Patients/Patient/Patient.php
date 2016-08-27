<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Patient;

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
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Patient
{
    /**
     * creats new patient id and generates a unique user id (UUID)
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientUuid = substr(str_shuffle(MD5(microtime())), 0, 20);
        $fullName = $data["title"]." ".$data["firstName"]." ".$data["lastName"];
        $passport = $data["patientPassport"];
        unset($data["patientPassport"]);

         if (!empty($_FILES)) {
            $files = $_FILES["file"];
            $location = "bin/data/records/patient/identification-documents";   
            $url = $location.DIRECTORY_SEPARATOR.$patientUuid.DIRECTORY_SEPARATOR;
            mkdir($url);
            foreach ($files["name"] as $key=>$null)
            {
                $tempFile = $files['tmp_name'][$key]; 
                $ext = explode(".", $files['name'][$key])[1];
                $targetFile =  $url. $key.".".$ext;
                move_uploaded_file($tempFile,$targetFile);
            } 

        }

        try
        {
            $result = DBQueryFactory::insert('Patients.Patient', [
                'PatientFullName'=>(is_null($fullName)) ? 'NULL' : QB::wrapString($fullName, "'"),
                'PatientPicture'=>(is_null($passport)) ? 'NULL' : QB::wrapString($passport, "'"),
                'PatientIdentificationDocumentUrl'=>QB::wrapString($url, "'") ?? 'NULL',
                'PatientUUID'=>QB::wrapString($patientUuid, "'")
            ]);

            $id = $result['lastInsertId'];

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'Patient',
                (string)(serialize($result))
            );
            
            $values = [];
            foreach ($data as $key=>$value){
                $values[] = "($id, ".QB::wrapString(ucfirst($key), "'").", ".QB::wrapString($value, "'").")";
            }


            $query = "INSERT INTO Patients.PatientRecordsFieldValue (PatientId, FieldTitle, FieldValue) VALUES ".implode(", ", $values);
            
            $queryResult = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientRecordsFieldValue',
                $query
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
            if (isset($data['FullName'])){
                $data['PatientFullName'] = QB::wrapString($data['FullName'], "'");
            }
            if (isset($data['PatientPhoneNumber'])){
                $data['PatientPhoneNumber'] = QB::wrapString($data['PatientPhoneNumber'], "'");
            }

            $updateBuilder->table("Patients.Patient");
            $updateBuilder->set($data);
            $updateBuilder->where("TypeID = $resourceId");

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
     * Modifies the content of a field title type
     */
    public static function editPatientRecordsFieldValue(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['FieldTitle'])){
                $data['FieldTitle'] = QB::wrapString($data['FieldTitle'], "'");
            }
            if (isset($data['FieldValue'])){
                $data['FieldValue'] = QB::wrapString($data['FieldValue'], "'");
            }

            $updateBuilder->table("Patients.PatientRecordsFieldValue");
            $updateBuilder->set($data);
            $updateBuilder->where("TypeID = $resourceId");

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
            ->from('Patients.Patient');
        if ($resourceId != 0){
            $selectBuilder->where('PatientID ='.$resourceId);
        }
        try
        {
            $viewPatients = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'Patient',
                (string)serialize($selectBuilder)
            );

            return $viewPatients;
                //$patientId = $viewPatients['PatientID'];
                $query = "SELECT * FROM Patients.PatientRecordsFieldValue WHERE PatientID = $resourceId";

                $viewPatientsRecords = (
                    DBConnectionFactory::getConnection()
                    ->query($query)
                )->fetchAll(\PDO::FETCH_ASSOC);

            if(count($viewPatients) > 0)
            {
                return array_merge($viewPatients,$viewPatientsRecords);
            }
            else
            {
                return null;
            }           
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
                ->from("Patients.Patient")
                ->where("PatientID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'Patient',
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