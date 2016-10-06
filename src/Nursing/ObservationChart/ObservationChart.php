<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ObservationChart;

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
 * class ObservationChart.
 *
 * ObservationChart Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class ObservationChart
{
    /**
     * creats new ObservationChart
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        
        $patientId = $data['patientId'] ?? null;
        $staffId = QB::wrapString($data['staffId'] ?? null, "'");
        $observationChartFieldValue = $data['observationChartFieldValue'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Nursing.ObservationChart', [
                'PatientID'=>$patientId,
                'StaffID'=>$staffId
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChart',
                (string)serialize($result)
            );

            $id = $result['lastInsertId'];

            foreach ($observationChartFieldValue as $datum){
                $observations[] = "($id, ".QB::wrapString($datum['fieldTitle'], "'").",".QB::wrapString($datum['fieldValue'], "'").")";
            }

            $query = "INSERT INTO Nursing.ObservationChartFieldValue (ObservationChartID, FieldTitle, FieldValue) 
                            VALUES ".implode(", ", $observations);

                DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldValue',
                (string)serialize($query)
            );
                           
            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );
            return ['lastInsertId'=>$id];
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (ObservationChart not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view observation chart data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.ObservationChart');
            //$selectBuilder->innerJoin('Nursing.ObservationChartFieldValue b', 'a.ObservationChartID = b.ObservationChartID');
        if ($resourceId != 0){
            $selectBuilder->where('ObservationChartID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartID',
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
     * view observation chart data
     */
    public static function viewObservationChartFieldValues(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.ObservationChartFieldValue');
            //$selectBuilder->innerJoin('Nursing.ObservationChartFieldValue b', 'a.ObservationChartID = b.ObservationChartID');
        if ($resourceId != 0){
            $selectBuilder->where('ObservationChartID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartID',
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
     * Modifies the content of a consultation note
     */
    public static function editObservationChartFieldValue(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Nursing.ObservationChartFieldValue");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldValueID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChartFieldValue',
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
     * Modifies the content of a consultation note
     */
    public static function editObservationChart(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Nursing.ObservationChart");
            $updateBuilder->set($data);
            $updateBuilder->where("ObservationChartID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChart',
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
     * delete consultancy sheet
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Nursing.ObservationChart")
                ->where("ObservationChartID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationChart',
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