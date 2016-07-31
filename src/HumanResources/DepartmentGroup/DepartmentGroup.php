<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\User\DepartmentGroup;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class NewDepartmentGroup.
 *
 * NewDepartmentGroup Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class DepartmentGroup
{
	/**
	 * Determines if a login data is valid
	 *
	 * @param array $data
	 */
    public static function create(array $data)
    {
        $groupName = $data['name'];
        $insertBuilder = (new Builder("QueryBuilder","Insert"))->getBuilder();

        try
        {
        	$insertBuilder
                ->into('Staffs.DepartmentGroup', ['GroupName'])
                ->value([$groupName]);

        	 $result = (
        	 		DBConnectionFactory::getConnection()
        	 		->query((string)$insertBuilder)
        	 	);
        	 
        	 DatabaseLog::log(Session::get('USER_ID'), Constant::EVENT_INSERT, 'Staffs', 'DepartmentGroup', (string)$insertBuilder);

             return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (new department group creation request)"
            ), Constant::UNDEFINED);
        }
    }
}