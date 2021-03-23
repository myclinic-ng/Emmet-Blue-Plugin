ALTER TABLE Consultancy.PatientQueue ADD RemovedFromQueue BIT DEFAULT 0, CONSTRAINT uk_patient_consultant UNIQUE(Patient);
GO
ALTER TABLE Consultancy.PatientQueue ADD DateRemovedFromQueue DATETIME
GO