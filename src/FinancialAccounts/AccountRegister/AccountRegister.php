<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\AccountRegister;

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
 * class AccountRegister.
 *
 * AccountRegister Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountRegister {
	public static function getRunningBalance(int $account, array $data)
    {
        if (isset($data["period"]) && $data["period"] != "" && $data["period"] != 0){
            $period = $data["period"];
        }
        else {
            $period = \EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod::getCurrentPeriod()["AccountingPeriodID"];
        }

        $periodInfo = \EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod::viewAlias((int) $period);

        if (!isset($periodInfo[0])){
            throw new \Exception("Unable to load date intervals due to missing period information");
        }

        $periodStart = $periodInfo[0]["PeriodStartDate"];
        $periodEnd = $periodInfo[0]["PeriodEndDate"];

        $query = "
                    SELECT a.EntryType, a.EntryValue FROM FinancialAccounts.GeneralJournalEntries a 
                    INNER JOIN FinancialAccounts.GeneralJournal b ON a.GeneralJournalID = b.GeneralJournalID 
                    WHERE a.AccountID = $account AND CAST(b.GeneralJournalDate AS DATE) BETWEEN CAST('$periodStart' AS DATE) AND CAST('$periodEnd' AS DATE)
                ";

        $transactions = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        $begBal = \EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod::viewBeginningBalanceByAccount((int) $period, ["account"=>$account]);

        if (isset($begBal[0])){
            $begBal = $begBal[0]["BalanceValue"];
        }
        else {
            $begBal = 0;
        }

        foreach($transactions as $entry){
            $type = rtrim(trim($entry["EntryType"]));

            if ($type == "debit"){
                $begBal -= $entry["EntryValue"];
            }
            else if($type == "credit"){
                $begBal += $entry["EntryValue"];
            }
        }
        return ["value"=>$begBal, "period"=>$periodInfo];
    }
}