CREATE SCHEMA EmmetBlueCloud;
GO

CREATE TABLE EmmetBlueCloud.Provider (
	PKey SMALLINT,
	ProviderID VARCHAR(30) DEFAULT NULL,
	ProviderAlias VARCHAR(50) DEFAULT NULL,
	ProviderSecretToken VARCHAR(256) DEFAULT NULL
);

CREATE TABLE EmmetBlueCloud.LinkedProfiles (
	ProfileID VARCHAR(20),
	AccountID VARCHAR(15) NOT NULL,
	PatientID INT NOT NULL,
	DateLinked DATETIME NOT NULL DEFAULT GETDATE(),
	LinkedBy INT,
	UNIQUE(AccountID, PatientID),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID),
	FOREIGN KEY (LinkedBy) REFERENCES Staffs.Staff (StaffID)
);