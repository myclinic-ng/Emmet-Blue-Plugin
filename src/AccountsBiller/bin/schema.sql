CREATE SCHEMA Accounts
GO

CREATE TABLE Accounts.BillingType (
	BillingTypeID INT PRIMARY KEY IDENTITY,
	BillingTypeName VARCHAR(100) NOT NULL UNIQUE,
	BillingTypeDescription VARCHAR (100)
);


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
	StaffID INT,
	FOREIGN KEY (BillingTransactionMetaID) REFERENCES [Accounts].[BillingTransactionMeta] (BillingTransactionMetaID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (BillingPaymentMethod) REFERENCES [Accounts].[BillingPaymentMethods] (PaymentMethodName) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID),
)
GO

CREATE TABLE Accounts.PaymentRequest (
	PaymentRequestID INT PRIMARY KEY IDENTITY,
	PaymentRequestUUID VARCHAR(20) UNIQUE,
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

CREATE TABLE Accounts.PaymentRequestItems (
	PaymentRequestItemsItems INT PRIMARY KEY IDENTITY,
	RequestID INT,
	ItemID INT,
	ItemQuantity INT,
	FOREIGN KEY (RequestID) REFERENCES Accounts.PaymentRequest (PaymentRequestID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (ItemID) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Accounts.PatientDepositsAccount (
	AccountID INT PRIMARY KEY NOT NULL IDENTITY(1000, 1),
	PatientID INT NOT NULL,
	AccountBalance MONEY NOT NULL DEFAULT 0.00,
	CreatedBy INT,
	DateCreated DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID),
	FOREIGN KEY (CreatedBy) REFERENCES [Staffs].[Staff] (StaffID),
	UNIQUE(PatientID)
)

CREATE TABLE Accounts.PatientDepositsAccountTransactions (
	TransactionID INT PRIMARY KEY NOT NULL IDENTITY(1000, 1),
	AccountID INT NOT NULL,
	StaffID INT,
	TransactionAmount MONEY,
	TransactionComment VARCHAR(500),
	TransactionDate DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (AccountID) REFERENCES Accounts.PatientDepositsAccount (AccountID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID)
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
	PatientType INT NOT NULL,
	BillingTypeItem INT NOT NULL,
	RuleType CHAR(1) NOT NULL,
	RuleValue INT NOT NULL,
	FOREIGN KEY (PatientType) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (BillingTypeItem) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(PatientType, BillingTypeItem),
	CHECK(RuleType = '*' OR RuleType = '+' OR RuleType = '-' OR RuleType = '%')
)

CREATE TABLE Accounts.DepartmentPatientTypesReportLink (
	LinkID INT IDENTITY,
	DepartmentID INT,
	PatientTypeID INT,
	FOREIGN KEY (DepartmentID) REFERENCES Staffs.Department (DepartmentID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (PatientTypeID) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (LinkID, DepartmentID, PatientTypeID) 
)

CREATE TABLE Accounts.PatientCategoriesHmoFieldTitles (
	FieldTitleID INT IDENTITY NOT NULL,
	PatientCategory INT,
	FieldTitleName VARCHAR(50),
	FieldTitleType VARCHAR(50),
	FieldTitleDescription VARCHAR(50),
	FOREIGN KEY (FieldTitleType) REFERENCES Patients.FieldTitleType(TypeName) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (PatientCategory) REFERENCES Patients.PatientTypeCategories(CategoryID) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (FieldTitleID, PatientCategory, FieldTitleName)
)

CREATE TABLE Accounts.PatientHmoProfile (
	ProfileID INT PRIMARY KEY IDENTITY(1000, 1),
	PatientID INT UNIQUE,
	PatientIdentificationDocument VARCHAR(500),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID)
)

CREATE TABLE Accounts.PatientHmoFieldValues (
	FieldValueID INT PRIMARY KEY IDENTITY NOT NULL,
	ProfileID INT,
	FieldTitle VARCHAR(50),
	FieldValue VARCHAR(max),
	LastModified DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (ProfileID) REFERENCES Accounts.PatientHmoProfile(ProfileID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(ProfileID, FieldTitle)
)
GO

CREATE TABLE Accounts.HmoSalesVerification (
	SalesID INT PRIMARY KEY IDENTITY(1000, 1) NOT NULL,
	DepartmentID INT,
	PatientID INT NOT NULL,
	SaleRequest VARCHAR(MAX),
	StaffID INT,
	RequestDate DATETIME DEFAULT GETDATE(),
	ProceedStatus INT,
	SignedBy INT,
	SignComment VARCHAR(400),
	SignedDate DATETIME,
	Status VARCHAR(100),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID),
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID),
	FOREIGN KEY (SignedBy) REFERENCES [Staffs].[Staff] (StaffID),
	FOREIGN KEY (DepartmentID) REFERENCES [Staffs].[Department] (DepartmentID)
)
GO

CREATE TABLE Accounts.HmoDocuments (
	DocumentID INT PRIMARY KEY IDENTITY(1000, 1) NOT NULL,
	DocumentNumber VARCHAR(50) NOT NULL UNIQUE,
	DocumentName VARCHAR(100),
	DocumentDescription VARCHAR(4000),
	DocumentCategory VARCHAR(20),
	DocumentCreator INT,
	DocumentCreationDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (DocumentCreator) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
)

CREATE TABLE Accounts.PatientTypeCategoriesDefaultPrices (
	DefaultPriceID INT PRIMARY KEY IDENTITY NOT NULL,
	BillingTypeItem INT,
	CategoryID INT,
	BillingTypeItemPrice MONEY NOT NULL,
	RateBased BIT,
	RateIdentifier VARCHAR(100),
	FOREIGN KEY (CategoryID) REFERENCES Patients.PatientTypeCategories (CategoryID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(BillingTypeItem, CategoryID)
)

CREATE TABLE Accounts.GeneralDefaultPrices (
	DefaultPriceID INT PRIMARY KEY IDENTITY NOT NULL,
	BillingTypeItem INT,
	BillingTypeItemPrice MONEY NOT NULL,
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(BillingTypeItem)
)

CREATE TABLE Accounts.PatientTypeTotalPaymentRules (
	RuleID INT PRIMARY KEY IDENTITY,
	PatientType INT NOT NULL UNIQUE,
	RuleType CHAR(1) NOT NULL,
	RuleValue INT NOT NULL,
	FOREIGN KEY (PatientType) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	CHECK(RuleType = '*' OR RuleType = '+' OR RuleType = '-' OR RuleType = '%')
)

CREATE TABLE Accounts.AppendedBillingTypePaymentRules (
	RuleID INT PRIMARY KEY IDENTITY,
	BillingTypeItem INT UNIQUE NOT NULL,
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE
)