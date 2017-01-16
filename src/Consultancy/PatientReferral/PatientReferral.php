<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Consultancy\PatientReferral;

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
 * class PatientReferral.
 *
 * PatientReferral Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class PatientReferral
{
    /**
     * creates new PatientReferral
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $patient = $data['patient'] ?? null;
        $referredTo = $data['referredTo'] ?? null;
        $referrer = $data['referrer'] ?? null;
        $referralNote = $data['referralNote'] ?? null;

        try
        {
            $result = DBQueryFactory::insert('Consultancy.PatientReferrals', [
                'ReferedTo'=>$referredTo,
            ]);

            $id = $result["lastInsertId"];

            $query = "INSERT INTO Consultancy.PatientReferralInfo VALUES ($id, $patient, $referrer, '$referralNote')";
            DBConnectionFactory::getConnection()->exec($query);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Consultancy',
                'PatientReferrals',
                (string)serialize($result)
            );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_INSERT,
                'Consultancy',
                'PatientReferralInfo',
                ''
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (referral not completed), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    public static function view(int $resourceId, array $data)
    {
        if (!isset($data["archived"]) || ($data["archived"]) != 1){
            $data["archived"] = 0;
        }

        $selectBuilder  = "SELECT * FROM Consultancy.PatientReferrals a INNER JOIN Consultancy.PatientReferralInfo b ON a.ReferralID = b.ReferralID WHERE a.ReferedTo = $resourceId AND a.ReferralArchived = ".$data["archived"];
        try
        {
            $viewOperation = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Consultancy',
                'PatientReferrals',
                (string)$selectBuilder
            );

            foreach ($viewOperation as $key=>$value){
                $viewOperation[$key]["patientInfo"] = \EmmetBlue\Plugins\Patients\Patient\Patient::view((int) $value["Patient"])["_source"];
            }

            return $viewOperation;     
        } 
        catch (\PDOException $e) 
        {
            throw new SQLException(
                sprintf(
                    "Error procesing request"
                ),
                Constant::UNDEFINED
            );
            
        }
    }

    
    public static function archivePatientReferral(int $resourceId)
    {
        $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Consultancy.PatientReferrals");
            $updateBuilder->set(["ReferralArchived"=>1]);
            $updateBuilder->where("ReferralID = $resourceId");

            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$updateBuilder)
                );
            //logging
            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_UPDATE,
                'Consultancy',
                'PatientReferrals',
                (string)(serialize($result))
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

    
    public static function delete(int $resourceId)
    {
        $deleteBuilder = (new Builder("QueryBuilder", "Delete"))->getBuilder();

        try
        {
            $deleteBuilder
                ->from("Consultancy.PatientReferrals")
                ->where("ReferralID = $resourceId");
            
            $result = (
                    DBConnectionFactory::getConnection()
                    ->exec((string)$deleteBuilder)
                );

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_DELETE,
                'Consultancy',
                'PatientReferrals',
                (string)$deleteBuilder
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