CREATE SCHEMA Consultancy;
GO

CREATE TABLE Consultancy.Allergies(
	AllergyID INT PRIMARY KEY IDENTITY NOT NULL,
	AllergyName VARCHAR(50) UNIQUE,
	AllergyDescription VARCHAR(500)
)
GO

CREATE TABLE Consultancy.AllergyTriggers(
	TriggerID INT PRIMARY KEY IDENTITY NOT NULL,
	Allergy INT,
	TriggerName VARCHAR(50) UNIQUE,
	TriggerDescription VARCHAR(500),
	FOREIGN KEY (Allergy) REFERENCES Consultancy.Allergies (AllergyID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

CREATE TABLE Consultancy.AllergySeverity(
	ID INT PRIMARY KEY IDENTITY NOT NULL,
	Severity VARCHAR(20) NOT NULL
)
GO

CREATE TABLE Consultancy.ExaminationTypes(
	ExamTypeID INT PRIMARY KEY IDENTITY NOT NULL,
	ExamTypeTitle VARCHAR(50) NOT NULL UNIQUE,
	ExamTypeDescription VARCHAR(500)
)
GO

CREATE TABLE Consultancy.ExaminationTypeOptions(
	OptionID INT PRIMARY KEY IDENTITY NOT NULL,
	ExamTypeID INT,
	OptionTitle VARCHAR(100) NOT NULL,
	FOREIGN KEY (ExamTypeID) REFERENCES Consultancy.ExaminationTypes (ExamTypeID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(ExamTypeID, OptionTitle)
)
GO

CREATE TABLE Consultancy.MedicalImaging(
	MedicalImagingID INT PRIMARY KEY IDENTITY NOT NULL,
	MedicalImagingName VARCHAR(100),
	MedicalImagingDescription VARCHAR(500)
)
GO

CREATE TABLE Consultancy.SavedDiagnosis(
	SavedDiagnosisID INT PRIMARY KEY IDENTITY,
	Patient INT NOT NULL,
	Consultant INT NOT NULL,
	Diagnosis VARCHAR(MAX) NOT NULL,
	DateModified DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (Patient) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (Consultant) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE CASCADE,
	UNIQUE(Patient, Consultant)
)
GO

CREATE TABLE Consultancy.PatientQueue (
	QueueID INT PRIMARY KEY IDENTITY,
	Patient INT NOT NULL,
	Consultant INT NOT NULL,
	QueueDate DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (Patient) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (Consultant) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO


CREATE TABLE Consultancy.PatientAdmission (
	PatientAdmissionID INT PRIMARY KEY IDENTITY(1000, 1) NOT NULL,
	Patient INT,
	Ward INT,
	Section INT,
	Consultant INT,
	Diagnosis INT,
	AdmissionDate DATETIME NOT NULL DEFAULT GETDATE(),
	ReceivedInWard BIT DEFAULT 0,
	DischargeStatus INT DEFAULT 0,
	FOREIGN KEY (Patient) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (Consultant) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (Diagnosis) REFERENCES Patients.PatientDiagnosis (DiagnosisID) ON UPDATE NO ACTION ON DELETE NO ACTION
)
GO

CREATE TABLE Consultancy.ConsultationSheet (
	ConsultationSheetID INT PRIMARY KEY IDENTITY,
	PatientAdmissionID INT,
	Note VARCHAR(MAX),
	Consultant INT,
	DateTaken DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientAdmissionID) REFERENCES Nursing.WardAdmission (WardAdmissionID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (Consultant) REFERENCES [Staffs].[Staff] (StaffID)
)
GO

CREATE TABLE Consultancy.PatientDischargeInformation (
	DischargeStatusID INT PRIMARY KEY IDENTITY,
	PatientAdmissionID INT,
	DischargedBy INT,
	DischargeNote VARCHAR(1000),
	DischargeDate DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientAdmissionID) REFERENCES Consultancy.PatientAdmission(PatientAdmissionID) ON UPDATE CASCADE ON DELETE SET NULL,
	FOREIGN KEY (DischargedBy) REFERENCES [Staffs].[Staff] (StaffID)
)	
GO

CREATE TABLE Consultancy.PatientReferrals (
	ReferralID INT PRIMARY KEY IDENTITY,
	ReferedTo INT NOT NULL,
	DateReferred DATETIME NOT NULL DEFAULT GETDATE(),
	ReferralArchived INT NOT NULL DEFAULT 0,
	FOREIGN KEY (ReferedTo) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE
)
GO

CREATE TABLE Consultancy.PatientReferralInfo (
	ReferralInfoID INT PRIMARY KEY IDENTITY,
	ReferralID INT,
	Patient INT NOT NULL,
	Referrer INT NOT NULL,
	ReferralNote VARCHAR(500),
	FOREIGN KEY (Patient) REFERENCES Patients.Patient(PatientID) ON UPDATE CASCADE,
	FOREIGN KEY (Referrer) REFERENCES [Staffs].[Staff] (StaffID),
	FOREIGN KEY (ReferralID) REFERENCES Consultancy.PatientReferrals (ReferralID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO