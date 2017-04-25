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
		$path = realpath($dir);

		$data = [];

		$objects = new \RecursiveDirectoryIterator($path);
		$counter = 0;
		foreach($objects as $name => $object){
			if ($counter == 1000){
				break;
			}

			if (is_dir($name)){
				if (file_exists($name."\meta.json")){
					$content = file_get_contents($name."\meta.json");

					while(!is_array(json_decode($content, true))){
						$content = json_decode($content, true);
					}

					$folderEx = explode("\\", $name);
					$folder = $folderEx[count($folderEx) - 1];
					$data[$folder] = json_decode($content, true);
					$data[$folder]["folder"] = $folder;

					// echo "PROCESSED $counter: $folder";
					$counter++;
				}
			}
		}

		return $data;
	}

	public static function genBilling(){
		$query = "SELECT DISTINCT BillingTypeItem FROM Accounts.BillingTypeItemsPrices";
		$result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);
		$gQ = [];
		foreach ($result as $item){
			// var_dump($item["BillingTypeItem"]) ;
			// die();
			if ($item["BillingTypeItem"] !== NULL){
				$i = $item["BillingTypeItem"];
				$q = "SELECT MAX(a.BillingTypeItemPrice) as amount, c.CategoryName FROM Accounts.BillingTypeItemsPrices a INNER JOIN Patients.PatientType b ON a.PatientType = b.PatientTypeID INNER JOIN Patients.PatientTypeCategories c ON b.CategoryName = c.CategoryName WHERE a.BillingTypeItem = $i AND c.CategoryID = 8 GROUP BY c.CategoryName";
				// die($q);
				$r = DBConnectionFactory::getConnection()->query($q)->fetchall(\PDO::FETCH_ASSOC);
				if (isset($r[0])){
					$gQ[] = ["item"=>$i, "price"=>$r[0]["amount"]];
				}
			}
		}

		// file_put_contents("C:\Emmetblue\server\_\bills.json", json_encode($gQ));
		return $gQ;
	}

	public static function insertBill(){
		$bills = self::genBilling();
		$q = [];
		foreach ($bills as $bill){
			$q[] = "(".$bill["item"].", 8, '".$bill["price"]."')";
		}

		$query = "INSERT INTO Accounts.PatientTypeCategoriesDefaultPrices(BillingTypeItem, CategoryID, BillingTypeItemPrice) VALUES ".implode(", ", $q);

		echo $query;
		die();
	}
}