SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		Samuel Adeshina
-- Create date: 22/09/2016
-- Description:	
-- =============================================
CREATE PROCEDURE Staffs.GetStaffBasicProfile 
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
		FROM Staffs.StaffRecordsFieldValue
		GROUP BY StaffID,
				FieldTitle,
				FieldValueID
		ORDER BY 1
		FOR xml PATH (''), TYPE)
		.value('.', 'NVARCHAR(MAX)'), 1, 1, '')

	SET @query = N'SELECT ' + @cols + N' from 
					(
					select FieldValue, FieldTitle, StaffID
					from Staffs.StaffRecordsFieldValue 
				) x
				pivot 
				(
					max(FieldValue)
					for FieldTitle in (' + @cols + N')
				) p'

	--EXEC sp_executesql @query;
	SET @queryRoleDept = N'SELECT j.StaffID, j.DepartmentName, k.RoleName FROM (SELECT a.StaffID, b.Name as DepartmentName FROM Staffs.StaffDepartment a INNER JOIN Staffs.Department b ON a.DepartmentID = b.DepartmentID ) j INNER JOIN (SELECT c.StaffID, d.Name as RoleName FROM Staffs.StaffRole c INNER JOIN Staffs.Role d ON c.RoleID = d.RoleID ) k ON j.StaffID = k.StaffID'
	SET @queryFinal = N'SELECT a.StaffID, a.StaffFullName, a.StaffPicture, a.StaffIdentificationDocument, a.LastModified, b.* FROM Staffs.StaffProfile a INNER JOIN ('+ @query+ N')  b ON a.StaffProfile = b.StaffProfile'
	SET @queryDept = N'SELECT h.*, g.DepartmentName, g.RoleName FROM ('+ @queryRoleDept+ N') g INNER JOIN ('+ @queryFinal+ N') h ON g.StaffID = h.StaffID'

	EXEC sp_executesql @queryDept
END
GO
