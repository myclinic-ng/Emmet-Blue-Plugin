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
	public static function viewBody(int $bodyId)
	{
		$bodyStatus = '0';
		$bodyBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
		$bodyBuilder
			->columns('*')
			->from('Mortuary.Body a');
			
			$bodyBuilder->innerJoin("Mortuary.BodyInformation b","a.BodyID = b.BodyID");//->where('BodyStatus = '.$bodyStatus);
			$bodyBuilder->innerJoin('Mortuary.DepositorDetails c', 'a.BodyID = c.BodyID');
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

			foreach ($viewBodyOperation as $key => $value) {
				$id = $value["BodyID"];
				$tags = [];
				$query = "SELECT * FROM Mortuary.BodyTag WHERE BodyID = $id";
				$result = (DBConnectionFactory::getConnection()->query((string)$query))->fetchAll(\PDO::FETCH_ASSOC);
				foreach ($result as $value){
					$tags[] = $value["TagName"];
				}
				$viewBodyOperation[$key]["Tags"] = $tags;	
			}
			
			return $viewBodyOperation;	
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
	/**
	 * logged in Body 
	 */
	public static function viewLoggedInBody()
	{
		$bodyStatus = 1;
		$bodyBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
		$bodyBuilder
			->columns('*')
			->from('Mortuary.Body a');
			$bodyBuilder->innerJoin("Mortuary.BodyInformation b","a.BodyID = b.BodyID");
			$bodyBuilder->innerJoin('Mortuary.DepositorDetails c', 'a.BodyID = c.BodyID');
			$bodyBuilder->innerJoin('Mortuary.BodyTag d', 'a.BodyID = d.BodyID')->where('a.BodyStatus ='.$bodyStatus);
		
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
			
			return $viewBodyOperation;			
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
	/**
	 * logged out Body 
	 */
	public static function viewLoggedOutBody()
	{
		$bodyStatus = 0;
		$bodyBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
		$bodyBuilder
			->columns('*')
			->from('Mortuary.Body a');
			$bodyBuilder->innerJoin("Mortuary.BodyInformation b","a.BodyID = b.BodyID");
			$bodyBuilder->innerJoin('Mortuary.DepositorDetails c', 'a.BodyID = c.BodyID')->where('BodyStatus = '.$bodyStatus);
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
			
			return $viewBodyOperation;	
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