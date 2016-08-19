CREATE SCHEMA Consultant;
GO
CREATE TABLE Consultant.Medication 
	MedicationID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	PatientID VARCHAR(50) NOT NULL,
	ConsultantID VARCHAR(50) NOT NULL,
	Medicat ion VARCHAR(100) NOT NULL,
	DateOfPrescription DATE NOT NULL,
	TimeOfPresription TIME NOT NULL,
)
GO

CREATE TABLE Consultant.Presription (
	PrescriptionID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	MedicationID VARCHAR(50) NOT NULL,
	PatientID VARCHAR(50) NOT NULL,
	ConsultantID VARCHAR(50) NOT NULL,
	Prescription VARCHAR(100) NOT NULL,
	DateOfPrescription DATE NOT NULL,
	TimeOfPresription TIME NOT NULL,
)

GO
CREATE TABLE Consultant.DiagnosticReport (

)