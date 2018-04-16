Use EmmetBlue;
GO
CREATE SCHEMA Biometrics;
GO

CREATE TABLE Biometrics.Humanity (
	id INT PRIMARY KEY IDENTITY,
	name VARCHAR(20) NOT NULL,
	category VARCHAR(50) NOT NULL,
	fingerprint VARBINARY(MAX) NOT NULL,
	dateCreated DATETIME DEFAULT GETDATE(),
	UNIQUE(name, category)
)