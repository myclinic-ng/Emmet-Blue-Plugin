<?php declare(strict_types=1);
/**
 * @license MIT
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 *
 * This file is part of the EmmetBlue project, please read the license document
 * available in the root level of the project
 */
namespace EmmetBlue\Plugins\EmmetblueCloud;

/**
 * class Lab.
 *
 * Lab Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class Lab
{
	public static function addFollowUp(array $data)
    {
        $result = Lab\FollowUp::register($data);

        return $result;
    }

    public static function sendPublishStatus(array $data){
        return Lab\FollowUp::publish($data);
    }
}