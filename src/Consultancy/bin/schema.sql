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