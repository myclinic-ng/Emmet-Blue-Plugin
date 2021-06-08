<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\Account;

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
 * class Account.
 *
 * Account Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Account {

    public static function create(array $data)
    {
        $name = $data["name"];
        $description = $data['description'] ?? NULL;
        $type = $data["type"] ?? 'NULL';

        try {
             $result = DBQueryFactory::insert('FinancialAccounts.Accounts', [
                'AccountName'=>QB::wrapString($name, "'"),
                'AccountDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString($description, "'"),
                'AccountTypeID'=>$type
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'Accounts',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (account not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }


    public static function view(int $resourceId=0)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.Accounts a')
            ->innerJoin('FinancialAccounts.AccountTypes b', 'a.AccountTypeID = b.TypeID')
            ->where('a.AccountStatus = \'Active\'');
        if ($resourceId != 0){
            $selectBuilder->andWhere('AccountID ='.$resourceId);
        }
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'Accounts',
                (string)$selectBuilder
            );

            return $result;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request: %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function viewAll()
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.Accounts a')
            ->innerJoin('FinancialAccounts.AccountTypes b', 'a.AccountTypeID = b.TypeID');

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'Accounts',
                (string)$selectBuilder
            );

            return $result;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request: %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function viewAllWithRunningBalances(int $period=0)
    {
        $accounts = self::view();

        if ($period == 0){
            $_period = \EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod\CurrentAccountingPeriod::view();

            if (isset($_period["AccountingPeriodID"])){
                $period = $_period["AccountingPeriodID"];
            }
        }
        
        foreach ($accounts as $key=>$account){
            $accounts[$key]["Balance"] = \EmmetBlue\Plugins\FinancialAccounts\AccountRegister\AccountRegister::getRunningBalance((int) $account["AccountID"], ["period"=>$period]);
        }

        return $accounts;
    }

    public static function viewAllWithRunningBalancesGroupByType(int $period=0)
    {
        $runningBalance = self::viewAllWithRunningBalances($period);
        $accountTypes = [];
        $categories = [];
        $period = [];

        foreach($runningBalance as $key=>$value){
            $period = $value["Balance"]["period"];
            if (!isset($accountTypes[$value["TypeID"]])){
                $accountTypes[$value["TypeID"]] = ["_meta"=>["TypeName"=>$value["TypeName"], "Total"=>0]];
            }

            $accountTypes[$value["TypeID"]][] = $value;
            $accountTypes[$value["TypeID"]]["_meta"]["Total"] += $value["Balance"]["value"];
        }

        $query = "SELECT a.CategoryID, a.CategoryName, a.SideOnEquation, b.TypeID FROM FinancialAccounts.AccountTypeCategories a INNER JOIN FinancialAccounts.AccountTypes b ON a.CategoryID = b.CategoryID";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
             if (!isset($categories[$value["CategoryID"]])){
                $categories[$value["CategoryID"]] = [
                    "_meta"=>[
                        "CategoryName"=>$value["CategoryName"],
                        "SideOnEquation"=>$value["SideOnEquation"],
                        "Total"=>0
                    ]
                ];
             } 

             if (isset($accountTypes[$value["TypeID"]])){
                $categories[$value["CategoryID"]][] = $accountTypes[$value["TypeID"]];
                $categories[$value["CategoryID"]]["_meta"]["Total"] += $accountTypes[$value["TypeID"]]["_meta"]["Total"];
             }  
        }

        return ["categories"=>$categories, "period"=>$period[0]];
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["AccountDescription"])){
                $data["AccountDescription"] = QB::wrapString($data["AccountDescription"], "'");
            }

            if (isset($data["AccountName"])){
                $data["AccountName"] = QB::wrapString($data["AccountName"], "'");
            }

            if (isset($data["AccountStatus"])){
                $data["AccountStatus"] = QB::wrapString($data["AccountStatus"], "'");
            }

            $updateBuilder->table("FinancialAccounts.Accounts");
            $updateBuilder->set($data);
            $updateBuilder->where("AccountID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'Accounts',
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