CREATE SCHEMA Pharmacy;
GO

CREATE TABLE Pharmacy.Store (
	StoreID INT PRIMARY KEY IDENTITY NOT NULL,
	StoreName VARCHAR(50) NOT NULL UNIQUE,
	StoreDescription VARCHAR(1000)
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
	Item INT,
	ItemBrand VARCHAR(50),
	ItemManufacturer VARCHAR(50),
	ItemQuantity INT,
	FOREIGN KEY (Item) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (StoreID) REFERENCES Pharmacy.Store(StoreID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(Item, StoreID)
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

CREATE TABLE Pharmacy.DispensoryStoreLink(
	LinkID INT PRIMARY KEY IDENTITY NOT NULL,
	Dispensory INT,
	Store INT,
	FOREIGN KEY (Store) REFERENCES Pharmacy.Store(StoreID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (Dispensory) REFERENCES Pharmacy.EligibleDispensory(EligibleDispensoryID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(Dispensory, Store)
)
GO

-- CREATE TABLE Pharmacy.Dispensee(
-- 	DispenseeID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	DispenseeType VARCHAR(20),
-- 	DispenseeTypeID INT
-- )
-- GO

CREATE TABLE Pharmacy.Dispensation(
	DispensationID INT PRIMARY KEY IDENTITY NOT NULL,
	DispensingStore INT,
	EligibleDispensory INT,
	DispenseeID VARCHAR(20),
	Patient INT,
	DispensationDate DATETIME NOT NULL DEFAULT GETDATE()
	FOREIGN KEY (DispensingStore) REFERENCES Pharmacy.Store(StoreID) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (EligibleDispensory) REFERENCES Pharmacy.EligibleDispensory(EligibleDispensoryID) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (DispenseeID) REFERENCES Staffs.Staff(StaffUUID) on UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (Patient) REFERENCES [Patients].[Patient] (PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
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