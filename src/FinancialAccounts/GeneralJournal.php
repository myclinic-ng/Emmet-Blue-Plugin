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
 * class GeneralJournal.
 *
 * GeneralJournal Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class GeneralJournal {

	public static function newGeneralJournal(array $data){
		return GeneralJournal\GeneralJournal::create($data);
	}

	public static function newEntry(array $data){
		return GeneralJournal\GeneralJournal::newEntry($data);
	}

	public static function viewGeneralJournal(int $resourceId = 0, array $data = []){
		return GeneralJournal\GeneralJournal::view($resourceId, $data);
	}

	public static function editGeneralJournal(int $resourceId, array $data){
		return GeneralJournal\GeneralJournal::edit($resourceId, $data);
	}

	public static function editEntry(int $resourceId, array $data){
		return GeneralJournal\GeneralJournal::editEntry($resourceId, $data);
	}

	public static function deleteGeneralJournal(int $resourceId){
		return GeneralJournal\GeneralJournal::delete($resourceId);
	}
}