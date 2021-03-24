<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\TransactionMeta;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class BillingTransactionMeta.
 *
 * BillingTransactionMeta Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class TransactionMeta
{
    private static function generateTransactionNumber()
    {
        $string = date(DATE_RFC2822);
        $date = new \DateTime($string);

        $str = $date->format('Ymd');
        $query = "SELECT TOP 1 BillingTransactionMetaID as id FROM Accounts.BillingTransactionMeta ORDER BY BillingTransactionMetaID DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
        if (isset($result[0])){
            $result = $result[0]["id"] + 1;
        }
        else {
            $result = 0;
        }

        return $str.$result;  
    }

    public static function create(array $data)
    {
        $patient = $data['patient'] ?? null;
        $type = $data['type'] ?? null;
        $createdBy = $data['createdBy'] ?? null;
        $items = $data['items'] ?? null;
        $status = $data['status'] ?? null;
        $amount = $data['amount'] ?? null;
        $transactionNumber = self::generateTransactionNumber();

        try
        {
            $result = DBQueryFactory::insert('Accounts.BillingTransactionMeta', [
                'BillingTransactionNumber'=>QB::wrapString($transactionNumber, "'"),
                'PatientID'=>$patient,
                'CreatedByUUID'=>(is_null($createdBy)) ? "NULL" : QB::wrapString($createdBy, "'"),
                'DateCreated'=>'GETDATE()',
                'BilledAmountTotal'=>(is_null($amount)) ? "NULL" : QB::wrapString((string)$amount, "'"),
                'BillingTransactionStatus'=>(is_null($status)) ? "NULL" : QB::wrapString((string)$status, "'")
            ]);
            
            $id = $result['lastInsertId']; 

            $itemNames = [];
            foreach ($items as $datum){
                $itemNames[] = "($id, ".QB::wrapString((string)$datum['itemCode'], "'").", ".QB::wrapString((string)$datum['itemQuantity'], "'").", ".QB::wrapString((string)$datum['itemPrice'], "'").")";
            }

            $query = "INSERT INTO Accounts.BillingTransactionItems (BillingTransactionMetaID, BillingTransactionItem, BillingTransactionItemQuantity, BillingTransactionItemPrice) VALUES ".implode(", ", $itemNames);

            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            return ['lastInsertId'=>$id, "transactionNumber"=>$transactionNumber];
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
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['BillingTransactionStatus'])){
                $data['BillingTransactionStatus'] = QB::wrapString($data['BillingTransactionStatus'], "'");
            }
            if (isset($data['BilledAmountTotal'])){
                $data['BilledAmountTotal'] = QB::wrapString($data['BilledAmountTotal'], "'");
            }
            if (isset($data['BillingType'])){
                $data['BillingType'] = QB::wrapString($data['BillingType'], "'");
            }

            $updateBuilder->table("Accounts.BillingTransactionMeta");
            $updateBuilder->set($data);
            $updateBuilder->where("BillingTransactionMetaID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function getTransactionNumber(int $resourceId){
        $query = "SELECT BillingTransactionNumber FROM Accounts.BillingTransactionMeta WHERE BillingTransactionMetaID=$resourceId";
        $result = (
            DBConnectionFactory::getConnection()
            ->query((string)$query)
        )->fetchAll(\PDO::FETCH_ASSOC);

        $result = $result[0] ?? 0;

        return $result;
    }

    /**
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        try
        {

            if ($resourceId == 0) {
                $query = "
                            SELECT 
                            ROW_NUMBER() OVER (ORDER BY a.BillingTransactionMetaID) AS RowNum, a.*, b.BillingAmountPaid, b.BillingAmountBalance , b.BillingTransactionDate
                            FROM Accounts.BillingTransactionMeta a 
                            FULL OUTER JOIN Accounts.BillingTransaction b ON a.BillingTransactionMetaID = b.BillingTransactionMetaID
                        ";

                $cQuery = "SELECT COUNT(*) as total FROM Accounts.BillingTransactionMeta a FULL OUTER JOIN Accounts.BillingTransaction b ON a.BillingTransactionMetaID = b.BillingTransactionMetaID";

                switch($data["filtertype"]){
                    case "patient":{
                        $add = " WHERE a.PatientID = '".$data["query"]."'";
                        break;
                    }
                    case "date":{
                        $sDate = QB::wrapString($data["startdate"], "'");
                        $eDate = QB::wrapString($data["enddate"], "'");
                        $add = " WHERE (CONVERT(date, a.DateCreated) BETWEEN $sDate AND $eDate) OR (CONVERT(date, b.BillingTransactionDate) BETWEEN $sDate AND $eDate)";
                        break;
                    }
                    case "department":{
                        $query .= " WHERE a.RequestDepartment = ".$data["query"];
                        break;
                    }
                    case "status":{
                        $add = " WHERE a.BillingTransactionStatus = ".$data["query"];
                        break;
                    }
                    case "staff":{
                        $add = " WHERE b.StaffID = ".$data["query"];
                        break;
                    }
                    case "patient-date":{
                        $add = " WHERE a.PatientID = '".$data["query"]."'";
                        $sDate = QB::wrapString($data["startdate"], "'");
                        $eDate = QB::wrapString($data["enddate"], "'");
                        $add .= " AND ((CONVERT(date, a.DateCreated) BETWEEN $sDate AND $eDate) OR (CONVERT(date, b.BillingTransactionDate) BETWEEN $sDate AND $eDate))";
                        break;
                    }
                }

                $query .= $add;
                $cQuery .= $add;
                $from = $data["from"];
                $to = $data["to"];

                $query = "SELECT * FROM ($query) AS RowConstrainedResult WHERE RowNum >= $from AND RowNum < $to ORDER BY RowNum";
            }
            else {
                $query = "
                    SELECT 
                    a.*, b.BillingAmountPaid, b.BillingAmountBalance , b.BillingTransactionDate
                    FROM Accounts.BillingTransactionMeta a 
                    FULL OUTER JOIN Accounts.BillingTransaction b ON a.BillingTransactionMetaID = b.BillingTransactionMetaID
                    WHERE a.BillingTransactionMetaID = $resourceId
                ";
            }

            // die($query);

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$query)
            )->fetchAll(\PDO::FETCH_ASSOC);

           
            foreach ($result as $key=>$metaItem)
            {
                $id = $metaItem["BillingTransactionMetaID"];
                $patient = $metaItem["PatientID"];
                $_patient = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $patient);
                if (isset($_patient["_source"])){
                    $result[$key]["PatientName"] = $_patient["_source"]["patientfullname"];
                }
                if (is_null($metaItem["BillingAmountPaid"])){
                    $status = 0;
                }
                else{
                    $status = 1;
                }

                $result[$key]["_meta"] = [
                    "status"=>$status
                ];


                $result[$key]["BillingAmountBalance"] = number_format((float)$metaItem["BillingAmountBalance"], 2, '.', '');
                $result[$key]["BilledAmountTotal"] = number_format((float)$metaItem["BilledAmountTotal"], 2, '.', '');
            }

            if ($resourceId == 0){
                $count = DBConnectionFactory::getConnection()->query($cQuery)->fetchAll(\PDO::FETCH_ASSOC)[0]["total"];

                // $result["_meta"] = [
                //     "pageCount"=>$count,
                //     "pageFrom"=>$from,
                //     "pageTo"=>$to
                // ];
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

    public static function viewByNumber(int $resourceId = 0, array $data = [])
    {
        try
        {
           $selectBuilder = "SELECT TOP 1 a.*, b.BillingTransactionCustomerName, b.BillingTransactionCustomerPhone, b.BillingAmountPaid, b.BillingAmountBalance, c.RequestDepartment, d.Name as RequestDepartmentName FROM Accounts.BillingTransactionMeta a FULL OUTER JOIN Accounts.BillingTransaction b ON a.BillingTransactionMetaID = b.BillingTransactionMetaID INNER JOIN Accounts.PaymentRequest c ON a.BillingTransactionMetaID = c.AttachedInvoice INNER JOIN Staffs.Department d ON c.RequestDepartment = d.DepartmentID WHERE BillingTransactionNumber = CAST($resourceId AS NUMERIC) ORDER BY b.BillingTransactionDate DESC";

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

           if (empty($data)){
                foreach ($result as $key=>$metaItem)
                {
                    $id = $metaItem["BillingTransactionMetaID"];
                    $result[$key]["BillingAmountPaid"] = $metaItem["BillingAmountPaid"] = number_format((float) (DBConnectionFactory::getConnection()->query("SELECT SUM(BillingAmountPaid) AS total FROM Accounts.BillingTransaction WHERE BillingTransactionMetaID = $id")->fetchAll(\PDO::FETCH_ASSOC)[0]["total"]), 2, '.', '');
                    $result[$key]["BillingAmountBalance"] = number_format((float)$metaItem["BillingAmountBalance"], 2, '.', '');
                    $result[$key]["BilledAmountTotal"] = number_format((float)$metaItem["BilledAmountTotal"], 2, '.', '');
                    $patient = $metaItem["PatientID"];
                    $query = "SELECT a.*, b.BillingTypeItemName FROM Accounts.BillingTransactionItems a INNER JOIN Accounts.BillingTypeItems b ON a.BillingTransactionItem = b.BillingTypeItemID WHERE a.BillingTransactionMetaID = $id";
                    $queryResult = (
                        DBConnectionFactory::getConnection()
                        ->query($query)
                    )->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($queryResult as $k=>$i){
                        $queryResult[$k]["BillingTransactionItemPrice"] = number_format((float)$i["BillingTransactionItemPrice"], 2, '.', '');
                    }

                    $result[$key]["BillingTransactionItems"] = $queryResult;
                    $result[$key]["PatientName"] = \EmmetBlue\Plugins\Patients\Patient\Patient::viewBasic((int) $patient)["_source"]["patientfullname"];
                    if (is_null($metaItem["BillingAmountPaid"])){
                        $status = 0;
                    }
                    else{
                        $status = 1;
                    }
                    $result[$key]["_meta"] = [
                        "status"=>$status
                    ];
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

    public static function search(array $data){
        $query = explode(" ", $data["query"]);
        $builtQuery = [];
        foreach ($query as $element){
            $builtQuery[] = "(".$element."* ".$element."~)";
        }

        $builtQuery = implode(" AND ", $builtQuery);
        
        $params = [
            'index'=>'accounts',
            'type'=>'transaction-meta',
            'size'=>$data['size'],
            'from'=>$data['from'],
            'body'=>array(
                "query"=>array(
                    "query_string"=>array(
                        "query"=>$builtQuery
                    )
                )
            )
        ];

        $esClient = ESClientFactory::getClient();

        return $esClient->search($params);
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.BillingTransactionMeta")
                ->where("BillingTransactionMetaID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
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
}