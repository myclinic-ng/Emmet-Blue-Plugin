<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class HmoDocument.
 *
 * HmoDocument Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 03/10/2016 06:49
 */
class HmoDocument
{
	/**
	 * Creates a new HmoDocument group
	 *
	 * @param $_POST
	 */
    public static function newHmoDocument(array $data)
    {
        $result = HmoDocument\HmoDocument::create($data);

        return $result;
    }

    /**
     * Selects HmoDocument UUID(s)
     */
    public static function viewHmoDocument(int $resourceId=0)
    {
        $result = HmoDocument\HmoDocument::view($resourceId);

        return $result;
    }

    /**
     * Deletes a HmoDocument UUID
     */
    public static function deleteHmoDocument(int $resourceId)
    {
    	$result = HmoDocument\HmoDocument::delete($resourceId);

    	return $result;
    }
}