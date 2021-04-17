<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients;

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
 * class MedicalSummary.
 *
 * MedicalSummary Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class MedicalSummary
{
    /**
     * Creates a new field title type
     *
     * @param $_POST
     */
    public static function newMedicalSummary(array $data)
    {
        $result = MedicalSummary\MedicalSummary::create($data);

        return $result;
    }

    /**
     * edits MedicalSummary
     */
    public static function editMedicalSummary(int $resourceId=0, array $data)
    {
        $result = MedicalSummary\MedicalSummary::edit($resourceId, $data);

        return $result;
    }

    /**
     * Selects MedicalSummary
     */
    public static function viewMedicalSummary(int $resourceId=0)
    {
        $result = MedicalSummary\MedicalSummary::view($resourceId);

        return $result;
    }

    /**
     * Deletes a MedicalSummary
     */
    public static function deleteMedicalSummary(int $resourceId)
    {
        $result = MedicalSummary\MedicalSummary::delete($resourceId);

        return $result;
    }

    public static function newField(array $data)
    {
        $result = MedicalSummary\Field::create($data);

        return $result;
    }

    public static function editField(int $resourceId, array $data)
    {
        $result = MedicalSummary\Field::edit($resourceId, $data);

        return $result;
    }

    public static function viewField(int $resourceId=0)
    {
        $result = MedicalSummary\Field::view($resourceId);

        return $result;
    }

    public static function deleteField(int $resourceId)
    {
        $result = MedicalSummary\Field::delete($resourceId);

        return $result;
    }
}