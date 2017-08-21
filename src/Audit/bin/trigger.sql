CREATE TRIGGER FinancialAuditing.updateUnlockLogStatus ON FinancialAuditing.UnlockLogStatus AFTER UPDATE
AS
	UPDATE FinancialAuditing.UnlockLogStatus SET LastModified = GETDATE()
	FROM Inserted i
	WHERE FinancialAuditing.UnlockLogStatus.LogID = i.LogID