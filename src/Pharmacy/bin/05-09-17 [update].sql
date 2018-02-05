CREATE TABLE Pharmacy.InventoryLabels(
	LabelID INT PRIMARY KEY IDENTITY NOT NULL,
	LabelUUID VARCHAR(30) UNIQUE NOT NULL,
	ItemID INT,
	ItemManufacturedDate DATETIME,
	ItemExpiryDate DATETIME,
	ItemBatchNumber VARCHAR(50),
	ItemSerialNumber VARCHAR(50),
	ItemDispensableInUnits BIT NOT NULL DEFAULT 0,
	ItemTotalUnit SMALLINT NOT NULL DEFAULT 1,
	LabelCreationTime DATETIME NOT NULL DEFAULT GETDATE(),
	LabelCreatedBy INT,
	LabelPrinted BIT DEFAULT 0,

	FOREIGN KEY (LabelCreatedBy) REFERENCES Staffs.Staff(StaffID) on UPDATE CASCADE ON DELETE SET NULL, 
	FOREIGN KEY (ItemID) REFERENCES Pharmacy.StoreInventory(ItemID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Pharmacy.InventoryLabelDispensation(
	LabelDispensationID INT PRIMARY KEY IDENTITY NOT NULL,
	LabelUUID VARCHAR(30) NOT NULL,
	DispensationID INT,
	DispensedQuantity SMALLINT DEFAULT 1,
	DispensationTime DATETIME NOT NULL DEFAULT GETDATE(),
	StaffID INT,

	FOREIGN KEY (StaffID) REFERENCES Staffs.Staff(StaffID), 
	FOREIGN KEY (LabelUUID) REFERENCES Pharmacy.InventoryLabels(LabelUUID), 
	FOREIGN KEY (DispensationID) REFERENCES Pharmacy.Dispensation(DispensationID) ON UPDATE CASCADE
)
GO