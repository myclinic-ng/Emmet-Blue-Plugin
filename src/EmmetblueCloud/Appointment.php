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
 * class Appointment.
 *
 * Appointment Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class Appointment
{
	public static function publish(array $data)
    {
        $result = Appointment\Appointment::publish($data);

        return $result;
    }

	public static function cancel(int $resourceId, array $data=[])
    {
        $result = Appointment\Appointment::cancelAppointment($resourceId, $data);

        return $result;
    }
}