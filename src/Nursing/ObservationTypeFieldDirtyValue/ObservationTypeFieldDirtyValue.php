<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ObservationTypeFieldDirtyValue;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;
use EmmetBlue\Core\DirtChecker;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class ObservationTypeFieldDirtyValue.
 *
 * ObservationTypeFieldDirtyValue Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class ObservationTypeFieldDirtyValue
{
    /**
     * creates new nursing resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $field = $data["field"] ?? null;
        $value = $data['value'] ?? null;

        switch ($data['condition']) {
            case 'eq':
                $condition = "=";
                break;
            
            case 'gt':
                $condition = ">";
                break;

            case 'lt':
                $condition = "<";
                break;

            default:
                $condition = null;
                break;
        }

        try
        {
            $result = DBQueryFactory::insert('Nursing.ObservationTypeFieldDirtyValues', [
                'Field'=>$field,
                'Value'=>QB::wrapString((string)$value, "'"),
                'Condition'=>QB::wrapString((string) $condition, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationTypeFieldDirtyValues',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (ObservationTypeFieldDirtyValue not created), %s",
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
            ->from('Nursing.ObservationTypeFieldDirtyValues');
        $selectBuilder->where('Field ='.$resourceId);
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ObservationTypeFieldDirtyValues',
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
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['Value'])){
                $data['Value'] = QB::wrapString($data['Value'], "'");
            }
            $updateBuilder->table("Nursing.ObservationTypeFieldDirtyValues");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldDirtyValueID = $resourceId");

            $result = (
                DBConnectionFactory::getConnection()
                ->query((string)$updateBuilder)
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
                ->from("Nursing.ObservationTypeFieldDirtyValues")
                ->where("FieldDirtyValueID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'ObservationTypeFieldDirtyValue',
                'ObservationTypeFieldDirtyValues',
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

    public static function containsDirt(array $data)
    {
        $conclusions = ["conclusion"=>false];
        foreach ($data as $datum){
            $field = $datum["field"];
            $value = $datum["value"];

            $dirts = self::view((int) $field);

            $dirty = false;
            foreach ($dirts as $dirt){
                $dirty = (new DirtChecker($value, $dirt["Value"], [$dirt["Condition"][0]]))->isDirty();
                // print_r([$value, $dirt["Value"], $dirt["Condition"][0], (int) $dirty]);
                $conclusions[$field] = ["field"=>$field, "dirty"=>(int)$dirty, "value"=>$value];
                if ($dirty) {
                    $conclusions["conclusion"] = true;
                    break;
                }
            }
        }

        return $conclusions;
    }
}