<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\Dispensation;

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
 * class Dispensation.
 *
 * Dispensation Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 24/08/2016 12:17
 */
class Dispensation
{

    public static function create(array $data)
    {
       // $DispensedItem = $data['eligibleDispensories'] ?? null;
        $dispensedItems = $data['dispensedItems'] ?? null;
        $dispensee = QB::wrapString((string)$data['dispensee'], "'") ?? null;
        $eligibleDispensory = $data['eligibleDispensory'] ?? 'null';
        $dispensingStore = $data['dispensingStore'] ?? 'null';
        $patient = $data['patient'] ?? 'null';

        try
        {

            $dispensationResult = DBQueryFactory::insert('Pharmacy.Dispensation', [
                'DispensingStore'=>$dispensingStore,
                'EligibleDispensory'=>$eligibleDispensory,
                'DispenseeID'=>$dispensee,
                'Patient'=>$patient
            ]);
            
            $dispensationId = $dispensationResult['lastInsertId'];
            //foreach dispensed item
            foreach ($dispensedItems as $datum){
                $dispensedItem[] = "($dispensationId, ".$datum['itemID'].",".$datum['quantity'].")";
            }

            $query = "INSERT INTO Pharmacy.DispensedItems (DispensationID, ItemID, DispensedQuantity) VALUES ".implode(", ", $dispensedItem);

            $result = (
                DBConnectionFactory::getConnection()
                ->exec($query)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a Dispensation
     */
    public static function editDispensation(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensingStore'])){
                $data['DispensingStore'] = QB::wrapString($data['DispensingStore'], "'");
            }
            if (isset($data['EligibleDispensory'])){
                $data['EligibleDispensory'] = QB::wrapString($data['EligibleDispensory'], "'");
            }
            
            $updateBuilder->table("Pharmacy.EligibleDispensory");
            $updateBuilder->set($data);
            $updateBuilder->where("EligibleDispensoryID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a DispensedItems
     */
    public static function editDispenseditems(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            if (isset($data['DispensedQuantity'])){
                $data['DispensedQuantity'] = QB::wrapString($data['DispensedQuantity'], "'");
            }
            
            $updateBuilder->table("Pharmacy.DispensedItems");
            $updateBuilder->set($data);
            $updateBuilder->where("DispensedItemsID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId = 0, array $data = [])
    {
        try
        {
            $selectBuilder = "SELECT a.*, b.StoreName, c.EligibleDispensory as Dispensory, d.PatientUUID, d.PatientFullName FROM Pharmacy.Dispensation a JOIN Pharmacy.Store b ON a.DispensingStore = b.StoreID JOIN Pharmacy.EligibleDispensory c ON a.EligibleDispensory = c.EligibleDispensoryID JOIN Patients.Patient d ON a.Patient = d.PatientID";

            if ($resourceId !== 0){
                $selectBuilder .= " WHERE a.DispensationID = $resourceId";
            }

            $dispensationResult = (
                DBConnectionFactory::getConnection()
                ->query((string)$selectBuilder)
            )->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dispensationResult as $key => $value) {
                $itemQ = "SELECT c.*, b.BillingTypeItemName, a.ItemBrand, a.ItemManufacturer FROM Pharmacy.DispensedItems c INNER JOIN Pharmacy.StoreInventory a ON c.ItemID = a.ItemID INNER JOIN Accounts.BillingTypeItems b ON a.Item = b.BillingTypeItemID WHERE c.DispensationID = ".$value["DispensationID"];
                $items = DBConnectionFactory::getConnection()->query($itemQ)->fetchAll(\PDO::FETCH_ASSOC);

                $dispensationResult[$key]["items"] = $items;
            }

            return $dispensationResult;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to retrieve requested data, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * deletes dispensedItems resource
     */

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Pharmacy.DispensedItems")
                ->where("DispensedItemID = $resourceId");
            
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