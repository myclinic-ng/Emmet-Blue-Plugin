<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\WardTransfer;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
/**
 * class WardTransfer.
 *
 * WardTransfer Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class WardTransfer
{
    /**
     * creates new WardTransfer
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $admissionId = $data['admissionId'] ?? 'NULL';
        $wardFrom = $data['wardFrom'] ?? 'NULL';
        $wardTo = $data['wardTo'] ?? 'NULL';
        $sectionTo = $data['sectionTo'] ?? 'NULL';
        $bedTo = $data['bedTo'] ?? 'NULL';
        $transferBy = $data["transferBy"] ?? 'NULL';
        $transferNote = $data["transferNote"] ?? NULL;

        try
        {

            DBConnectionFactory::getConnection()->exec(
                "UPDATE Consultancy.PatientAdmission SET Ward = $wardTo, Section = $sectionTo WHERE PatientAdmissionID = $admissionId"
            );

            DBConnectionFactory::getConnection()->exec(
                "UPDATE Nursing.WardAdmission SET Bed = $bedTo WHERE PatientAdmissionID = $admissionId"
            );

            $result = DBQueryFactory::insert('Nursing.WardTransferLog', [
                'PatientAdmissionID'=>$admissionId,
                'WardFrom'=>$wardFrom,
                'WardTo'=>$wardTo,
                'TransferNote'=>QB::wrapString($transferNote, "'"),
                'TransferredBy'=>$transferBy
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Nursing',
                'WardTransferLog',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient not transferred), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.WardName as WardToName')
            ->from('Nursing.WardTransferLog a')
            ->innerJoin('Nursing.Ward b', 'a.WardTo = b.WardID')
            ->where("a.PatientAdmissionID = $resourceId");
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach($result as $key=>$value){
                $id = $value["TransferredBy"];
                $result[$key]["TransferredByName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $id)["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'WardTransferLOG',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }
}