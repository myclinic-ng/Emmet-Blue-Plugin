CREATE SCHEMA Accounts
GO

CREATE TABLE Accounts.BillingType (
	BillingTypeID INT PRIMARY KEY IDENTITY,
	BillingTypeName VARCHAR(100) NOT NULL UNIQUE,
	BillingTypeDescription VARCHAR (100)
);

-- DEPRECATED
CREATE TABLE Accounts.BillingTypeCustomerCategories (
	CustomerCategoryID INT PRIMARY KEY IDENTITY,
	CustomerCategoryName VARCHAR(100) UNIQUE NOT NULL,
	CustomerCategoryDescription VARCHAR(250)
)
-- /DEPRECATED

CREATE TABLE Accounts.BillingTransactionStatuses (
	StatusID INT PRIMARY KEY IDENTITY,
	StatusName VARCHAR(20) UNIQUE NOT NULL,
	StatusDescription VARCHAR(250)
)

CREATE TABLE Accounts.BillingPaymentMethods (
	PaymentMethodID INT PRIMARY KEY IDENTITY,
	PaymentMethodName VARCHAR(20) UNIQUE NOT NULL,
	PaymentMethodDescription VARCHAR(250)
)

-- DEPRECATED
CREATE TABLE Accounts.BillingCustomerInfo (
	CustomerContactID INT PRIMARY KEY IDENTITY,
	CustomerCategoryID INT,
	CustomerContactName VARCHAR(100),
	CustomerContactPhone VARCHAR(20),
	CustomerContactAddress VARCHAR(500),
	FOREIGN KEY (CustomerCategoryID) REFERENCES [Accounts].[BillingTypeCustomerCategories] (CustomerCategoryID) ON UPDATE CASCADE ON DELETE SET NULL
)
-- /DEPRECATED

