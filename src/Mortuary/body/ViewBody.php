<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Mortuary\Body;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
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
 * class ViewBody.
 *
 * ViewBody Controller
 *
 * @author 
 * @since v0.0.1 08/06/2016 14:2016
 */
class ViewBody
{ 
	/**
	 * viewBodyinfo method
	 *
	 * @param int $bodyId
	 * @author bardeson Lucky <Ahead!!> <flashup4all@gmail.com>
	 */
	public static function viewBodyInfo(int $bodyId)
	{
		$bodyBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
		$bodyBuilder
			->columns('*')
			->from('Mortuary.Body');
		if ($bodyId != 0){

			$bodyBuilder->where('BodyID ='.$bodyId);
		}
		try
		{
			$viewBodyOperation = (DBConnectionFactory::getConnection()->query((string)$bodyBuilder))->fetchAll(\PDO::FETCH_ASSOC);

			DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Mortuary',
				'Body',
				(string)$bodyBuilder
			);
			//selecting all body info
			$bodyInfoBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
			$bodyInfoBuilder
			->columns('*')
			->from('Mortuary.BodyInformation')
			->where('BodyID ='.$bodyId);
			$viewBodyInfo = (DBConnectionFactory::getConnection()->query((string)$bodyInfoBuilder))->fetchAll(\PDO::FETCH_ASSOC);
			//selecting all body info
			DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Mortuary',
				'BodyInformation',
				(string)$bodyInfoBuilder
			);
			//selecting all body depositors info
			$bodyDepositorBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
			$bodyDepositorBuilder
			->columns('*')
			->from('Mortuary.DepositorDetails')
			->where('BodyID ='.$bodyId);
			$viewBodyDepositor = (DBConnectionFactory::getConnection()->query((string)$bodyDepositorBuilder))->fetchAll(\PDO::FETCH_ASSOC);
			//selecting all body info
			DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Mortuary',
				'DepositorDetails',
				(string)$bodyDepositorBuilder
			);
			//selecting all body depositors info
			$bodyNextOfKinBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
			$bodyNextOfKinBuilder
			->columns('*')
			->from('Mortuary.DepositorDetails')
			->where('BodyID ='.$bodyId);
			$viewBodyNextOfKin = (DBConnectionFactory::getConnection()->query((string)$bodyNextOfKinBuilder))->fetchAll(\PDO::FETCH_ASSOC);
			//selecting all body info
			DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Mortuary',
				'DepositorDetails',
				(string)$bodyNextOfKinBuilder
			);

			if(count($viewBodyOperation) > 0)
			{
				return array_merge($viewBodyOperation, $viewBodyinfo, $viewBodyDepositor, $viewBodyNextOfKin);
			}
			else
			{
				return null;
			}			
		} 
		catch (\PDOException $e) 
		{
			throw new SQLException(
				sprintf(
					"Error procesing request"
				),
				Constant::UNDEFINED
			);
			
		}
	}
}