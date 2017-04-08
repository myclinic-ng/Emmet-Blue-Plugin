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
 * PrescriptionTemplate Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class PrescriptionTemplate
{
    /**
     * Creates a new Consultancy sheet
     *
     * @param $_POST
     */
    public static function newPrescriptionTemplate(array $data)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::create($data);

        return $result;
    }

    public static function newTemplateItem(array $data)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::createItem($data);

        return $result;
    }

    /**
     * Selects PrescriptionTemplate
     */
    public static function viewPrescriptionTemplate(int $resourceId=0)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::view($resourceId);

        return $result;
    }

    public static function viewTemplateItems(int $resourceId=0)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::viewItems($resourceId);

        return $result;
    }


    /**
     * edit PrescriptionTemplate
     */
    public static function editPrescriptionTemplate(int $resourceId=0, array $data)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::editPrescriptionTemplate($resourceId, $data);

        return $result;
    }

    /**
     * Deletes a PrescriptionTemplate
     */
    public static function deletePrescriptionTemplate(int $resourceId)
    {
        $result = PrescriptionTemplate\PrescriptionTemplate::delete($resourceId);

        return $result;
    }
}