<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Pharmacy\InventoryLabel;

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
 * class InventoryLabel.
 *
 * InventoryLabel Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 06/09/2017 11:32
 */
class InventoryLabel
{
    public static function create(array $data)
    {
        $labelQty = $data['labelQty'] ?? null;
        $item = $data['item'] ?? null;
        $staff = $data["staff"] ?? 'NULL';
        $manufacturedDate = $data["data"]["manufacturedDate"] ?? null;
        $expiryDate = $data["data"]["expiryDate"] ?? null;
        $batchNumber = $data["data"]["batchNumber"] ?? null;
        $serialNumber = $data["data"]["serialNumber"] ?? null;
        $dispensableInUnits = (isset($data["data"]["dispensableInUnits"]) && !$data["data"]["dispensableInUnits"]) ? 1 : 0;
        $totalUnit = $data["data"]["totalUnit"] ?? 1;

        $currentTimestamp = (new \DateTime())->getTimeStamp();

        $values = [];
        for ($counter = 0; $counter < $labelQty; $counter++){
            $uuid = $currentTimestamp.$counter;
            $values[] = "('$uuid', $item, CONVERT(date, '$manufacturedDate'), CONVERT(date, '$expiryDate'), '$batchNumber', '$serialNumber', $dispensableInUnits, $totalUnit, $staff)";
        }

        $query = "INSERT INTO Pharmacy.InventoryLabels (LabelUUID, ItemID, ItemManufacturedDate, ItemExpiryDate, ItemBatchNumber, ItemSerialNumber, ItemDispensableInUnits, ItemTotalUnit, LabelCreatedBy) VALUES ". implode(", ", $values);
        
        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

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

    public static function getPrintableLabels(int $resourceId, array $data){
        $manufacturedDate = $data["manufacturedDate"] ?? null;
        $expiryDate = $data["expiryDate"] ?? null;
        $batchNumber = $data["batchNumber"] ?? null;
        $count = $data["count"] ?? 200;

        if (is_null($batchNumber)){
            $query = "SELECT TOP $count a.* FROM Pharmacy.InventoryLabels a LEFT OUTER JOIN Pharmacy.InventoryLabelDispensation b ON a.LabelUUID = b.LabelUUID WHERE b.LabelUUID IS NULL AND (a.ItemID=$resourceId AND a.LabelPrinted = 0)";
        }
        else {
            $query = "SELECT TOP $count a.* FROM Pharmacy.InventoryLabels a LEFT OUTER JOIN Pharmacy.InventoryLabelDispensation b ON a.LabelUUID = b.LabelUUID WHERE b.LabelUUID IS NULL AND (a.ItemID=$resourceId AND a.ItemBatchNumber = '$batchNumber' AND CONVERT(date, a.ItemManufacturedDate) = CONVERT(date, '$manufacturedDate') AND CONVERT(date,a.ItemExpiryDate) = CONVERT(date, '$expiryDate') AND a.LabelPrinted = 0)";
        }

        // die($query);

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $result;

    }

    public static function printLabels(array $data){
        $store = $data["store"];
        $labels = $data["labels"];
        $labels = implode(",", $labels);

        $query = "UPDATE Pharmacy.InventoryLabels SET LabelPrinted = 1, LabelCurrentStore=$store, LabelPrintedDate=GETDATE() WHERE LabelID IN ($labels)";
        $result = DBConnectionFactory::getConnection()->exec($query);

        return $result;
    }

    public static  function getLabelDetails(array $data){
        $uuid = $data["uuid"];

        $conn = DBConnectionFactory::getConnection();

        $query = "SELECT * FROM Pharmacy.InventoryLabels WHERE LabelID = $uuid";
        $result = $conn->query($query)->fetchAll(\PDO::FETCH_ASSOC);
            
        $result = $result[0] ?? $result;

        $_query = "SELECT SUM(a.DispensedQuantity) AS Total from Pharmacy.InventoryLabelDispensation a INNER JOIN Pharmacy.InventoryLabels b ON a.LabelUUID = b.LabelUUID WHERE b.LabelID = $uuid";
        $total = $conn->query($_query)->fetchAll(\PDO::FETCH_ASSOC)[0]["Total"];

        $result["AvailableQuantity"] = $result["ItemTotalUnit"] - $total;

        return $result;
    }

    public static function newDispensation(array $data){
        $staff = $data["staff"] ?? 'NULL';
        $values = [];

        foreach ($data["data"] as $key => $value) {
            $label = $value["labeluuid"] ?? null;
            $dispensation = $value["dispensation"] ?? null;
            $quantity = $value["quantity"] ?? null;

            $values[] = "('$label', $dispensation, $quantity, $staff)";
        }

        $query = "INSERT INTO Pharmacy.InventoryLabelDispensation (LabelUUID, DispensationID, DispensedQuantity, StaffID) VALUES ".implode(", ", $values);
        
        try
        {
            $result = DBConnectionFactory::getConnection()->exec($query);

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
}