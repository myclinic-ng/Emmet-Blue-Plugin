<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DatabaseQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class ViewAccountBillingType.
 *
 * ViewAccountBillingType Controller
 *
 * @author Samuel Adeshina
 * @since v0.0.1 08/06/2016 14:2016
 */
class ViewAccountsBillingTypeItems
{ 
    /* viewAccountBillingType method
     *
     * @param int $accountBillingTypeId
     * @author Samuel Adeshina <samueladeshina73@gmail.com>
     */
    public static function viewAccountsBillingTypeItems(int $resourceId = 0, array $data = [])
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
            
            $selectBuilder->from("Accounts.BillingTypeItems");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingType = $resourceId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

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

    public static function viewById(int $resourceId = 0, array $data = [])
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
            
            $selectBuilder->from("Accounts.BillingTypeItems");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingTypeItemID = $resourceId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

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

    public static function viewAccountsBillingTypeItemsByStaffUUID(int $resourceId = 0, array $data = []){
        $uuid=$data['uuid'];

        $query = "SELECT a.BillingTypeName, b.* FROM Accounts.BillingType a JOIN (
                    SELECT a.* FROM Accounts.BillingTypeItems a JOIN (
                        SELECT a.* FROM Accounts.DepartmentBillingLink a JOIN (
                            SELECT a.DepartmentID, b.StaffUUID FROM Staffs.StaffDepartment a JOIN Staffs.Staff b ON a.StaffID = b.StaffID
                        ) b ON a.DepartmentID = b.DepartmentID WHERE b.StaffUUID = '$uuid'
                    ) b ON a.BillingType = b.BillingTypeID
                ) b ON a.BillingTypeID = b.BillingType";
                
        $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);

        $billingTypes = [];

        foreach ($result as $item){
            if (!isset($billingTypes[$item["BillingTypeName"]])){
                $billingTypes[$item["BillingTypeName"]] = [];
            }
            $billingType = $item["BillingTypeName"];
            unset($item["BillingTypeName"]);
            $billingTypes[$billingType][] = $item;
        }

        return $billingTypes;
    }


    public static function viewItemIntervals(int $resourceId, array $data = [])
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
            
            $selectBuilder->from("Accounts.BillingTypeItemsInterval");

            if ($resourceId !== 0){
                $selectBuilder->where("BillingTypeItemID = $resourceId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

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

    public static function isRateBased(int $patientId, array $data){
        $item = $data["item"] ?? null;

        $q = "SELECT PatientType FROM Patients.Patient WHERE PatientID = $patientId";
        $type = (DBConnectionFactory::getConnection()->query($q))->fetchAll(\PDO::FETCH_ASSOC)[0]["PatientType"];

        $query = "SELECT * FROM Accounts.BillingTypeItemsPrices WHERE BillingTypeItem=$item AND PatientType=$type";
        $result = (DBConnectionFactory::getConnection()->query($query))->fetchAll(\PDO::FETCH_ASSOC);

        if ($result){
            $rateBased = $result[0]["RateBased"];
            $rateIdentifier = $result[0]["RateIdentifier"];
            $intervalBased = $result[0]["IntervalBased"];

            return ["rateBased"=>$rateBased, "rateIdentifier"=>$rateIdentifier, "intervalBased"=>$intervalBased];
        }

        return [];
    }
}