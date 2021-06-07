<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
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
    public static function getPatientArchiveDir(){
        return Constant::getGlobals()["patient-archive-dir"];
    }

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
         * '$patientUuid' which will also be created inside the getPatientArchiveDir()
         * directory.
         */
        $patientDir = Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid;
        $profileDir = $patientDir.DIRECTORY_SEPARATOR.'profile';
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories';
        $bioDir = $patientDir.DIRECTORY_SEPARATOR.'biometrics';
        if (!mkdir($patientDir)){
            return false;
        }
        if (!mkdir($profileDir) || !mkdir($repoDir) || !mkdir($bioDir)){
            unlink($patientDir);
            return false;
        }

        self::$patientFolders = [
            "patient" => $patientDir,
            "profile" => $profileDir,
            "repo" => $repoDir,
            "biometrics" => $bioDir
        ];

        return true;
    }

    protected static function uploadPhotoAndDocuments($passport, $documents = null, $fingerprints = []){
        if (!isset(self::$patientFolders["profile"]) || is_null(self::$patientFolders["profile"])){
            return false;
        }

        self::base64ToJpeg($passport, self::$patientFolders["profile"].DIRECTORY_SEPARATOR."photo.jpg");
        
        if (!is_null($documents)){
            self::base64ToJpeg($documents, self::$patientFolders["profile"].DIRECTORY_SEPARATOR."documents.jpg");
        }

        if (!empty($fingerprints)){
            foreach($fingerprints as $name=>$fingerprint){
                self::base64ToJpeg($fingerprint, self::$patientFolders["biometrics"].DIRECTORY_SEPARATOR."$name.jpg");
            }
        }
        return true;
    }

    public static function updatePhoto(array $data){
        $query = "SELECT PatientUUID FROM Patients.Patient WHERE PatientID = ".$data["patient"];
        $patientUuid = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
        if (isset($patientUuid[0])){
            $patientUuid = $patientUuid[0]["PatientUUID"];

            self::$patientFolders = [
                "patient" => Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid,
                "profile" => Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid.DIRECTORY_SEPARATOR.'profile',
                "repo" => Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid.DIRECTORY_SEPARATOR.'repositories'
            ];

            if (!is_dir(self::$patientFolders["patient"])){
                mkdir(self::$patientFolders["patient"]);
                mkdir(self::$patientFolders["profile"]);
                mkdir(self::$patientFolders["repo"]);
            }

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
        
        $query = "UPDATE Patients.Patient SET PatientProfileLockStatus = 0 WHERE PatientID = $patient";
        $result = DBConnectionFactory::getConnection()->exec($query);

        $query = "INSERT INTO Patients.PatientProfileUnlockLog (PatientID, Staff) VALUES ($patient, $staff)";
        $connection = DBConnectionFactory::getConnection();
        $_result = $connection->prepare($query)->execute();

        $_result = [$_result, "lastInsertId"=>$connection->lastInsertId()];

        \EmmetBlue\Plugins\Audit\UnlockLog::setStatus((int)$_result["lastInsertId"], ["status"=>0, "staff"=>$staff]);

        return $result;
    }

    public static function create(array $data)
    {
        if (isset($data["patientName"])){
            $fullName = $data["patientName"];
            $type = $data["patientType"] ?? null;
            $passport = $data["patientPassport"] ?? null;
            $documents = $data["documents"] ?? null;
            $fingerprints = $data["fingerprints"] ?? [];
            $patientId = $data["patientId"] ?? null;

            $hospitalHistory = $data["hospitalHistory"] ?? [];
            $diagnosis = $data["diagnosis"] ?? [];
            $operation = $data["operation"] ?? [];
            $creator = $data["createdBy"] ?? null;
            
            $patientUuid = strtoupper(uniqid());

            unset(
                $data["patientPassport"],
                $data["documents"],
                $data["hospitalHistory"],
                $data["diagnosis"],
                $data["operation"],
                $data["patientType"],
                $data["patientName"],
                $data["createdBy"],
                $data["fingerprints"],
                $data["patientId"]
            );
            
            try
            {
                $insertData = [
                    'PatientFullName'=>(is_null($fullName)) ? 'NULL' : QB::wrapString((string)$fullName, "'"),
                    'PatientPicture'=> QB::wrapString(self::getPatientArchiveDir().$patientUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."photo.jpg", "'"),
                    'PatientType'=>(is_null($type)) ? 'NULL' : QB::wrapString((string)$type, "'"),
                    'PatientIdentificationDocument'=> QB::wrapString(self::getPatientArchiveDir().$patientUuid.DIRECTORY_SEPARATOR.'profile'.DIRECTORY_SEPARATOR."documents.jpg", "'"),
                    'PatientUUID'=>QB::wrapString((string)$patientUuid, "'"),
                    'CreatedBy'=>is_null($creator) ? 'NULL' : $creator
                ];

                if (!is_null($patientId)){
                    $insertData["PatientID"] = $patientId;
                }

                $insertKeys = implode(",",array_keys($insertData));
                $insertVals = implode(",",array_values($insertData));

                $query = "INSERT INTO Patients.Patient ($insertKeys) VALUES($insertVals);";

                if (!is_null($patientId)){
                    $query = "SET IDENTITY_INSERT Patients.Patient ON;".$query."SET IDENTITY_INSERT Patients.Patient OFF;";
                }
                
                $connection = DBConnectionFactory::getConnection();
                $result = $connection->prepare($query)->execute();

                $result = [$result, "lastInsertId"=>$connection->lastInsertId()];

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
                                if (!self::uploadPhotoAndDocuments($passport, $documents, $fingerprints)){
                                    self::delete((int)$id);
                                }
                                else {
                                    //enroll patient;
                                    if (!empty($fingerprints)){
                                        $files = [];
                                        foreach ($fingerprints as $name => $value) {
                                            $files[] = self::$patientFolders["biometrics"].DIRECTORY_SEPARATOR."$name.jpg";
                                        }

                                        $fingerEnrollResult = \EmmetBlue\Plugins\Biometrics\Fingerprint::enroll($id, "PATIENT", $files);
                                    }
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
                        'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                        'type' =>'patient-info',
                        'id'=>$id,
                        'body'=>$body
                    ];

                    $esClient->index($params);
                }
                catch (\Exception $e){
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

            if ($resourceId !== ""){
                $updateBuilder->table("Patients.PatientRecordsFieldValue");
                $updateBuilder->set($data);
                $updateBuilder->where("FieldValueID = $resourceId");
            }
            else {
                $updateBuilder = "INSERT INTO Patients.PatientRecordsFieldValue (PatientID, FieldTitle, FieldValue) VALUES ($patient, ".$data['FieldTitle'].", ".$data['FieldValue'].")";
            }

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
                    'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                    'type' =>'patient-info',
                    'id'=>$patient,
                    'body'=>$body
                ];

                $esClient->index($params);
            }
            catch (\Exception $e){
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

    public static function viewPatientCreator(int $resourceId){
        $query = "SELECT a.CreatedBy FROM Patients.Patient a WHERE PatientID=$resourceId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $staff = [];

        if (isset($result[0]) && $result[0]["CreatedBy"] !== "NULL"){
            $staff = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffProfile((int) $result[0]["CreatedBy"]);

            $staff = isset($staff[0]) ? $staff[0] : $staff;
        }

        return $staff;
    }

    private static function viewCommon(int $resourceId = 0){
        $result = [];
        try {
            $esClient = ESClientFactory::getClient();
            if ($resourceId == 0)
            {
                $result = self::search(["query"=>"", "size"=>10, "from"=>0]);
            }
            else {
                $params = [
                    'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                    'type' =>'patient-info',
                    'id'=>$resourceId
                ];

                $result = $esClient->get($params);
            }
        }
        catch (\Exception $e){
            $query = "Patients.GetPatientBasicProfile ".$resourceId;
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $result = ["_source"=>$result[0] ?? []];
        }

        // $query = "Patients.GetPatientBasicProfile ".$resourceId;
        // $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        // $result = ["_source"=>$result[0] ?? []];

        return $result;
    }


    /**
     * view patients UUID
     */
    public static function viewBasic(int $resourceId = 0)
    {
        $result = self::viewCommon($resourceId);        

        if (isset($result["_source"])){
            foreach ($result["_source"] as $key=>$value){
                unset($result["_source"][$key]);
                $result["_source"][strtolower($key)] = $value;
            }  
        }

        return $result;
    }

    public static function view(int $resourceId = 0)
    {
        $result = self::viewCommon($resourceId);
        
        if (isset($result["_source"])){
            foreach ($result["_source"] as $key=>$value){
                unset($result["_source"][$key]);
                $result["_source"][strtolower($key)] = $value;
                $result["_source"]["auditflags"] = \EmmetBlue\Plugins\Audit\Flags::viewByPatient((int) $resourceId);
                $result["_source"]["isLinkedToCloud"] = \EmmetBlue\Plugins\EmmetblueCloud\PatientProfile::isLinked((int) $resourceId); 
                $result["_source"]["admissionStatus"] = \EmmetBlue\Plugins\Nursing\WardAdmission\WardAdmission::getAdmissionDetails((int) $resourceId); 
                $result["_source"]["createdByProfile"] = self::viewPatientCreator((int) $resourceId); 
                if (strtolower($key) == "patientprofilelockstatus"){
                    $result["_source"][strtolower($key)] = self::retrieveLockStatus((int) $resourceId);
                }
            }   
        }

        return $result;
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

    private static function viewPatientsByCurrentDayVisit(){
        $start = (new \DateTime())->format("m/d/Y");
        $end = (new \DateTime("+1day"))->format("m/d/Y");

        $query = "SELECT DISTINCT c.PatientID FROM Patients.PatientProfileUnlockLog a INNER JOIN Patients.Patient c ON a.PatientID = c.PatientID WHERE CONVERT(date, a.DateLogged) BETWEEN '$start' AND '$end'";

        $results = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $result = [
            "hits"=>[
                "hits"=>[],
                "total"=>count($results)
            ]
        ];

        foreach ($results as $_result){
            $query = "Patients.GetPatientBasicProfile ".$_result["PatientID"];
            $_result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $_result = ["_source"=>$_result[0] ?? []];
            $result["hits"]["hits"][] = $_result;
        }

        return $result;
    }

    public static function search(array $data)
    {
        try {
            if ($data["query"] == ""){
                $result = self::viewPatientsByCurrentDayVisit();
            }
            else {
                $query = explode(" ", $data["query"]);
                $builtQuery = [];
                foreach ($query as $element){
                    $builtQuery[] = "(".$element."* ".$element."~)";
                }

                $builtQuery = implode(" AND ", $builtQuery);
                
                $params = [
                    'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
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
            }
            
        }
        catch(\Exception $e){
            $query = $data["query"];
            $size = $data['size'] ?? 1;

            if ($query == "*"){
                $query = "";
            }
            $query = "SELECT TOP $size PatientID FROM Patients.Patient WHERE (PatientFullName LIKE '%$query%' OR PatientFullName = '$query' OR PatientUUID LIKE '%$query%')  AND ProfileDeleted = 0";

            $results = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            $result = [
                "hits"=>[
                    "hits"=>[],
                    "total"=>count($results)
                ]
            ];

            foreach ($results as $_result){
                $query = "Patients.GetPatientBasicProfile ".$_result["PatientID"];
                $_result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

                $_result = ["_source"=>$_result[0] ?? []];
                $result["hits"]["hits"][] = $_result;
            }
        }

        // $query = $data["query"];
        // $size = $data['size'];

        // if ($query == "*"){
        //     $query = "";
        // }
        // $sql_query = "SELECT TOP $size PatientID FROM Patients.Patient WHERE (PatientFullName LIKE '%$query%' OR PatientFullName = '$query' OR PatientUUID LIKE '%$query%')  AND ProfileDeleted = 0";

        // $results = DBConnectionFactory::getConnection()->query($sql_query)->fetchAll(\PDO::FETCH_ASSOC);

        // if (count($results) == 0){
        //     $sql_query = "SELECT a.PatientID FROM Patients.Patient a INNER JOIN (SELECT DISTINCT TOP $size PatientID FROM Patients.PatientRecordsFieldValue WHERE FieldValue LIKE '%$query%') b ON a.PatientID = b.PatientID WHERE a.ProfileDeleted = 0;";

        //     $results = DBConnectionFactory::getConnection()->query($sql_query)->fetchAll(\PDO::FETCH_ASSOC);
        // }

        // $result = [
        //     "hits"=>[
        //         "hits"=>[],
        //         "total"=>count($results)
        //     ]
        // ];

        // foreach ($results as $_result){
        //     $query = "Patients.GetPatientBasicProfile ".$_result["PatientID"];
        //     $_result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        //     $_result = ["_source"=>$_result[0] ?? []];
        //     $result["hits"]["hits"][] = $_result;
        // }

        foreach ($result["hits"]["hits"] as $key=>$hit){
            if (isset($hit["_source"]["patientid"]) || isset($hit["_source"]["PatientID"])){
                foreach($result["hits"]["hits"][$key]["_source"] as $k=>$v){
                    unset($result["hits"]["hits"][$key]["_source"][$k]);
                    $result["hits"]["hits"][$key]["_source"][strtolower($k)] = $v;
                    if (strtolower($k) == "patientprofilelockstatus"){
                        $id = $hit["_source"]["PatientID"] ?? $hit["_source"]["patientid"];
                        $result["hits"]["hits"][$key]["_source"][strtolower($k)] = (int) self::retrieveLockStatus((int) $id)["status"];
                    }
                }

                $result["hits"]["hits"][$key]["_source"]["auditflags"] = \EmmetBlue\Plugins\Audit\Flags::viewByPatient(
                    (int) $result["hits"]["hits"][$key]["_source"]["patientid"] ?? $result["hits"]["hits"][$key]["_source"]["patientid"]
                ); 
                $result["hits"]["hits"][$key]["_source"]["isLinkedToCloud"] = \EmmetBlue\Plugins\EmmetblueCloud\PatientProfile::isLinked(
                    (int) $result["hits"]["hits"][$key]["_source"]["patientid"]
                ); 

                $result["hits"]["hits"][$key]["_source"]["createdByProfile"] = self::viewPatientCreator(
                    (int) $result["hits"]["hits"][$key]["_source"]["patientid"]
                );   
            }
            else {
                unset($result["hits"]["hits"][$key]);
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
                    (string)$query
                );

                try {            
                    $esClient = ESClientFactory::getClient();

                    $params = [
                        'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                        'type' =>'patient-info',
                        'id'=>$resourceId
                    ];

                    return $esClient->delete($params);
                }
                catch (\Exception $e){
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
        $query = "SELECT * FROM Patients.Patient a INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID INNER JOIN Patients.PatientProfileUnlockLog c ON a.PatientID = c.PatientID WHERE a.PatientProfileLockStatus = 0 AND CONVERT(date, c.DateLogged) = CONVERT(date, GETDATE())";

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

            $queue = \EmmetBlue\Plugins\Consultancy\PatientQueue::getPatientQueueInfo((int) $value["PatientID"]);
            $result[$key]["queueInfo"] = $queue;
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
                    'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                    'type' =>'patient-info',
                    'id'=>$patient,
                    'body'=>$body
                ];

                $esClient->index($params);
            }
            catch (\Exception $e){
            }

            return true;
        }

        return false;
    }

    public static function retrieveLockStatus(int $resourceId){
        $query = "SELECT PatientProfileLockStatus AS status FROM Patients.Patient WHERE PatientID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            $result = $result[0];
        }
        else {
            $result["status"] = -1;
        }

        return $result;
    }

    public static function viewRecordFields(int $resourceId){
        $query = "
                    SELECT FieldValueID, FieldTitle, FieldValue FROM Patients.PatientRecordsFieldValue WHERE PatientID = $resourceId
                    UNION 
                    SELECT NULL AS FieldValueID, FieldTitleName AS FieldTitle, NULL AS FieldValue FROM Patients.PatientRecordsFieldTitle
                    WHERE FieldTitleName NOT IN (SELECT FieldTitle FROM Patients.PatientRecordsFieldValue WHERE PatientID = $resourceId)
                ";

        return DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
    }

    public static function viewPatientsByRegistration(array $data){
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY LastModified) AS RowNum, PatientID, LastModified FROM Patients.Patient";
        if (isset($data['startdate'])){
            $selectBuilder .= " WHERE CONVERT(date, LastModified) BETWEEN '".$data["startdate"]."' AND '".$data["enddate"]."'"; 
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (PatientFullName LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = (string) $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key=>$result){
                $src = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $result["PatientID"])["_source"];
                if (!empty($src)){
                    $viewOperation[$key]["PatientInfo"] = $src;
                }
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
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

    public static function reIndexElasticSearch(){
        $query = "Patients.GetPatientBasicProfile ";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        
        $esClient = ESClientFactory::getClient();
        foreach($result as $patient){
            $params = [
                'index'=>Constant::getGlobals()["patient-es-archive-index"] ?? '',
                'type' =>'patient-info',
                'id'=>$patient["PatientID"],
                'body'=>$patient
            ];

            $esClient->index($params);
        }
    }
}