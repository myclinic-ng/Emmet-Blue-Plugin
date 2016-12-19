<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod;

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
 * class AccountingPeriodBeginningBalance.
 *
 * AccountingPeriodBeginningBalance Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountingPeriodBeginningBalance {

	public static function create(array $data)
	{
        $periodId = $data["period"];
        $balances = $data["balances"];

        $values = [];

        foreach ($balances as $typeId=>$value){
           $balance = $value["balance"];

           $values[] = "($periodId, $typeId, '$balance')";
        }

        $query = "INSERT INTO FinancialAccounts.AccountingPeriodBeginningBalances (AccountingPeriodID, AccountID, BalanceValue) VALUES ".implode(", ", $values);

		try {
            // die($query);
			$result = DBConnectionFactory::getConnection()->exec($query);

            if($result){
                \EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod\AccountingPeriodAlias::edit((int) $periodId, ["PeriodEditable"=>0]);
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountingPeriodBeginningBalances',
                (string)serialize($result)
            );

            return $result;
		}
		catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (balance not saved), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
	}


	public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.AccountingPeriodBeginningBalances')
            ->where('AccountingPeriodID ='.$resourceId);
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountingPeriodBeginningBalances',
                (string)$selectBuilder
            );

            return $result;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request: %s",
                    $e->getMessage()
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
            if (isset($data["BalanceValue"])){
                $data["BalanceValue"] = QB::wrapString($data["BalanceValue"], "'");
            }

            $updateBuilder->table("FinancialAccounts.AccountingPeriodBeginningBalances");
            $updateBuilder->set($data);
            $updateBuilder->where("BeginningBalanceID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountingPeriodBeginningBalances',
                (string)(serialize($result))
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}