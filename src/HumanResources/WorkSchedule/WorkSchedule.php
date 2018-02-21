<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\HumanResources\WorkSchedule;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class NewWorkSchedule.
 *
 * NewWorkSchedule Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 10/02/2018 13:52
 */
class WorkSchedule
{
	/**
	 * Determines if a login data is valid
	 *
	 * @param array $data
	 */
    public static function create(array $data)
    {
        $staff = $data['staffId'];
        $startDate = $data['startDate'];
        $startTime = $data["startTime"];
        $endTime = $data["endTime"];
        $daysCount = $data["daysCount"] ?? 1;

        $dates = [];

        for ($cntr = 0; $cntr < $daysCount; $cntr += 1){
            $date = new \DateTime($startDate);
            $date->modify("+$cntr day");
            $_start = clone $date;
            $_end = clone $date;
            $_start->setTime((int)$startTime[0], (int)$startTime[1]);
            $_end->setTime((int)$endTime[0], (int)$endTime[1]);

            $_dates = [$_start->format('Y-m-d H:i:s'),$_end->format('Y-m-d H:i:s')];

            $dates[] = "($staff, '$_dates[0]', '$_dates[1]')";
        }

        $query = "INSERT INTO Staffs.WorkSchedules (StaffID, StartDate, EndDate) VALUES ".implode(", ", $dates);

        try
        {
        	$result = DBConnectionFactory::getConnection()->query($query);
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Modifies the content of a department group record
     */
    public static function edit(int $resourceId, array $data)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Staffs.WorkSchedule");
            $updateBuilder->set($data);
            $updateBuilder->where("WorkScheduleID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * Returns department group data
     *
     * @param int $resourceId optional
     */
    public static function view(int $resourceId = 0, array $data = [])
    {
        $start = $data["start"];
        $end = $data["end"];
        $query = "SELECT a.*, c.PrimaryColor, c.SecondaryColor FROM Staffs.WorkSchedules a INNER JOIN Staffs.StaffRole b ON a.StaffID = b.StaffID LEFT OUTER JOIN Staffs.StaffRoleColorTags c ON b.RoleID = c.RoleID WHERE CONVERT(date, a.StartDate) BETWEEN CONVERT(date, '$start') AND CONVERT(date, '$end')";

        $result = DBConnectionFactory::getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $key=>$schedule){
            $staff = \EmmetBlue\Plugins\HumanResources\StaffProfile\StaffProfile::viewStaffFullName((int) $schedule["StaffID"]);
            $result[$key]["role"] = \EmmetBlue\Plugins\HumanResources\Staff\Staff::viewStaffRole((int) $schedule["StaffID"])["Name"];
            $result[$key]["name"] = $staff["StaffFullName"];
            $result[$key]["image"] = $staff["StaffPicture"];
        }

        return $result;
    }

    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Staffs.WorkSchedule")
                ->where("WorkScheduleID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process delete request, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }
}