<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;

/**
 * class DrugNames Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class DrugNames
{
   public static function search(array $data){
        $dirts = [
             "(", ")", "-", ".", "'"
        ];
        foreach ($dirts as $dirt){
            $data["phrase"] = str_replace($dirt, " ", $data["phrase"]);
        }
        $phrase = $data["phrase"];
        $size = $data['size'] ?? 500;
        $from = $data['from'] ?? 0;

        $builtQuery = "%$phrase%";

        $uuid=$data['staff'];

        $query = "SELECT TOP 10 a.BillingTypeName, b.* FROM Accounts.BillingType a JOIN (
                    SELECT a.* FROM Accounts.BillingTypeItems a JOIN (
                        SELECT a.* FROM Accounts.DepartmentBillingLink a JOIN (
                            SELECT a.DepartmentID, b.StaffUUID FROM Staffs.StaffDepartment a JOIN Staffs.Staff b ON a.StaffID = b.StaffID
                        ) b ON a.DepartmentID = b.DepartmentID WHERE b.StaffUUID = '$uuid'
                    ) b ON a.BillingType = b.BillingTypeID
                ) b ON a.BillingTypeID = b.BillingType WHERE b.BillingTypeItemName LIKE '$builtQuery'";
                
        $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$query)
                )->fetchAll(\PDO::FETCH_ASSOC);

        $billingTypes = [];

        foreach ($result as $item){
            $query = "SELECT SUM(a.ItemQuantity) as TotalQuantity, COUNT(a.StoreID) as StoreCount FROM Pharmacy.StoreInventoryItems a INNER JOIN Pharmacy.StoreInventory b ON a.Item = b.ItemID WHERE b.Item = ".$item["BillingTypeItemID"]. "AND a.StoreID IN (SELECT DISTINCT Store FROM Pharmacy.DispensoryStoreLink)";
            $qResult = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];

            if ($qResult["StoreCount"] > 0){
                $item["_meta"] = $qResult;

                $billingTypes[] = $item; //["BillingTypeItemName"];    
            }
            else {
                continue;
            }
        }

        return $billingTypes;
    }
}