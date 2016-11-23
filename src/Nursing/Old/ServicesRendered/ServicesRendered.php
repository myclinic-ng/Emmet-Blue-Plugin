<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ServicesRendered;

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
 * class ServicesRendered.
 *
 * ServicesRendered Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 22/09/2016 03:25
 */
class ServicesRendered
{
    /**
     * creates new ServicesRendered resource
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $patientId = $data['patientId'] ?? null;
        $servicesRenderedDate = $data['servicesRenderedDate'];
        $servicesRenderedItems = $data['servicesRenderedItems'] ?? null;
        try
        {
            $result = DBQueryFactory::insert('Nursing.ServicesRendered', [
                'PatientID'=>QB::wrapString((string)$patientId, "'"),
                'ServicesRenderedDate'=>QB::wrapString((string)$servicesRenderedDate, "'")
            ]);

            $id = $result['lastInsertId'];

            foreach ($servicesRenderedItems as $datum){
                $items[] = "($id, ".QB::wrapString($datum['servicesRenderedItems'], "'").")";
            }

            $query = "INSERT INTO Nursing.ServicesRenderedItems (ServicesRenderedID, ServicesRenderedItem) VALUES ".implode(", ", $items);
            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            return ['lastInsertId'=>$id];
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
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
     * view ServicesRendered data
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.ServicesRendered a');
            $selectBuilder->innerJoin('Nursing.ServicesRenderedItems b', 'a.ServicesRenderedID = b.ServicesRenderedID');
        if ($resourceId != 0){
            $selectBuilder->where('ServicesRenderedID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
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
     * Modifies a ServicesRendered resource
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['PatientID'])){
                $data['PatientID'] = QB::wrapString($data['PatientID'], "'");
            }
            $updateBuilder->table("Nursing.ServicesRendered");
            $updateBuilder->set($data);
            $updateBuilder->where("ServicesRenderedID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            /*//logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
                (string)(serialize($result))
            );*/

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
     * Modifies a ServicesRenderedItems resource
     */
    public static function editServicesRenderedItems(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['ServicesRenderedID'])){
                $data['ServicesRenderedID'] = QB::wrapString($data['ServicesRenderedID'], "'");
            }
            if (isset($data['ServicesRenderedItems'])){
                $data['ServicesRenderedItems'] = QB::wrapString($data['ServicesRenderedItems'], "'");
            }
            $updateBuilder->table("Nursing.ServicesRenderedItems");
            $updateBuilder->set($data);
            $updateBuilder->where("ServicesRenderedItemID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            /*//logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRenderedItems',
                (string)(serialize($result))
            );*/

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
                ->from("Nursing.ServicesRendered")
                ->where("ServicesRenderedID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
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