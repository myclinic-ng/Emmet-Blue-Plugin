<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\GeneralJournal;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class GeneralJournal.
 *
 * GeneralJournal Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class GeneralJournal {

    private static function isEntryBalanced($entries){
        $credit = [];
        $debit = [];

        foreach ($entries as $value) {
            $var = $value["type"];
            $$var[] = $value["value"];
        }

        return array_sum($credit) == array_sum($debit);
    }

	public static function create(array $data)
	{
        $date = $data["date"] ?? null;
		$description = $data['description'] ?? null;
        $staff = $data["staff"];

        $entries = $data['entries'];

        // if (!self::isEntryBalanced($entries)){
        //     throw new \Exception("Journal Entry must balance to submit it");
        // }

		try {
			 $result = DBQueryFactory::insert('FinancialAccounts.GeneralJournal', [
                'GeneralJournalDate'=>QB::wrapString($date, "'"),
                'GeneralJournalDescription'=>QB::wrapString((string)$description, "'"),
                'GeneralJournalTotalAmount'=>0,
                'StaffID'=>$staff
            ]);

            $journalId = $result["lastInsertId"];

            self::newEntry(["entries"=>$entries, "journalId"=>$journalId]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'FinancialAccounts',
                'GeneralJournal',
                (string)serialize($result)
            );

            return $result;
		}
		catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (general journal not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
	}


	public static function view(int $resourceId = 0, array $data)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.GeneralJournal');
        if (!empty($data)){
            $resourceId = 0;
            if (isset($data["startdate"], $data["enddate"])){
                $sDate = QB::wrapString($data["startdate"], "'");
                $eDate = QB::wrapString($data["enddate"], "'");
                $selectBuilder->where("GeneralJournalDate BETWEEN $sDate AND $eDate");
            }
            else if (isset($data["query"])){
                $query = QB::wrapString($data["query"], "'%", "%'");
                $selectBuilder->where("GeneralJournalDescription LIKE $query");
            }
        }

        if ($resourceId != 0){
            $selectBuilder->where('GeneralJournalID ='.$resourceId);
        }

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'GeneralJournal',
                (string)$selectBuilder
            );

            foreach ($result as $key=>$journal){
                $query = "SELECT a.*, b.AccountName FROM FinancialAccounts.GeneralJournalEntries a INNER JOIN FinancialAccounts.Accounts b ON a.AccountID = b.AccountID WHERE a.GeneralJournalID = ".$journal['GeneralJournalID'];
                $entries = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

                $entries = ($entries) ? $entries : [];
                $result[$key]["JournalEntries"] = $entries;
            }

            return $result;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["GeneralJournalDescription"])){
                $data["GeneralJournalDescription"] = QB::wrapString($data["GeneralJournalDescription"], "'");
            }

            if (isset($data["GeneralJournalDate"])){
                $data["GeneralJournalDate"] = QB::wrapString($data["GeneralJournalDate"], "'");
            }

            $updateBuilder->table("FinancialAccounts.GeneralJournal");
            $updateBuilder->set($data);
            $updateBuilder->where("GeneralJournalID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_UPDATE,
                'FinancialAccounts',
                'GeneralJournal',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function editEntry(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["EntryDescription"])){
                $data["EntryDescription"] = QB::wrapString($data["EntryDescription"], "'");
            }

            if (isset($data["EntryType"])){
                $data["EntryType"] = QB::wrapString($data["EntryType"], "'");
            }

            if (isset($data["EntryValue"])){
                $data["EntryValue"] = QB::wrapString($data["EntryValue"], "'");
            }

            $updateBuilder->table("FinancialAccounts.GeneralJournalEntries");
            $updateBuilder->set($data);
            $updateBuilder->where("EntryID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_UPDATE,
                'FinancialAccounts',
                'GeneralJournalEntries',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function newEntry(array $data)
    {
        $entries = $data['entries'];
        $journalId = $data["journalId"];

        $entrySqlValue = [];

        foreach ($entries as $entry){
            $description = $entry['description'] ?? null;
            $accountId = $entry['account'];
            $entryType = trim($entry['type']) ?? null;
            $entryValue = $entry['value'] ?? null;

            $entrySqlValue[] = "(".implode(",", [$journalId, QB::wrapString((string)$description, "'"), $accountId, QB::wrapString((string)$entryType, "'"), QB::wrapString((string)$entryValue, "'")]).")";
        }

        $query = "INSERT INTO FinancialAccounts.GeneralJournalEntries (GeneralJournalID, EntryDescription, AccountID, EntryType, EntryValue) VALUES ".implode(", ", $entrySqlValue);

        $_result = DBConnectionFactory::getConnection()->exec($query);

        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_INSERT,
            'FinancialAccounts',
            'GeneralJournalEntries',
            (string)serialize($_result)
        );

        return $_result;
    }

    public static function delete(int $resourceId){
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("FinancialAccounts.GeneralJournal")
                ->where("GeneralJournalID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}