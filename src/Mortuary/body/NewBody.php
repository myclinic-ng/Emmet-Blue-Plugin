<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\Mortuary\Body;

use EmmetBlue\Core\Builder\BuilderFactory as Builder;
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
 * class NewBody.
 *
 * NewBody Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class NewBody
{
	public static function default(array $data)
	{
		$physicianId = 3;//$data['physicianId'] ?? 'NULL';
		$tags = $data['tag'] ?? 'NULL';
		$dateOfDeath = $data['dateOfDeath'] ?? 'NULL';
		$placeOfDeath = $data['placeOfDeath'] ?? 'NULL';
		//body Info
		$bodyFirstName = $data['firstName'] ?? 'NULL';
		$bodyOtherName = $data['otherNames'] ?? 'NULL';
		$bodyFullName = $bodyFirstName." ".$bodyOtherName;
		$bodyDateOfBirth = $data['dateOfBirth'] ?? 'NULL';
		$bodyGender = $data['gender'] ?? 'NULL';
		//depositors data
		$depositorFirstName = $data['depositorFirstName'] ?? 'NULL';
		$depositorOtherName = $data['depositorOtherNames'] ?? 'NULL';
		$depositorFullName = $depositorFirstName." ".$depositorOtherName;
		$depositorAddress = $data['depositorAddress'] ?? 'NULL';
		$depositorRelationshipType = $data['depositorRelationshipType'] ?? 'NULL';
		$depositorPhoneNumber = $data['depositorPhoneNumber'] ?? 'NULL';
		//next of kin data
		$nextOfKinFirstName = $data['nextOfKinFirstName'] ?? 'NULL';
		$nextOfKinOtherName = $data['nextOfKinOtherNames'] ?? 'NULL';
		$nextOfKinFullName = $nextOfKinFirstName." ".$nextOfKinOtherName;
		$nextOfKinAddress = $data['nextOfKinAddress'] ?? 'NULL';
		$nextOfKinRelationshipType = $data['nextOfKinRelationshipType'] ?? 'NULL';
		$nextOfKinPhoneNumber = $data['nextOfKinPhoneNumber'] ?? 'NULL';

		$packed = [
			'DeathPhysicianID'=>$physicianId,
			'DateOfDeath'=>($dateOfDeath !== 'NULL') ? QB::wrapString($dateOfDeath, "'") : $dateOfDeath,
			'PlaceOfDeath'=>($placeOfDeath !== 'NULL') ? QB::wrapString($placeOfDeath, "'") : $placeOfDeath,
			'BodyStatus'=>1
		];

		$bodyResult = DatabaseQueryFactory::insert('Mortuary.Body', $packed);
		$bodyId = $bodyResult['lastInsertId'];
		//body info query
		$packed = [
			'BodyID'=>$bodyId,
			'BodyFullName'=>($bodyFullName !== 'NULL') ? QB::wrapString($bodyFullName, "'") : $bodyFullName,
			'BodyDateOfBirth'=>($bodyDateOfBirth !== 'NULL') ? QB::wrapString($bodyDateOfBirth, "'") : $bodyDateOfBirth,
			'BodyGender'=>($bodyGender !== 'NULL') ? QB::wrapString($bodyGender, "'") : $bodyGender,
			'BodyNextOfKinFullName'=>($nextOfKinFullName !== 'NULL') ? QB::wrapString($nextOfKinFullName, "'") : $nextOfKinFullName,
			'BodyNextOfKinAddress'=>($nextOfKinAddress !== 'NULL') ? QB::wrapString($nextOfKinAddress, "'") : $nextOfKinAddress,
			'BodyNextOfKinRelationshipType'=>($nextOfKinRelationshipType !== 'NULL') ? QB::wrapString($nextOfKinRelationshipType, "'") : $nextOfKinRelationshipType,
			'BodyNextOfKinPhoneNumber'=>($nextOfKinPhoneNumber !== 'NULL') ? QB::wrapString($nextOfKinPhoneNumber, "'") : $nextOfKinPhoneNumber
		];

		$bodyInfoResult = DatabaseQueryFactory::insert('Mortuary.BodyInformation', $packed);
		//depositors query
		$packed = [
			'BodyID'=>$bodyId,
			'DepositorFullName'=>($depositorFullName !== 'NULL') ? QB::wrapString($depositorFullName, "'") : $depositorFullName,
			'DepositorAddress'=>($depositorAddress !== 'NULL') ? QB::wrapString($depositorAddress, "'") : $depositorAddress,
			'depositorRelationshipType'=>($depositorRelationshipType !== 'NULL') ? QB::wrapString($depositorRelationshipType, "'") : $depositorRelationshipType,
			'depositorPhoneNumber'=>($depositorPhoneNumber !== 'NULL') ? QB::wrapString($depositorPhoneNumber, "'") : $depositorPhoneNumber
		];

		$bodyDepositorResult = DatabaseQueryFactory::insert('Mortuary.DepositorDetails', $packed);
		//body tags
		 foreach ($tags as $datum){
            $bodyTags[] = "($id, ".QB::wrapString($datum, "'").")";
        }

        $bodyTagQuery = "INSERT INTO Mortuary.BodyTag (BodyID, TagName) VALUES ".implode(", ", $bodyTags);

        DatabaseLog::log(
            Session::get('USER_ID'),
            Constant::EVENT_SELECT,
            'Mortuary',
            'BodyTag',
            (string)serialize($query)
        );
                       
        $bodyTagResult = (
            DBConnectionFactory::getConnection()
            ->exec($bodyTagQuery)
        );
           
		return array_merge($bodyResult, $bodyInfoResult, $bodyDepositorResult, $bodyTagResult);
		
	}
}