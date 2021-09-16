<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab\LabRequest;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class LabRequest.
 *
 * LabRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class LabRequest
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientID = $data['patientID'] ?? 'null';
        $clinicalDiagnosis = $data['clinicalDiagnosis'] ?? null;
        $requestedBy = $data['requestedBy'] ?? null;
        $investigations = $data["investigations"] ?? [];
        $requestNote = $investigation['requestNote'] ?? null;

        foreach ($investigations as $investigation){
            $investigationRequired = $investigation['investigationRequired'] ?? null;
            $investigationType = $investigation['investigationType'] ?? 'null';
            $labId = $investigation["labId"] ?? null;

            try
            {
                $result = DBQueryFactory::insert('Lab.LabRequests', [
                    'PatientID'=>$patientID,
                    'ClinicalDiagnosis'=>QB::wrapString((string)$clinicalDiagnosis, "'"),
                    'InvestigationRequired'=>QB::wrapString((string)$investigationRequired, "'"),
                    'RequestedBy'=>QB::wrapString((string)$requestedBy, "'"),
                    'InvestigationType'=>$investigationType,
                    'LabID'=>$labId,
                    'RequestNote'=>QB::wrapString((string)$requestNote, "'")
                ]);

                return $result;
            }
            catch (\PDOException $e)
            {
                throw new SQLException(sprintf(
                    "Unable to process request (LabRequest not created), %s",
                    $e->getMessage()
                ), Constant::UNDEFINED);
            }
        }
    }

    public static function view(int $resourceId, array $data = [])
    {
        // LEFT OUTER JOIN (SELECT * FROM Lab.InvestigationTypes b LEFT OUTER JOIN Lab.Labs c ON b.InvestigationTypeLab = c.LabID) d ON a.InvestigationType = d.InvestigationTypeID WHERE a.RequestID = $resourceId
        $selectBuilder = "SELECT f.PatientFullName, f.PatientUUID, e.*, g.*, j.* FROM Patients.Patient f LEFT OUTER JOIN (SELECT * FROM Lab.LabRequests a) e ON f.PatientID = e.PatientID INNER JOIN Patients.PatientType g ON f.PatientType = g.PatientTypeID LEFT OUTER JOIN Lab.Labs j ON e.LabID = j.LabID WHERE (e.RequestAcknowledged = 0 OR e.RequestAcknowledged = -1) ";
        if (isset($data["startdate"]) && isset($data["enddate"])){
            $sDate = QB::wrapString($data["startdate"], "'");
            $eDate = QB::wrapString($data["enddate"], "'");

            $selectBuilder .= " AND CONVERT(date, e.RequestDate) BETWEEN ($sDate AND $eDate)";
        }

        if ($resourceId != 0){
            $selectBuilder .= " AND e.LabID = $resourceId";
        }

        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query($selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
                
            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"])["StaffFullName"];
            }

            // DatabaseLog::log(
            //     Session::get('USER_ID'),
            //     Constant::EVENT_SELECT,
            //     'Lab',
            //     'LabRequests',
            //     (string)$selectBuilder
            // );

            // $viewOperation  = $viewOperation[0] ?? $viewOperation;
            
            return $viewOperation;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function viewByPatient(int $resourceId, array $data)
    {
        $resourceId = QB::wrapString($data["patient"], "'");
        $selectBuilder = "SELECT f.PatientFullName, f.PatientUUID, e.*, g.* FROM Patients.Patient f LEFT OUTER JOIN (SELECT * FROM Lab.LabRequests a LEFT OUTER JOIN (SELECT * FROM Lab.InvestigationTypes b LEFT OUTER JOIN Lab.Labs c ON b.InvestigationTypeLab = c.LabID) d ON a.InvestigationType = d.InvestigationTypeID) e ON f.PatientID = e.PatientID INNER JOIN Patients.PatientType g ON f.PatientType = g.PatientTypeID WHERE f.PatientUUID = $resourceId AND (e.RequestAcknowledged = 0 OR e.RequestAcknowledged = -1)";
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($viewOperation as $key=>$result){
                $viewOperation[$key]["RequestedByFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $result["RequestedBy"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)$selectBuilder
            );

            return $viewOperation;        
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    /**
     * Modifies a Ward resource
     */
    public static function edit(int $resourceId, array $data)
    {
        
    }

    /**
     * delete
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Lab.LabRequests")
                ->where("RequestID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'LabRequests',
                (string)$deleteBuilder
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function closeRequest(array $data){
        $id = $data["request"] ?? null;
        $staff = $data["staff"] ?? null;

        $query = "SELECT COUNT(*) AS Count FROM Lab.LabRequests WHERE RequestID = $id AND RequestAcknowledged = 0";
        $r = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["Count"];
        if ($r > 0){
            $query = "UPDATE Lab.LabRequests SET RequestAcknowledged = -1, RequestAcknowledgedBy = $staff WHERE RequestID = $id";
        }
        else {
            $query = "SELECT COUNT(*) AS Count FROM Lab.LabRequests WHERE RequestAcknowledged = -1 AND RequestID = $id";
            $r = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["Count"];

            if ($r > 0){
                $query = "UPDATE Lab.LabRequests SET RequestAcknowledged = 1, RequestAcknowledgedBy = $staff WHERE RequestID = $id; UPDATE Lab.Patients SET Unlocked = 1 WHERE REquestID = $id";
            }
        }

        $result = DBConnectionFactory::getConnection()->exec($query);
        return $result;
    }
}