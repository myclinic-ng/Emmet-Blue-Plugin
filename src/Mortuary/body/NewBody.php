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

		// body info
		$bodyFirstName = $data['firstName'] ?? 'NULL';
		$bodyOtherNames = $data['otherNames'] ?? 'NULL';
		$bodyDateOfBirth = $data['dateOfBirth'] ?? 'NULL';
		$bodyGender = $data['gender'] ?? 'NULL';
		// depositors info
		$depositorFirstName = $data['depositorFirstName'] ?? 'NULL';
		$depositorotherName = $data['depositorOtherNames'] ?? 'NULL';
		$depositorAddress = $data['depositorAddress'] ?? 'NULL';
		$depositorRelationshipType = $data['depositorRelationshipType'] ?? 'NULL';
		$depositorphoneNumber = $data['depositorOtherNames'] ?? 'NULL';
		//next of kin info
		$nextOfKinFirstName = $data['nextOfKinFirstName'] ?? 'NULL';
		$nextOfKinotherNames = $data['nextOfKinOtherNames'] ?? 'NULL';
		$nextOfKinAddress = $data['nextOfKinAddress'] ?? 'NULL';
		$nextOfKinRelationshipType = $data['nextOfKinRelationshipType'] ?? 'NULL';
		$nextOfKinphoneNumber = $data['nextOfKinOtherNames'] ?? 'NULL';
		//body tag query
		$packed = [
			'DeathPhysicianID'=>$physicianId,
			'BodyTag'=>($tag !== 'NULL') ? QB::wrapString($tag, "'") : $tag,
			'DateOfDeath'=>($dateOfDeath !== 'NULL') ? QB::wrapString($dateOfDeath, "'") : $dateOfDeath,
			'PlaceOfDeath'=>($placeOfDeath !== 'NULL') ? QB::wrapString($placeOfDeath, "'") : $placeOfDeath,
			
		];

		$bodyTagResult = DatabaseQueryFactory::insert('Mortuary.Body', $packed);
		$bodyId = $bodyTagResult['lastInsertId'];
		//body info query
		$packed = [
			'BodyID'=>$bodyId,
			'BodyFirstName'=>($bodyFirstName !== 'NULL') ? QB::wrapString($bodyFirstName, "'") : $bodyFirstName,
			'BodyOtherNames'=>($bodyOtherNames !== 'NULL') ? QB::wrapString($bodyOtherNames, "'") : $bodyOtherNames,
			'BodyDateOfBirth'=>($bodyDateOfBirth !== 'NULL') ? QB::wrapString($bodyDateOfBirth, "'") : $bodyDateOfBirth,
			'BodyGender'=>($bodyGender !== 'NULL') ? QB::wrapString($bodyGender, "'") : $bodyGender
		];

		$bodyInfoResult = DatabaseQueryFactory::insert('Mortuary.BodyInformation', $packed);
		//depositors info query
		$packed = [
			'BodyID'=>$bodyId,
			'DepositorFirstName'=>($depositorFirstName !== 'NULL') ? QB::wrapString($depositorFirstName, "'") : $depositorFirstName,
			'DepositorOtherLastName'=>($depositorOtherName !== 'NULL') ? QB::wrapString($depositorOtherName, "'") : $depositorOtherName,
			'DepositorAddress'=>($depositorAddress !== 'NULL') ? QB::wrapString($depositorAddress, "'") : $depositorAddress,
			'depositorRelationshipType'=>($depositorRelationshipType !== 'NULL') ? QB::wrapString($depositorRelationshipType, "'") : $depositorRelationshipType,
			'depositorPhoneNumber'=>($depositorPhoneNumber !== 'NULL') ? QB::wrapString($depositorPhoneNumber, "'") : $depositorPhoneNumber
		];

		$depositorInfoResult = DatabaseQueryFactory::insert('Mortuary.DepositorDetails', $packed);
		//next of kin query
		$packed = [
			'BodyID'=>$bodyId,
			'NextOfKinFirstName'=>($nextOfKinFirstName !== 'NULL') ? QB::wrapString($nextOfKinFirstName, "'") : $nextOfKinFirstName,
			'NextOfKinOtherLastName'=>($nextOfKinOtherName !== 'NULL') ? QB::wrapString($nextOfKinOtherName, "'") : $nextOfKinOtherName,
			'NextOfKinAddress'=>($nextOfKinAddress !== 'NULL') ? QB::wrapString($nextOfKinAddress, "'") : $nextOfKinAddress,
			'NextOfKinRelationshipType'=>($nextOfKinRelationshipType !== 'NULL') ? QB::wrapString($nextOfKinRelationshipType, "'") : $nextOfKinRelationshipType,
			'NextOfKinPhoneNumber'=>($nextOfKinPhoneNumber !== 'NULL') ? QB::wrapString($nextOfKinPhoneNumber, "'") : $nextOfKinPhoneNumber
		];

		$nextOfKinResult = DatabaseQueryFactory::insert('Mortuary.NextOfKinDetails', $packed);
		return array_merge($bodyTagResult, $bodyInfoResult, $depositorInfoResult, $nextOfKinResult);

	}
}