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
 * class AccountRegister.
 *
 * AccountRegister Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountRegister {
	public static function getRunningBalance(int $resourceId, array $data = []){
		return AccountRegister\AccountRegister::getRunningBalance($resourceId, $data);
	}

	public static function getAccountEntries(int $resourceId, array $data){
		return AccountRegister\AccountRegister::getAccountEntries($resourceId, $data);
	}
}