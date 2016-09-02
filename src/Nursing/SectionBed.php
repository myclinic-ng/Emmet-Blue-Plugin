<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing;

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
 * class SectionBed.
 *
 * SectionBed Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 01/09/2016 04:25pm
 */
class SectionBed
{
	/**
	 * Creates a new SectionBed
	 *
	 * @param $_POST
	 */
    public static function newSectionBed(array $data)
    {
        $result = SectionBed\SectionBed::create($data);

        return $result;
    }

    /**
     * Selects Ward
     */
    public static function viewSectionBed(int $resourceId=0)
    {
        $result = SectionBed\SectionBed::view($resourceId);

        return $result;
    }

    /**
     * edit Ward
     */
    public static function editSectionBed(int $resourceId=0, array $data)
    {
        $result = SectionBed\SectionBed::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a Ward
     */
    public static function deleteSectionBed(int $resourceId)
    {
    	$result = SectionBed\SectionBed::delete($resourceId);

    	return $result;
    }
}