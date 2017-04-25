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
 * class PatientonsultationSheet.
 *
 * ExaminationType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ExaminationType
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newExaminationType(array $data)
    {
        $result = ExaminationType\ExaminationType::create($data);

        return $result;
    }

    /**
     * Selects ExaminationType
     */
    public static function viewExaminationType(int $resourceId=0)
    {
        $result = ExaminationType\ExaminationType::view($resourceId);

        return $result;
    }


    /**
     * edit ExaminationType
     */
    public static function editExaminationType(int $resourceId=0, array $data)
    {
        $result = ExaminationType\ExaminationType::editExaminationType($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ExaminationType
     */
    public static function deleteExaminationType(int $resourceId)
    {
        $result = ExaminationType\ExaminationType::delete($resourceId);

        return $result;
    }
}