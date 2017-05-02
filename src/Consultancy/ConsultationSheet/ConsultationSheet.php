<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\ConsultationSheet;

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
 * class ConsultationSheet.
 *
 * ConsultationSheet Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 25/08/2016 13:35
 */
class ConsultationSheet
{
    /**
     * creats new ConsultationSheet
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $admissionId = $data['admissionId'] ?? null;
        $consultant = $data['consultant'] ?? null;
        $note = $data['note'] ?? null;
        $note = nl2br(htmlentities($note, ENT_QUOTES));

        try
        {
            $result = DBQueryFactory::insert('Consultancy.ConsultationSheet', [
                'Note'=>QB::wrapString($note, "'"),
                'PatientAdmissionID'=>$admissionId,
                'Consultant'=>$consultant
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Consultation sheet not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Tag data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*')
            ->from('Consultancy.ConsultationSheet a')
            ->where('a.PatientAdmissionID ='.$resourceId);
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder. " ORDER BY DateTaken DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $consultantDetail = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Consultant"]);
                $result[$i]["ConsultantFullName"] = $consultantDetail["StaffFullName"];
                $result[$i]["ConsultantPicture"] = $consultantDetail["StaffPicture"];
                $result[$i]["ConsultantRole"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $j["Consultant"])["Name"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)$selectBuilder
            );

           return $result;
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

    public static function viewMostRecentNote(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('TOP 1 a.*')
            ->from('Consultancy.ConsultationSheet a')
            ->where('a.PatientAdmissionID ='.$resourceId);
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder. " ORDER BY DateTaken DESC"))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["ConsultantFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Consultant"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)$selectBuilder
            );

           return ($result[0]) ? $result[0] : $result;
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

    public static function getFilterableConsultants(int $resourceId){
        $selectBuilder = "SELECT DISTINCT Consultant FROM Consultancy.ConsultationSheet WHERE PatientAdmissionID = $resourceId";
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $i=>$j){
                $result[$i]["ConsultantFullName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $j["Consultant"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)$selectBuilder
            );

           return $result;
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
     * Modifies the content of a ConsultationSheet
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Consultancy.ConsultationSheet");
            $updateBuilder->set($data);
            $updateBuilder->where("ConsultationSheetID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * delete ConsultationSheet
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Consultancy.ConsultationSheet")
                ->where("ConsultationSheetID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ConsultationSheet',
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
}