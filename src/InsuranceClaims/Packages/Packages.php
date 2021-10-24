<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\InsuranceClaims\Packages;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

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
	/**
	 * Creates a new field title type
	 *
	 * @param $_POST
	 */
    public static function newPackage(array $data)
    {
        $packageName = $data["packageName"] ?? "";
        $packageDescription = $data["packageDescription"] ?? "";
        $packageCost = $data["packageCost"] ?? "";

        $categoryId = EmmetBlue\Plugins\Patients\PatientTypeCategory\PatientTypeCategory::create([
            "categoryName"=>$packageName,
            "categoryDescription"=>$packageDescription
        ])["lastInsertId"];

        $query = "INSERT INTO InsuranceClaims.Packages (CategoryID, PackageCost) VALUES ($categoryId, $packageCost);";

        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;

    }

    /**
     * edits Packages
     */
    public static function editPackage(int $resourceId=0, array $data)
    {
        $result = Packages\Packages::edit($resourceId, $data);

        return $result;
    }

    /**
     * Selects Packages
     */
    public static function viewPackages(int $resourceId=0)
    {
        $result = Packages\Packages::view($resourceId);

        return $result;
    }

    /**
     * Deletes a Packages
     */
    public static function deletePackages(int $resourceId)
    {
    	$result = Packages\Packages::delete($resourceId);

    	return $result;
    }
}