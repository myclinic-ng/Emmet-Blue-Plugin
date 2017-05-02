<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Dispensation;

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
 * class Reports.
 *
 * Reports Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 02/05/2017 10:41
 */
class Reports
{

    public static function retrieveDispensedItems(int $resourceId = 0, array $data = [])
    {
        $filters = $data["filtertype"] ?? null;
        try
        {
            $selectBuilder = "SELECT ROW_NUMBER() OVER (ORDER BY a.DispensationDate) AS RowNum, 
                                a.*, b.ItemID, b.DispensedQuantity, c.BillingTypeItemName, c.BillingType, d.BillingTypeName, e.StaffID, g.PatientTypeName, g.CategoryName, h.StoreName, i.EligibleDispensory AS DispensoryName
                                FROM Pharmacy.Dispensation a 
                                INNER JOIN Pharmacy.DispensedItems b ON a.DispensationID = b.DispensationID
                                INNER JOIN Accounts.BillingTypeItems c ON b.ItemID = c.BillingTypeItemID
                                INNER JOIN Accounts.BillingType d ON c.BillingType = d.BillingTypeID
                                INNER JOIN Staffs.Staff e ON a.DispenseeID = e.StaffUUID
                                INNER JOIN Patients.Patient f ON a.Patient = f.PatientID
                                INNER JOIN Patients.PatientType g ON f.PatientType = g.PatientTypeID
                                INNER JOIN Pharmacy.Store h ON a.DispensingStore = h.StoreID
                                INNER JOIN Pharmacy.EligibleDispensory i ON i.EligibleDispensoryID = a.EligibleDispensory";

            if (!is_null($filters)){
                $sDate = QB::wrapString($data["startdate"], "'");
                $eDate = QB::wrapString($data["enddate"], "'");
                $selectBuilder .= " WHERE (CONVERT(date, a.DispensationDate)) BETWEEN $sDate AND $eDate";


                switch($data["filtertype"]){
                    case "patient":{
                        $selectBuilder .= " AND a.Patient = ".$data["query"];
                        break;
                    }
                    case "staff":{
                        $selectBuilder .= " AND e.StaffID = ".$data["query"];
                        break;
                    }
                    case "patienttype":{
                        $selectBuilder .= " AND g.CategoryName = '".$data["query"]."'";
                        break;
                    }
                    case "billingtype":{
                        $selectBuilder .= " AND c.BillingType = ".$data["query"];
                        break;
                    }
                    case "store":{
                        $selectBuilder .= " AND a.DispensingStore = ".$data["query"];
                        break;
                    }
                    default:{

                    }
                }

                unset($data["filtertype"], $data["query"], $data["startdate"], $data["enddate"]);

                if (isset($data["constantstatus"]) && $data["constantstatus"] != ""){
                   unset($data["constantstatus"]);
                }
            }

            if ($resourceId !== 0){
                $selectBuilder .= " AND a.ItemID = $resourceId";
            }

            if (isset($data["paginate"])){
                if (isset($data["keywordsearch"])){
                    $keyword = $data["keywordsearch"];
                    $selectBuilder .= " AND (c.BillingTypeItemName LIKE '%$keyword%' OR g.PatientTypeName LIKE '%$keyword%')";
                }
                $size = $data["from"] + $data["size"];
                $_query = $selectBuilder;
                $selectBuilder = "SELECT * FROM ($selectBuilder) AS RowConstrainedResult WHERE RowNum >= ".$data["from"]." AND RowNum < ".$size." ORDER BY RowNum";
            }

            $dispensationResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dispensationResult as $key => $value) {
                $dispensationResult[$key]["patientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $value["Patient"])["_source"];
                $dispensationResult[$key]["staffInfo"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"]);
                $dispensationResult[$key]["staffInfo"]["Role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $value["StaffID"])["Name"];
            }

            if (isset($data["paginate"])){
                $total = count(DBConnectionFactory::getConnection()->query($_query)->fetchAll(\PDO::FETCH_ASSOC));
                // $filtered = count($_result) + 1;
                $dispensationResult = [
                    "data"=>$dispensationResult,
                    "total"=>$total,
                    "filtered"=>$total
                ];
            }

            return $dispensationResult;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}