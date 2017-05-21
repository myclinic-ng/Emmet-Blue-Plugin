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
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class Reports.
 *
 * Reports Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 17/05/2017
 */
class Reports
{
    public static function getTotalRegistration(array $data)
    {
        $result = Reports\Registration::total($data);

        return $result;
    }

    public static function getTotalRegistrationByCategories(array $data)
    {
        $result = Reports\Registration::totalByCategories($data);

        return $result;
    }

    public static function getTotalRegistrationByCategory(array $data)
    {
        $result = Reports\Registration::totalByCategory($data);

        return $result;
    }
}