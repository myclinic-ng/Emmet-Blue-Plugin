<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\PharmacyRequest;

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
 * class PharmacyRequest.
 *
 * PharmacyRequest Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PharmacyRequest
{
    /**
     * creates new PharmacyRequest
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patientId = $data['patientId'] ?? 'NULL';
        $request = $data['request'] ?? null;
        $requestDate = $data['requestDate'] ?? 'NULL';
        $staff = $data['staff'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Nursing.PharmacyRequests', [
                'PatientID'=>$patientId,
                'Request'=>QB::wrapString($request, "'"),
                'RequestDate'=>QB::wrapString($requestDate, "'"),
                'Staff'=>QB::wrapString($staff, "'")
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'PharmacyRequests',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (pharmacy request created), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId = 0)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.PharmacyRequests');
        if ($resourceId != 0){
           $selectBuilder->where('PatientID = '.$resourceId);
        }
        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $key=>$value){
                $id = $value["PatientID"];
                $patient = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $id);

                $result[$key]["patientInfo"] = $patient["_source"];
            }

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'PharmacyRequests',
                (string)$selectBuilder
            );

            return $result;
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request %s",
                    $e->getMessage()
                ),
                Constant::UNDEFINED
            );
            
        }
    }
}