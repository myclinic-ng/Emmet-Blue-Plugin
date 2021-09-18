CREATE TABLE Patients.LinkedExternalPatients (
	LinkedExternalPatientID INT PRIMARY KEY IDENTITY NOT NULL,
	LocalPatientID INT NOT NULL,
	ExternalPatientID INT NOT NULL,
	ExternalBusinessID INT NOT NULL,
	DateLinked DATETIME DEFAULT GETDATE(),
	UNIQUE (ExternalPatientID, ExternalBusinessID),

	FOREIGN KEY (LocalPatientID) REFERENCES Patients.Patient (PatientID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO