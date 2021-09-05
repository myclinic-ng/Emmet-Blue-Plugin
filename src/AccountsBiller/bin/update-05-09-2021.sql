CREATE TABLE Accounts.PatientTypeDepositsAccount (
	AccountID INT PRIMARY KEY NOT NULL IDENTITY(1000, 1),
	PatientTypeID INT NOT NULL,
	AccountBalance MONEY NOT NULL DEFAULT 0.00,
	CreatedBy INT,
	DateCreated DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (PatientTypeID) REFERENCES Patients.PatientType(PatientTypeID),
	FOREIGN KEY (CreatedBy) REFERENCES [Staffs].[Staff] (StaffID),
	UNIQUE(PatientTypeID)
)

CREATE TABLE Accounts.PatientTypeDepositsAccountTransactions (
	TransactionID INT PRIMARY KEY NOT NULL IDENTITY(1000, 1),
	AccountID INT NOT NULL,
	StaffID INT,
	TransactionAmount MONEY,
	TransactionComment VARCHAR(500),
	TransactionDate DATETIME DEFAULT GETDATE(),
	FOREIGN KEY (AccountID) REFERENCES Accounts.PatientTypeDepositsAccount (AccountID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (StaffID) REFERENCES [Staffs].[Staff] (StaffID)
)

CREATE TRIGGER Accounts.UpdatePatientTypeAccountsDepositBalance 
ON Accounts.PatientTypeDepositsAccountTransactions 
AFTER INSERT 
AS 
BEGIN
  DECLARE @amount AS MONEY, @account AS INT, @oldAmount AS MONEY;
  SELECT @amount = TransactionAmount, @account = AccountID FROM inserted;
  SELECT @oldAmount = AccountBalance FROM Accounts.PatientTypeDepositsAccount WHERE AccountID = @account;
  IF (@amount + @oldAmount < 0)
  BEGIN
    ROLLBACK TRANSACTION;
    RAISERROR('Error: Balance in deposit account cannot be less than zero', 16, 1);
  END
  ELSE
  BEGIN
    UPDATE Accounts.PatientTypeDepositsAccount SET AccountBalance = CAST(@amount + @oldAmount AS MONEY) WHERE AccountID = @account;
  END
END
GO