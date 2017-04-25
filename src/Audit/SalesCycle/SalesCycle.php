<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Audit\SalesCycle;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class SalesCycle.
 *
 * SalesCycle Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class SalesCycle {
    public static function view(array $data){
        $query = "SELECT a.*, b.Name AS DepartmentName FROM FinancialAuditing.SalesLog a INNER JOIN Staffs.Department b ON a.Department = b.DepartmentID ";
        if (isset($data["paymentrequest"]) && $data["paymentrequest"] == 1){
            $query .= "WHERE a.PaymentRequestNumber IS NOT NULL ";
        }
        else {
            $query .= "WHERE a.PaymentRequestNumber IS NULL ";
        }

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function viewComplete(array $data = []){
        $salesLogs = self::view(["paymentrequest"=>1]);

        return $salesLogs;
    }

    public static function viewBroken(array $data = []){
        $salesLogs = self::view(["paymentrequest"=>1]);

        return $salesLogs;
    }

    public static function viewRogue(array $data = []){
        $salesLogs = self::view(["paymentrequest"=>0]);

        return $salesLogs;
    }
}