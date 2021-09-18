<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab\LabResult;

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
 * class LabResult.
 *
 * LabResult Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class LabResult
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientId = $data['patientLabNumber'] ?? null;
        $investigationName = $data['investigationName'] ?? null;
        $report = $data['report'] ?? null;
        $reportedBy = $data['reportedBy'] ?? null;
        $requests = $data["requests"] ?? [];
        $meta = $data["meta"] ?? [];
        try
        {
            $repoData = [
                "patient"=>$patientId,
                "name"=>$investigationName,
                "type"=>"lab_result",
                "creator"=>$reportedBy
            ];
            $repoId = \EmmetBlue\Plugins\Patients\PatientRepository\PatientRepository::create($repoData)["lastInsertId"];

            $repoItemData = [
                "repository"=>$repoId,
                "name"=>"Investigation Conclusion",
                "category"=>"file",
                "file"=>serialize($report),
                "file_ext"=>$meta["fileExt"] ?? "img",
                "creator"=>$reportedBy
            ];

            if (isset($meta["category"]) && $meta["category"] == "json"){
                $repoItemData["json"] = $report;
                $repoItemData["category"] = "json";
            }

            \EmmetBlue\Plugins\Patients\RepositoryItem\RepositoryItem::create($repoItemData);

            if (!empty($requests)){
                $reqs = [];
                $reqs2 = [];
                foreach ($requests as $key=>$value){
                    $reqs[] = "($value, $repoId, '$reportedBy')";
                    $reqs2[] = "UPDATE Lab.Patients SET Published = 1 WHERE PatientLabNumber = $value";

                    try {
                        \EmmetBlue\Plugins\EmmetblueCloud\Lab::sendPublishStatus([
                            "patient"=>$patientId,
                            "labNumber"=>$value,
                            "staff"=>$reportedBy
                        ]);
                    }
                    catch(\Exception $e){
                        
                    }
                }

                $query = "INSERT INTO Lab.LabResults (PatientLabNumber, RepositoryID, ReportedBy) VALUES ".implode(", ", $reqs);
                $result = DBConnectionFactory::getConnection()->exec($query);

                DBConnectionFactory::getConnection()->exec(implode(";", $reqs2));
            }

            $feedback = [];

            $query = "SELECT b.ExternalRequestID, b.ExternalBusinessID FROM Lab.Patients a INNER JOIN Lab.LinkedExternalRequests b ON a.RequestID = b.LocalRequestID WHERE a.PatientLabNumber = $patientId";
            $res = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
            
            if (count($res) > 0){
                $requestId = $res[0]["ExternalRequestID"];
                $businessId = $res[0]["ExternalBusinessID"];
                foreach ($requests as $key=>$value){
                    $requests[$key] = $requestId;
                }

                $requestData = $data;
                $requestData["requestId"] = $requestId;
                $requestData["requests"] = $requests;

                $query = "SELECT * FROM EmmetBlueCloud.BusinessLinkAuth WHERE ExternalBusinessID = ".$businessId;
                $_res = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

                return [$requestData, $_res];

                if (count($_res) > 0){
                    $auth = $_res[0];
                    $url = $auth["EndpointUrl"]."/lab/lab-result/create-with-request-id";
                    $token = $auth["Token"];
                    $token_user = $auth["UserId"];

                    $request = HTTPRequest::post($url, $requestData, [
                        'AUTHORIZATION'=>$token
                    ]);

                    $response = json_decode($request->body, true);

                    $feedback = $response;
                }
            }

            return ["repoId"=>$repoId, "feedback"=>$feedback];
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (LabResult not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function createWithRequestId(array $data){
        $requestId = $data["requestId"];
        $query = "SELECT PatientLabNumber FROM Lab.Patients WHERE RequestID = $requestId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        if (count($result) > 0){
            $labNumber = $result[0]["PatientLabNumber"];
            $data['patientLabNumber'] = $labNumber;
            $data['requests'][0] = $labNumber;

            return self::create($data);
        }
    }

    /**
     * view
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = "SELECT * FROM Lab.LabResults WHERE PatientLabNumber = $resourceId";
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabResults',
                (string)$selectBuilder
            );

            foreach ($viewOperation as $key => $value) {
                $viewOperation[$key]["Report"] = unserialize($value["Report"]);
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
        // $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        // try
        // {
        //     if (isset($data['FullName'])){
        //         $data['FullName'] = QB::wrapString($data['FullName'], "'");
        //     }
        //     $updateBuilder->table("Lab.LabResults");
        //     $updateBuilder->set($data);
        //     $updateBuilder->where("LabResultLabNumber = $resourceId");

        //     $result = (
        //             DBConnectionFactory::getConnection()
        //             ->query((string)$updateBuilder)
        //         );

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

    /**
     * delete
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Lab.LabResults")
                ->where("ResultID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabResults',
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

    public static function getResults(array $data){
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('ROW_NUMBER() OVER (ORDER BY a.RegistrationDate) AS RowNum, a.PatientLabNumber, a.PatientID, a.FullName, a.InvestigationRequired, a.RegistrationDate, a.RequestID, a.RequestedBy, a.DateRequested, a.Published, a.Unlocked, b.InvestigationTypeName, b.InvestigationTypeID, c.LabName, c.LabID')
            ->from('Lab.Patients a')
            ->innerJoin('Lab.InvestigationTypes b', 'a.InvestigationTypeRequired = b.InvestigationTypeID')
            ->innerJoin('Lab.Labs c', 'b.InvestigationTypeLab = c.LabID')
            ->where('a.Published = 0');

        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY b.DateReported DESC) AS RowNum, a.RequestID, a.InvestigationTypeRequired, 
                            a.RegistrationDate, a.PatientID, b.*, c.InvestigationTypeLab, c.InvestigationTypeName, d.LabName
                            FROM Lab.Patients a INNER JOIN Lab.LabResults b ON a.PatientLabNumber = b.PatientLabNumber
                            INNER JOIN Lab.InvestigationTypes c ON a.InvestigationTypeRequired = c.InvestigationTypeID
                            INNER JOIN Lab.Labs d ON c.InvestigationTypeLab = d.LabID
                            LEFT OUTER JOIN Patients.Patient e ON a.PatientID = e.PatientID
                            INNER JOIN Staffs.StaffProfile f ON b.ReportedBy = f.StaffID WHERE 1=1
                        ";
        if (isset($data['startdate'])){
            $selectBuilder .= " AND CONVERT(date, b.DateReported) BETWEEN '".$data["startdate"]."' AND '".$data["enddate"]."'"; 
        }
        if (isset($data["patient"])){
            $selectBuilder .= " AND e.PatientUUID = '".$data["patient"]."'";
        }

        if (isset($data["investigation"])){
            $selectBuilder .= " AND a.InvestigationRequired = ".$data["investigation"];
        }

        if (isset($data["lab"])){
            $selectBuilder .= " AND d.LabID = ".$data["lab"];
        }

        if (isset($data["staff"])){
            $selectBuilder .= " AND b.ReportedBy = ".$data["staff"];
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (e.PatientFullName LIKE '%$keyword%' OR f.StaffFullName LIKE '%$keyword%' OR c.InvestigationTypeName LIKE '%$keyword%' OR d.LabName LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = (string) $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $result["PatientID"])["_source"];
                $viewOperation[$key]["ReportedByDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["ReportedBy"]);
            }

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

    public static function viewByRepositoryId(int $repositoryId){
        $query = "SELECT a.PatientLabNumber, a.ResultID, a.DateReported, a.ReportedBy, b.FullName, b.DateOfBirth, b.PatientID, b.Gender, b.RequestedBy, b.DateRequested FROM Lab.LabResults a INNER JOIN Lab.Patients b ON a.PatientLabNumber = b.PatientLabNumber WHERE a.RepositoryID=$repositoryId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        $result = $result[0] ?? [];
        if (!empty($result)){
            $result["ReportedBy"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["ReportedBy"]);
            $result["RequestedBy"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"]);
            $result["PatientPicture"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $result["PatientID"])["_source"]["patientpicture"];

        }

        return $result;
    }
}