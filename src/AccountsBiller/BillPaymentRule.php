<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class BillPaymentRule.
 *
 * BillPaymentRule Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class BillPaymentRule
{
	public static function newBillPaymentRule(array $data){
		return BillPaymentRule\BillPaymentRule::create($data);
	}

	public static function newTotal(array $data){
		return BillPaymentRule\BillPaymentRule::createTotal($data);
	}

	public static function newAppendItem(array $data){
		return BillPaymentRule\BillPaymentRule::createAppendItem($data);
	}

	public static function updateBillPaymentRule(int $resourceId, array $data){
		return BillPaymentRule\BillPaymentRule::edit($resourceId, $data);
	}

	public static function deleteBillPaymentRule(int $resourceId){
		return BillPaymentRule\BillPaymentRule::delete($resourceId);
	}

	public static function deleteTotal(int $resourceId){
		return BillPaymentRule\BillPaymentRule::deleteTotal($resourceId);
	}

	public static function deleteAppendItem(int $resourceId){
		return BillPaymentRule\BillPaymentRule::deleteAppendItem($resourceId);
	}

	public static function viewBillPaymentRule(int $resourceId = 0, array $data = []){
		return BillPaymentRule\BillPaymentRule::view($resourceId, $data);
	}

	public static function viewTotal(int $resourceId = 0, array $data = []){
		return BillPaymentRule\BillPaymentRule::viewTotal($resourceId, $data);
	}

	public static function viewAppendItems(int $resourceId = 0){
		return BillPaymentRule\BillPaymentRule::viewAppendItems($resourceId);
	}
}