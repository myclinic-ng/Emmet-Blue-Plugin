<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy;

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
 * class EligibleDispensory.
 *
 * store and Inventory properties Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class EligibleDispensory
{
	public static function newEligibleDispensory(array $data)
	{
		return EligibleDispensory\EligibleDispensory::create($data);
	}

	public static function viewEligibleDispensory(int $resourceId=0, array $data = [])
	{
		return EligibleDispensory\EligibleDispensory::view($resourceId, $data);
	}

	public static function viewByStore(int $resourceId=0)
	{
		return EligibleDispensory\EligibleDispensory::viewByStore($resourceId);
	}

	public static function deleteEligibleDispensory(int $resourceId)
	{
		return EligibleDispensory\EligibleDispensory::delete($resourceId);
	}

	public static function editEligibleDispensory(int $resourceId, array $data)
    {
        $result = EligibleDispensory\EligibleDispensory::editEligibleDispensory($resourceId, $data);

        return $result;
    }
}