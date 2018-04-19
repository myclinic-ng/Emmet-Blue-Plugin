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
class InitPatientCategories
{
	public static $categories = [
		"General"=>["Customer"]
	];

	public static function run(){
		try {
			foreach (self::$categories as $category=>$types){
				$result = \EmmetBlue\Plugins\Patients\PatientTypeCategory\PatientTypeCategory::create(["categoryName"=>$category]);

				foreach ($types as $type){
					\EmmetBlue\Plugins\Patients\PatientType\PatientType::create([
						"patientTypeName"=>$type,
						"patientTypeCategory"=>$category
					]);
				}
			}
		}
		catch(\Exception $e){ 
		}

		return true;
	}

	
}