CREATE TABLE Accounts.PatientTypeCategoriesBillingTypeItemLink (
	LinkID INT PRIMARY KEY IDENTITY,
	PatientTypeCategoryID INT,
	BillingTypeItemID INT,
	FOREIGN KEY (PatientTypeCategoryID) REFERENCES Patients.PatientTypeCategories (CategoryID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (BillingTypeItemID) REFERENCES Accounts.BillingTypeItems (BillingTypeItemID) ON UPDATE CASCADE ON DELETE CASCADE
);