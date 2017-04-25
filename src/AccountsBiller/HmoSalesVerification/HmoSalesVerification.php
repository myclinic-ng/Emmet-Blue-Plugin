<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\HmoSalesVerification;

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
 * class BillingHmoSalesVerification.
 *
 * BillingHmoSalesVerification Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoSalesVerification
{
    public static function create(array $data)
    {
        $patient = $data['patient'] ?? 'null';
        $requestBy = $data['requestBy'] ?? null;
        $items = $data['items'] ?? null;

        $query = "SELECT b.DepartmentID from Staffs.Staff a JOIN Staffs.StaffDepartment b ON a.StaffID = b.StaffID WHERE a.StaffID = $requestBy";
        $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$query)
            )->fetchAll(\PDO::FETCH_ASSOC);
        $requestDepartment = $result[0]["DepartmentID"];

        $items = base64_encode(serialize($items));
        try
        {
            $result = DBQueryFactory::insert('Accounts.HmoSalesVerification', [
                'DepartmentID'=>$requestDepartment,
                'PatientID'=>$patient,
                'SaleRequest'=>QB::wrapString($items, "'"),
                'StaffID'=>$requestBy
            ]);

            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("a.*, b.Name as DepartmentName");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Accounts.HmoSalesVerification a")->innerJoin("Staffs.Department b", "a.DepartmentID = b.DepartmentID");

            if ($resourceId !== 0){
                $selectBuilder->where("SalesID = $resourceId");
            }

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach($result as $key=>$data){
                $result[$key]["SaleRequest"] = unserialize(base64_decode($data["SaleRequest"]));
                $result[$key]["StaffFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["StaffID"])["StaffFullName"];
                if ($data["SignedBy"] != null){
                    $result[$key]["SignedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["SignedBy"])["StaffFullName"];
                }

                $result[$key]["PatientInformation"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $data["PatientID"])["_source"];
            }

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function loadUnprocessedRequests(int $patientType){
        $query = "SELECT a.SalesID, a.DepartmentID, a.StaffID, a.RequestDate, b.PatientID, b.PatientFullName, c.Name as DepartmentName FROM Accounts.HmoSalesVerification a INNER JOIN Patients.Patient b On a.PatientID = b.PatientID INNER JOIN Staffs.Department c ON a.DepartmentID = c.DepartmentID WHERE b.PatientType=$patientType AND a.ProceedStatus IS NULL";

        $result = (
            DBConnectionFactory::getConnection()
            ->query((string)$query)
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $key=>$data){
            $result[$key]["PatientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $data["PatientID"])["_source"];
            $result[$key]["StaffDetails"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $data["StaffID"]);
        }

        return $result;
    }

    public static function getStatus(array $data)
    {
        $patient = $data["uuid"];
        $staff = $data["staff"];

        $q = "SELECT DepartmentID FROM Staffs.StaffDepartment WHERE StaffID = ".$staff;
        $department = DBConnectionFactory::getConnection()->query($q)->fetchAll(\PDO::FETCH_ASSOC)[0]["DepartmentID"];

        $query = "SELECT PatientID FROM Patients.Patient WHERE PatientUUID = '$patient'";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);


        $patientId = $result[0]["PatientID"];

        $query = "SELECT TOP 1 * FROM Accounts.HmoSalesVerification WHERE DepartmentID = $department AND PatientID = $patientId ORDER BY RequestDate DESC";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    public static function verifyRequest(array $data){
        $proceedStatus = $data["proceedStatus"] ?? null;
        $signComment = $data["signComment"] ?? null;
        $signedBy = $data["signedBy"] ?? null;
        $status = $data["status"] ?? null;
        $request = $data["request"] ?? null;

        $query = "UPDATE Accounts.HmoSalesVerification SET ProceedStatus = $proceedStatus, SignedBy = $signedBy, SignComment = '$signComment', SignedDate = GETDATE(), Status = '$status' WHERE SalesID = $request";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }
}