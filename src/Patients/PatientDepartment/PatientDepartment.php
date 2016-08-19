<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientDepartment;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class PatientDepartment.
 *
 * PatientDepartment Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientDepartment
{
    /**
     * creats new patientDepartmentId request
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        
        $patientId = $data['patientId'] ?? null;
        $departmentId = $data['departmentId'] ?? null;

         try
        {
        	$result = DBQueryFactory::insert('Patients.PatientDepartment', [
                'PatientId'=>$patientId,
                'DepartmentId'=>$departmentId
            ]);

            DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Patients',
				'PatientDepartment',
				(string)(serialize($result))
			);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient UUID not created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
    /**
     * view patients Department request id
     */
    public static function view(int $resourceId)
	{
		$selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
		$selectBuilder
			->columns('*')
			->from('Patients.PatientDepartment');
		if ($resourceId != 0){
			$selectBuilder->where('PatientDepartmentId ='.$resourceId);
		}
		try
		{
			$viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

			DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Patients',
				'PatientDepartment',
				(string)$selectBuilder
			);

			if(count($viewOperation) > 0)
			{
				return $viewOperation;
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
    /**
     * delete patient UUID
     */
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Patients.PatientDepartment")
                ->where("PatientDepartmentId = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
				Session::get('USER_ID'),
				Constant::EVENT_SELECT,
				'Patients',
				'PatientDepartment',
				(string)$deleteBuilder
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