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
 * class MessagePacket.
 *
 * MessagePacket Controller
 *
 * @author Samuel Adeshina <samueladeshina73@gmail.com>
 * @since v0.0.1 20/08/2016 03:29AM
 */
class MessagePacket
{
    public static function sendAppointmentPacket(array $data)
    {
        $result = MessagePacket\Packets::appointment($data);

        return $result;
    }

    public static function sendAppointmentCancelPacket(array $data)
    {
        $result = MessagePacket\Packets::appointmentCancel($data);

        return $result;
    }

    public static function sendLabPacket(array $data)
    {
        $result = MessagePacket\Packets::lab($data);

        return $result;
    }
}