<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\AccountTypeCategory;

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
 * class AccountTypeCategory.
 *
 * AccountTypeCategory Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountTypeCategory {

	public static function create(array $data)
	{
    	$name = $data["name"];
		$description = $data['description'] ?? null;

		try {
			 $result = DBQueryFactory::insert('FinancialAccounts.AccountTypeCategories', [
                'CategoryName'=>QB::wrapString($name, "'"),
                'CategoryDescription'=>QB::wrapString($description, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypeCategories',
                (string)serialize($result)
            );

            return $result;
		}
		catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (category not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
	}


	public static function view(int $resourceId = 0)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.AccountTypeCategories');
        if ($resourceId != 0){
            $selectBuilder->where('CategoryID ='.$resourceId);
        }
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypeCategories',
                (string)$selectBuilder
            );

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

    public static function viewWithTypes()
    {
        $categories = self::view();

        foreach ($categories as $key=>$category) {
            $categories[$key]["types"] = \EmmetBlue\Plugins\FinancialAccounts\AccountType::viewAccountType((int) $category["CategoryID"]);
        }
        
        return $categories;
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["CategoryDescription"])){
                $data["CategoryDescription"] = QB::wrapString($data["CategoryDescription"], "'");
            }

            if (isset($data["CategoryName"])){
                $data["CategoryName"] = QB::wrapString($data["CategoryName"], "'");
            }

            $updateBuilder->table("FinancialAccounts.AccountTypeCategories");
            $updateBuilder->set($data);
            $updateBuilder->where("CategoryID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypeCategories',
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