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
 * class AccountTypeCategory.
 *
 * AccountTypeCategory Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountTypeCategory {

	public static function newAccountTypeCategory(array $data){
		return AccountTypeCategory\AccountTypeCategory::create($data);
	}

	public static function viewAccountTypeCategory(int $data = 0){
		return AccountTypeCategory\AccountTypeCategory::view($data);
	}

	public static function viewWithTypes(int $data = 0){
		return AccountTypeCategory\AccountTypeCategory::viewWithTypes($data);
	}

	public static function editAccountTypeCategory(int $resourceId, array $data){
		return AccountTypeCategory\AccountTypeCategory::edit($resourceId, $data);
	}
}