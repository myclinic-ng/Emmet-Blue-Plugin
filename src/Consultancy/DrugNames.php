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
         "(", ")", "-"
     ];
     foreach ($dirts as $dirt){
         $data["phrase"] = str_replace($dirt, " ", $data["phrase"]);
     }
     $phrase = $data["phrase"];
     $size = $data['size'] ?? 500;
     $from = $data['from'] ?? 0;

    //  $query = explode(" ", $phrase);
    //  $builtQuery = [];
    //  foreach ($query as $element){
    //      $builtQuery[] = "%".$element."%";
    //  }

    // $builtQuery = implode(" OR b.BillingTypeItemName LIKE ", $builtQuery);

    $builtQuery = "%$phrase%";

    $uuid=$data['staff'];

    $query = "SELECT a.BillingTypeName, b.* FROM Accounts.BillingType a JOIN (
                SELECT a.* FROM Accounts.BillingTypeItems a JOIN (
                    SELECT a.* FROM Accounts.DepartmentBillingLink a JOIN (
                        SELECT a.DepartmentID, b.StaffUUID FROM Staffs.StaffDepartment a JOIN Staffs.Staff b ON a.StaffID = b.StaffID
                    ) b ON a.DepartmentID = b.DepartmentID WHERE b.StaffUUID = '$uuid'
                ) b ON a.BillingType = b.BillingTypeID
            ) b ON a.BillingTypeID = b.BillingType WHERE b.BillingTypeItemName LIKE '$builtQuery'";

    // die($query);
            
    $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$query)
            )->fetchAll(\PDO::FETCH_ASSOC);

    $billingTypes = [];

    foreach ($result as $item){
        $billingTypes[] = $item["BillingTypeItemName"];
    }

    return $billingTypes;
     
     // $params = [
     //     'index'=>'rxnorm',
     //     'type'=>'drugnames',
     //     'size'=>$size,
     //     'from'=>$from,
     //     'body'=>array(
     //         "query"=>array(
     //             "query_string"=>array(
     //                 "query"=>$builtQuery
     //             )
     //         )
     //     )
     // ];

     // $esClient = ESClientFactory::getClient();

     // return $esClient->search($params);
   }
}