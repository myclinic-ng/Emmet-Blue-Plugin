<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\AccountsBiller\PatientTypeCategoriesBillingTypeItemLink;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class AccountsPatientTypeCategoriesBillingTypeItemLink.
 *
 * AccountsPatientTypeCategoriesBillingTypeItemLink Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 31/10/2021 18:22
 */
class PatientTypeCategoriesBillingTypeItemLink
{
	public static function create(array $data)
    {
        $category = $data['category'];
        $billingTypes = $data["billingTypeItem"] ?? null;

        try
        {
            $query = "INSERT INTO Accounts.PatientTypeCategoriesBillingTypeItemLink (PatientTypeCategoryID, BillingTypeItemID) VALUES ";
            $values = [];
            foreach ($billingTypes as $type){
                $values[] ="($category, $type)";
            }
            $queryWithValues = $query.implode(", ", $values);
            $result = DBConnectionFactory::getConnection()->exec($queryWithValues);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (new link creation request), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }


    public static function viewByCategory(int $resourceId){
        $query = "SELECT * FROM Accounts.PatientTypeCategoriesBillingTypeItemLink a INNER JOIN Accounts.BillingTypeItems c ON a.BillingTypeItemID = c.BillingTypeItemID WHERE a.PatientTypeCategoryID = $resourceId";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        

        return $result;
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Accounts.PatientTypeCategoriesBillingTypeItemLink")
                ->where("LinkID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}