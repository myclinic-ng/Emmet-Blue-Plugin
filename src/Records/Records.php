<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Records;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;


class Records{
	public static function indexRecords(array $data){
		$dir = $data["dir"];
		$path = realpath($dir));

		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
		foreach($objects as $name => $object){
		    echo "$name\n";
		}
	}
}