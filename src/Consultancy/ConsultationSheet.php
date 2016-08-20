<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy;

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
 * class PatientonsultationSheet.
 *
 * ConsultationSheet Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ConsultationSheet
{
	/**
	 * Creates a new Consultancy sheet
	 *
	 * @param $_POST
	 */
    public static function newConsultationSheet(array $data)
    {
        $result = ConsultationSheet\ConsultationSheet::create($data);

        return $result;
    }

    /**
     * Selects ConsultationSheet
     */
    public static function viewConsultationSheet(int $resourceId=0)
    {
        $result = ConsultationSheet\ConsultationSheet::view($resourceId);

        return $result;
    }

    /**
     * edit ConsultationSheet
     */
    public static function editConsultationSheet(int $resourceId=0, array $data)
    {
        $result = ConsultationSheet\ConsultationSheet::view($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ConsultationSheet
     */
    public static function deleteConsultationSheet(int $resourceId)
    {
    	$result = ConsultationSheet\ConsultationSheet::delete($resourceId);

    	return $result;
    }
}