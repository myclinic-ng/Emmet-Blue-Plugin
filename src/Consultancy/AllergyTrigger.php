<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy;

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
 * class AllergyTrigger.
 *
 * AllergyTrigger Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class AllergyTrigger
{
	public static function newAllergyTrigger(array $data)
    {
        $result = AllergyTrigger\AllergyTrigger::create($data);

        return $result;
    }

    public static function viewAllergyTrigger(int $resourceId=0)
    {
        $result = AllergyTrigger\AllergyTrigger::view($resourceId);

        return $result;
    }

    public static function viewByAllergy(int $resourceId=0)
    {
        $result = AllergyTrigger\AllergyTrigger::viewByAllergy($resourceId);

        return $result;
    }

    public static function editAllergyTrigger(int $resourceId=0, array $data)
    {
        $result = AllergyTrigger\AllergyTrigger::view($resourceId, $data);

        return $result;
    }

    public static function deleteAllergyTrigger(int $resourceId)
    {
    	$result = AllergyTrigger\AllergyTrigger::delete($resourceId);

    	return $result;
    }
}