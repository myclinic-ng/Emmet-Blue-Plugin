<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\PharmacyRequest;

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
 * class PharmacyRequest.
 *
 * PharmacyRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class PharmacyRequest
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientID = $data['patientId'] ?? null;
        $requestedBy = $data['requestedBy'] ?? null;
        $request = $data['request'] ?? 'NULL';

        $request = base64_encode(serialize($request));

        try
        {
            $result = DBQueryFactory::insert('Pharmacy.PrescriptionRequests', [
                'PatientID'=>$patientID,
                'RequestedBy'=>$requestedBy,
                'Request'=>QB::wrapString((string)$request, "'"),
                'Acknowledged'=>0
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Pharmacy',
                'PrescriptionRequests',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (PharmacyRequest not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId, array $data=[])
    {
        $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.RequestDate DESC) AS RowNum, a.*
                         FROM Pharmacy.PrescriptionRequests a
                         INNER JOIN Patients.Patient d ON a.PatientID = d.PatientID 
                         INNER JOIN Patients.PatientType c ON d.PatientType = c.PatientTypeID";

        if ($resourceId !== 0){
            $selectBuilder .= " WHERE a.RequestID = $resourceId";
        }
        else {
            $selectBuilder .= " WHERE a.Acknowledged = 0";
        }  

        if (isset($data["paginate"])){
            if (isset($data["keywordsearch"])){
                $keyword = $data["keywordsearch"];
                $selectBuilder .= " AND (d.PatientFullName LIKE '%$keyword%' OR d.PatientUUID LIKE '%$keyword%' OR c.PatientTypeName LIKE '%$keyword%')";
            }
            $size = $data["from"] + $data["size"];
            $_query = $selectBuilder;
            $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
        }

        // die($selectBuilder);
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($viewOperation as $key => $value) {
                $id = $value['PatientID'];
                $patient = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $id);
                $viewOperation[$key]["patientInfo"] = $patient["_source"];
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int)$value["RequestedBy"])["StaffFullName"];
                $viewOperation[$key]["AcknowledgedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int)$value["AcknowledgedBy"])["StaffFullName"];
                $viewOperation[$key]["Request"] = unserialize(base64_decode($value["Request"]));

                $admissionDetails = \EmmetBlue\Plugins\Nursing\WardAdmission\WardAdmission::getAdmissionDetails((int) $id);
                if ($admissionDetails){
                    $viewOperation[$key]["isAdmitted"] = true;
                    $viewOperation[$key]["admissionDetails"] = $admissionDetails;
                }
                else {
                    $viewOperation[$key]["isAdmitted"] = false;
                }
            }
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Pharmacy',
                'PrescriptionRequests',
                (string)$selectBuilder
            );

            if ($resourceId !== 0 && isset($viewOperation[0])){
                $viewOperation = $viewOperation[0];
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


    /**
     * delete
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.PrescriptionRequests")
                ->where("RequestID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Pharmacy',
                'PrescriptionRequests',
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

    public static function close(int $resourceId, array $data = []){
        $status = $data["status"] ?? -1;
        $staff = $data["staff"] ?? null;
        $itemStatus = $data["itemStatus"] ?? [];
        $labels = $data["labels"] ?? [];

        $conn = DBConnectionFactory::getConnection();
        $query = "SELECT DispensationID FROM Pharmacy.Dispensation WHERE RequestID = $resourceId";
        $result = $conn->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($result[0])){
            $id = $result[0]["DispensationID"];
            $query = "UPDATE Pharmacy.DispensedItems SET DispensationStatus = 1 WHERE DispensationID = $id";
            $result = $conn->exec($query);
        }
        
        if (!empty($itemStatus)){
            $_query = "UPDATE Pharmacy.DispensedItems SET DispensationStatus = 0 WHERE DispensedItemsID = ".implode(" OR DispensedItemsID = ", $itemStatus);
            $result = $conn->exec($_query);
        }

        if (!empty($labels)){
            $data = [];
            foreach ($labels as $key => $value) {
                $data[] = [
                    "labeluuid" => $value["LabelUUID"],
                    "quantity" => $value["ItemDispensedUnit"],
                    "dispensation" => $id
                ];
            };

            $labelSave = \EmmetBlue\Plugins\Pharmacy\InventoryLabel\InventoryLabel::newDispensation([
                "data" => $data,
                "staff"=> $staff
            ]);
        }

        $query = "UPDATE Pharmacy.PrescriptionRequests SET Acknowledged = $status, AcknowledgedBy = $staff WHERE RequestID = $resourceId";
        return $conn->exec($query);
    }

    public static function smartify(array $data){
        /**
         * [item category][:][item full name][no. of times=>{bd, tds, qds, od}][duration]
         */
        $result = ["valid"=>false, "reason"=>"UNVALIDATED"];
        $prescriptionParts = ["item"=>[], "times"=>0, "duration"=>0];
        $timesCodes = ["bd", "tds", "qds", "od", "dly", "nocte"];
        $codeTimes = [
            "bd"=>2, "tds"=>3, "qds"=>4, "od"=>1, "dly"=> 1, "nocte"=>1
        ];

        $string = strtolower($data["prescription"]);

        $foundNumOfTimes = [];

        foreach ($timesCodes as $code){
            if (strpos(strtolower($string), " $code ") !== false){
                $foundNumOfTimes[] = $code;
            }
        }

        if (count($foundNumOfTimes) !== 1){
            $result["reason"] = "prescription number of times must be unique and must be either one of ".implode(", ", $timesCodes);
        }
        else {
            $stringParts = explode(" ".$foundNumOfTimes[0]." ", $string);
            if (count($stringParts) !== 2){
                $result["reason"] = "prescription not formatted according to SMART contract";
            }
            else {
                $notDays = false;
                $items = explode(" ", $stringParts[0]);
                $_litem = $items[count($items) - 1];
                if (strpos(strtolower($_litem), "mls") !== false){
                    $notDays = true;
                    $_litem = str_replace("mls", "", $_litem);
                    $_litem = rtrim(trim((string)$_litem));
                }
                
                if (!is_numeric($_litem)){
                    $result["reason"] = "You must specify how many ".$foundNumOfTimes[0]." to use for this prescription";
                }
                else {
                    unset($items[count($items) - 1]);

                    $items = [implode(" ", $items), $_litem];

                    $item = $items[0];
                    $q = $items[1] ?? "1";
                    $q = rtrim(trim($q));
                    $q = preg_replace("/[^0-9\/]/", "", $q);

                    $duration = $stringParts[1];

                    $itemParts = explode("::", $item);
                    if (count($itemParts) !== 2){
                        $result["reason"]= "prescription not formatted according to SMART contract";                    
                    }
                    else {
                        $prescriptionParts["item"]["category"] = rtrim(trim($itemParts[0]));
                        $prescriptionParts["item"]["name"] = rtrim(trim($itemParts[1]));

                        $prescriptionParts["duration"] = preg_replace("/[^0-9\/]/", "", $duration);

                        $prescriptionParts["times"] = $codeTimes[$foundNumOfTimes[0]] * $q;
                        $prescriptionParts["qtyPerTime"] = $q;
                        $prescriptionParts["qtyPerDay"] = $codeTimes[$foundNumOfTimes[0]];

                        $result = ["valid"=>true, "reason"=>"properly formatted"];
                    }
                }
            }
        }

        $result["parts"] = $prescriptionParts;

        if ($result["valid"]){
            $category = $result["parts"]["item"]["category"];
            $name = $result["parts"]["item"]["name"];

            $query = "SELECT * FROM Accounts.BillingType WHERE BillingTypeName = '$category'";
            $_result  = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
            if (!isset($_result[0])){
                $result["valid"] = false;
                $result["reason"] = "Item doesn't exist";
            }
            else {
                $id = $_result[0]["BillingTypeID"];
                $query = "SELECT * FROM Accounts.BillingTypeItems WHERE BillingType = $id AND BillingTypeItemName = '$name'";
                if (!isset($_result[0])){
                    $result["valid"] = false;
                    $result["reason"] = "Item doesn't exist";
                }
            }

            $duration = explode("/", $result["parts"]["duration"]);
            $duration[1] = $duration[1] ?? 1;
            $duration = $duration[0] / $duration[1];

            $qty = $result["parts"]["times"] * $duration;

            $result["parts"]["quantity"] = $qty;
            $result["parts"]["notDays"] = $notDays;
            $mlsorNot = $notDays ? "mls" : "";
            $daysOrBtl = $notDays ? "btl" : "days";
            $result["parts"]["methodString"] = $result["parts"]["qtyPerTime"].$mlsorNot.", ".$result["parts"]["qtyPerDay"]." times dly x ".$duration." ".$daysOrBtl;
            $result["parts"]["quantity"] = $notDays ? $duration : $result["parts"]["quantity"];
        }

        return $result;
    }
}