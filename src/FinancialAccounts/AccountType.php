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
 * class AccountType.
 *
 * AccountType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountType {

	public static function newAccountType(array $data){
		return AccountType\AccountType::create($data);
	}

	public static function viewAccountType(int $data = 0){
		return AccountType\AccountType::view($data);
	}

	public static function getSidesOnEquation(int $data = 0){
		return AccountType\AccountType::getSidesOnEquation();
	}

	public static function editAccountType(int $resourceId, array $data){
		return AccountType\AccountType::edit($resourceId, $data);
	}
}