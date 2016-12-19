CREATE SCHEMA FinancialAccounts;
GO

CREATE TABLE FinancialAccounts.AccountTypeCategories (
	CategoryID INT PRIMARY KEY IDENTITY,
	CategoryName VARCHAR(50) NOT NULL UNIQUE,
	CategoryDescription VARCHAR(300),
	SideOnEquation CHAR(1),
	CHECK (SideOnEquation IN ('R', 'L'))
)

CREATE TABLE FinancialAccounts.AccountTypes (
	TypeID INT PRIMARY KEY IDENTITY,
	CategoryID INT,
	TypeName VARCHAR(50) NOT NULL UNIQUE,
	TypeDescription VARCHAR(300),
	FOREIGN KEY (CategoryID) REFERENCES FinancialAccounts.AccountTypeCategories(CategoryID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE FinancialAccounts.Accounts (
	AccountID INT PRIMARY KEY IDENTITY(10000, 100),
	AccountTypeID INT NOT NULL,
	AccountName VARCHAR(50) NOT NULL UNIQUE,
	AccountDescription VARCHAR(300),
	AccountStatus VARCHAR(10) NOT NULL DEFAULT 'Active',
	DateCreated DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (AccountTypeID) REFERENCES FinancialAccounts.AccountTypes(TypeID) ON UPDATE CASCADE,
	CHECK (AccountStatus IN ('Active', 'Inactive'))
)


CREATE TABLE FinancialAccounts.AccountingPeriods (
	PeriodID INT PRIMARY KEY IDENTITY(1000, 1),
	PeriodStartDate DATE NOT NULL,
	PeriodEndDate DATE NOT NULL,
	PeriodAlias VARCHAR(100),
	PeriodEditable INT NOT NULL DEFAULT 1, 
	DateCreated DATETIME NOT NULL DEFAULT GETDATE()
)

CREATE TABLE FinancialAccounts.AccountingPeriodBeginningBalances (
	BeginningBalanceID INT PRIMARY KEY IDENTITY,
	AccountingPeriodID INT NOT NULL,
	AccountID INT NOT NULL,
	BalanceValue MONEY DEFAULT 0.00,
	FOREIGN KEY (AccountingPeriodID) REFERENCES FinancialAccounts.AccountingPeriods (PeriodID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (AccountID) REFERENCES FinancialAccounts.Accounts (AccountID) ON UPDATE CASCADE
)