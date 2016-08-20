CREATE SCHEMA Consultancy;
GO
CREATE TABLE Consultancy.ConsultationSheet (
	ConsultationSheetID INTEGER PRIMARY KEY IDENTITY,
	ConsultantID INTEGER NOT NULL,
	Note VARCHAR(max) NOT NULL,
	Meta VARCHAR(max) NOT NULL,
)
GO
