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
 * class BillingPaymentRequest.
 *
 * BillingPaymentRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class PaymentRequest
{
	public static function newPaymentRequest(array $data)
	{
		return PaymentRequest\PaymentRequest::create($data);
	}

	public static function viewPaymentRequest(int $resourceId=0, array $data = [])
	{
		return PaymentRequest\PaymentRequest::view($resourceId, $data);
	}

	public static function getStatus(int $resourceId=0, array $data = [])
	{
		return PaymentRequest\PaymentRequest::getStatus($resourceId, $data);
	}

	public static function deletePaymentRequest(int $resourceId)
	{
		return PaymentRequest\PaymentRequest::delete($resourceId);
	}

	public static function editPaymentRequest(int $resourceId, array $data)
    {
        $result = PaymentRequest\PaymentRequest::edit($resourceId, $data);

        return $result;
    }
}