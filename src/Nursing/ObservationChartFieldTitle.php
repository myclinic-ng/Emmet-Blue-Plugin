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
 * class Tags.
 *
 * Tags Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ObservationChartFieldTitle
{
	/**
	 * Creates a new ObservationChartFieldTitle
	 *
	 * @param $_POST
	 */
    public static function newObservationChartFieldTitle(array $data)
    {
        $result = ObservationChartFieldTitle\ObservationChartFieldTitle::create($data);

        return $result;
    }

    /**
     * Selects ObservationChartFieldTitle
     */
    public static function viewObservationChartFieldTitle(int $resourceId=0)
    {
        $result = ObservationChartFieldTitle\ObservationChartFieldTitle::view($resourceId);

        return $result;
    }

    /**
     * edit ObservationChartFieldTitleType
     */
    public static function editObservationChartFieldTitle(int $resourceId=0, array $data)
    {
        $result = ObservationChartFieldTitle\ObservationChartFieldTitle::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ObservationChartFieldTitleType
     */
    public static function deleteObservationChartFieldTitle(int $resourceId)
    {
    	$result = ObservationChartFieldTitle\ObservationChartFieldTitle::delete($resourceId);

    	return $result;
    }
}