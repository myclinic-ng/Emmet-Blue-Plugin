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

-- CREATE TABLE Nursing.Observation(
-- 	ObservationID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	PatientID VARCHAR(20),
-- 	StaffID VARCHAR(20),
-- 	ObservationDate DATE NOT NULL DEFAULT GETDATE(),
-- 	FOREIGN KEY (PatientID) REFERENCES Patients.Patient(PatientUUID) ON UPDATE CASCADE ON DELETE CASCADE,
-- 	FOREIGN KEY (StaffID) REFERENCES Staffs.Staff(StaffUUID) ON UPDATE CASCADE ON DELETE NO ACTION
-- )
-- GO

-- CREATE TABLE Nursing.ObservationFieldValue (
-- 	FieldValueID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	ObservationID INT,
-- 	Field VARCHAR(50),
-- 	FieldValue VARCHAR(max),
-- 	FOREIGN KEY (Field) REFERENCES Nursing.ObservationField(FieldName) ON UPDATE CASCADE ON DELETE NO ACTION,
-- 	FOREIGN KEY (ObservationID) REFERENCES Nursing.Observation(ObservationID) ON UPDATE CASCADE ON DELETE CASCADE
-- )
-- GO
-- CREATE TABLE Nursing.Ward(
-- 	WardID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	WardName VARCHAR(50) UNIQUE,
-- 	WardDescription VARCHAR(50),
-- 	CreatedDate DATE NOT NULL DEFAULT GETDATE(),
-- 	UpdatedDate DATE NOT NULL DEFAULT GETDATE()
-- )
-- GO
-- CREATE TABLE Nursing.WardSection(
-- 	WardSectionID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	WardID INT,
-- 	WardSectionName VARCHAR(50) UNIQUE,
-- 	WardSectionDescription VARCHAR(50),
-- 	CreatedDate DATE NOT NULL DEFAULT GETDATE(),
-- 	UpdatedDate DATE NOT NUll DEFAULT GETDATE(),
-- 	FOREIGN KEY (WardID) REFERENCES Nursing.Ward(WardID) ON UPDATE CASCADE ON DELETE CASCADE
-- )
-- GO
-- CREATE TABLE Nursing.SectionBed(
-- 	SectionBedID INT PRIMARY KEY IDENTITY NOT NULL,
-- 	WardSectionID INT,
-- 	BedName VARCHAR(50) UNIQUE,
-- 	BedDescription VARCHAR(50),
-- 	FOREIGN KEY (WardSectionID) REFERENCES Nursing.WardSection(WardSectionID) ON UPDATE CASCADE ON DELETE CASCADE
-- )
-- GO
-- CREATE TABLE Nursing.BedAssignment(
-- 	BedAssignmentID INT PRIMARY KEY IDENTITY NOT NUll,
-- 	BedName VARCHAR(50),
-- 	AssignmentLeased BIT,
-- 	AssignmentDate DATE NOT NULL DEFAULT GETDATE(),
-- 	FOREIGN KEY (BedName) REFERENCES Nursing.SectionBed(BedName) ON UPDATE CASCADE ON DELETE CASCADE
-- )
-- GO

-- CREATE TABLE Nursing.ServicesRendered(
-- 	ServicesRenderedID INT PRIMARY KEY IDENTITY,
-- 	PatientID INT,
-- 	ServicesRenderedDate DATETIME NOT NULL,
-- 	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE CASCADE ON DELETE SET NULL
-- )
-- GO

-- CREATE TABLE Nursing.ServicesRenderedItems (
-- 	ServicesRenderedItemID INT PRIMARY KEY IDENTITY,
-- 	ServicesRenderedID INT,
-- 	ServicesRenderedItem INT,
-- 	FOREIGN KEY (ServicesRenderedItem) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE,
-- 	FOREIGN KEY (ServicesRenderedID) REFERENCES Nursing.ServicesRendered (ServicesRenderedID) ON UPDATE CASCADE ON DELETE CASCADE
-- )
-- GO