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
                $patient = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $id);
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
        $query = "UPDATE Pharmacy.PrescriptionRequests SET Acknowledged = $status, AcknowledgedBy = $staff WHERE RequestID = $resourceId";
        return DBConnectionFactory::getConnection()->exec($query);
    }
}