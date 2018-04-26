<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Setup;

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
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 18/04/2018 11:03 PM
 */
class InitDefaultAccessDepartments
{
	public static $pharmacy = "Pharmacy";
	public static $hospital = "Medical Doctor/Consultation";
	public static $lab = "Combined Lab";

	public static $primary = "IT/System Administration";

	public static function run($businessType = ""){
		switch (strtolower($businessType)) {
			case 'pharmacy':
				self::$primary = self::$pharmacy;
				break;
			case 'hospital':
				self::$primary = self::$hospital;
				break;
			case 'lab':
				self::$primary = self::$lab;
				break;
			
			default:
				# code...
				break;
		}
		$query = "SELECT StaffID FROM Staffs.Staff WHERE StaffID = 1";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
		if (isset($result[0])){
			$staffId = $result[0]["StaffID"];

			$query = "SELECT DepartmentID FROM Staffs.Department WHERE Name = '".self::$primary."'";
			$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
			if (isset($result[0])){
				$primaryId = $result[0]["DepartmentID"];

				\EmmetBlue\Plugins\HumanResources\StaffDepartment\StaffDepartment::edit((int) $staffId, [
					"DepartmentID"=>$primaryId
				]);;
			}

			$query = "SELECT DepartmentID FROM Staffs.Department";
			$result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
			foreach ($result as $department){
				$departmentId = $department["DepartmentID"];

				try {
					\EmmetBlue\Plugins\HumanResources\StaffDepartment\StaffDepartment::assignSecondary([
						"staff"=>$staffId,
						"department"=>$departmentId
					]);
				}
				catch(\Exception $e){
				}
			}
		}

		return true;
	}
}