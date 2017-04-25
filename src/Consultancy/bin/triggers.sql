CREATE TRIGGER Consultancy.PreventDuplicateAdmission ON Consultancy.PatientAdmission AFTER INSERT
AS
	BEGIN
		DECLARE @PatientID AS INT, @currentAdmissions AS INT;
		SELECT @PatientID = Patient FROM inserted;
		SELECT @currentAdmissions = COUNT(*) FROM Consultancy.PatientAdmission WHERE Patient = @PatientID AND DischargeStatus IN (0, -1)
		IF (@currentAdmissions > 1)
			BEGIN
				ROLLBACK TRANSACTION
			END
	END;
GO

CREATE TRIGGER Consultancy.UpdateAdmissionOnDischarge ON Consultancy.PatientDischargeInformation AFTER INSERT
AS
	BEGIN
		DECLARE @admissionId AS INT;
		SELECT @admissionId = PatientAdmissionID FROM inserted;
		UPDATE Consultancy.PatientAdmission SET DischargeStatus = -1 WHERE PatientAdmissionID = @admissionId
	END
GO