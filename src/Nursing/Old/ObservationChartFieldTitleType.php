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
class ObservationChartFieldTitleType
{
	/**
	 * Creates a new ObservationChartFieldTitleType
	 *
	 * @param $_POST
	 */
    public static function newObservationChartFieldTitleType(array $data)
    {
        $result = ObservationChartFieldTitleType\ObservationChartFieldTitleType::create($data);

        return $result;
    }

    /**
     * Selects ObservationChartFieldTitleType
     */
    public static function viewObservationChartFieldTitleType(int $resourceId=0)
    {
        $result = ObservationChartFieldTitleType\ObservationChartFieldTitleType::view($resourceId);

        return $result;
    }

    /**
     * edit ObservationChartFieldTitleType
     */
    public static function editObservationChartFieldTitleType(int $resourceId=0, array $data)
    {
        $result = ObservationChartFieldTitleType\ObservationChartFieldTitleType::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ObservationChartFieldTitleType
     */
    public static function deleteObservationChartFieldTitleType(int $resourceId)
    {
    	$result = ObservationChartFieldTitleType\ObservationChartFieldTitleType::delete($resourceId);

    	return $result;
    }
}