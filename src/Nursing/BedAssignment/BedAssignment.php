<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\BedAssignment;

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
 * class BedAssignment.
 *
 * BedAssignment Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 01/09/2016 04:30pm
 */
class BedAssignment
{
    /**
     * creates new bed Assignment resource
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $bedName = $data['bedName'];
        $assignmentLeased = $data['assignmentLeased'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Nursing.BedAssignment', [
                'BedName'=>QB::wrapString($bedName, "'"),
                'AssignmentLeased'=>$assignmentLeased
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'BedAssignment',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (Nursng ward not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view Wards data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.BedAssignment a')
            ->innerJoin('Nursing.SectionBed b', 'a.BedName = b.BedName ')
            ->innerJoin('Nursing.WardSection c', 'b.WardSectionID = c.WardSectionID');
        if ($resourceId != 0){
            $selectBuilder->where('BedAssignmentID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'BedAssignment',
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
    /*view beds based on section id*/
    public static function viewSectionBeds($resourceId){
        $query = "SELECT * FROM Nursing.BedAssignment a JOIN Nursing.SectionBed b ON a.BedName = b.BedName WHERE b.WardSectionID = $resourceId";

    try
        {
            $viewPaymentRequestOperation = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Accounts',
                'PaymentRequest',
                (string)$query
            );
            
            return $viewPaymentRequestOperation;  
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
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();
        try
        {
            $updateBuilder->table("Nursing.BedAssignment");
            $updateBuilder->set($data);
            $updateBuilder->where("BedAssignmentID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'BedAssignment',
                (string)($result)
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
     * delete a ward resource
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Nursing.BedAssignment")
                ->where("BedAssignmentID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'BedAssignment',
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