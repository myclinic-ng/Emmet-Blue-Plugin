SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Samuel Adeshina
-- Create date: 22/09/2016
-- Description:	
-- =============================================
CREATE PROCEDURE Accounts.GetPatientHmoProfile @patient nvarchar(10) = 'NULL'
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @cols AS nvarchar(max),
        @query AS nvarchar(max),
		@queryFinal AS nvarchar(max),
		@queryRoleDept AS nvarchar(max),
		@queryDept AS nvarchar(max)

	SELECT
		@cols = STUFF((SELECT DISTINCT
		',' + QUOTENAME(FieldTitle)
		FROM Accounts.PatientHmoFieldValues
		GROUP BY ProfileID,
				FieldTitle,
				FieldValueID
		ORDER BY 1
		FOR xml PATH (''), TYPE)
		.value('.', 'NVARCHAR(MAX)'), 1, 1, '')

	SET @query = N'SELECT ' + @cols + N' from 
					(
					select FieldValue, FieldTitle, ProfileID
					from Accounts.PatientHmoFieldValues 
				) x
				pivot 
				(
					max(FieldValue)
					for FieldTitle in (' + @cols + N')
				) p'

	IF (@patient = 'NULL')
		BEGIN
			SET @queryFinal = N'SELECT a.ProfileID, a.PatientIdentificationDocument, b.* FROM Accounts.PatientHmoProfile a INNER JOIN ('+ @query+ N')  b ON a.PatientID = b.PatientID'
		END
	ELSE
		BEGIN
			SET @queryFinal = N'SELECT a.ProfileID, a.PatientIdentificationDocument, b.* FROM Accounts.PatientHmoProfile a INNER JOIN ('+ @query+ N')  b ON a.PatientID = b.PatientID WHERE a.PatientID = '+@patient
		END

	EXEC sp_executesql @queryFinal
END
GO
