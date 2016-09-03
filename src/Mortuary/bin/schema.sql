CREATE SCHEMA Mortuary;
GO

CREATE TABLE Mortuary.Body (
	BodyID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	DeathPhysicianID INTEGER,
	BodyTag VARCHAR(50) NOT NULL,
	DateOfDeath DATE NOT NULL,
	PlaceOfDeath VARCHAR(100) NOT NULL
)
GO

CREATE TABLE Mortuary.BodyInformation (
	BodyInformationID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BodyID INTEGER,
	BodyFirstName VARCHAR(20) NOT NULL,
	BodyOtherNames VARCHAR(20),
	BodyDateOfBirth DATE,
	BodyGender VARCHAR(10) NOT NULL,
	BodyNextOfKinFullName VARCHAR(50),
	BodyNextOfKinAddress VARCHAR(100),
	BodyNextOfKinPhoneNumber VARCHAR(15)
	FOREIGN KEY (BodyID) REFERENCES Mortuary.Body(BodyID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO
CREATE TABLE Mortuary.DepositorDetails(
	DepositorDetailsID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BodyID INTEGER,
	DepositorFirstName VARCHAR(20),
	DepositorOtherNames VARCHAR(20),
	DepositorAddress VARCHAR (max),
	DepositorRelationshipType VARCHAR(20),
	DepositorPhoneNumber VARCHAR(20),
	FOREIGN KEY (BodyID) REFERENCES Mortuary.Body(BodyID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO

--DEPRECATED: Remove this
CREATE TABLE Mortuary.NextOfKinDetails(
	NextOfKinID INTEGER PRIMARY KEY IDENTITY NOT NULL,
	BodyID INTEGER,
	NextOfKinFirstName VARCHAR(20),
	NextOfKinOtherNames VARCHAR(20),
	NextOfKinAddress VARCHAR (max),
	NextOfKinRelationshipType VARCHAR(20),
	NextOfKinPhoneNumber VARCHAR(20),
	FOREIGN KEY (BodyID) REFERENCES Mortuary.Body(BodyID) ON UPDATE CASCADE ON DELETE CASCADE
)
GO
-- /DEPRECATED