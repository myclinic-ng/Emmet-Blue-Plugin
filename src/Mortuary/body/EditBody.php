<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Bardeson Lucky <flashup4all@gmail.com> <Ahead!!>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Mortuary\Body;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
use EmmetBlue\Core\Builder\QueryBuilder\DeleteQueryBuilder;
use EmmetBlue\Core\Factory\DatabaseConnectionFactory as DBConnectionFactory;
use EmmetBlue\Core\Factory\DatabaseQueryFactory as DatabaseQueryFactory;
use EmmetBlue\Core\Builder\QueryBuilder\QueryBuilder as QB;
use EmmetBlue\Core\Exception\SQLException;
use EmmetBlue\Core\Exception\UndefinedValueException;
use EmmetBlue\Core\Session\Session;
use EmmetBlue\Core\Logger\DatabaseLog;
use EmmetBlue\Core\Logger\ErrorLog;
use EmmetBlue\Core\Constant;

/**
 * class EditBody.
 *
 * EditBody Controller
 *
 * @author Bardeson Lucky <flashup4all@gmail.com>
 * @since v0.0.1 15/06/2016 14:20
 */
class EditBody
{
     /**
     * Modifies the content of a consultation note
     */
    public static function edit(int $resourceId, array $data)
    {
        //
        $body['DateOfDeath'] = QB::wrapString($data['DateOfDeath'], "'");
        $body['PlaceOfDeath'] = QB::wrapString($data['PlaceOfDeath'], "'");

        //body information
        $bodyInfo['BodyFullName'] = QB::wrapString($data['BodyFullName'], "'");
        $bodyInfo['BodyDateOfBirth'] = QB::wrapString($data['BodyDateOfBirth'], "'");
        $bodyInfo['BodyGender'] = QB::wrapString($data['BodyGender'], "'");
        $bodyInfo['BodyNextOfKinFullName'] = QB::wrapString($data['BodyNextOfKinFullName'], "'");
        $bodyInfo['BodyNextOfKinAddress'] = QB::wrapString($data['BodyNextOfKinAddress'], "'");
        $bodyInfo['BodyNextOfKinRelationshipType'] = QB::wrapString($data['BodyNextOfKinRelationshipType'], "'");
        $bodyInfo['BodyNextOfKinPhoneNumber'] = QB::wrapString($data['BodyNextOfKinPhoneNumber'], "'");

        //depositors info
        $depositorInfo['DepositorFullName'] = QB::wrapString($data['DepositorFullName'], "'");
        $depositorInfo['DepositorAddress'] = QB::wrapString($data['DepositorAddress'], "'");
        $depositorInfo['DepositorRelationshipType'] = QB::wrapString($data['DepositorRelationshipType'], "'");
        $depositorInfo['DepositorPhoneNumber'] = QB::wrapString($data['DepositorPhoneNumber'], "'");
         //return $depositorInfo;

       $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Mortuary.Body");
            $updateBuilder->set($body);
            $updateBuilder->where("BodyID = $resourceId");

            $bodyResult = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBuilder)
                );

            //return $bodyResult;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
        //update body Info
        $updateBodyInformation = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBodyInformation->table("Mortuary.BodyInformation");
            $updateBodyInformation->set($bodyInfo);
            $updateBodyInformation->where("BodyID = $resourceId");

            $bodyInformationResult = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateBodyInformation)
                );

            //return $bodyInformationResult;
        }
        catch (\PDOException $e)
        {
            throw new SQLException(sprintf(
                "Unable to process update, %s",
                $e->getMessage()
            ), Constant::UNDEFINED);
        }
        //update body depositors Info
        $updateDepositorInformation = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateDepositorInformation->table("Mortuary.DepositorDetails");
            $updateDepositorInformation->set($depositorInfo);
            $updateDepositorInformation->where("BodyID = $resourceId");

            $bodyDepositorInformationResult = (
                    DBConnectionFactory::getConnection()
                    ->query((string)$updateDepositorInformation)
                );

            return $bodyDepositorInformationResult;
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
    * update body status resource
    */
    public static function editBodyStatus($resourceId, $data){
        $status['BodyStatus'] = QB::wrapString($data['bodystatus'],"'");
         $updateBuilder = (new Builder("QueryBuilder", "Update"))->getBuilder();

        try
        {
            $updateBuilder->table("Mortuary.Body");
            $updateBuilder->set($status);
            $updateBuilder->where("BodyID = $resourceId");

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
}