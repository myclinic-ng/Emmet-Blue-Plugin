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
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
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
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Patient
{
    CONST PATIENT_ARCHIVE_DIR = "bin\\data\\records\\archives\\patient\\";

    protected static $patientFolders = [];

    protected static function base64ToJpeg($base64_string, $output_file) {
        if (is_string($base64_string)){
            $data = explode(',', $base64_string);

            file_put_contents($output_file, base64_decode($data[1]));
       } 

        return $output_file; 
    }

    protected static function createPatientFolders(string $patientUuid)
    {
        /**
         * Create 'profile' and 'repositories' folders inside a folder named
         * '$patientUuid' which will also be created inside the PATIENT_ARCHIVE_DIR
         * directory.
         */
        $patientDir = self::PATIENT_ARCHIVE_DIR.$patientUuid;
        $profileDir = $patientDir.DIRECTORY_SEPARATOR.'profile';
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories';
        if (!mkdir($patientDir)){
            return false;
        }
        if (!mkdir($profileDir) || !mkdir($repoDir)){
            unlink($patientDir);
            return false;
        }

        self::$patientFolders = [
            "patient" => $patientDir,
            "profile" => $profileDir,
            "repo" => $repoDir
        ];

        return true;
    }

    protected static function uploadPhotoAndDocuments($passport, $documents = null){
        if (!isset(self::$patientFolders["profile"]) || is_null(self::$patientFolders["profile"])){
            return false;
        }

        self::base64ToJpeg($passport, self::$patientFolders["profile"].DIRECTORY_SEPARATOR."photo.jpg");
        
        if (!is_null($documents)){
            self::base64ToJpeg($documents, self::$patientFolders["profile"].DIRECTORY_SEPARATOR."documents.jpg");
        }
        return true;
    }

    #DEPRECATED METHOD
    public static function getImage(array $data){
        $imageLocation = $data["image-dir"];

        return file_get_contents($imageLocation);
    }   

    public static function updatePhoto(array $data){
        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = ".$data["patient"];
        $patientUuid = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
        if (isset($patientUuid[0])){
            $patientUuid = $patientUuid[0]["PatientUUID"];

            self::$patientFolders = [
                "patient" => self::PATIENT_ARCHIVE_DIR.$patientUuid,
                "profile" => self::PATIENT_ARCHIVE_DIR.$patientUuid.DIRECTORY_SEPARATOR.'profile',
                "repo" => self::PATIENT_ARCHIVE_DIR.$patientUuid.DIRECTORY_SEPARATOR.'repositories'
            ];

            return self::uploadPhotoAndDocuments($data['photo']);
        }

        return false;
    }

    public static function lockProfile(array $data){
        $patient = $data['patient'];
        $query = "UPDATE Patients.Patient SET PatientProfileLockStatus = 1 WHERE PatientID = $patient";
        $result = DBConnectionFactory::getConnection()->exec($query);

        $q = HospitalHistory::new((int)$patient, [0=>["dateAttended"=>date("Y-m-d H:i:s")]]);

        return $result;
    }

    public static function unlockProfile(array $data){
        $patient = $data['patient'] ?? null;
        $staff = $data['staff'] ?? null;
        $department = $data['department'] ?? null;
        $requestNumber = $data['paymentRequest'] ?? null;

        // $salesData = [
        //     "patient"=>$patient,
        //     "staff"=>$staff,
        //     "department"=>$department,
        //     "salesAction"=>"Unlocked Profile"
        // ];

        // if (!is_null($requestNumber)){
        //     $salesData["paymentRequest"] = $requestNumber;
        // }

        // $logSales = \EmmetBlue\Plugins\Audit\SalesLog\SalesLog::create($salesData);
        
        $query = "UPDATE Patients.Patient SET PatientProfileLockStatus = 0 WHERE PatientID = $patient";
        $result = DBConnectionFactory::getConnection()->exec($query);

        $query = "INSERT INTO Patients.PatientProfileUnlockLog (PatientID, Staff) VALUES ($patient, $staff)";
        DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static function create(array $data)
    {
        if (isset($data["patientName"])){
            $fullName = $data["patientName"];
            $type = $data["patientType"] ?? null;
            $passport = $data["patientPassport"] ?? null;
            $documents = $data["documents"] ?? null;

            $hospitalHistory = $data["hospitalHistory"] ?? [];
            $diagnosis = $data["diagnosis"] ?? [];
            $operation = $data["operation"] ?? [];
            
            $patientUuid = strtoupper(uniqid());

            unset(
                $data["patientPassport"],
                $data["documents"],
                $data["hospitalHistory"],
                $data["diagnosis"],
                $data["operation"],
                $data["patientType"],
                $data["patientName"]
            );
            
            try
            {
                $result = DBQueryFactory::insert('Patients.Patient', [
                    'PatientFullName'=>(is_null($fullName)) ? 'NULL' : QB::wrapString((string)$fullName, "'"),
                    'PatientPicture'=> QB::wrapString(self::PATIENT_ARCHIVE_DIR.$patientUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."photo.jpg", "'"),
                    'PatientType'=>(is_null($type)) ? 'NULL' : QB::wrapString((string)$type, "'"),
                    'PatientIdentificationDocument'=> QB::wrapString(self::PATIENT_ARCHIVE_DIR.$patientUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."documents.jpg", "'"),
                    'PatientUUID'=>QB::wrapString((string)$patientUuid, "'")
                ]);

                if ($result){
                    $id = $result['lastInsertId'];

                    $values = [];
                    foreach ($data as $key=>$value){
                        if ($key == "Date Of Birth"){
                            $value = (new \DateTime($value))->format('Y-m-d\TH:i:s');
                        }

                        $values[] = "($id, ".QB::wrapString((string)ucfirst($key), "'").", ".QB::wrapString((string)$value, "'").")";
                    }

                    $values[] = "($id, 'Patient', '$id')";

                    // return $values;

                    $query = "INSERT INTO Patients.PatientRecordsFieldValue (PatientId, FieldTitle, FieldValue) VALUES ".implode(", ", $values);

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->exec($query)
                    );
                    
                    if ($queryResult){
                        if (!HospitalHistory::new((int)$id, $hospitalHistory)){
                             // || !Diagnosis::new((int)$id, $diagnosis)
                            self::delete((int)$id);
                        }
                        else{
                            //upload documents now
                            if(!self::createPatientFolders($patientUuid)){
                                self::delete((int)$id);
                            }
                            else {
                                if (!self::uploadPhotoAndDocuments($passport, $documents)){
                                    self::delete((int)$id);
                                }
                            }
                        }
                    }
                    else {
                        self::delete((int)$id);
                    }

                    DatabaseLog::log(
                        Session::get('USER_ID'),
                        Constant::EVENT_INSERT,
                        'Patients',
                        'PatientRecordsFieldValue',
                        $query
                    );                
                }

                DatabaseLog::log(
                    Session::get('USER_ID'),
                    Constant::EVENT_INSERT,
                    'Patients',
                    'Patient',
                    (string)(serialize($result))
                );
                
                try {
                	$body = DBConnectionFactory::getConnection()->query("Patients.GetPatientBasicProfile $id")->fetchAll(\PDO::FETCH_ASSOC)[0];

		            $esClient = ESClientFactory::getClient();

		            $params = [
		                'index'=>'archives',
		                'type' =>'patient-info',
		                'id'=>$id,
		                'body'=>$body
		            ];

		            $esClient->index($params);
		        }
		        catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
		        }
                
                return $result;
            }
            catch (\PDOException $e)
            {
                self::delete((int)$id);
                throw new SQLException(sprintf(
                    "Unable to process request (patient not created), %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }

        throw new \Exception("Required data not set");
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
                $data['PatientFullName'] = QB::wrapString((string)$data['FullName'], "'");
            }
            if (isset($data['PatientPhoneNumber'])){
                $data['PatientPhoneNumber'] = QB::wrapString((string)$data['PatientPhoneNumber'], "'");
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
    public static function editPatientRecordsFieldValue(array $edits)
    {   
        $patient = $edits["patient"];
        $queries = [];
        foreach ($edits["data"] as $data){
            $resourceId = $data["resourceId"];
            unset($data["resourceId"]);
            $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();
            if (isset($data['FieldTitle'])){
                $data['FieldTitle'] = QB::wrapString((string)$data['FieldTitle'], "'");
            }

            if (isset($data['FieldValue'])){
                $data['FieldValue'] = QB::wrapString((string)$data['FieldValue'], "'");
            }

            $updateBuilder->table("Patients.PatientRecordsFieldValue");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldValueID = $resourceId");

            $queries[] = (string) $updateBuilder;
        }

        try
        {

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec(implode("; ", $queries))
                );

            $query = "SELECT FieldValue FROM Patients.PatientRecordsFieldValue WHERE PatientID = $patient AND (FieldTitle='First Name' OR FieldTitle='Last Name')";
            $names = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

            $nameStr = "";
            foreach ($names as $value) {
                $nameStr .= $value["FieldValue"]. " ";
            }

            $query = "UPDATE Patients.Patient SET PatientFullName = '$nameStr' WHERE PatientID = $patient";
            DBConnectionFactory::getConnection()->exec($query);
                
            try {
                $body = DBConnectionFactory::getConnection()->query("Patients.GetPatientBasicProfile ".$patient)->fetchAll(\PDO::FETCH_ASSOC)[0];

                if (isset($body["Date Of Birth"])){
                   $body["Date Of Birth"] = (new \DateTime($body["Date Of Birth"]))->format('Y-m-d\TH:i:s');
                }

                $esClient = ESClientFactory::getClient();

                $params = [
                    'index'=>'archives',
                    'type' =>'patient-info',
                    'id'=>$patient,
                    'body'=>$body
                ];

                $esClient->index($params);
            }
            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
            }

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
    public static function view(int $resourceId = 0)
    {
        try {
            
            $esClient = ESClientFactory::getClient();
            if ($resourceId == 0)
            {
                return self::search(["query"=>"", "size"=>10, "from"=>0]);
            }

            $params = [
                'index'=>'archives',
                'type' =>'patient-info',
                'id'=>$resourceId
            ];

            $result = $esClient->get($params);

            foreach ($result["_source"] as $key=>$value){
            	unset($result["_source"][$key]);
            	$result["_source"][strtolower($key)] = $value;
            }

            return $result;
        }
        catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
            $query = "SELECT * FROM Patients.Patient WHERE PatientID = $resourceId AND ProfileDeleted = 0";
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        }
    }

    public static function viewByPatientType(int $resourceId){
        $query = "SELECT * FROM Patients.Patient WHERE PatientType = $resourceId AND ProfileDeleted = 0";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$value){
            $id = $value["PatientID"];

            $result[$key] = self::view((int) $id)["_source"];
        }

        return $result;
    }

    public static function search(array $data)
    {
        if ($data["query"] == ""){
            $data["query"] = "*";
        }

        $query = explode(" ", $data["query"]);
        $builtQuery = [];
        foreach ($query as $element){
            $builtQuery[] = "(".$element."* ".$element."~)";
        }

        $builtQuery = implode(" AND ", $builtQuery);
        
        $params = [
            'index'=>'archives',
            'type'=>'patient-info',
            'size'=>$data['size'] ?? 1,
            'from'=>$data['from'] ?? 0,
            'body'=>array(
                "query"=>array(
                    "query_string"=>array(
                        "query"=>$builtQuery
                    )
                )
            )
        ];

        $esClient = ESClientFactory::getClient();

        $result = $esClient->search($params);

        foreach ($result["hits"]["hits"] as $key=>$hit){
        	foreach($result["hits"]["hits"][$key]["_source"] as $k=>$v){
        		unset($result["hits"]["hits"][$key]["_source"][$k]);
        		$result["hits"]["hits"][$key]["_source"][strtolower($k)] = $v;
        	}
        }
        
        return $result;
    }

    /**
     * delete patient
     */
    public static function delete(int $resourceId)
    {
        try
        {
            $query = "UPDATE Patients.Patient SET ProfileDeleted = 1 WHERE PatientID = $resourceId";

            $result = DBConnectionFactory::getConnection()->exec($query);

            if ($result){
	            DatabaseLog::log(
	                Session::get('USER_ID'),
	                Constant::EVENT_DELETE,
	                'Patients',
	                'Patient',
	                (string)$deleteBuilder
	            );

            	try {            
		            $esClient = ESClientFactory::getClient();

		            $params = [
		                'index'=>'archives',
		                'type' =>'patient-info',
		                'id'=>$resourceId
		            ];

		            return $esClient->delete($params);
		        }
		        catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
		        }
            }
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function getUnlockedProfiles(array $data){
        $query = "SELECT * FROM Patients.Patient a INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID WHERE PatientProfileLockStatus = 0";

        if (isset($data["patienttype"]) && $data["patienttype"] != ""){
            $types = explode(",", $data["patienttype"]);

            $str = implode(" OR a.PatientType=", $types);

            $query .= " AND (a.PatientType=$str)";
        }

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$value){
            $visitPdo = DBConnectionFactory::getConnection()->query("SELECT TOP 1 * FROM Consultancy.PatientDiagnosisLog a INNER JOIN Staffs.StaffProfile b ON a.StaffID = b.StaffID WHERE a.PatientID = ".$value['PatientID']." ORDER BY a.DateLogged DESC")->fetchall(\PDO::FETCH_ASSOC);

            if (isset($visitPdo[0])){
                $result[$key]["LastVisitDetails"] = $visitPdo[0];
            }
            else {
                $result[$key]["LastVisitDetails"] = [];
            }
        }

        return $result;
    }

    public static function changeType(array $data){
        $patient = $data["patient"] ?? null;
        $type = $data["type"] ?? null;
        $staff = $data["staff"] ?? null;

        $prevType = DBConnectionFactory::getConnection()->query("SELECT PatientType FROM Patients.Patient WHERE PatientID = $patient")->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($prevType[0])){
            $prevType = $prevType[0]["PatientType"];
        }
        else {
            $prevType = null;
        }

        $query = "UPDATE Patients.Patient SET PatientType = $type WHERE PatientID = $patient";

        if (DBConnectionFactory::getConnection()->exec($query)){
            $q = "INSERT INTO Patients.PatientTypeChangeLog (PatientID, PreviousType, NewType, ChangedBy) VALUES ($patient, $prevType, $type, $staff)";
            DBConnectionFactory::getConnection()->exec($q);

            try {
                $body = DBConnectionFactory::getConnection()->query("Patients.GetPatientBasicProfile ".$patient)->fetchAll(\PDO::FETCH_ASSOC)[0];

                if (isset($body["Date Of Birth"])){
                    $body["Date Of Birth"] = (new \DateTime($body["Date Of Birth"]))->format('Y-m-d\TH:i:s');
                }

                $esClient = ESClientFactory::getClient();

                $params = [
                    'index'=>'archives',
                    'type' =>'patient-info',
                    'id'=>$patient,
                    'body'=>$body
                ];

                $esClient->index($params);
            }
            catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e){
            }

            return true;
        }

        return false;
    }

    public static function viewRecordFields(int $resourceId){
        $query = "SELECT * FROM Patients.PatientRecordsFieldValue WHERE PatientID = $resourceId";

        return DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
    }
}