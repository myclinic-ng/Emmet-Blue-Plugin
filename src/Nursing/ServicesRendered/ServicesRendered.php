<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Nursing\ServicesRendered;

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
 * class ServicesRendered.
 *
 * ServicesRendered Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 19/08/2016 13:35
 */
class ServicesRendered
{
    /**
     * creates new ServicesRendered
     *
     * @param array $data
     */
    public static function create(array $data)
    {
        $admissionId = $data['admissionId'] ?? 'NULL';
        $billingTypeItem = $data['item'] ?? 'NULL';
        $billingTypeItemName = $data["name"] ?? null;
        $billingTypeItemQuantity = $data["quantity"] ?? 'null';
        $nurse = $data["nurse"] ?? 'null';
        $doctorInCharge = $data["doctor"] ?? 'null';

        try
        {
            $result = DBQueryFactory::insert('Nursing.ServicesRendered', [
                'PatientAdmissionID'=>$admissionId,
                'BillingTypeItem'=>$billingTypeItem,
                'BillingTypeItemName'=>QB::wrapString($billingTypeItemName, "'"),
                'BillingTypeItemQuantity'=>$billingTypeItemQuantity,
                'Nurse'=>$nurse,
                'DoctorInCharge'=>$doctorInCharge
            ]);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
                (string)serialize($result)
            );

            return $result;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process request (service not saved), %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
    }

    /**
     * view allergies
     */
    public static function view(int $resourceId)
    {
        $selectBuilder = (new Builder('QueryBuilder','Select'))->getBuilder();
        $selectBuilder
            ->columns('*')
            ->from('Nursing.ServicesRendered')
            ->where('PatientAdmissionID = '.$resourceId);

        try
        {
            $result = (DBConnectionFactory::getConnection()->query((string)$selectBuilder))->fetchAll(\PDO::FETCH_ASSOC);

            DatabaseLog::log(
                Session::get('USER_ID'),
                Constant::EVENT_SELECT,
                'Nursing',
                'ServicesRendered',
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

    
    public static function editServicesRendered(int $resourceId, array $data)
    {

    }    
}