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
use EmmetBlue\Core\Factory\ElasticSearchClientFactory as ESClientFactory;
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
        $eventDate = $data["eventDate"] ?? null;
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
                'EventDate'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventDate, "'"),
                'EventTime'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventTime, "'"),
                'EventActor'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventActor, "'"),
                'EventLinkId'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventLinkId, "'"),
                'EventLink'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventLink, "'"),
                'EventText'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventText, "'"),
                'EventIcon'=>(is_null($eventDate)) ? 'NULL' : QB::wrapString((string)$eventIcon, "'")
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
        $columnArray = ["EventID", "PatientID", "EventDate", "EventTime", "EventActor", "EventLinkID", "EventLink", "EventText", "EventIcon"];
        $columns = implode(", ", array_map(function($col){
            return "$col AS ".strtolower($col);
        }, $columnArray));

        $query = "SELECT $columns FROM Patients.PatientEvents WHERE PatientID = $resourceId ORDER BY EventDate DESC";
        $result = DBConnectionFactory::getConnection()->query($query)->fetchall(\PDO::FETCH_ASSOC);

        return $result;
    }

    public static function delete(int $resourceId)
    {
    }
}