CREATE SCHEMA FinancialAuditing;
GO

CREATE TABLE FinancialAuditing.SalesLog (
	SalesLogID INT PRIMARY KEY IDENTITY,
	Department INT,
	PatientID INT,
	StaffID INT,
	PaymentRequestNumber VARCHAR(20),
	Action VARCHAR(30),
	SalesDate DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (Department) REFERENCES Staffs.Department (DepartmentID),
	FOREIGN KEY (PatientID) REFERENCES Patients.Patient (PatientID),
	FOREIGN KEY (StaffID) REFERENCES Staffs.Staff (StaffID),
	FOREIGN KEY (PaymentRequestNumber) REFERENCES Accounts.PaymentRequest(PaymentRequestUUID)
)

CREATE TABLE FinancialAuditing.UnlockLogStatus (
	StatusID INT PRIMARY KEY IDENTITY,
	LogID INT UNIQUE,
	Status INT NOT NULL,
	StatusNote VARCHAR(200),
	StaffID INT,
	FOREIGN KEY (LogID) REFERENCES Patients.PatientProfileUnlockLog (LogID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (StaffID) REFERENCES Staffs.Staff (StaffID),
)