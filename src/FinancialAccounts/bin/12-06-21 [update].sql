CREATE TABLE FinancialAccounts.GeneralJournalEntryMetadataFieldTypes (
	TypeID INT PRIMARY KEY IDENTITY,
	TypeName VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE FinancialAccounts.GeneralJournalEntryMetadataFields (
	FieldID INT PRIMARY KEY IDENTITY,
	FieldName VARCHAR(100) UNIQUE,
	FieldType varchar(50),
	FieldDescription VARCHAR(50),
	FOREIGN KEY (FieldType) REFERENCES FinancialAccounts.GeneralJournalEntryMetadataFieldTypes (TypeName) ON UPDATE CASCADE ON DELETE NO ACTION
);

CREATE TABLE FinancialAccounts.GeneralJournalEntryMetadata (
	MetadataID INT PRIMARY KEY IDENTITY,
	EntryID INT,
	FieldName VARCHAR(100),
	FieldValue VARCHAR(500),
	FOREIGN KEY (EntryID) REFERENCES FinancialAccounts.GeneralJournalEntries (EntryID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (FieldName) REFERENCES FinancialAccounts.GeneralJournalEntryMetadataFields (FieldName) ON UPDATE CASCADE ON DELETE NO ACTION
);

INSERT INTO FinancialAccounts.GeneralJournalEntryMetadataFieldTypes(TypeName) VALUES ('number'), ('text'), ('longtext'), ('currency');

INSERT INTO FinancialAccounts.GeneralJournalEntryMetadataFields (FieldName, FieldType) VALUES
('Name', 'text'),
('Receipt No.', 'text'),
('Cheque No.', 'text'),
('Payment Method', 'text'),
('Payment Bank', 'text');
GO

CREATE TABLE FinancialAccounts.BillingTypeJournalAccountLink (
	JournalAccountLinkID INT PRIMARY KEY IDENTITY,
	BillingTypeID INT,
	AccountID INT,
	FOREIGN KEY (BillingTypeID) REFERENCES Accounts.BillingType (BillingTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (AccountID) REFERENCES FinancialAccounts.Accounts (AccountID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(BillingTypeID, AccountID)
);

CREATE TABLE FinancialAccounts.BillingTypeItemJournalAccountLink (
	JournalAccountLinkID INT PRIMARY KEY IDENTITY,
	BillingTypeItemID INT,
	AccountID INT,
	FOREIGN KEY (BillingTypeItemID) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (AccountID) REFERENCES FinancialAccounts.Accounts (AccountID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(BillingTypeItemID, AccountID)
);

ALTER TABLE FinancialAccounts.GeneralJournalEntries ADD UserGeneratedID VARCHAR(30) UNIQUE
GO