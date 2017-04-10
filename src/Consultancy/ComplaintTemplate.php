<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
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
 * ComplaintTemplate Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class ComplaintTemplate
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newComplaintTemplate(array $data)
    {
        $result = ComplaintTemplate\ComplaintTemplate::create($data);

        return $result;
    }

    public static function newTemplateItem(array $data)
    {
        $result = ComplaintTemplate\ComplaintTemplate::createItem($data);

        return $result;
    }

    /**
     * Selects ComplaintTemplate
     */
    public static function viewComplaintTemplate(int $resourceId=0)
    {
        $result = ComplaintTemplate\ComplaintTemplate::view($resourceId);

        return $result;
    }

    public static function viewTemplateItems(int $resourceId=0)
    {
        $result = ComplaintTemplate\ComplaintTemplate::viewItems($resourceId);

        return $result;
    }


    /**
     * edit ComplaintTemplate
     */
    public static function editComplaintTemplate(int $resourceId=0, array $data)
    {
        $result = ComplaintTemplate\ComplaintTemplate::editComplaintTemplate($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a ComplaintTemplate
     */
    public static function deleteComplaintTemplate(int $resourceId)
    {
        $result = ComplaintTemplate\ComplaintTemplate::delete($resourceId);

        return $result;
    }
}