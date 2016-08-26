CREATE SCHEMA Patients;
GO

CREATE TABLE Patients.FieldTitleType (
	TypeID,
	TypeName,
	TypeDescription
)
GO

CREATE TABLE Patients.PatientRecordsFieldTitle (
	FieldTitleID,
	FieldTitleName,
	FieldTitleType,
	FieldTitleDescription
)
GO

CREATE TABLE Patients.Patient (
	PatientID INT PRIMARY KEY IDENTITY,
	PatientFullName VARCHAR(50),
	PatientPhoneNumber VARCHAR(20),
	PatientUUID VARCHAR(20) UNIQUE NOT NULL,
)
GO;

CREATE TABLE Patients.PatientRecordsFieldValue (
	FieldValueID,
	PatientID,
	FieldTitle,
	FieldValue VARCHAR(MAX)
)
GO;

CREATE TABLE Patients.PatientDepartment (
	PatientDepartmentID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	DepartmentID INT,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Patients.PatientTransaction(
	PatientTransactionID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	Link VARCHAR(max),
	Meta VARCHAR(max),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Patients.PatientRepository (
	RepositoryItemID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	RepositoryItemNumber VARCHAR(50) NOT NULL UNIQUE,
	RepositoryItemName VARCHAR(100),
	RepositoryItemDescription VARCHAR(4000),
	RepositoryItemUrl VARCHAR(MAX),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO