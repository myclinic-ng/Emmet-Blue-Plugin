<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\AccountType;

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
 * class AccountType.
 *
 * AccountType Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class AccountType {

	public static function create(array $data)
	{
    	$name = $data["name"];
		$description = $data['description'] ?? NULL;
        $category = $data["category"] ?? 'NULL';

		try {
			 $result = DBQueryFactory::insert('FinancialAccounts.AccountTypes', [
                'TypeName'=>QB::wrapString($name, "'"),
                'TypeDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString($description, "'"),
                'CategoryID'=>$category
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypes',
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


	public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('FinancialAccounts.AccountTypes a')
            ->innerJoin('FinancialAccounts.AccountTypeCategories b', 'a.CategoryID = b.CategoryID');
        if ($resourceId != 0){
            $selectBuilder->where('a.CategoryID ='.$resourceId);
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypes',
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

    public static function getSidesOnEquation(){
        $query = "SELECT a.TypeID, b.SideOnEquation FROM FinancialAccounts.AccountTypes a INNER JOIN FinancialAccounts.AccountTypeCategories b ON a.CategoryID = b.CategoryID";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data["TypeDescription"])){
                $data["TypeDescription"] = QB::wrapString($data["TypeDescription"], "'");
            }

            if (isset($data["TypeName"])){
                $data["TypeName"] = QB::wrapString($data["TypeName"], "'");
            }

            $updateBuilder->table("FinancialAccounts.AccountTypes");
            $updateBuilder->set($data);
            $updateBuilder->where("TypeID = $resourceId");


            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountTypes',
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