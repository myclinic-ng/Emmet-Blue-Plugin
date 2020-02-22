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
class InitPharmacyStores
{
	public static $stores = [
		"Drug Store"=>[
			"Sales Dispensary"
		]
	];

	public static function run(){
		try {
			foreach (self::$stores as $store=>$dispensaries){
				$storeId = \EmmetBlue\Plugins\Pharmacy\Store\Store::create(["name"=>$store]);

				if (isset($storeId["lastInsertId"])){
					$storeId = $storeId["lastInsertId"];

					foreach ($dispensaries as $dispensary){
						$dispensaryId = \EmmetBlue\Plugins\Pharmacy\EligibleDispensory\EligibleDispensory::create(["name"=>$dispensary]);

						if (isset($dispensaryId["lastInsertId"])){
							$dispensaryId = $dispensaryId["lastInsertId"];

							\EmmetBlue\Plugins\Pharmacy\DispensoryStoreLink\DispensoryStoreLink::create(["dispensory"=>$dispensaryId, "store"=>$storeId]);
						}
					}
				}

			}	
		}
		catch(\Exception $e){ }

		return true;
	}

	
}