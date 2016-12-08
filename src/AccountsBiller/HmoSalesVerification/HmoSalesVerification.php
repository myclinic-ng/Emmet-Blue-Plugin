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
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Accounts.BillingHmoSalesVerification a");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingHmoSalesVerificationID = $resourceId");
            }


            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

           if (empty($data)){
                foreach ($result as $key=>$metaItem)
                {
                    $id = $metaItem["BillingHmoSalesVerificationID"];
                    $patient = $metaItem["PatientID"];
                    $query = "SELECT * FROM Accounts.BillingTransactionItems WHERE BillingHmoSalesVerificationID = $id";
                    $query2 = "SELECT FieldTitle, FieldValue FROM Patients.PatientRecordsFieldValue WHERE PatientID=$patient";

                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->query($query)
                    )->fetchAll(\PDO::FETCH_ASSOC);

                    $queryResult2 = (
                        DBConnectionFactory::getConnection()
                        ->query($query2)
                    )->fetchAll(\PDO::FETCH_ASSOC);

                    $name = "";
                    foreach ($queryResult2 as $value){
                        if ($value["FieldTitle"] == 'Title'){
                            $name .= $value["FieldValue"];
                        }
                        else if ($value["FieldTitle"] == 'FirstName'){
                            $name .= " ".$value["FieldValue"];
                        }
                        else if ($value["FieldTitle"] == 'LastName'){
                            $name .= " ".$value["FieldValue"];
                        }
                    }

                    $result[$key]["BillingTransactionItems"] = $queryResult;
                    $result[$key]["PatientName"] = $name;
                }
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

    public static function getStatus(array $data)
    {
        $patient = $data["uuid"];
        $department = $data["department"];

        $query = "SELECT PatientID FROM Patients.Patient WHERE PatientUUID = '$patient'";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);


        $patientId = $result[0]["PatientID"];

        $query = "SELECT TOP 1 * FROM Accounts.HmoSalesVerification WHERE Status IS NULL AND PatientID = $patientId ORDER BY RequestDate DESC";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }
}