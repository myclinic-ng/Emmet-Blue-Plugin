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

CREATE TABLE Patients.PatientTypeCategories (
	CategoryID INT PRIMARY KEY IDENTITY,
	CategoryName VARCHAR(100) UNIQUE,
	CategoryDescription VARCHAR(500)
)

CREATE TABLE Patients.PatientType (
	PatientTypeID INT PRIMARY KEY IDENTITY,
	CategoryName VARCHAR(100),
	PatientTypeName VARCHAR(100),
	PatientTypeDescription VARCHAR(500),
	FOREIGN KEY (CategoryName) REFERENCES Patients.PatientTypeCategories(CategoryName) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Patients.Patient (
	PatientID INT PRIMARY KEY IDENTITY,
	PatientFullName VARCHAR(50),
	PatientPicture VARCHAR(MAX),
	PatientType INT,
	PatientIdentificationDocument VARCHAR(MAX),
	PatientProfileLockStatus BIT DEFAULT 1,
	ProfileDeleted BIT DEFAULT 0,
	PatientUUID VARCHAR(20) UNIQUE NOT NULL,
	LastModified DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientType) REFERENCES Patients.PatientType (PatientTypeID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Patients.PatientRecordsFieldValue (
	FieldValueID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	FieldTitle VARCHAR(50),
	FieldValue VARCHAR(max),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (FieldTitle) REFERENCES Patients.PatientRecordsFieldTitle(FieldTitleName) ON UPDATE CASCADE ON DELETE NO ACTION
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
	DiagnosisDate DATETIME DEFAULT GETDATE(),
	CodeNumber VARCHAR(50),
	DiagnosisType VARCHAR(20),
	DiagnosisTitle VARCHAR(100),
	Diagnosis VARCHAR(MAX),
	DiagnosisBy VARCHAR(20),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (DiagnosisBy) REFERENCES Staffs.Staff (StaffUUID) ON UPDATE CASCADE ON DELETE SET NULL,
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


CREATE TABLE Patients.PatientEvents (
	EventID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	EventDate DATE NOT NULL,
	EventTime TIME NOT NULL,
	EventActor VARCHAR(50) NOT NULL,
	EventLinkID VARCHAR(30),
	EventLink VARCHAR(MAX),
	EventText VARCHAR(100),
	EventIcon VARCHAR(30),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Patients.PatientAllergies (
	AllergyID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	AllergyTitle VARCHAR(50),
	AllergySeverity VARCHAR(20),
	AllergyType VARCHAR(50),
	AllergyDescription VARCHAR(500),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientAllergyTriggers (
	TriggerID INT PRIMARY KEY IDENTITY NOT NULL,
	AllergyID INT,
	TriggerTitle VARCHAR(100),
	FOREIGN KEY (AllergyID) REFERENCES Patients.PatientAllergies(AllergyID) ON UPDATE CASCADE ON DELETE CASCADE
)

CREATE TABLE Patients.PatientAllergySymptoms (
	TriggerID INT PRIMARY KEY IDENTITY NOT NULL,
	AllergyID INT,
	Symptom VARCHAR(100),
	FOREIGN KEY (AllergyID) REFERENCES Patients.PatientAllergies(AllergyID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Patients.PatientRepositoryTypes (
	RepositoryTypeID  INT PRIMARY KEY IDENTITY NOT NULL,
	RepositoryTypeName VARCHAR(30) UNIQUE NOT NULL
)
GO

CREATE TABLE Patients.PatientRepository (
	RepositoryID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	RepositoryNumber VARCHAR(50) NOT NULL UNIQUE,
	RepositoryName VARCHAR(100),
	RepositoryType VARCHAR(30),
	RepositoryDescription VARCHAR(4000),
	RepositoryCreator INT,
	RepositoryCreationDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (RepositoryCreator) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE,
	FOREIGN KEY (RepositoryType) REFERENCES Patients.PatientRepositoryTypes (RepositoryTypeName)
)
GO

CREATE TABLE Patients.PatientRepositoryItems (
	RepositoryItemID INT PRIMARY KEY IDENTITY NOT NULL,
	RepositoryID INT,
	RepositoryItemNumber VARCHAR(50) NOT NULL UNIQUE,
	RepositoryItemName VARCHAR(100),
	RepositoryItemDescription VARCHAR(4000),
	RepositoryItemCategory VARCHAR(20),
	RepositoryItemCreator INT,
	RepositoryItemCreationDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (RepositoryItemCreator) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (RepositoryID) REFERENCES Patients.PatientRepository
)
GO

CREATE TABLE Patients.RepositoryItemsMediaMeta (
	RepositoryItemsMetaID INT PRIMARY KEY IDENTITY NOT NULL,
	RepositoryItemID INT,
	RepositoryItemType VARCHAR(20),
	RepositoryItemUrl VARCHAR(500),
	RepositoryItemSize BIGINT,
	FOREIGN KEY (RepositoryItemID) REFERENCES Patients.PatientRepositoryItems ON UPDATE CASCADE
)
GO

CREATE TABLE Patients.RepositoryItemsComments (
	RepositoryItemsCommentID INT PRIMARY KEY NOT NULL,
	RepositoryItemID INT,
	CommenterID INT,
	Comment VARCHAR(4000),
	CommentDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (RepositoryItemID) REFERENCES Patients.PatientRepositoryItems,
	FOREIGN KEY (CommenterID) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO

CREATE TRIGGER Patients.DeletePatientRepository
   ON Patients.PatientRepository
   INSTEAD OF DELETE
AS 
BEGIN
	SET NOCOUNT ON;
	DELETE FROM Patients.PatientRepositoryItems WHERE RepositoryID IN (SELECT RepositoryID FROM DELETED)
	DELETE FROM Patients.PatientRepository WHERE  RepositoryID IN (SELECT RepositoryID FROM DELETED)
END
GO

CREATE TRIGGER Patients.DeletePatientRepositoryItems
   ON Patients.PatientRepositoryItems
   INSTEAD OF DELETE
AS 
BEGIN
	SET NOCOUNT ON;
	DELETE FROM Patients.RepositoryItemsMediaMeta WHERE RepositoryItemID IN (SELECT RepositoryItemID FROM DELETED)
	DELETE FROM Patients.RepositoryItemsComments WHERE RepositoryItemID IN (SELECT RepositoryItemID FROM DELETED)
	DELETE FROM Patients.PatientRepositoryItems WHERE RepositoryItemID IN (SELECT RepositoryItemID FROM DELETED)
END
GO