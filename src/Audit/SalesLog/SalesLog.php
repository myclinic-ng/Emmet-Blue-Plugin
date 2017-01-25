<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Audit\SalesLog;

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
 * class SalesLog.
 *
 * SalesLog Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class SalesLog {
	public static function create(array $data)
	{
    	$department = $data["department"];
		$patient = $data['patient'] ?? NULL;
		$staff = $data['staff'] ?? NULL;
        $action = $data["salesAction"] ?? NULL;
        $paymentRequest = $data["paymentRequest"] ?? NULL;

		try {
			 $result = DBQueryFactory::insert('FinancialAuditing.SalesLog', [
                'PaymentRequestNumber'=>(is_null($paymentRequest)) ? 'NULL' : QB::wrapString($paymentRequest, "'"),
                'Action'=>(is_null($action)) ? 'NULL' : QB::wrapString($action, "'"),
                'Department'=>$department,
                'PatientID'=>$patient,
                'StaffID'=>$staff
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAuditing',
                'SalesLog',
                (string)serialize($result)
            );

            return $result;
		}
		catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (log not recorded), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
	}
}