<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Biometrics;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session as CoreSession;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class FingerprintCache.
 *
 * FingerprintCache Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 13/04/2018 3:04 AM
 */
class FingerprintCache
{
    public static function stream()
    {
        $imageLoc = Constant::getGlobals()["fpcache-file"];
        if (is_file($imageLoc)){
            $tiff = file_get_contents($imageLoc);
            $encodedTiff = base64_encode($tiff);

            self::clearCache();

            return $encodedTiff;
        }

        return false;
    }

    public static function clearCache(){
        $imageLoc = Constant::getGlobals()["fpcache-file"];
        if (is_file($imageLoc)){
            unlink($imageLoc);
        }

        return true;
    }
}