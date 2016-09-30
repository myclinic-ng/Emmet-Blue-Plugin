<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\PatientEvent;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DBQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

use EmmetBlue\Plugins\Permission\Permission as Permission;

/**
 * class PatientEvent.
 *
 * PatientEvent Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class PatientEvent
{
    public static function create(array $data)
    {
        $patient = $data["patient"];
        $eventdate = $data["eventDate"] ?? null;
        $eventTime = $data["eventTime"] ?? null;
        $eventActor = $data["eventActor"] ?? null;
        $eventLinkId = $data["eventLinkId"] ?? null;
        $eventLink = $data["eventLink"] ?? null;
        $eventText = $data["eventText"] ?? null;
        $eventIcon = $data["eventIcon"] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Patients.PatientEvents', [
                'PatientID'=>$patient,
                'EventDate'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventDate, "'"),
                'EventTime'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventTime, "'"),
                'EventActor'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventActor, "'"),
                'EventLinkId'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventLinkId, "'"),
                'EventLink'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventLink, "'"),
                'EventText'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventText, "'"),
                'EventIcon'=>(is_null($EventDate)) ? 'NULL' : QB::wrapString($EventIcon, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Patients',
                'PatientEvents',
                (string)(serialize($result))
            );
            
            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (patient event not registered), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function edit(int $resourceId, array $data)
    {
    }

    public static function view(int $resourceId)
    {
    }

    public static function delete(int $resourceId)
    {
    }
}