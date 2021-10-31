<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\InsuranceClaims;

/**
 * class Profiles.
 *
 * Profiles Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 31/10/2021 11:15
 */
class Profiles
{
    public static function setPrimaryAccount(array $data)
    {
        $result = Profiles\Profiles::setPrimaryAccount($data);

        return $result;
    }
    public static function getPrimaryAccount(int $resourceId)
    {
        $result = Profiles\Profiles::getPrimaryAccount($resourceId);

        return $result;
    }
}