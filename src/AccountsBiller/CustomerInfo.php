<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
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
	public static function newCustomerInfo(array $data)
	{
		return CustomerInfo\CustomerInfo::create($data);
	}

	public static function viewCustomerInfo(int $resourceId=0, array $data = [])
	{
		return CustomerInfo\CustomerInfo::view($resourceId, $data);
	}

	public static function deleteCustomerInfo(int $resourceId)
	{
		return CustomerInfo\CustomerInfo::delete($resourceId);
	}

	public static function editCustomerInfo(int $resourceId, array $data)
    {
        $result = CustomerInfo\CustomerInfo::edit($resourceId, $data);

        return $result;
    }
}