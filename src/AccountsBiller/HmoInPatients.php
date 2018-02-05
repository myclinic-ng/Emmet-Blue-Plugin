<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class HmoInPatients.
 *
 * HmoInPatients Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoInPatients
{
    public static function viewHmoInPatients(int $resourceId=0)
    {
        $staffLinkedPatients = \EmmetBlue\Plugins\AccountsBiller\DepartmentPatientTypesReportLink\DepartmentPatientTypesReportLink::viewByStaff($resourceId);

        $patientTypes = [];

        foreach ($staffLinkedPatients as $key => $value) {
           $patientTypes[] = "b.PatientType = ".$value["PatientTypeID"];
        }

        $patientTypesString = implode(" OR ", $patientTypes);

        $query = "SELECT a.*, c.WardName, d.WardSectionName FROM Consultancy.PatientAdmission a INNER JOIN Patients.Patient b ON a.Patient = b.PatientID LEFT OUTER JOIN Nursing.Ward c ON a.Ward = c.WardID LEFT OUTER JOIN Nursing.WardSection d ON a.Section = d.WardSectionID WHERE a.ReceivedInWard = 1 AND (a.DischargeStatus = -1) AND ($patientTypesString)";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key]["PatientInformation"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $value["Patient"])["_source"];
        }

        return $result;
    }
}