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
		$physicianId = $data['physicianId'] ?? 'NULL';
		$tag = $data['tag'] ?? 'NULL';
		$dateOfDeath = $data['dateOfDeath'] ?? 'NULL';
		$placeOfDeath = $data['placeOfDeath'] ?? 'NULL';
		//body Info
		$bodyFirstName = $data['firstName'] ?? 'NULL';
		$bodyOtherName = $data['otherNames'] ?? 'NULL';
		$bodyDateOfBirth = $data['dateOfBirth'] ?? 'NULL';
		$bodyGender = $data['gender'] ?? 'NULL';
		//depositors data
		$depositorFirstName = $data['depositorFirstName'] ?? 'NULL';
		$depositorOtherName = $data['depositorOtherNames'] ?? 'NULL';
		$depositorAddress = $data['depositorAddress'] ?? 'NULL';
		$depositorRelationshipType = $data['depositorRelationshipType'] ?? 'NULL';
		$depositorPhoneNumber = $data['depositorPhoneNumber'] ?? 'NULL';
		//next of kin data
		$nextOfKinFirstName = $data['nextOfKinFirstName'] ?? 'NULL';
		$nextOfKinOtherName = $data['nextOfKinOtherNames'] ?? 'NULL';
		$nextOfKinAddress = $data['nextOfKinAddress'] ?? 'NULL';
		$nextOfKinRelationshipType = $data['nextOfKinRelationshipType'] ?? 'NULL';
		$nextOfKinPhoneNumber = $data['nextOfKinPhoneNumber'] ?? 'NULL';

		$packed = [
			'DeathPhysicianID'=>$physicianId,
			'BodyTag'=>($tag !== 'NULL') ? QB::wrapString($tag, "'") : $tag,
			'DateOfDeath'=>($dateOfDeath !== 'NULL') ? QB::wrapString($dateOfDeath, "'") : $dateOfDeath,
			'PlaceOfDeath'=>($placeOfDeath !== 'NULL') ? QB::wrapString($placeOfDeath, "'") : $placeOfDeath,
			
		];

		$bodyResult = DatabaseQueryFactory::insert('Mortuary.Body', $packed);
		$bodyId = $bodyResult['lastInsertId'];
		//body info query
		$packed = [
			'BodyID'=>$bodyId,
			'BodyFirstName'=>($bodyFirstName !== 'NULL') ? QB::wrapString($bodyFirstName, "'") : $bodyFirstName,
			'BodyOtherNames'=>($bodyOtherName !== 'NULL') ? QB::wrapString($bodyOtherName, "'") : $bodyLastName,
			'BodyDateOfBirth'=>($bodyDateOfBirth !== 'NULL') ? QB::wrapString($bodyDateOfBirth, "'") : $bodyDateOfBirth,
			'BodyGender'=>($bodyGender !== 'NULL') ? QB::wrapString($bodyGender, "'") : $bodyGender
		];

		$bodyInfoResult = DatabaseQueryFactory::insert('Mortuary.BodyInformation', $packed);
		//depositors query
		$packed = [
			'BodyID'=>$bodyId,
			'DepositorFirstName'=>($depositorFirstName !== 'NULL') ? QB::wrapString($depositorFirstName, "'") : $depositorFirstName,
			'DepositorOtherNames'=>($depositorOtherName !== 'NULL') ? QB::wrapString($depositorOtherName, "'") : $depositorOtherName,
			'DepositorAddress'=>($depositorAddress !== 'NULL') ? QB::wrapString($depositorAddress, "'") : $depositorAddress,
			'depositorRelationshipType'=>($depositorRelationshipType !== 'NULL') ? QB::wrapString($depositorRelationshipType, "'") : $depositorRelationshipType,
			'depositorPhoneNumber'=>($depositorPhoneNumber !== 'NULL') ? QB::wrapString($depositorPhoneNumber, "'") : $depositorPhoneNumber
		];

		$bodyDepositorResult = DatabaseQueryFactory::insert('Mortuary.DepositorDetails', $packed);
		//next of kin query
		$packed = [
			'BodyID'=>$bodyId,
			'NextOfKinFirstName'=>($nextOfKinFirstName !== 'NULL') ? QB::wrapString($nextOfKinFirstName, "'") : $nextOfKinFirstName,
			'NextOfKinOtherNames'=>($nextOfKinOtherName !== 'NULL') ? QB::wrapString($nextOfKinOtherName, "'") : $nextOfKinOtherName,
			'NextOfKinAddress'=>($nextOfKinAddress !== 'NULL') ? QB::wrapString($nextOfKinAddress, "'") : $nextOfKinAddress,
			'NextOfKinRelationshipType'=>($nextOfKinRelationshipType !== 'NULL') ? QB::wrapString($nextOfKinRelationshipType, "'") : $nextOfKinRelationshipType,
			'NextOfKinPhoneNumber'=>($nextOfKinPhoneNumber !== 'NULL') ? QB::wrapString($nextOfKinPhoneNumber, "'") : $nextOfKinPhoneNumber
		];

		$bodyNextOfKinResult = DatabaseQueryFactory::insert('Mortuary.NextOfKinDetails', $packed);
		return array_merge($bodyResult, $bodyInfoResult, $bodyDepositorResult, $bodyNextOfKinResult);
		
	}
}