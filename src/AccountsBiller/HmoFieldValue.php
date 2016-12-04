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
 * class HmoFieldValue.
 *
 * HmoFieldValue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoFieldValue
{
	/**
	 * Creates a new patiet hmo profile
	 *
	 * @param array $data Dept. Group Data
	 */
    public static function newHmoFieldValue(array $data)
    {
        $result = HmoFieldValue\HmoFieldValue::create($data);

        return $result;
    }

    public static function viewHmoFieldValue(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldValue\HmoFieldValue::view($resourceId, $data);

        return $result;
    }

    public static function editHmoFieldValue(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldValue\HmoFieldValue::edit($resourceId, $data);

        return $result;
    }

    public static function viewProfile(int $resourceId=0)
    {
        $result = HmoFieldValue\HmoFieldValue::viewProfile($resourceId);

        return $result;
    }

    public static function viewProfileByUuid(int $resourceId=0, array $data = [])
    {
        $result = HmoFieldValue\HmoFieldValue::viewProfileByUuid($resourceId, $data);

        return $result;
    }
}