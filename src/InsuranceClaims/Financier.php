<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\InsuranceClaims;

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
 * class Financier.
 *
 * Financier Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 09/10/2021 12:04
 */
class Financier
{

    public static function newFinancier(array $data)
    {
        $result = Financier\Financier::newFinancier($data);

        return $result;
    }

    public static function viewFinanciers(int $resourceId=0)
    {
        $result = Financier\Financier::viewFinanciers($resourceId);

        return $result;
    }

    public static function newInsuranceId(array $data)
    {
        $result = Financier\Financier::newInsuranceId($data);

        return $result;
    }
}