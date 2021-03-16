<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Lab\InvestigationTypeField;

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
 * class InvestigationTypeField.
 *
 * InvestigationTypeField Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class InvestigationTypeField
{
    /**
     * creates new lab resources
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $investigationType = $data['investigationType'] ?? null;
        $fieldType = $data['fieldType'] ?? null;
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null; 

        try
        {
            $result = DBQueryFactory::insert('Lab.InvestigationTypeFields', [
                'InvestigationType'=>$investigationType,
                'FieldType'=>$fieldType,
                'FieldName'=>QB::wrapString((string)$name, "'"),
                'FieldDescription'=>QB::wrapString((string)$description, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFields',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (InvestigationTypeField not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function newDefaultValue(array $data)
    {
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Lab.InvestigationTypeFieldDefaults', [
                'Field'=>$field,
                'Value'=>QB::wrapString((string)$value, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFieldDefaults',
                (string)(serialize($result))
            );
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (default value not created), %s",
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
            ->from('Lab.InvestigationTypeFields a')
            ->innerJoin('Lab.InvestigationTypeFieldTypes b', 'a.FieldType = b.TypeID')
            ->where('a.InvestigationType ='.$resourceId);
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFields',
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

    public static function viewDefaultValues(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Lab.InvestigationTypeFieldDefaults')
            ->where('Field ='.$resourceId);
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFieldDefaults',
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

    public static function viewFieldTypes(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Lab.InvestigationTypeFieldTypes');
        if ($resourceId != 0){
            $selectBuilder->where('a.TypeID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFieldTypes',
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
            if (isset($data['FieldName'])){
                $data['FieldName'] = QB::wrapString($data['FieldName'], "'");
            }
            if (isset($data['FieldDescription'])){
                $data['FieldDescription'] = QB::wrapString($data['FieldDescription'], "'");
            }
            $updateBuilder->table("Lab.InvestigationTypeFields");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldID = $resourceId");

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
                ->from("Lab.InvestigationTypeFields")
                ->where("FieldID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'InvestigationTypeField',
                'InvestigationTypeFields',
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

    public static function deleteDefaultValue(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Lab.InvestigationTypeFieldDefaults")
                ->where("FieldDefaultID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Lab',
                'InvestigationTypeFieldDefaults',
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