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
 * class BillingTransactionMeta.
 *
 * BillingTransactionMeta Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class TransactionMeta
{
	public static function newTransactionMeta(array $data)
	{
		return TransactionMeta\TransactionMeta::create($data);
	}

	public static function viewTransactionMeta(int $resourceId=0, array $data = [])
	{
		return TransactionMeta\TransactionMeta::view($resourceId, $data);
	}

	public static function deleteTransactionMeta(int $resourceId)
	{
		return TransactionMeta\TransactionMeta::delete($resourceId);
	}

	public static function editTransactionMeta(int $resourceId, array $data)
    {
        $result = TransactionMeta\TransactionMeta::edit($resourceId, $data);

        return $result;
    }
}