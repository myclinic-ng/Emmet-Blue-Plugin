<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller;

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
 * class AccountsBillingType.
 *
 * AccountsBillingType Controller
 *
 * @author Samuel Adeshina
 * @since v0.0.1 08/06/2016 14:20
 */
class BillingTypeItems
{
	public static function newBillingTypeItems(array $data)
	{
		return AccountsBillingTypeItems\NewAccountsBillingTypeItems::default($data);
	}

	public static function newPriceStructure(array $data)
	{
		return AccountsBillingTypeItems\NewAccountsBillingTypeItems::newPriceStructure($data);
	}

	public static function newPriceStructureByPatientCategory(array $data)
	{
		return AccountsBillingTypeItems\NewAccountsBillingTypeItems::newCategoryPrice($data);
	}

	public static function newGeneralPriceStructure(array $data)
	{
		return AccountsBillingTypeItems\NewAccountsBillingTypeItems::newGeneralPrice($data);
	}

	public static function viewBillingTypeItems(int $resourceId=0, array $data = [])
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewAccountsBillingTypeItems($resourceId, $data);
	}

	public static function viewById(int $resourceId=0, array $data = [])
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewById($resourceId, $data);
	}

	public static function viewByStaffUuid(int $resourceId=0, array $data=[]){

		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewAccountsBillingTypeItemsByStaffUUID($resourceId, $data);
	}

	public static function viewItemIntervals(int $resourceId=0, array $data = [])
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewItemIntervals($resourceId, $data);
	}

	public static function viewItemPriceByCategory(int $resourceId=0)
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewCategoryPrice($resourceId);
	}

	public static function viewGeneralItemPrice(int $resourceId=0)
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::viewGeneralPrice($resourceId);
	}

	public static function isRateBased(int $patientId, array $data)
	{
		return AccountsBillingTypeItems\ViewAccountsBillingTypeItems::isRateBased($patientId, $data);	
	}

	public static function deleteBillingTypeItems(int $resourceId)
	{
		return AccountsBillingTypeItems\DeleteAccountsBillingTypeitems::delete($resourceId);
	}

	public static function editBillingTypeItems(int $resourceId, array $data)
    {
        $result = AccountsBillingTypeItems\EditAccountsBillingTypeItems::edit($resourceId, $data);

        return $result;
    }
}