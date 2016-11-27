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
 * class ServicesRendered.
 *
 * ServicesRendered Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ServicesRendered
{
    public static function newServicesRendered(array $data)
    {
        $result = ServicesRendered\ServicesRendered::create($data);

        return $result;
    }

    public static function view(int $resourceId)
    {
        $result = ServicesRendered\ServicesRendered::view($resourceId);

        return $result;
    }

    // public static function editServicesRendered(int $resourceId=0, array $data)
    // {
    //     $result = ServicesRendered\ServicesRendered::editServicesRendered($resourceId, $data);

    //     return $result;
    // }

    // public static function deleteServicesRendered(int $resourceId)
    // {
    //     $result = ServicesRendered\ServicesRendered::delete($resourceId);

    //     return $result;
    // }
}