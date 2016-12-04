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