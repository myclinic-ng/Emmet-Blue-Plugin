CREATE TRIGGER Accounts.PreventBillingTypeItemsDuplicates
 ON Accounts.BillingTypeItems
 INSTEAD OF INSERT
AS
BEGIN
  SET NOCOUNT ON;

  IF NOT EXISTS (SELECT 1 FROM inserted AS i 
    INNER JOIN Accounts.BillingTypeItems AS t
    ON i.BillingType = t.BillingType
    AND i.BillingTypeItemName = t.BillingTypeItemName
  )
  BEGIN
    INSERT Accounts.BillingTypeItems(BillingType, BillingTypeItemName)
      SELECT BillingType, BillingTypeItemName FROM inserted;
  END
  ELSE
  BEGIN
    RAISERROR('Error: Duplicate items are not allowed', 16, 1);
  END
END
GO

CREATE TRIGGER Accounts.UpdatePatientAccountsDepositBalance 
ON Accounts.PatientDepositsAccountTransactions 
AFTER INSERT 
AS 
BEGIN
  DECLARE @amount AS MONEY, @account AS INT, @oldAmount AS MONEY;
  SELECT @amount = TransactionAmount, @account = AccountID FROM inserted;
  SELECT @oldAmount = AccountBalance FROM Accounts.PatientDepositsAccount WHERE AccountID = @account;
  IF (@amount + @oldAmount < 0)
  BEGIN
    ROLLBACK TRANSACTION;
    RAISERROR('Error: Balance in deposit account cannot be less than zero', 16, 1);
  END
  ELSE
  BEGIN
    UPDATE Accounts.PatientDepositsAccount SET AccountBalance = CAST(@amount + @oldAmount AS MONEY) WHERE AccountID = @account;
  END
END
GO