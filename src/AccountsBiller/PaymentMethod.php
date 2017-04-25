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
 * class AccountsPaymentMethod.
 *
 * AccountsPaymentMethod Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PaymentMethod
{
	public static function newPaymentMethod(array $data)
	{
		return PaymentMethod\PaymentMethod::create($data);
	}

	public static function viewPaymentMethod(int $resourceId=0, array $data = [])
	{
		return PaymentMethod\PaymentMethod::view($resourceId, $data);
	}

	public static function deletePaymentMethod(int $resourceId)
	{
		return PaymentMethod\PaymentMethod::delete($resourceId);
	}

	public static function editPaymentMethod(int $resourceId, array $data)
    {
        $result = PaymentMethod\PaymentMethod::edit($resourceId, $data);

        return $result;
    }
}