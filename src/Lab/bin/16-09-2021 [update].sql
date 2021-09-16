ALTER TABLE Lab.Labs ALTER COLUMN LabName VARCHAR(250)
GO

CREATE TABLE Lab.LinkedExternalLab (
	LinkedExternalLabID INT PRIMARY KEY IDENTITY,
	LabID INT,
	ExternalBusinessID INT,
	ExternalLabID INT,
	DateCreated DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (LabID) REFERENCES Lab.Labs (LabID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

ALTER TABLE Lab.LabRequests ADD LabID INT FOREIGN KEY REFERENCES Lab.Labs (LabID) ON UPDATE CASCADE ON DELETE SET NULL
GO