CREATE SCHEMA Pharmacy;
GO

CREATE TABLE Pharmacy.Store (
	StoreID INT PRIMARY KEY IDENTITY NOT NULL,
	StoreName VARCHAR(50) NOT NULL UNIQUE,
	StoreDescription VARCHAR(100)
)
GO

CREATE TABLE Pharmacy.StoreInventoryProperties (
	StoreInventoryPropertiesID INT PRIMARY KEY IDENTITY NOT NULL,
	StoreID INT,
	PropertyName VARCHAR(20) NOT NULL,
	FOREIGN KEY (StoreID) REFERENCES Pharmacy.Store(StoreID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO
CREATE TABLE Pharmacy.StoreInventory(
	ItemID INT PRIMARY KEY IDENTITY NOT NULL,
	StoreID INT,
	ItemName VARCHAR(50),
	ItemQuantity INT,
	FOREIGN KEY (StoreID) REFERENCES Pharmacy.Store(StoreID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO
CREATE TABLE Pharmacy.StoreInventoryTags(
	TagID INT PRIMARY KEY IDENTITY NOT NULL,
	ItemID INT,
	TagTitle VARCHAR(50),
	TagName VARCHAR(100),
	FOREIGN KEY (ItemID) REFERENCES Pharmacy.StoreInventory(ItemID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Pharmacy.EligibleDispensory(
	EligibleDispensoryID INT PRIMARY KEY IDENTITY NOT NULL,
	EligibleDispensory VARCHAR(20) UNIQUE
)
GO

CREATE TABLE Pharmacy.Dispensee(
	DispenseeID INT PRIMARY KEY IDENTITY NOT NULL,
	DispenseeType VARCHAR(20),
	DispenseeTypeID INT
)
GO
CREATE TABLE Pharmacy.Dispensation(
	DispensationID INT PRIMARY KEY IDENTITY NOT NULL,
	DispensingStore VARCHAR(50),
	EligibleDispensory VARCHAR(20),
	DispenseeID INT,
	DispensationDate DATETIME NOT NULL DEFAULT 'GETDATE()',
	FOREIGN KEY (DispensingStore) REFERENCES Pharmacy.Store(StoreName) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (EligibleDispensory) REFERENCES Pharmacy.EligibleDispensory(EligibleDispensory) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (DispenseeID) REFERENCES Pharmacy.Dispensee(DispenseeID) on UPDATE CASCADE ON DELETE SET NULL
)
GO
CREATE TABLE Pharmacy.DispensedItems(
	DispensedItemsID INT PRIMARY KEY IDENTITY NOT NULL,
	DispensationID INT NOT NULL,
	ItemID INT,
	DispensedQuantity INT,
	FOREIGN KEY (DispensationID) REFERENCES Pharmacy.Dispensation(DispensationID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO