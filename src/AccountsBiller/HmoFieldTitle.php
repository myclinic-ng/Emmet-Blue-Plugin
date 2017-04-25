<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class HmoFieldTitle.
 *
 * HmoFieldTitle Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoFieldTitle
{
	/**
	 * Creates a new staff
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newHmoFieldTitle(array $data)
    {
        $result = HmoFieldTitle\HmoFieldTitle::create($data);

        return $result;
    }

    public static function viewHmoFieldTitle(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldTitle\HmoFieldTitle::view($resourceId, $data);

        return $result;
    }

    public static function viewByPatientType(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldTitle\HmoFieldTitle::viewByPatientType($resourceId, $data);

        return $result;
    }

    public static function editHmoFieldTitle(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldTitle\HmoFieldTitle::edit($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a staff
     */
    public static function deleteHmoFieldTitle(int $resourceId)
    {
    	$result = HmoFieldTitle\HmoFieldTitle::delete($resourceId);

    	return $result;
    }
}