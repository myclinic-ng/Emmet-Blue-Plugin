<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources;

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
 * class WorkSchedule.
 *
 * WorkSchedule Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 10/02/2018 13:52
 */
class WorkSchedule
{
	/**
	 * Creates a new staff
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newWorkSchedule(array $data)
    {
        $result = WorkSchedule\WorkSchedule::create($data);

        return $result;
    }

     /**
     * Edits a department group
     */
    public static function editWorkSchedule(int $resourceId, array $data)
    {
        $result = WorkSchedule\WorkSchedule::edit($resourceId, $data);

        return $result;
    }

    /**
     * Selects department group(s)
     */
    public static function viewWorkSchedule(int $resourceId=0, array $data = [])
    {
        $result = WorkSchedule\WorkSchedule::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a staff
     */
    public static function deleteWorkSchedule(int $resourceId)
    {
    	$result = WorkSchedule\WorkSchedule::delete($resourceId);

    	return $result;
    }
}