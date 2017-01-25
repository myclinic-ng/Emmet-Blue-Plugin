<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Audit;

/**
 * class SalesCycle.
 *
 * SalesCycle Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class SalesCycle {
	public static function viewComplete(array $data = []){
		return SalesCycle\SalesCycle::viewComplete($data);
	}

	public static function viewBroken(array $data = []){
		return SalesCycle\SalesCycle::viewBroken($data);
	}

	public static function viewRogue(array $data = []){
		return SalesCycle\SalesCycle::viewRogue($data);
	}
}