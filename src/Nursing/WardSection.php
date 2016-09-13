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
 * class WardSection.
 *
 * WardSection Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class WardSection
{
    /**
     * Creates a new WardSection
     *
     * @param $_POST
     */
    public static function newWardSection(array $data)
    {
        $result = WardSection\WardSection::create($data);

        return $result;
    }

    /**
     * Selects Ward
     */
    public static function viewWardSection(int $resourceId=0)
    {
        $result = WardSection\WardSection::view($resourceId);

        return $result;
    }

    /**
     * edit Ward
     */
    public static function editWardSection(int $resourceId=0, array $data)
    {
        $result = WardSection\WardSection::edit($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a Ward
     */
    public static function deleteWardSection(int $resourceId)
    {
        $result = WardSection\WardSection::delete($resourceId);

        return $result;
    }
}