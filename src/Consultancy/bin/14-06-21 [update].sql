CREATE TABLE Consultancy.DiagnosisCodes (
	DiagnosisCodeID INT PRIMARY KEY IDENTITY NOT NULL,
	DiagnosisCodeTitle VARCHAR(100),
	DiagnosisCodeDescription VARCHAR(500),
	CreatedBy INT,
	DateCreated DATETIME NOT NULL DEFAULT GETDATE(),
	DateModified DATETIME DEFAULT NULL,

	FOREIGN KEY (CreatedBy) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)

CREATE TABLE Consultancy.DiagnosisCodeData (
	DataID INT PRIMARY KEY IDENTITY NOT NULL,
	DiagnosisCodeID INT,
	Code VARCHAR(50) NOT NULL,
	Title VARCHAR(100) NOT NULL,

	UNIQUE(DiagnosisCodeID, Code, Title),
	FOREIGN KEY (DiagnosisCodeID) REFERENCES Consultancy.DiagnosisCodes (DiagnosisCodeID) ON UPDATE CASCADE ON DELETE CASCADE
)