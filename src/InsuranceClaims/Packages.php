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
 * class Packages.
 *
 * Packages Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/10/2021 11:15
 */
class Packages
{
    public static function newPackage(array $data)
    {
        $result = Packages\Packages::newPackage($data);

        return $result;
    }

    public static function editPackage(int $resourceId=0, array $data)
    {
        $result = Packages\Packages::edit($resourceId, $data);

        return $result;
    }

    public static function viewPackages(int $resourceId=0)
    {
        $result = Packages\Packages::view($resourceId);

        return $result;
    }
    
    public static function deletePackages(int $resourceId)
    {
        $result = Packages\Packages::delete($resourceId);

        return $result;
    }
}