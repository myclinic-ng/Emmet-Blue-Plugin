<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\CustomerInfo;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class AccountsCustomerInfo.
 *
 * AccountsCustomerInfo Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class CustomerInfo
{
	public static function create(array $data)
    {
        $category = $data['category'] ?? null;
        $name = $data['name'];
        $phone = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        try
        {
        	$result = DBQueryFactory::insert('Accounts.BillingCustomerInfo', [
                'CustomerCategoryID'=>(is_null($category)) ? "NULL" : QB::wrapString($category, "'"),
                'CustomerContactName'=>QB::wrapString($name, "'"),
                'CustomerContactPhone'=>(is_null($phone)) ? "NULL" : QB::wrapString($phone, "'"),
                'CustomerContactAddress'=>(is_null($address)) ? "NULL" : QB::wrapString($address, "'")
            ]);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (new department group creation request), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['CustomerContactName'])){
                $data['CustomerContactName'] = QB::wrapString($data['CustomerContactName'], "'");
            }
            if (isset($data['CustomerContactPhone'])){
                $data['CustomerContactPhone'] = QB::wrapString($data['CustomerContactPhone'], "'");
            }
            if (isset($data['CustomerContactAddress'])){
                $data['CustomerContactAddress'] = QB::wrapString($data['CustomerContactAddress'], "'");
            }

            $updateBuilder->table("Accounts.BillingCustomerInfo");
            $updateBuilder->set($data);
            $updateBuilder->where("CustomerContactID = $resourceId");

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

    /**
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $selectBuilder = (new Builder("QueryBuilder", "Select"))->getBuilder();

        try
        {
            if (empty($data)){
                $selectBuilder->columns("*");
            }
            else {
                $selectBuilder->columns(implode(", ", $data));
            }
            
            $selectBuilder->from("Accounts.BillingCustomerInfo");

            if ($resourceId !== 0){
                $selectBuilder->where("CustomerContactID = $resourceId");
            }

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$selectBuilder)
                )->fetchAll(\PDO::FETCH_ASSOC);

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.BillingCustomerInfo")
                ->where("CustomerContactID = $resourceId");
            
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