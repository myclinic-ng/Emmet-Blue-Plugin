<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
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
 * class ObservationChart.
 *
 * ObservationChart Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ObservationChart
{
    /**
     * Creates a new ObservationChart sheet
     *
     * @param $_POST
     */
    public static function newObservationChart(array $data)
    {
        $result = ObservationChart\ObservationChart::create($data);

        return $result;
    }

    /**
     * Selects ObservationChart
     */
    public static function viewObservationChart(int $resourceId=0)
    {
        $result = ObservationChart\ObservationChart::view($resourceId);

        return $result;
    }
    /**
     * Selects ObservationChart
     */
    public static function viewObservationChartFieldValues(int $resourceId=0)
    {
        $result = ObservationChart\ObservationChart::viewObservationChartFieldValues($resourceId);

        return $result;
    }

    /**
     * edit ObservationChart
     */
    public static function editObservationChart(int $resourceId=0, array $data)
    {
        $result = ObservationChart\ObservationChart::editObservationChart($resourceId, $data);

        return $result;
    }
    /**
     * edit ConsultationSheetTags
     */
    public static function editObservationChartFieldValue(int $resourceId=0, array $data)
    {
        $result = ObservationChart\ObservationChart::editObservationChartFieldValue($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ObservationChart
     */
    public static function deleteObservationChart(int $resourceId)
    {
        $result = ObservationChart\ObservationChart::delete($resourceId);

        return $result;
    }
}