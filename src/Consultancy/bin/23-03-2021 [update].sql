ALTER TABLE Consultancy.PatientQueue ADD RemovedFromQueue BIT DEFAULT 0;
GO
ALTER TABLE Consultancy.PatientQueue ADD DateRemovedFromQueue DATETIME
GO