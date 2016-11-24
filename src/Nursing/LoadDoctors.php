<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing;

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
 * class LoadDoctors.
 *
 * LoadDoctors Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/01/2016 04:21pm
 */
class LoadDoctors
{
    /**
     * view Wards data
     */
    public static function view(int $resourceId=0)
    {
        $departments = DBConnectionFactory::getConnection()->query(
            "SELECT * FROM Nursing.ConsultantDepartments"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];

        foreach ($departments as $department){
            $query = "SELECT a.StaffID, a.StaffUsername FROM Staffs.StaffPassword a INNER JOIN Staffs.StaffDepartment b ON a.StaffID = b.StaffID WHERE b.DepartmentID = ".$department["Department"];
            $result[] = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];
        }

        return $result;
    }

    public static function viewQueueCount(int $resourceId=0)
    {
        $doctors = self::view();

        foreach ($doctors as $key=>$doctor){
            $query = "SELECT COUNT(*) as count FROM Consultancy.PatientQueue WHERE Consultant = ".$doctor["StaffID"];
            $doctors[$key]["queueCount"] = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["count"];
        }

        return $doctors;
    }
}