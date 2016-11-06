CREATE SCHEMA Lab
GO

CREATE TABLE Lab.Labs (
	LabID INT PRIMARY KEY IDENTITY,
	LabName VARCHAR(20),
	LabDescription VARCHAR(100)
)

CREATE TABLE Lab.InvestigationTypes (
	InvestigationTypeID INT PRIMARY KEY IDENTITY,
	InvestigationTypeLab INT,
	InvestigationTypeName VARCHAR(50),
	InvestigationTypeDescription VARCHAR(100),
	FOREIGN KEY (InvestigationTypeLab) REFERENCES Lab.Labs (LabID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Lab.InvestigationTypeFieldTypes (
	TypeID INT PRIMARY KEY IDENTITY,
	TypeName VARCHAR(20),
	TypeDescription VARCHAR(50)
)

CREATE TABLE Lab.InvestigationTypeFields (
	FieldID INT PRIMARY KEY IDENTITY,
	InvestigationType INT,
	FieldType INT,
	FieldName VARCHAR(20),
	FieldDescription VARCHAR(50),
	FOREIGN KEY (InvestigationType) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (FieldType) REFERENCES Lab.InvestigationTypeFieldTypes (TypeID) ON UPDATE CASCADE ON DELETE NO ACTION
)

CREATE TABLE Lab.InvestigationTypeFieldDefaults (
	FieldDefaultID INT PRIMARY KEY IDENTITY,
	Field INT,
	Value VARCHAR(20),
	FOREIGN KEY (Field) REFERENCES Lab.InvestigationTypeFields (FieldID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Lab.Patients (
	PatientLabNumber INT PRIMARY KEY IDENTITY(1000, 1),
	PatientID INT,
	FullName VARCHAR(30),
	DateOfBirth DateTime,
	Gender VARCHAR(10),
	Address VARCHAR(50),
	PhoneNumber VARCHAR(13),
	Clinic VARCHAR(50),
	ClinicalDiagnosis VARCHAR(50),
	InvestigationTypeRequired INT,
	InvestigationRequired VARCHAR(100),
	RegistrationDate DateTime DEFAULT GETDATE(),
	FOREIGN KEY (InvestigationTypeRequired) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
)