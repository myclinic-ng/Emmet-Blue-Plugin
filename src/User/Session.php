<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.calculhmac(clent, data)om>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\User;

/**
 * class Session.
 *
 * Session Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 08/06/2016 14:20
 */
class Session
{
	private static $sessionLocation = "bin/data/session";

	public static function load()
	{
		$sessionString = file_get_contents(self::$sessionLocation);
		$decodedSessionString = base64_decode($sessionString);

		return unserialize($decodedSessionString);
	}

	public static function save($session)
	{
		$serializedSession = serialize($session);
		$encodedSessionString = base64_encode($serializedSession);

		file_put_contents(self::$sessionLocation, $encodedSessionString);
	}

	
}