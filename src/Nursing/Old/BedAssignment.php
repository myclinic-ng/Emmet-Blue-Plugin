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
 * class BedAssignment.
 *
 * BedAssignment Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class BedAssignment
{
	/**
	 * Creates a new BedAssignment
	 *
	 * @param $_POST
	 */
    public static function newBedAssignment(array $data)
    {
        $result = BedAssignment\BedAssignment::create($data);

        return $result;
    }

    /**
     * Selects Ward
     */
    public static function viewBedAssignment(int $resourceId=0)
    {
        $result = BedAssignment\BedAssignment::view($resourceId);

        return $result;
    }

    /**
     * edit Ward
     */
    public static function editBedAssignment(int $resourceId=0, array $data)
    {
        $result = BedAssignment\BedAssignment::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a Ward
     */
    public static function deleteBedAssignment(int $resourceId)
    {
    	$result = BedAssignment\BedAssignment::delete($resourceId);

    	return $result;
    }
}