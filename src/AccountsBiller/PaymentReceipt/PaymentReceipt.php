<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\PaymentReceipt;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class PaymentReceipt.
 *
 * PaymentReceipt Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PaymentReceipt
{
    public static function create(array $data)
    {
        $patientId = $data['patient'] ?? null;
        $report = $data['receipt'] ?? null;
        $transaction = $data['transaction'] ?? null;
        $printedBy = $data['staff'] ?? null;

        try
        {
            $trans = \EmmetBlue\Plugins\AccountsBiller\Transaction\Transaction::view((int)$transaction)[0];
            $strs = [];
            if (isset($trans["BillingTransactionCustomerName"])){
                $strs[] = "Received from: ".$trans["BillingTransactionCustomerName"];
            }
            if (isset($trans["BillingAmountPaid"])){
                $strs[] = "Amount: ".$trans["BillingAmountPaid"];
            }
            if (isset($trans["BillingPaymentMethod"])){
                $strs[] = "Method: ".$trans["BillingPaymentMethod"];
            }

            $repoData = [
                "patient"=>$patientId,
                "name"=>"Payment Receipt. #$transaction",
                "type"=>"payment_receipt",
                "creator"=>$printedBy,
                "description"=>implode(". ", $strs)
            ];

            $repoId = \EmmetBlue\Plugins\Patients\PatientRepository\PatientRepository::create($repoData)["lastInsertId"];

            $repoItemData = [
                "repository"=>$repoId,
                "name"=>"Receipt No. #$transaction",
                "category"=>"file",
                "file"=>serialize($report),
                "file_ext"=>"img",
                "creator"=>$printedBy
            ];

            \EmmetBlue\Plugins\Patients\RepositoryItem\RepositoryItem::create($repoItemData);

            $query = "INSERT INTO Accounts.PaymentReceipts (PatientID, AssociatedTransaction, RepositoryID, PrintedBy) VALUES ($patientId, $transaction, $repoId, $printedBy)";

            return DBConnectionFactory::getConnection()->exec($query);
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Receipt Repository not updated), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function edit(int $resourceId, array $data)
    {

    }

    public static function view(array $data = [])
    {
        $query = 
                "
                    SELECT 
                        ROW_NUMBER() OVER (ORDER BY a.RequestDate DESC) AS RowNum,
                        a.*, 
                        b.Name, 
                        b.GroupID, 
                        c.PatientUUID, 
                        c.PatientFullName, 
                        c.PatientType,
                        c.PatientPicture, 
                        d.GroupName, 
                        e.CategoryName AS PatientCategoryName, 
                        e.PatientTypeName, 
                        f.*,
                        g.BillingTransactionNumber as AttachedInvoiceNumber,
                        g.BillingTransactionStatus  
                    FROM Accounts.BillingTransaction f
                    INNER JOIN Accounts.PaymentRequest a ON f.BillingTransactionMetaID = a.AttachedInvoice
                    JOIN Staffs.Department b ON a.RequestDepartment=b.DepartmentID 
                    JOIN Staffs.DepartmentGroup d ON b.GroupID=d.DepartmentGroupID 
                    JOIN Patients.Patient c ON a.RequestPatientID=c.PatientID 
                    JOIN Patients.PatientType e ON c.PatientType = e.PatientTypeID 
                    LEFT OUTER JOIN Accounts.BillingTransactionMeta g ON g.BillingTransactionMetaID = f.BillingTransactionMetaID
                ";

        switch($data["filtertype"]){
            case "patient":{
                $query .= " WHERE c.PatientUUID = '".$data["query"]."'";
                break;
            }
            case "date":{
                $sDate = QB::wrapString($data["startdate"], "'");
                $eDate = QB::wrapString($data["enddate"], "'");
                $query .= " WHERE CONVERT(date, f.BillingTransactionDate) BETWEEN $sDate AND $eDate";
                break;
            }
            case "department":{
                $query .= " WHERE a.RequestDepartment = ".$data["query"];
                break;
            }
            case "status":{
                $query .= " WHERE a.RequestFulfillmentStatus = ".$data["query"];
                break;
            }
            case "staff":{
                $query .= " WHERE f.StaffID = ".$data["query"];
                break;
            }
            case "patientcategory":{
                $query .= " WHERE e.CategoryName = '".$data["query"]."'";
                break;
            }
            case "patienttype":{
                $query .= " WHERE c.PatientType = '".$data["query"]."'";
                break;
            }
            case "paymentmethod":{
                $query .= " WHERE f.BillingPaymentMethod = '".$data["query"]."'";
                break;
            }
            case "invoice":{
                $query .= " WHERE a.AttachedInvoice = ".$data["query"];
                break;
            }
        }

        unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);

        if (isset($data["constantstatus"]) && $data["constantstatus"] != ""){
           $query .= " AND a.RequestFulfillmentStatus = ".$data["constantstatus"];
           unset($data["constantstatus"]);
        }

        if (!empty($data)){
            $_filters = ["status"=>[], "department"=>[], "date"=>[]];
            if (isset($data["_status"]) && $data["_status"] != ""){
                $data["_status"] = explode(",", str_replace(" ", "", $data["_status"]));

                foreach ($data["_status"] as $value) {
                    $_filters["status"][] = "a.RequestFulfillmentStatus=".$value;
                }
            }

            if (isset($data["_date"]) && $data["_date"] != ""){
                $data["_date"] = explode(",", str_replace(" ", "", $data["_date"]));

                foreach ($data["_date"] as $value) {
                    $_filters["date"][] = "CAST(f.BillingTransactionDate AS DATE) ='".$value."'";
                }
            }

            if (isset($data["_department"]) && $data["_department"] != ""){
                $data["_department"] = explode(",", str_replace(" ", "", $data["_department"]));

                foreach ($data["_department"] as $value) {
                    $_filters["department"][] = "a.RequestDepartment =".$value;
                }
            }

            if (isset($data["_patienttype"]) && $data["_patienttype"] != ""){
                $data["_patienttype"] = explode(",", str_replace(" ", "", $data["_patienttype"]));

                foreach ($data["_patienttype"] as $value) {
                    $_filters["patienttype"][] = "e.PatientTypeID =".$value;
                }
            }

            if (isset($data["_paymentmethod"]) && $data["_paymentmethod"] != ""){
                $data["_paymentmethod"] = explode(",", str_replace(" ", "", $data["_paymentmethod"]));

                foreach ($data["_paymentmethod"] as $value) {
                    $_filters["paymentmethod"][] = "f.BillingPaymentMethod ='".$value."'";
                }
            }

            $string[] = empty($_filters["status"]) ? "1=1" : "(".implode(" OR ", $_filters["status"]).")";
            $string[] = empty($_filters["date"]) ? "1=1" : "(".implode(" OR ", $_filters["date"]).")";
            $string[] = empty($_filters["department"]) ? "1=1" : "(".implode(" OR ", $_filters["department"]).")";
            $string[] = empty($_filters["patienttype"]) ? "1=1" : "(".implode(" OR ", $_filters["patienttype"]).")";
            $string[] = empty($_filters["paymentmethod"]) ? "1=1" : "(".implode(" OR ", $_filters["paymentmethod"]).")";

            $string = implode(" AND ", $string);

            if ($string != ""){
                $query .= " AND (".$string.")";
            }
        }

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $query .= " AND (c.PatientFullName LIKE '%$keyword%' OR e.PatientTypeName LIKE '%$keyword%' OR b.Name LIKE '%$keyword%' OR e.CategoryName LIKE '%$keyword%')";
            }

            $_query = $query;
            $size = $data["size"] + $data["from"];
            $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }
        // die($query);
        try
        {
            $viewPaymentRequestOperation = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($viewPaymentRequestOperation as $value) {
                $key = $value["PaymentRequestID"];
                if (!isset($result[$key])){
                    $result[$key] = $value; 
                }
                else {
                    $result[$key]["BillingAmountPaid"] += $value["BillingAmountPaid"];
                }

                $result[$key]["RequestByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullNameFromUUID(["uuid"=>$result[$key]["RequestBy"]])["StaffFullName"];
            }
                
            $_result = [];
            $meta = [
                "sumTotal"=>0,
                "totalPatients"=>[]
            ];

            foreach ($result as $value){
                $_result[] = $value;
                $meta["sumTotal"] += $value["BillingAmountPaid"];
                if (!in_array($value["RequestPatientID"], $meta["totalPatients"])){
                    $meta["totalPatients"][] = $value["RequestPatientID"];
                }
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $meta["totalPatients"] = count($meta["totalPatients"]);
                $meta["totalReceipts"] = count($_result);
                $_result = [
                    "data"=>$_result,
                    "meta"=>$meta,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }

            return $_result;  
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

    public static function delete(int $resourceId)
    {

    }
}