CREATE TABLE EmmetBlueCloud.BusinessInfo (
	BusinessInfoID INT NOT NULL PRIMARY KEY IDENTITY,
	BusinessID INT NOT NULL,
	BusinessName VARCHAR(100),
	BusinessLogo VARCHAR(6555)
)
GO

CREATE TABLE EmmetBlueCloud.BusinessLinkAuth (
	AuthID INT NOT NULL PRIMARY KEY IDENTITY,
	ExternalBusinessID INT UNIQUE,
	Token VARCHAR(500),
	UserId INT,
	EndpointUrl VARCHAR(100),
	DateCreated DATETIME DEFAULT GETDATE()
)
GO