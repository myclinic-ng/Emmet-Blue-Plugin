CREATE SCHEMA Patients;
GO

CREATE TABLE Patients.FieldTitleType (
	TypeID INT PRIMARY KEY IDENTITY NOT NULL,
	TypeName VARCHAR(50) UNIQUE,
	TypeDescription VARCHAR(50)
)

CREATE TABLE Patients.PatientRecordsFieldTitle (
	FieldTitleID INT PRIMARY KEY IDENTITY NOT NULL,
	FieldTitleName VARCHAR(50) UNIQUE,
	FieldTitleType VARCHAR(50),
	FieldTitleDescription VARCHAR(50),
	FOREIGN KEY (FieldTitleType) REFERENCES Patients.FieldTitleType(TypeName) ON UPDATE CASCADE ON DELETE NO ACTION
)

CREATE TABLE Patients.PatientType (
	PatientTypeID INT PRIMARY KEY IDENTITY,
	PatientTypeName VARCHAR(50) UNIQUE,
	PatientTypeDescription VARCHAR(500)
)

CREATE TABLE Patients.Patient (
	PatientID INT PRIMARY KEY IDENTITY,
	PatientFullName VARCHAR(50),
	PatientPicture VARCHAR(MAX),
	PatientType VARCHAR(50),
	PatientIdentificationDocument VARCHAR(MAX),
	PatientUUID VARCHAR(20) UNIQUE NOT NULL,
	FOREIGN KEY (PatientType) REFERENCES Patients.PatientType (PatientTypeName) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Patients.PatientRecordsFieldValue (
	FieldValueID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	FieldTitle VARCHAR(50),
	FieldValue VARCHAR(max),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (FieldTitle) REFERENCES Patients.PatientRecordsFieldTitle(FieldTitleName) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientHospitalHistory (
	HospitalHistoryID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT NOT NULL,
	DateAttended DATETIME,
	ReferredBy VARCHAR(50),
	Physician VARCHAR(50),
	Ward VARCHAR(50),
	DateDischarged DATETIME,
	DischargedTo VARCHAR(50),
	Condition VARCHAR(50),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientDiagnosis (
	DiagnosisID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT NOT NULL,
	DaignosisDate DATETIME,
	CodeNumber VARCHAR(50),
	DiagnosisType VARCHAR(20),
	Diagnosis VARCHAR(MAX),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	CHECK (DiagnosisType = 'operation' OR DiagnosisType = 'diagnosis')
)

CREATE TABLE Patients.PatientProcessCheck (
	ProcessCheckID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT NOT NULL,
	ProcessCheck VARCHAR(50),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientProcessCheckDates (
	ProcessCheckDateID INT PRIMARY KEY IDENTITY NOT NULL,
	ProcessCheckID INT NOT NULL,
	ProcessCheckDateTitle VARCHAR(50),
	ProcessCheckDate DATE,
	FOREIGN KEY (ProcessCheckID) REFERENCES Patients.PatientProcessCheck(ProcessCheckID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientDepartment (
	PatientDepartmentID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	DepartmentID INT,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientTransaction(
	PatientTransactionID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	Link VARCHAR(max),
	Meta VARCHAR(max),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)

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