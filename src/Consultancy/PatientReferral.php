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
 * PatientReferral Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class PatientReferral
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newPatientReferral(array $data)
    {
        $result = PatientReferral\PatientReferral::create($data);

        return $result;
    }

    /**
     * Selects PatientReferral
     */
    public static function viewPatientReferral(int $resourceId=0, array $data = [])
    {
        $result = PatientReferral\PatientReferral::view($resourceId, $data);

        return $result;
    }


    /**
     * edit PatientReferral
     */
    public static function archivePatientReferral(array $resourceId)
    {
        $result = PatientReferral\PatientReferral::archivePatientReferral((int) $resourceId["referral"]);

        return $result;
    }

    /**
     * Deletes a PatientReferral
     */
    public static function deletePatientReferral(int $resourceId)
    {
        $result = PatientReferral\PatientReferral::delete($resourceId);

        return $result;
    }
}