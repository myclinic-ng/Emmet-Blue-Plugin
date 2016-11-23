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
	Value VARCHAR(500),
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
	ClinicalDiagnosis VARCHAR(100),
	InvestigationTypeRequired INT,
	InvestigationRequired VARCHAR(100),
	RegistrationDate DateTime DEFAULT GETDATE(),
	FOREIGN KEY (InvestigationTypeRequired) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
)

CREATE TABLE Lab.LabRequests (
	RequestID INT PRIMARY KEY IDENTITY(1000, 1),
	PatientID INT NOT NULL,
	ClinicalDiagnosis VARCHAR(100),
	InvestigationRequired VARCHAR(100) NOT NULL,
	RequestedBy INT NOT NULL,
	InvestigationType INT,
	RequestNote VARCHAR(500),
	RequestAcknowledged INT,
	RequestAcknowledgedBy INT,
	RequestDate DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (InvestigationType) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (RequestedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE NO ACTION,
)

CREATE TABLE Lab.LabResults (
	ResultID INT PRIMARY KEY IDENTITY(1000, 1),
	PatientLabNumber INT,
	RepositoryID INT,
	DateReported DATETIME DEFAULT GETDATE(),
	Report VARCHAR(MAX),
	ReportLab INT,
	ReportedBy INT,
	FOREIGN KEY (ReportedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE NO ACTION,
	FOREIGN KEY (ReportLab) REFERENCES Lab.Labs (LabID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (PatientLabNumber) REFERENCES Lab.Patients (PatientLabNumber) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (RepositoryID) REFERENCES Patients.PatientRepository (RepositoryID) ON UPDATE NO ACTION ON DELETE NO ACTION
)