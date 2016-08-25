CREATE SCHEMA Consultancy;
GO

CREATE TABLE Consultancy.Tags(
	TagID,
	TagName,
	TagDescription
)
GO

CREATE TABLE Consultancy.ConsultationSheet (
	ConsultationSheetID INTEGER PRIMARY KEY IDENTITY,
	ConsultantID INTEGER NOT NULL,
	Note VARCHAR(max) NOT NULL,
	Meta VARCHAR(max) NOT NULL,
	CreationDate DATETIME NOT NULL DEFAULT 'GETDATE()',
	LastModified DATETIME NOT NULL DEFAULT 'GETDATE()'
)
GO

CREATE TABLE Consultancy.ConsultationSheetTags (
	ConsultationSheetTagID INT PRIMARY KEY IDENTITY,
	SheetID INT,
	TagID INT
)
GO
