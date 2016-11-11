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