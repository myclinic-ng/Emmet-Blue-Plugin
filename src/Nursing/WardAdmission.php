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
 * class WardAdmission.
 *
 * WardAdmission Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class WardAdmission
{
    public static function newWardAdmission(array $data)
    {
        $result = WardAdmission\WardAdmission::create($data);

        return $result;
    }

    public static function viewAdmittedPatients(int $resourceId=0)
    {
        $result = WardAdmission\WardAdmission::viewAdmittedPatients($resourceId);

        return $result;
    }

    public static function editWardAdmission(int $resourceId=0, array $data)
    {
        $result = WardAdmission\WardAdmission::editWardAdmission($resourceId, $data);

        return $result;
    }

    public static function deleteWardAdmission(int $resourceId)
    {
        $result = WardAdmission\WardAdmission::delete($resourceId);

        return $result;
    }
}