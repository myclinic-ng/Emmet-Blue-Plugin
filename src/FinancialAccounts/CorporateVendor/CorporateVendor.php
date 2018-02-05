<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\CorporateVendor;

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
 * class CorporateVendor.
 *
 * CorporateVendor Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class CorporateVendor {

	public static function create(array $data)
	{
    	$name = $data["name"];
		$description = $data['description'] ?? NULL;
        $address = $data["address"] ?? NULL;

		try {
			 $result = DBQueryFactory::insert('FinancialAccounts.CorporateVendors', [
                'VendorName'=>QB::wrapString($name, "'"),
                'VendorDescription'=>(is_null($description)) ? 'NULL' : QB::wrapString($description, "'"),
                'VendorAddress'=>(is_null($address)) ? 'NULL' : QB::wrapString($address, "'"),
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'CorporateVendors',
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
            ->from('FinancialAccounts.CorporateVendors a');
        if ($resourceId != 0){
            $selectBuilder->where('a.VendorID ='.$resourceId);
        }
        
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'CorporateVendors',
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
            if (isset($data["VendorDescription"])){
                $data["VendorDescription"] = QB::wrapString($data["VendorDescription"], "'");
            }

            if (isset($data["VendorName"])){
                $data["VendorName"] = QB::wrapString($data["VendorName"], "'");
            }

            $updateBuilder->table("FinancialAccounts.CorporateVendors");
            $updateBuilder->set($data);
            $updateBuilder->where("VendorID = $resourceId");


            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'CorporateVendors',
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