CREATE TRIGGER FinancialAccounts.GeneralJournalEntriesModified ON FinancialAccounts.GeneralJournalEntries AFTER UPDATE
AS
	BEGIN
		DECLARE @OldID AS INT, @JournalID AS INT, @totalAmount AS MONEY;
		SELECT @OldID = EntryID, @JournalID = GeneralJournalID FROM inserted;
		SELECT @totalAmount = SUM(EntryValue) FROM FinancialAccounts.GeneralJournalEntries WHERE GeneralJournalID = @JournalID AND EntryType='credit';

		IF EXISTS (
			SELECT * FROM FinancialAccounts.GeneralJournalEntries WHERE
			(SELECT SUM(EntryValue) FROM FinancialAccounts.GeneralJournalEntries WHERE EntryType='credit' AND GeneralJournalID = @JournalID)
			=
			(SELECT SUM(EntryValue) FROM FinancialAccounts.GeneralJournalEntries WHERE EntryType='debit' AND GeneralJournalID = @JournalID)
			AND GeneralJournalID = @JournalID
		)
			BEGIN
				UPDATE FinancialAccounts.GeneralJournalEntries SET DateModified = GETDATE() WHERE EntryID = @OldID;
				UPDATE FinancialAccounts.GeneralJournal SET GeneralJournalTotalAmount = @totalAmount WHERE GeneralJournalID = @JournalID
			END
		ELSE
			BEGIN
				ROLLBACK TRANSACTION
			END
	END;
GO

CREATE TRIGGER FinancialAccounts.GeneralJournalModified ON FinancialAccounts.GeneralJournal AFTER UPDATE
AS
	BEGIN
		DECLARE @colId AS INT;
		SELECT @colId = GeneralJournalID FROM inserted;
		UPDATE FinancialAccounts.GeneralJournal SET DateModified = GETDATE() WHERE GeneralJournalID = @colId;
	END;
GO

CREATE TRIGGER FinancialAccounts.GeneralJournalEntriesCreated ON FinancialAccounts.GeneralJournalEntries AFTER INSERT
AS
	BEGIN
		DECLARE @totalAmount AS MONEY, @JournalID AS INT;
		SELECT @JournalID = GeneralJournalID FROM inserted;
		SELECT @totalAmount = SUM(EntryValue) FROM FinancialAccounts.GeneralJournalEntries WHERE GeneralJournalID = @JournalID AND EntryType='credit';
		UPDATE FinancialAccounts.GeneralJournal SET GeneralJournalTotalAmount = @totalAmount WHERE GeneralJournalID = @JournalID
	END;
GO