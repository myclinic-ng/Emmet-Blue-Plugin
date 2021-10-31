CREATE SCHEMA InsuranceClaims
GO

CREATE TABLE [InsuranceClaims].[FinancierTypes] (
	FinancierTypeID INT PRIMARY KEY IDENTITY,
	TypeName VARCHAR(50) UNIQUE
);

CREATE TABLE [InsuranceClaims].[Financiers] (
	FinancierID INT PRIMARY KEY IDENTITY,
	FinancierUID VARCHAR(50) UNIQUE,
	FinancierType VARCHAR(50) DEFAULT NULL,
	DateCreated DATETIME DEFAULT GETDATE(),

	FOREIGN KEY (FinancierType) REFERENCES InsuranceClaims.FinancierTypes (TypeName) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE [InsuranceClaims].[FinancierPatientTypeLinks] (
	LinkID INT PRIMARY KEY IDENTITY,
	FinancierID INT NOT NULL,
	PatientTypeID INT NOT NULL,

	UNIQUE (FinancierID, PatientTypeID),

	FOREIGN KEY (FinancierID) REFERENCES InsuranceClaims.Financiers (FinancierID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (PatientTypeID) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE [InsuranceClaims].[Packages] (
	PackageID INT PRIMARY KEY IDENTITY,
	CategoryID INT UNIQUE,
	PackageCost MONEY,
	PackageStatus BIT DEFAULT 1,
	PackageDescription VARCHAR(100),
	FOREIGN KEY (CategoryID) REFERENCES Patients.PatientTypeCategories (CategoryID) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE [InsuranceClaims].[PrimaryAccounts] (
	PrimaryAccountID INT PRIMARY KEY IDENTITY,
	PatientTypeID INT UNIQUE,
	PatientID INT NOT NULL,

	FOREIGN KEY (PatientTypeID) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE NO ACTION ON DELETE NO ACTION
);