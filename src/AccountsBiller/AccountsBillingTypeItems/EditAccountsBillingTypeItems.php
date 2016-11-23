<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com> <Ahead!!>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\AccountsBillingTypeItems;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Builder\QueryBuilder\EditQueryBuilder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DatabaseQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class EditAccountBillingType.
 *
 * EditAccountBillingType Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 15/06/2016 14:20
 */
class EditAccountsBillingTypeItems
{
	/**
	 * Edit method
	 * @author Samuel Adeshina
	 * @param int $accountBillingTypeId
	 */
	public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
        	if (isset($data['BillingTypeItemName']))
        	{
        		$data["BillingTypeItemName"] = QB::wrapString($data["BillingTypeItemName"], "'");
        	}

            $updateBuilder->table("Accounts.BillingTypeItems");
            $updateBuilder->set($data);
            $updateBuilder->where("BillingTypeItemID = $resourceId");

            $query = "SELECT COUNT(*) as Total FROM Accounts.BillingTypeItems WHERE BillingType = (SELECT BillingType FROM Accounts.BillingTypeItems WHERE BillingTypeItemID = $resourceId) AND BillingTypeItemName = ".$data['BillingTypeItemName'];
            $count = (integer) DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC)[0]["Total"];

            if ($count != 0){
                throw new SQLException(sprintf(
                    "Duplicate values are not allowed. '%s' already exists for the selected billing type",
                    $data['BillingTypeItemName']
                ), Constant::UNDEFINED);
            }
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
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