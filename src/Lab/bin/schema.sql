CREATE SCHEMA Lab
GO

CREATE Table Labs (
	LabID INT PRIMARY KEY IDENTITY,
	LabName VARCHAR(20),
	LabDescription VARCHAR(100)
)

CREATE TABLE InvestigationTypes (
	InvestigationTypeID INT PRIMARY KEY IDENTITY,
	InvestigationTypeLab INT,
	InvestigationTypeName VARCHAR(50),
	InvestigationTypeDescription VARCHAR(100),
	FOREIGN KEY (InvestigationTypeLab) REFERENCES Lab.Labs (LabID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE InvestigationTypeFieldTypes (
	TypeID INT PRIMARY KEY IDENTITY,
	TypeName VARCHAR(20),
	TypeDescription VARCHAR(50)
)

CREATE TABLE InvestigationTypeFields (
	FieldID INT PRIMARY KEY IDENTITY,
	InvestigationType INT,
	FieldType INT,
	FieldName VARCHAR(20),
	FieldDescription VARCHAR(50),
	FOREIGN KEY (InvestigationType) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (FieldType) REFERENCES Lab.InvestigationTypes (InvestigationTypeFieldTypes) ON UPDATE CASCADE ON DELETE NO ACTION
)