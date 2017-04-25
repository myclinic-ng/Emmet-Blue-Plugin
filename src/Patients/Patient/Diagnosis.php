<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Patients\Patient;

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
 * class PatientDiagnosis.
 *
 * PatientDiagnosis Controller
 *
 * @author Samuel Adeshina <Samueladeshina73@gmail.com>
 * @since v0.0.1 26/08/2016 12:33
 */
class Diagnosis
{
    /**
     * view patients UUID
     */
    public static function view(int $patientId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Patients.PatientDiagnosis');
        $selectBuilder->where('PatientID ='.$patientId);
        try
        {
            $viewPatients = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Patients',
                'PatientDiagnosis',
                (string)serialize($selectBuilder)
            );

            return $viewPatients;    
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error processing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    public static function new(int $patientId, array $diagnosis){
        $diagnosisArray = [];
        if (count($diagnosis) >= 1 && is_array($diagnosis[0])){
            foreach ($diagnosis as $value){
                $diagnosisDate = isset($value["date"]) ? QB::wrapString($value["date"], "'") : 'NULL';
                $codeNumber = isset($value["codeNumber"]) ? QB::wrapString($value["codeNumber"], "'") : 'NULL';
                $diagnosisType = isset($value["diagnosisType"]) ? QB::wrapString($value["diagnosisType"], "'") : "'diagnosis'";
                $diagnosis = isset($value["diagnosis"]) ? QB::wrapString($value["diagnosis"], "'") : 'NULL';

                $diagnosisArray[] = "($patientId, $diagnosisDate, $codeNumber, $diagnosisType, $diagnosis)";
            }
        }
        else {
            $diagnosisDate = isset($diagnosis["diagnosisDate"]) ? QB::wrapString($diagnosis["diagnosisDate"], "'") : 'NULL';
            $codeNumber = isset($diagnosis["codeNumber"]) ? QB::wrapString($diagnosis["codeNumber"], "'") : 'NULL';
            $diagnosisType = isset($diagnosis["diagnosisType"]) ? QB::wrapString($diagnosis["diagnosisType"], "'") : "'diagnosis'";
            $diagnosis = isset($diagnosis["diagnosis"]) ? QB::wrapString($diagnosis["diagnosis"], "'") : 'NULL';

            $diagnosisArray[] = "($patientId, $diagnosisDate, $codeNumber, $diagnosisType, $diagnosis)";
        }

        $query = "INSERT INTO Patients.PatientDiagnosis VALUES ".implode(", ", $diagnosisArray);
        
        $queryResult = (
            DBConnectionFactory::getConnection()
            ->exec($query)
        );

        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_INSERT,
            'Patients',
            'PatientDiagnosis',
            $query
        );

        return $queryResult;
    }
}