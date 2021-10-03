<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\RepositoryItem;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UnallowedOperationException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use FileUpload;
use EmmetBlue\Core\CustomFileNameGenerator as CFNG;

use EmmetBlue\Plugins\Permission\Permission as Permission;

use EmmetBlue\Core\Factory\HTTPRequestFactory as HTTPRequest;

/**
 * class RepositoryItem.
 *
 * RepositoryItem Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class RepositoryItem
{
    public static function getPatientArchiveDir(){
        return Constant::getGlobals()["patient-archive-dir"];
    }
    
    protected static $allowedExtensions = [
        "image"=>["jpg", "png", "jpeg", "image"],
        "text"=>["txt"],
        "pdf"=>["pdf"]
    ];

    public static function uploadRepoItems($patientUuid, $repoNumber, $files, $name)
    {
        $patientDir = Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoNumber;

        $validator = new FileUpload\Validator\MimeTypeValidator(['image/png', 'image/jpg']);
        $pathResolver = new FileUpload\PathResolver\Simple($repoDir);
        $fileSystem = new FileUpload\FileSystem\Simple();
        $filenamegenerator = new CFNG($name);

        $fileUpload = new FileUpload\FileUpload($files, $_SERVER);

        $fileUpload->setPathResolver($pathResolver);
        $fileUpload->setFileSystem($fileSystem);
        // $fileUpload->addValidator($validator);
        $fileUpload->setFileNameGenerator($filenamegenerator);

        list($files, $headers) = $fileUpload->processAll();

        return true;
    }

    public static function createRepoFile($patientUuid, $repoNumber, $file, $name)
    {
        $patientDir = Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().$patientUuid;
        $repoDir = $patientDir.DIRECTORY_SEPARATOR.'repositories'.DIRECTORY_SEPARATOR.$repoNumber;

        file_put_contents($repoDir. DIRECTORY_SEPARATOR . $name, $file);

        return true;
    }

    public static function create(array $data)
    {   
        $requestData = $data;

        $repository = $data["repository"] ?? 'null';
        $number = substr(str_shuffle(MD5(microtime())), 0, 20);
        $name = $data["name"] ?? null;
        $description = $data["description"] ?? null;
        $category = $data["category"] ?? null;
        $creator = $data["creator"] ?? null;

        if (isset($_FILES["documents"])){
            $fileNameArray = explode(".", $_FILES["documents"]["name"]);
            $ext = $fileNameArray[count($fileNameArray) - 1];
            $document = $_FILES["documents"];
        }
        else if (isset($data["documents"])){
            $data["file"] = $data["documents"];
            $ext = $category;
            $document = $data["documents"];
        }
        else if(isset($data["json"])){
            $ext = "json";
        }
        else if($category == "file"){
            $ext = $data["file_ext"];
        }
        
        if($category == "image"){
            $document = str_replace(" ", "+", $document);
            $document = base64_decode($document);
            $docs = explode(",", $document);
            $ext = explode(";", explode("/", $docs[0])[1])[0];

            $category = $ext;
        }

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientRepositoryItems', [
                'RepositoryID'=>$repository,
                'RepositoryItemNumber'=>QB::wrapString($number.".".$ext, "'"),
                'RepositoryItemName'=>(is_null($name)) ? 'NULL' : QB::wrapString((string)$name, "'"),
                'RepositoryItemDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'"),
                'RepositoryItemCategory'=>(is_null($category)) ? 'NULL' : QB::wrapString((string)$category, "'"),
                'RepositoryItemCreator'=>(is_null($creator)) ? 'NULL' : $creator
            ]);

            if ($result){
                $query = "SELECT a.RepositoryNumber, b.PatientUUID FROM Patients.PatientRepository a JOIN Patients.Patient b ON a.PatientID = b.PatientID WHERE a.RepositoryID = $repository";
                $uuids = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0];
                $ruuid = $uuids["RepositoryNumber"];
                $puuid = $uuids["PatientUUID"];

                switch (strtolower($category))
                {
                    case "image":
                    case "jpg":
                    case "jpeg":
                    case "png":
                    {
                        if (in_array(strtolower($ext), self::$allowedExtensions["image"])){
                            $document = base64_decode($docs[1]);
                            
                           if (!self::createRepoFile($puuid, $ruuid, $document, $number.".".$ext)){
                                self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            }
                        }
                        else {
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            throw new UnallowedOperationException(sprintf(
                                "Unallowed file type detected. .%s files are not allowed",
                                $ext
                            ), Constant::UNDEFINED);
                        }
                        break;
                    }
                    case "text":
                    {
                        if (in_array(strtolower($ext), self::$allowedExtensions[strtolower($category)])){
                           if (!self::createRepoFile($puuid, $ruuid, $document, $number.".".$ext)){
                                self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            }
                        }
                        else {
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                            throw new UnallowedOperationException(sprintf(
                                "Unallowed file type detected. .%s files are not allowed",
                                $ext
                            ), Constant::UNDEFINED);
                        }
                        break;
                    }
                    case "json":
                    {
                        $json = $data["json"] ?? null;

                        if (!self::createRepoFile($puuid, $ruuid, serialize($json), $number.".".$ext)){
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                        }
                        break;
                    }
                    case "pdf":
                    case "file":{
                        $json = $data["file"] ?? null;
                        
                        $decodedFile = base64_decode($json, true);

                        $_doc = explode(",", $decodedFile);

                        if (isset($_doc[1])){
                            $json = rtrim(trim($_doc[1]));
                            $decodedFile = base64_decode($json, true);
                        }


                        if (!self::createRepoFile($puuid, $ruuid, $decodedFile, $number.".".$ext)){
                            self::delete((int)$result["lastInsertId"], $puuid, $number.".".$ext);
                        }
                        break;
                    }
                }

                self::sendAcrossLabs($requestData);
            }

            // DatabaseLog::log(
            //     Session::get('USER_ID'),
            //     Constant::EVENT_SELECT,
            //     'Patients',
            //     'RepositoryItem',
            //     (string)(serialize($result))
            // );
            
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
     * view patients UUID
     */
    public static function view(int $resourceId)
    {
        
    }
    /**
     * delete patient
     */

    public static function sendAcrossLabs(array $data){
        $repository = $data["repository"];

        $query = "SELECT c.* FROM Lab.LabResults a INNER JOIN Lab.Patients b ON a.PatientLabNumber = b.PatientLabNumber INNER JOIN Lab.LinkedExternalRequests c ON b.RequestID = c.LocalRequestID WHERE a.RepositoryID=$repository";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $feedback = [];

        if (count($result) > 0){
            $requestId = $result[0]["ExternalRequestID"];
            $businessId = $result[0]["ExternalBusinessID"];

            $requestData = $data;
            $requestData["requestId"] = $requestId;

            $query = "SELECT * FROM EmmetBlueCloud.BusinessLinkAuth WHERE ExternalBusinessID = ".$businessId;
            $_res = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            if (count($_res) > 0){
                $auth = $_res[0];
                $url = $auth["EndpointUrl"]."/patients/repository-item/receive-from-external-lab";
                $token = $auth["Token"];
                $token_user = $auth["UserId"];

                $request = HTTPRequest::post($url, $requestData, [
                    'AUTHORIZATION'=>$token
                ]);

                $response = json_decode($request->body, true);

                $feedback = $response;
            }
        }

        return $feedback;
    }

    public static function receiveFromExternalLab(array $data){
        $requestId = $data["requestId"];
        $query = "SELECT a.RepositoryID FROM Lab.LabResults a FULL JOIN Lab.Patients b ON a.PatientLabNumber = b.PatientLabNumber WHERE b.RequestID = $requestId";

        $feedback = $query;
        return $feedback;

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $feedback = [];
        if (count($result) > 0){
            $repoId = $result[0]["RepositoryID"];
            $data["repository"] = $repoId;

            $feedback = self::create($data);
        }

        return $feedback;
    }

    public static function delete(int $resourceId, string $uuid, string $file)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        $query = "SELECT b.RepositoryNumber FROM Patients.PatientRepositoryItems a INNER JOIN Patients.PatientRepository b ON a.RepositoryID = b.RepositoryID WHERE RepositoryItemID = $resourceId";
        $ruuid = ((DBConnectionFactory::getConnection()->query($query))->fetchAll())[0]["RepositoryNumber"];
        $deleteBuilder = (new Builder("QueryBuilder", "delete"))->getBuilder();

        if (is_file(Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid.DIRECTORY_SEPARATOR.$file)){
            unlink(Constant::getGlobals()["file-server-path"].self::getPatientArchiveDir().DIRECTORY_SEPARATOR.$uuid.DIRECTORY_SEPARATOR."repositories".DIRECTORY_SEPARATOR.$ruuid.DIRECTORY_SEPARATOR.$file);
        }

        try
        {
            $deleteBuilder
                ->from("Patients.PatientRepositoryItems")
                ->where("RepositoryItemID = $resourceId");
            
            $result = (
                DBConnectionFactory::getConnection()
                ->exec((string)$deleteBuilder)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'RepositoryItem',
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