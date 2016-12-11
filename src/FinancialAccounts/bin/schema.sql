CREATE SCHEMA FinancialAccounts;
GO

CREATE TABLE FinancialAccounts.AccountTypeCategories (
	CategoryID INT PRIMARY KEY IDENTITY,
	CategoryName VARCHAR(50) NOT NULL,
	CategoryDescription VARCHAR(300)
)

CREATE TABLE FinancialAccounts.AccountTypes (
	TypeID INT PRIMARY KEY IDENTITY,
	CategoryID INT,
	TypeName VARCHAR(50) NOT NULL,
	TypeDescription VARCHAR(300),
	FOREIGN KEY (CategoryID) REFERENCES FinancialAccounts.AccountTypeCategories(CategoryID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE FinancialAccounts.Accounts (
	AccountID INT PRIMARY KEY IDENTITY(1000, 100),
	AccountTypeID INT,
	AccountName VARCHAR(50) NOT NULL,
	AccountDescription VARCHAR(300),
	FOREIGN KEY (AccountTypeID) REFERENCES FinancialAccounts.AccountTypes(TypeID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO