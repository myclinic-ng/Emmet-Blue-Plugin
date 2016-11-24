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
 * PatientQueue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class PatientQueue
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newPatientQueue(array $data)
    {
        $result = PatientQueue\PatientQueue::create($data);

        return $result;
    }

    /**
     * Selects PatientQueue
     */
    public static function viewPatientQueue(int $resourceId=0)
    {
        $result = PatientQueue\PatientQueue::view($resourceId);

        return $result;
    }


    /**
     * edit PatientQueue
     */
    public static function editPatientQueue(int $resourceId=0, array $data)
    {
        $result = PatientQueue\PatientQueue::editPatientQueue($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a PatientQueue
     */
    public static function deletePatientQueue(int $resourceId)
    {
        $result = PatientQueue\PatientQueue::delete($resourceId);

        return $result;
    }
}