CREATE TABLE Accounts.BillingTypeItems (
	BillingTypeItemID INT PRIMARY KEY IDENTITY(1000, 1),
	BillingType INT,
	BillingTypeItemName VARCHAR (100),
	FOREIGN KEY (BillingType) REFERENCES [Accounts].[BillingType] (BillingTypeID) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE Accounts.BillingTypeItemsPrices (
	BillingTypeItemsPricesID INT PRIMARY KEY IDENTITY,
	BillingTypeItem INT,
	PatientType INT,
	BillingTypeItemPrice MONEY NOT NULL,
	RateBased BIT,
	RateIdentifier VARCHAR(100),
	IntervalBased BIT,
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(BillingTypeItem, PatientType)
);

CREATE TABLE Accounts.BillingTypeItemsInterval (
	BillingTypeItemsIntervalID INT PRIMARY KEY IDENTITY,
	BillingTypeItemID INT NOT NULL,
	Interval INT DEFAULT 1,
	IntervalIncrementType VARCHAR(50) DEFAULT 'custom',
	IntervalIncrement INT,
	CHECK (IntervalIncrementType = 'geometric' OR IntervalIncrementType = 'multiplicative' OR IntervalIncrementType = 'additive' OR IntervalIncrementType = 'custom'),
	FOREIGN KEY (BillingTypeItemID) REFERENCES Accounts.BillingTypeItems(BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE 
);

CREATE TABLE Accounts.BillingTransactionMeta (
	BillingTransactionMetaID INT PRIMARY KEY IDENTITY,
	BillingTransactionNumber VARCHAR(15) UNIQUE NOT NULL,
	PatientID INT,
	BillingType VARCHAR(100),
	BilledAmountTotal MONEY,
	CreatedByUUID VARCHAR(20),
	DateCreated DATETIME NOT NULL DEFAULT GETDATE(),
	DateCreatedDateOnly DATE DEFAULT Cast(DateAdd(day, datediff(day, 0, GETDATE()), 0) as Date),
	BillingTransactionStatus VARCHAR(20) NOT NULL DEFAULT 'Unknown',
	Status VARCHAR(10),
	FOREIGN KEY (BillingType) REFERENCES [Accounts].[BillingType] (BillingTypeName) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (PatientID) REFERENCES [Patients].[Patient] (PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (CreatedByUUID) REFERENCES [Staffs].[Staff] (StaffUUID) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (BillingTransactionStatus) REFERENCES [Accounts].[BillingTransactionStatuses] (StatusName) ON UPDATE CASCADE ON DELETE NO ACTION
)

CREATE TABLE Accounts.BillingTransactionItems (
	BillingTransactionItemID INT PRIMARY KEY IDENTITY,
	BillingTransactionMetaID INT NOT NULL,
	BillingTransactionItem INT,
	BillingTransactionItemQuantity INT,
	BillingTransactionItemPrice MONEY,
	FOREIGN KEY (BillingTransactionMetaID) REFERENCES [Accounts].[BillingTransactionMeta] (BillingTransactionMetaID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (BillingTransactionItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE NO ACTION ON DELETE NO ACTION
)

CREATE TABLE Accounts.BillingTransaction (
	BillingTransactionID INT PRIMARY KEY IDENTITY,
	BillingTransactionMetaID INT,
	BillingTransactionDate DATETIME NOT NULL DEFAULT GETDATE(),
	BillingTransactionCustomerName VARCHAR(50),
	BillingTransactionCustomerPhone VARCHAR(20),
	BillingTransactionCustomerAddress VARCHAR(500),
	BillingPaymentMethod VARCHAR(20) NOT NULL,
	BillingAmountPaid MONEY NOT NULL,
	BillingAmountBalance MONEY,
	FOREIGN KEY (BillingTransactionMetaID) REFERENCES [Accounts].[BillingTransactionMeta] (BillingTransactionMetaID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (BillingPaymentMethod) REFERENCES [Accounts].[BillingPaymentMethods] (PaymentMethodName) ON UPDATE CASCADE ON DELETE NO ACTION
)
GO

CREATE TABLE Accounts.PaymentRequest (
	PaymentRequestID INT PRIMARY KEY IDENTITY,
	PaymentRequestUUID VARCHAR(20),
	RequestPatientID INT NOT NULL,
	RequestBy VARCHAR(20),
	RequestDepartment INT,
	RequestDate DATETIME DEFAULT GETDATE(),
	RequestFulfillmentStatus INT DEFAULT 0,
	AttachedInvoice INT,
	RequestFulfilledBy VARCHAR(20),
	RequestFulFilledDate DATETIME,
	FOREIGN KEY (RequestPatientID) REFERENCES [Patients].[Patient] (PatientID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (RequestBy) REFERENCES [Staffs].[Staff] (StaffUUID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (RequestFulfilledBy) REFERENCES [Staffs].[Staff] (StaffUUID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (RequestDepartment) REFERENCES Staffs.Department (DepartmentID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (AttachedInvoice) REFERENCES [Accounts].[BillingTransactionMeta] (BillingTransactionMetaID) ON UPDATE NO ACTION ON DELETE SET NULL
)
GO

CREATE TABLE Accounts.PaymentRequestItems (
	PaymentRequestItemsItems INT PRIMARY KEY IDENTITY,
	RequestID INT,
	ItemID INT,
	ItemQuantity INT,
	FOREIGN KEY (RequestID) REFERENCES Accounts.PaymentRequest (PaymentRequestID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (ItemID) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Accounts.DepartmentBillingLink (
	LinkID INT IDENTITY,
	DepartmentID INT,
	BillingTypeID INT,
	FOREIGN KEY (DepartmentID) REFERENCES Staffs.Department (DepartmentID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (BillingTypeID) REFERENCES Accounts.BillingType (BillingTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (LinkID, DepartmentID, BillingTypeID) 
)

CREATE TABLE Accounts.BillPaymentRules (
	RuleID INT PRIMARY KEY IDENTITY,
	PatientType INT NOT NULL UNIQUE,
	RuleType CHAR(1) NOT NULL,
	RuleValue INT NOT NULL,
	FOREIGN KEY (PatientType) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	CHECK(RuleType = '*' OR RuleType = '+' OR RuleType = '-' OR RuleType = '%')
)
GO

CREATE TRIGGER Accounts.PreventBillingTypeItemsDuplicates
 ON Accounts.BillingTypeItems
 INSTEAD OF INSERT
AS
BEGIN
  SET NOCOUNT ON;

  IF NOT EXISTS (SELECT 1 FROM inserted AS i 
    INNER JOIN Accounts.BillingTypeItems AS t
    ON i.BillingType = t.BillingType
    AND i.BillingTypeItemName = t.BillingTypeItemName
  )
  BEGIN
    INSERT Accounts.BillingTypeItems(BillingType, BillingTypeItemName)
      SELECT BillingType, BillingTypeItemName FROM inserted;
  END
  ELSE
  BEGIN
    RAISERROR('Error: Duplicate items are not allowed', 16, 1);
  END
END
GO