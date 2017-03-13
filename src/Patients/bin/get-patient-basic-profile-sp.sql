SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Samuel Adeshina
-- Create date: 22/09/2016
-- Description:	
-- =============================================
CREATE PROCEDURE Patients.GetPatientBasicProfile @PatientID INT = NULL
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	DECLARE @cols AS nvarchar(max),
        @query AS nvarchar(max),
		@queryFinal AS nvarchar(max)

	SELECT
		@cols = STUFF((SELECT DISTINCT
		',' + QUOTENAME(FieldTitle)
		FROM Patients.PatientRecordsFieldValue
		GROUP BY PatientID,
				FieldTitle,
				FieldValueID
		ORDER BY 1
		FOR xml PATH (''), TYPE)
		.value('.', 'NVARCHAR(MAX)'), 1, 1, '')

	SET @query = N'SELECT ' + @cols + N' from 
					(
					select FieldValue, FieldTitle, PatientID
					from Patients.PatientRecordsFieldValue 
				) x
				pivot 
				(
					max(FieldValue)
					for FieldTitle in (' + @cols + N')
				) p'

	--EXEC sp_executesql @query;

	SET @queryFinal = N'SELECT * FROM Patients.PatientType a INNER JOIN (SELECT * FROM Patients.Patient a INNER JOIN ('+ @query+ N')  b ON a.PatientID = b.Patient WHERE a.ProfileDeleted = 0) b ON a.PatientTypeID = b.PatientType'

	IF (@PatientID IS NOT NULL)
	BEGIN
		SET @queryFinal = N'SELECT * FROM Patients.PatientType a INNER JOIN (SELECT * FROM Patients.Patient a INNER JOIN ('+ @query+ N')  b ON a.PatientID = b.Patient WHERE a.ProfileDeleted = 0 AND a.PatientID = @PatientID) b ON a.PatientTypeID = b.PatientType'
		EXEC sp_executesql @queryFinal, N'@PatientID INT', @PatientID = @PatientID
	END
	ELSE
	BEGIN
		EXEC sp_executesql @queryFinal
	END
END
GO
