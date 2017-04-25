<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\HmoFieldTitle;

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
 * class NewHmoFieldTitle.
 *
 * NewHmoFieldTitle Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class HmoFieldTitle
{
	/**
	 * @param array $data
	 */
    public static function create(array $data)
    {  
        $patientCategory = $data['patientCategory'];
        $name = $data['name'];
        $type= $data['type'];
        $description = $data['description'] ?? null;

        try
        {
        	$result = DBQueryFactory::insert('Accounts.PatientCategoriesHmoFieldTitles', [
                'PatientCategory'=>$patientCategory,
                'FieldTitleName'=>QB::wrapString((string)$name, "'"),
                'FieldTitleType'=>QB::wrapString((string)$type, "'"),
                'FieldTitleDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString((string)$description, "'")
            ]);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $data['FieldTitleName'] = QB::wrapString((string)$data['FieldTitleName'], "'");
            $data['FieldTitleDescription'] = QB::wrapString((string)$data['FieldTitleDescription'], "'");
            $updateBuilder->table("Accounts.PatientCategoriesHmoFieldTitles");
            $updateBuilder->set($data);
            $updateBuilder->where("FieldTitleID = $resourceId");

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
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Accounts.PatientCategoriesHmoFieldTitles");
            $selectBuilder->where("PatientCategory = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function viewByPatientType(int $resourceId = 0)
    {
        $query = "SELECT CategoryID FROM Patients.PatientTypeCategories a INNER JOIN Patients.PatientType b ON a.CategoryName = b.CategoryName WHERE PatientTypeID = $resourceId";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($result[0])){
            $category = $result[0]["CategoryID"];

            return self::view((int)$category);
        }

        throw new SQLException(sprintf(
            "Unable to retrieve requested data"
        ), Constant::UNDEFINED);
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.PatientCategoriesHmoFieldTitles")
                ->where("FieldTitleID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
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