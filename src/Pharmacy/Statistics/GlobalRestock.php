<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Statistics;

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
 * class Statistics.
 *
 * Statisticss and store inventory properies Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class GlobalRestock
{
    public static function daysSinceLastRestock(int $resourceId)
    {
        $query = "SELECT TOP 1 RestockDate FROM Pharmacy.GlobalRestockLog WHERE StoreID = $resourceId ORDER BY RestockDate DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
            $lastDate = $result[0]["RestockDate"];

            $diff = date_diff(date_create(date('Y-m-d H:i:s')), date_create(date('Y-m-d H:i:s', strtotime($lastDate))));

            return $diff->days;
        }

        return 0;
    }

    public static function itemQuantityDuringLastRestock(int $resourceId)
    {
        $query = "SELECT TOP 1 ItemQuantity FROM Pharmacy.GlobalRestockLog WHERE StoreID = $resourceId ORDER BY RestockDate DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (isset($result[0])){
           return $result[0]["ItemQuantity"];
        }

        return 0;
    }
}