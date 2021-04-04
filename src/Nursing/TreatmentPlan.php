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
 * class TreatmentPlan.
 *
 * TreatmentPlan Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 04/04/2021 18:17
 */
class TreatmentPlan
{
    public static function newTreatmentPlan(array $data)
    {
        $result = TreatmentPlan\TreatmentPlan::create($data);

        return $result;
    }

    public static function view(int $resourceId)
    {
        $result = TreatmentPlan\TreatmentPlan::view($resourceId);

        return $result;
    }

    public static function viewMostRecent(int $resourceId)
    {
        $result = TreatmentPlan\TreatmentPlan::viewMostRecent($resourceId);

        return $result;
    }

    public static function deleteTreatmentPlan(int $resourceId)
    {
        $result = TreatmentPlan\TreatmentPlan::deleteTreatmentPlan($resourceId);

        return $result;
    }
}