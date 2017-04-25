<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ShiftSummary;

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
 * class ShiftSummary.
 *
 * ShiftSummary Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class ShiftSummary
{
    /**
     * creates new ShiftSummary
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $admissionId = $data['admissionId'] ?? 'NULL';
        $ward = $data['ward'] ?? 'NULL';
        $title = $data['title'] ?? 'NULL';
        $summary = $data['summary'] ?? 'NULL';
        $nurse = $data["nurse"] ?? 'NULL';

        try
        {
            $result = DBQueryFactory::insert('Nursing.ShiftSummary', [
                'PatientAdmissionID'=>$admissionId,
                'Nurse'=>$nurse,
                'Ward'=>$ward,
                'SummaryTitle'=>QB::wrapString($title, "'"),
                'Summary'=>QB::wrapString($summary, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Nursing',
                'ShiftSummary',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (summary not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId = 0, array $data)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('a.*, b.WardName as WardName')
            ->from('Nursing.ShiftSummary a')
            ->innerJoin('Nursing.Ward b', 'a.Ward = b.WardID');
        if (isset($data["date"])){
            $date = QB::wrapString($data["date"], "'");
            $selectBuilder->where("CAST(a.SummaryDate AS DATE) = $date");
        }
        else{
            $selectBuilder->where("CAST(a.SummaryDate AS DATE) = CAST(GETDATE() AS DATE)");
        }

        if ($resourceId != 0){
            $selectBuilder->andWhere('a.PatientAdmissionID ='.$resourceId);
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            foreach($result as $key=>$value){
                $id = $value["Nurse"];
                $result[$key]["NurseName"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $id)["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ShiftSummary',
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