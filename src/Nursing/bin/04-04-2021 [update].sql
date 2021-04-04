CREATE TABLE Nursing.AdmissionTreatmentChartStatus (
	StatusID INT PRIMARY KEY IDENTITY NOT NULL,
	TreatmentChartID INT,
	Note VARCHAR(500),
	StaffID INT,
	AssociatedDate DATETIME NOT NULL DEFAULT GETDATE(),
	DateLogged DATETIME NOT NULL DEFAULT GETDATE(),
	FOREIGN KEY (TreatmentChartID) REFERENCES [Nursing].[AdmissionTreatmentChart] (TreatmentChartID),
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID) ON UPDATE CASCADE ON DELETE SET NULL
)
GO