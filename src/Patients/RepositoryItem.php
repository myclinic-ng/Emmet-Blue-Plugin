<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class RepositoryItem.
 *
 * RepositoryItem Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 03/10/2016 06:49
 */
class RepositoryItem
{
	/**
	 * Creates a new RepositoryItem group
	 *
	 * @param $_POST
	 */
    public static function newRepositoryItem(array $data)
    {
        $result = RepositoryItem\RepositoryItem::create($data);

        return $result;
    }

    /**
     * Selects RepositoryItem UUID(s)
     */
    public static function viewRepositoryItem(int $resourceId=0)
    {
        $result = RepositoryItem\RepositoryItem::view($resourceId);

        return $result;
    }

    public static function receiveFromExternalLab(array $data)
    {
        $result = RepositoryItem\RepositoryItem::receiveFromExternalLab($data);

        return $result;
    }
    

    public static function sendAcrossLabs(array $data)
    {
        $result = RepositoryItem\RepositoryItem::sendAcrossLabs($data);

        return $result;
    }


    public static function viewRepositoryItem(int $resourceId=0)
    {
        $result = RepositoryItem\RepositoryItem::view($resourceId);

        return $result;
    }

    /**
     * Deletes a RepositoryItem UUID
     */
    public static function deleteRepositoryItem(int $resourceId)
    {
    	$result = RepositoryItem\RepositoryItem::delete($resourceId);

    	return $result;
    }
}