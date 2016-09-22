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
 * class ServicesRendered.
 *
 * ServicesRendered Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 22/09/2016 03:29AM
 */
class ServicesRendered
{
    /**
     * Creates a new ServicesRendered
     *
     * @param $_POST
     */
    public static function newServicesRendered(array $data)
    {
        $result = ServicesRendered\ServicesRendered::create($data);

        return $result;
    }

    /**
     * Selects ServicesRendered
     */
    public static function viewServicesRendered(int $resourceId=0)
    {
        $result = ServicesRendered\ServicesRendered::view($resourceId);

        return $result;
    }

    /**
     * edit ServicesRendered
     */
    public static function editServicesRendered(int $resourceId=0, array $data)
    {
        $result = ServicesRendered\ServicesRendered::edit($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ServicesRendered
     */
    public static function deleteServicesRendered(int $resourceId)
    {
        $result = ServicesRendered\ServicesRendered::delete($resourceId);

        return $result;
    }
}