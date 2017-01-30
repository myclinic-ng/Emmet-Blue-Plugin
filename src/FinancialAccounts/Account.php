<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts;

/**
 * class Account.
 *
 * Account Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Account {

	public static function newAccount(array $data){
		return Account\Account::create($data);
	}

	public static function viewAccount(int $data = 0){
		return Account\Account::view($data);
	}

	public static function viewAllAccounts(){
		return Account\Account::viewAll();
	}

	public static function viewAllAccountsWithRunningBalances(int $period=0){
		return Account\Account::viewAllWithRunningBalances($period);
	}

	public static function editAccount(int $resourceId, array $data){
		return Account\Account::edit($resourceId, $data);
	}
}