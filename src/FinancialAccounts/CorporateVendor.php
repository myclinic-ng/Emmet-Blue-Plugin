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
 * class CorporateVendor.
 *
 * CorporateVendor Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class CorporateVendor {

	public static function newCorporateVendor(array $data){
		return CorporateVendor\CorporateVendor::create($data);
	}

	public static function viewCorporateVendor(int $data = 0){
		return CorporateVendor\CorporateVendor::view($data);
	}

	public static function editCorporateVendor(int $resourceId, array $data){
		return CorporateVendor\CorporateVendor::edit($resourceId, $data);
	}
}