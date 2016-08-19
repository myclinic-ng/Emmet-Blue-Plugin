<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\StaffProfile;

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
 * class NewStaffProfile.
 *
 * NewStaffProfile Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class StaffProfile
{
    /**
     * @param array $data
     */
    public static function create(array $data)
    {
        $staff = $data['staff'] ?? null;
        unset($data['staff']);

        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
           $selectBuilder->columns('*')->from('Staffs.StaffProfileRecords');

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            $staffProfileRecords = [];
            $staffProfile = [];
            foreach ($result as $value)
            {
                $staffProfileRecords[strtolower($value["RecordName"])] = $value;
            }

            foreach ($data as $key=>$value)
            {
                $key = strtolower($key);
                if (isset($staffProfileRecords[$key]))
                {
                    $record = $staffProfileRecords[$key];
                    $staffProfile[$record["RecordID"]] = $value;
                }
            }

            print_r($_FILES);
            die();
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }

        /*try
        {
            $result = DBQueryFactory::insert('Staffs.StaffProfiles', [
                'RecordName'=>QB::wrapString($name, "'"),
                'RecordType'=>QB::wrapString($type, "'"),
                'RecordDescription'=>QB::wrapString($description, "'")
            ]);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }*/
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $data['RecordName'] = QB::wrapString($data['RecordName'], "'");
            $data['RecordDescription'] = QB::wrapString($data['RecordDescription'], "'");
            $updateBuilder->table("Staffs.StaffProfiles");
            $updateBuilder->set($data);
            $updateBuilder->where("RecordID = $resourceId");

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
            
            $selectBuilder->from("Staffs.StaffProfiles");

            if ($resourceId !== 0){
                $selectBuilder->where("RecordID = $resourceId");
            }

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

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.StaffProfiles")
                ->where("RecordID = $resourceId");
            
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