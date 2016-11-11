<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\ExaminationType;

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
 * class ExaminationType.
 *
 * ExaminationType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class ExaminationType
{
    /**
     * creats new ExaminationType
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;
        $options = $data["options"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Consultancy.ExaminationTypes', [
                'ExamTypeTitle'=>QB::wrapString($title, "'"),
                'ExamTypeDescription'=>QB::wrapString($description, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ExaminationTypes',
                (string)serialize($result)
            );

            $id = $result["lastInsertId"];

            $query = "INSERT INTO Consultancy.ExaminationTypeOptions VALUES ";

            $holder = [];

            foreach($options as $option){
                $title = $option["title"];
                $holder[] = "($id, '$title')";
            }

            $_result = DBConnectionFactory::getConnection()->exec($query.implode(", ", $holder));
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (exam type not created), %s",
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
            ->from('Consultancy.ExaminationTypes');
        if ($resourceId != 0){
            $selectBuilder->where('ExamTypeID ='.$resourceId);
        }
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ExaminationTypes',
                (string)$selectBuilder
            );

            foreach ($viewOperation as $key => $value) {
                $options = DBConnectionFactory::getConnection()->query("SELECT * FROM Consultancy.ExaminationTypeOptions WHERE ExamTypeID = ".$value["ExamTypeID"])->fetchAll(\PDO::FETCH_ASSOC);
                $viewOperation[$key]["options"] = $options;
            }

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

    
    public static function editExaminationType(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Consultancy.ExaminationTypes");
            $updateBuilder->set($data);
            $updateBuilder->where("ExamTypeID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ExaminationTypes',
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
                ->from("Consultancy.ExaminationTypes")
                ->where("ExamTypeID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'ExaminationTypes',
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