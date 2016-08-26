<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients;

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
 * class FieldTitleType.
 *
 * FieldTitleType Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 26/08/2016 12:54PM
 */
class FieldTitleType
{
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newFieldTitleType(array $data)
    {
        $result = FieldTitleType\FieldTitleType::create($data);

        return $result;
    }

    /**
     * edits FieldTitleType
     */
    public static function editFieldTitleType(int $resourceId=0)
    {
        $result = FieldTitleType\FieldTitleType::edit($resourceId);

        return $result;
    }

    /**
     * Selects FieldTitleType
     */
    public static function viewFieldTitleType(int $resourceId=0)
    {
        $result = FieldTitleType\FieldTitleType::view($resourceId);

        return $result;
    }

    /**
     * Deletes a FieldTitleType
     */
    public static function deleteFieldTitleType(int $resourceId)
    {
    	$result = FieldTitleType\FieldTitleType::delete($resourceId);

    	return $result;
    }
}