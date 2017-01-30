<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\FinancialAccounts\AccountingPeriod;

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
 * class CurrentAccountingPeriod.
 *
 * CurrentAccountingPeriod Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class CurrentAccountingPeriod {

	public static function create(array $data)
	{
        $periodId = $data["period"] ?? null;
        $staffId = $data["staffId"] ?? null;

		try {
			 $result = DBQueryFactory::insert('FinancialAccounts.CurrentAccountingPeriod', [
                'AccountingPeriodID'=>$periodId,
                'StaffID'=>$staffId
            ]);



            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'FinancialAccounts',
                'CurrentAccountingPeriod',
                (string)serialize($result)
            );

            return $result;
		}
		catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
	}


	public static function view()
    {
        $query = "SELECT TOP 1 a.*, b.PeriodAlias FROM FinancialAccounts.CurrentAccountingPeriod a INNER JOIN FinancialAccounts.AccountingPeriods b ON a.AccountingPeriodID = b.PeriodID ORDER BY a.SetDate DESC";
        try
        {
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$value){
                $result[$key]["SetBy"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"])["StaffFullName"];
            }

            if (isset($result[0])){
                $result = $result[0];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountingPeriods',
                (string)$query
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

    public static function viewHistory()
    {
        $query = "SELECT a.*, b.PeriodAlias FROM FinancialAccounts.CurrentAccountingPeriod a INNER JOIN FinancialAccounts.AccountingPeriods b ON a.AccountingPeriodID = b.PeriodID ORDER BY a.SetDate DESC";
        try
        {
            $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($result as $key=>$value){
                $result[$key]["SetBy"] = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $value["StaffID"])["StaffFullName"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'FinancialAccounts',
                'AccountingPeriods',
                (string)$query
            );

            return $result;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing reques: %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
        }
    }
}