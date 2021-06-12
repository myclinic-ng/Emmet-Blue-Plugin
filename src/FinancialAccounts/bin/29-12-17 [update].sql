CREATE TABLE FinancialAccounts.CorporateVendors (
	VendorID INT PRIMARY KEY IDENTITY,
	VendorName VARCHAR(50) NOT NULL UNIQUE,
	VendorDescription VARCHAR(300),
	VendorAddress VARCHAR(100)
)