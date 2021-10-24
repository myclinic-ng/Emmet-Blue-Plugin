CREATE SCHEMA InsuranceClaims
GO

CREATE TABLE [InsuranceClaims].[Financiers] (
	FinancierID INT PRIMARY KEY IDENTITY,
	FinancierUID VARCHAR(50) UNIQUE,
	DateCreated DATETIME DEFAULT GETDATE()
);

CREATE TABLE [InsuranceClaims].[FinancierPatientTypeLinks] (
	LinkID INT PRIMARY KEY IDENTITY,
	FinancerID INT NOT NULL,
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
	FOREIGN KEY (CategoryID) REFERENCES Patients.PatientTypeCategory (CategoryID) ON UPDATE CASCADE ON DELETE CASCADE
);