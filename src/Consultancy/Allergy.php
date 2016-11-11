<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
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
 * Allergy Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class Allergy
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newAllergy(array $data)
    {
        $result = Allergy\Allergy::create($data);

        return $result;
    }

    /**
     * Selects Allergy
     */
    public static function viewAllergy(int $resourceId=0)
    {
        $result = Allergy\Allergy::view($resourceId);

        return $result;
    }


    /**
     * edit Allergy
     */
    public static function editAllergy(int $resourceId=0, array $data)
    {
        $result = Allergy\Allergy::editAllergy($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a Allergy
     */
    public static function deleteAllergy(int $resourceId)
    {
        $result = Allergy\Allergy::delete($resourceId);

        return $result;
    }
}