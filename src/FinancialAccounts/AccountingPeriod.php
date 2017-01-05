<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts;

/**
 * class AccountingPeriod.
 *
 * AccountingPeriod Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountingPeriod {

	public static function newAlias(array $data){
		return AccountingPeriod\AccountingPeriodAlias::create($data);
	}

	public static function viewAlias(int $data = 0){
		return AccountingPeriod\AccountingPeriodAlias::view($data);
	}

	public static function editAlias(int $resourceId, array $data){
		return AccountingPeriod\AccountingPeriodAlias::edit($resourceId, $data);
	}

	public static function deleteAlias(int $resourceId){
		return AccountingPeriod\AccountingPeriodAlias::delete($resourceId);
	}

	public static function newBeginningBalance(array $data){
		return AccountingPeriod\AccountingPeriodBeginningBalance::create($data);
	}

	public static function viewBeginningBalance(int $data){
		return AccountingPeriod\AccountingPeriodBeginningBalance::view($data);
	}

	public static function editBeginningBalance(int $resourceId, array $data){
		return AccountingPeriod\AccountingPeriodBeginningBalance::edit($resourceId, $data);
	}
}