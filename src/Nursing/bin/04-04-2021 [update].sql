CREATE TABLE Nursing.AdmissionTreatmentPlan (
	TreatmentPlanID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT,
	Drug VARCHAR(100),
	Dose VARCHAR(50),
	Route VARCHAR(50),
	HourlyInterval SMALLINT,
	NumberOfDays SMALLINT,
	StartDate DATETIME NOT NULL,
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	LoggedBy INT,
	Deleted SMALLINT DEFAULT 0,
	DateDeleted DATETIME NOT NULL DEFAULT GETDATE(),
	DeletedBy INT,
	Note VARCHAR(500),
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (LoggedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY (DeletedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE NO ACTION ON DELETE NO ACTION
)
GO

DROP TABLE IF EXISTS Nursing.AdmissionTreatmentChart
GO

CREATE TABLE Nursing.AdmissionTreatmentChart (
	TreatmentChartID INT PRIMARY KEY IDENTITY NOT NULL,
	PatientAdmissionID INT NOT NULL,
	Drug VARCHAR(100),
	Dose VARCHAR(50),
	Route VARCHAR(50),
	-- Time VARCHAR(20),
	Note VARCHAR(500),
	LoggedBy INT,
	Date DATETIME NOT NULL DEFAULT GETDATE(),
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	Deleted SMALLINT DEFAULT 0,
	FOREIGN KEY (PatientAdmissionID) REFERENCES [Consultancy].[PatientAdmission] (PatientAdmissionID),
	FOREIGN KEY (LoggedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO

ALTER TABLE Nursing.AdmissionTreatmentChart ADD TreatmentPlanID INT FOREIGN KEY REFERENCES Nursing.AdmissionTreatmentPlan(TreatmentPlanID) ON UPDATE NO ACTION ON DELETE SET NULL;
GO

CREATE TABLE Nursing.AdmissionTreatmentChartStatus (
	StatusID INT PRIMARY KEY IDENTITY NOT NULL,
	TreatmentChartID INT,
	Status BIT NOT NULL DEFAULT 1,
	Note VARCHAR(500),
	StaffID INT,
	AssociatedDate DATETIME NOT NULL DEFAULT GETDATE(),
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (TreatmentChartID) REFERENCES [Nursing].[AdmissionTreatmentChart] (TreatmentChartID),
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO