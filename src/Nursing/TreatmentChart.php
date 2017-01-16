<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class TreatmentChart.
 *
 * TreatmentChart Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class TreatmentChart
{
    public static function newTreatmentChart(array $data)
    {
        $result = TreatmentChart\TreatmentChart::create($data);

        return $result;
    }

    public static function view(int $resourceId)
    {
        $result = TreatmentChart\TreatmentChart::view($resourceId);

        return $result;
    }

    public static function viewMostRecent(int $resourceId)
    {
        $result = TreatmentChart\TreatmentChart::viewMostRecent($resourceId);

        return $result;
    }

    // public static function editTreatmentChart(int $resourceId=0, array $data)
    // {
    //     $result = TreatmentChart\TreatmentChart::editTreatmentChart($resourceId, $data);

    //     return $result;
    // }

    // public static function deleteTreatmentChart(int $resourceId)
    // {
    //     $result = TreatmentChart\TreatmentChart::delete($resourceId);

    //     return $result;
    // }
}