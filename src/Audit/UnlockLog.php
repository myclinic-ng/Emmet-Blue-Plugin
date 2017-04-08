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
 * class UnlockLog.
 *
 * UnlockLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 01/23/2017 6:49 AM
 */
class UnlockLog {
	public static function viewUnlocked(array $data = []){
		return Logs\UnlockLog::view($data);
	}

	public static function getMedicalLog(array $data = []){
		return Logs\MedicalLog::view($data);
	}

	public static function getDepartmentalLog(array $data = []){
		$pharm = Logs\DepartmentalLog::viewPharmacyLog($data);

		return [
			"pharmacy"=>$pharm
		];
	}

	public static function setStatus(int $resourceId, array $data){
		return Logs\UnlockLog::setStatus($resourceId, $data);		
	}
}