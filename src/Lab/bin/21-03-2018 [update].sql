USE EmmetBlue;

CREATE TABLE Lab.InvestigationTypeColumns (
	ColumnID INT PRIMARY KEY IDENTITY,
	InvestigationType INT,
	ColumnName VARCHAR(50) NOT NULL,

	FOREIGN KEY (InvestigationType) REFERENCES Lab.InvestigationTypes (InvestigationTypeID) ON UPDATE CASCADE ON DELETE CASCADE
)