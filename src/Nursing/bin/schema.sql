CREATE SCHEMA Nursing;
GO

CREATE TABLE Nursing.ObservationTypes (
	ObservationTypeID INT PRIMARY KEY IDENTITY NOT NULL,
	ObservationTypeName VARCHAR(100) NOT NULL,
	ObservationTypeDescription VARCHAR(500)
)
GO

CREATE TABLE Nursing.ObservationTypeFieldTypes (
	TypeID INT PRIMARY KEY IDENTITY NOT NULL,
	TypeName VARCHAR(50) UNIQUE,
	TypeDescription VARCHAR(50)
)
GO

CREATE TABLE Nursing.ObservationTypeFields (
	FieldID INT PRIMARY KEY IDENTITY NOT NULL,
	FieldObservationType INT,
	FieldName VARCHAR(100),
	FieldType INT,
	FieldDescription VARCHAR(500),
	FOREIGN KEY (FieldType) REFERENCES Nursing.ObservationTypeFieldTypes(TypeID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (FieldObservationType) REFERENCES Nursing.ObservationTypes (ObservationTypeID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Nursing.ObservationTypeFieldDefaults (
	FieldDefaultID INT PRIMARY KEY IDENTITY,
	Field INT,
	Value VARCHAR(500),
	FOREIGN KEY (Field) REFERENCES Nursing.ObservationTypeFields (FieldID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Nursing.ObservationTypeFieldDirtyValues (
	FieldDirtyValueID INT PRIMARY KEY IDENTITY,
	Field INT,
	Condition CHAR(3) NOT NULL,
	Value VARCHAR(50),
	FOREIGN KEY (Field) REFERENCES Nursing.ObservationTypeFields (FieldID) ON UPDATE CASCADE ON DELETE CASCADE,
	CHECK (Condition IN ('>', '<', '='))
)
GO

CREATE TABLE Nursing.PatientProcessLog (
	LogID INT PRIMARY KEY IDENTITY,
	PatientID INT,
	Nurse INT,
	ObservationSummary VARCHAR(500),
	Consultant INT,
	Department INT,
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (Nurse) REFERENCES Staffs.Staff(StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (Department) REFERENCES Staffs.Department (DepartmentID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO

CREATE TABLE Nursing.Observations (
	ObservationID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	RepositoryID INT,
	ObservationType INT,
	Observation VARCHAR(MAX),
	StaffID INT,
	ObservationDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (StaffID) REFERENCES Staffs.Staff(StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (ObservationType) REFERENCES Nursing.ObservationTypes (ObservationTypeID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (RepositoryID) REFERENCES Patients.PatientRepository (RepositoryID) ON UPDATE NO ACTION ON DELETE NO ACTION
)
GO

CREATE TABLE Nursing.ConsultantDepartments (
	ConsultantDepartmentID INT PRIMARY KEY IDENTITY NOT NULL,
	Department INT UNIQUE NOT NULL,
	FOREIGN KEY (Department) REFERENCES Staffs.Department (DepartmentID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Nursing.Ward(
	WardID INT PRIMARY KEY IDENTITY NOT NULL,
	WardName VARCHAR(50) NOT NULL UNIQUE,
	WardDescription VARCHAR(500),
	CreatedDate DATETIME NOT NULL DEFAULT GETDATE()
)
GO
CREATE TABLE Nursing.WardSection(
	WardSectionID INT PRIMARY KEY IDENTITY NOT NULL,
	WardID INT,
	WardSectionName VARCHAR(50) NOT NULL,
	WardSectionDescription VARCHAR(500),
	CreatedDate DATETIME NOT NULL DEFAULT GETDATE(),
	UNIQUE(WardID, WardSectionName),
	FOREIGN KEY (WardID) REFERENCES Nursing.Ward(WardID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO
CREATE TABLE Nursing.SectionBed(
	SectionBedID INT PRIMARY KEY IDENTITY NOT NULL,
	WardSectionID INT,
	BedName VARCHAR(50),
	BedDescription VARCHAR(500),
	BedStatus INT NOT NULL DEFAULT 0,
	UNIQUE(WardSectionID, BedName),
	FOREIGN KEY (WardSectionID) REFERENCES Nursing.WardSection(WardSectionID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Nursing.WardAdmission (
	WardAdmissionID INT PRIMARY KEY IDENTITY(1000, 1) NOT NULL,
	PatientAdmissionID INT UNIQUE NOT NULL,
	Bed INT NOT NULL,
	AdmissionProcessedBy INT,
	AdmissionDate DATETIME NOT NULL DEFAULT GETDATE(),
	DischargeStatus BIT DEFAULT 0,
	FOREIGN KEY (AdmissionProcessedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (Bed) REFERENCES Nursing.SectionBed(SectionBedID) ON UPDATE CASCADE
)
GO

CREATE TABLE Nursing.AdmissionTreatmentChart (
	TreatmentChartID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT NOT NULL,
	Drug VARCHAR(100),
	Dose VARCHAR(50),
	Route VARCHAR(50),
	Time VARCHAR(10),
	Note VARCHAR(500),
	Nurse INT,
	Date DATETIME NOT NULL DEFAULT GETDATE(),
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	Deleted SMALLINT DEFAULT 0,
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (Nurse) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO

CREATE TABLE Nursing.ServicesRendered (
	ServicesRenderedID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT,
	BillingTypeItem INT,
	BillingTypeItemName VARCHAR(100),
	BillingTypeItemQuantity INT,
	Nurse INT,
	DoctorInCharge INT,
	ServicesRenderedDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (Nurse) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (DoctorInCharge) REFERENCES [Staffs].[Staff] (StaffID),
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE SET NULL,
)
GO

CREATE TABLE Nursing.AdmissionBillingItems (
	AdmissionBillingItemID INT PRIMARY KEY IDENTITY NOT NULL,
	BillingTypeItem INT UNIQUE,
	FOREIGN KEY (BillingTypeItem) REFERENCES [Accounts].[BillingTypeItems] (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Nursing.PharmacyRequests (
	PharmacyRequestID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT,
	Request VARCHAR(500) NOT NULL,
	RequestDate DATETIME NOT NULL DEFAULT GETDATE(),
	Staff INT,
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID),
	FOREIGN KEY (Staff) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Nursing.PharmacyRequestsTreatmentCharts (
	TreatmentChartID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientID INT NOT NULL,
	Drug VARCHAR(100),
	Dose VARCHAR(50),
	Route VARCHAR(50),
	Time VARCHAR(10),
	Note VARCHAR(500),
	Nurse INT,
	Date DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientID),
	FOREIGN KEY (Nurse) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Nursing.WardTransferLog (
	TransferLogID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT,
	WardFrom INT,
	WardTo INT,
	TransferNote VARCHAR(1000),
	TransferredBy INT,
	TransferDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (TransferredBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (WardTo) REFERENCES Nursing.Ward(WardID) ON UPDATE CASCADE
)

CREATE TABLE Nursing.ShiftSummary (
	SummaryID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT,
	Nurse INT NOT NULL,
	Ward INT NOT NULL,
	SummaryTitle VARCHAR(50),
	Summary VARCHAR(1000),
	SummaryDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (Ward) REFERENCES Nursing.Ward(WardID) ON UPDATE CASCADE,
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (Nurse) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE
)