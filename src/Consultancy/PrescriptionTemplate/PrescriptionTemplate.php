<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\PrescriptionTemplate;

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
 * class PrescriptionTemplate.
 *
 * PrescriptionTemplate Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PrescriptionTemplate
{
    /**
     * creats new PrescriptionTemplate
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $title = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $createdBy = $data["staff"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Consultancy.PrescriptionTemplates', [
                'TemplateName'=>QB::wrapString($title, "'"),
                'TemplateDescription'=>!is_null($description) ? QB::wrapString($description, "'") : "NULL",
                'CreatedBy'=>$createdBy
            ]);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (template not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function createItem(array $data)
    {
        
        $template = $data['template'];
        $item = $data['item'];
        $note = $data["note"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Consultancy.PrescriptionTemplateItems', [
                'TemplateID'=>QB::wrapString($template, "'"),
                'Item'=>QB::wrapString($item, "'"),
                'Note'=>!is_null($note) ? QB::wrapString($note, "'") : "NULL",
            ]);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (template item not created), %s",
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
            ->columns('*')
            ->from('Consultancy.PrescriptionTemplates');
        if ($resourceId != 0){
            $selectBuilder->where('TemplateID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PrescriptionTemplates',
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

    public static function viewItems(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Consultancy.PrescriptionTemplateItems')
            ->where('TemplateID ='.$resourceId);
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PrescriptionTemplateItems',
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

    
    public static function editPrescriptionTemplate(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["TemplateName"])){
                $data["TemplateName"] = QB::wrapString($data["TemplateName"], "'");
            }
            if (isset($data["TemplateDescription"])){
                $data["TemplateDescription"] = QB::wrapString($data["TemplateDescription"], "'");
            }
            $updateBuilder->table("Consultancy.PrescriptionTemplates");
            $updateBuilder->set($data);
            $updateBuilder->where("TemplateID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_UPDATE,
                'Consultancy',
                'PrescriptionTemplates',
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
                ->from("Consultancy.PrescriptionTemplates")
                ->where("TemplateID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PrescriptionTemplates',
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