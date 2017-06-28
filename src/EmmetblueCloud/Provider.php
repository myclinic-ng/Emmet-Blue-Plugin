<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Provider.
 *
 * Provider Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class Provider {
	public static function updateSecretToken(array $data){
		$newSecret = $data["secret"];

		$query = "UPDATE EmmetBlueCloud.Provider SET ProviderSecretToken = '$newSecret'";
		return DBConnectionFactory::getConnection()->exec($query);
	}
	public static function updateID(array $data){
		$newID = $data["id"];

		$query = "UPDATE EmmetBlueCloud.Provider SET ProviderID = '$newID'";
		return DBConnectionFactory::getConnection()->exec($query);
	}
	public static function updateAlias(array $data){
		$newAlias = $data["alias"];

		$query = "UPDATE EmmetBlueCloud.Provider SET ProviderAlias = '$newAlias'";
		return DBConnectionFactory::getConnection()->exec($query);
	}

	public static function getDetails(){
		$query = "SELECT * FROM EmmetBlueCloud.Provider WHERE PKey = 1";

		return DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0];
	}
}