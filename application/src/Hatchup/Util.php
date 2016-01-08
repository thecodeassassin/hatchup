<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */


namespace Hatchup;


class Util
{
    /**
     * @param $date
     *
     * @return bool
     */
    public static function validateDate($date)
    {
        return (bool) preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date);
    }

    /**
     * generate UUID version 4
     *
     * @return string
     */
    public static function generateUuid4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param string $dateTime
     * @param string $format
     *
     * @return bool
     */
    public static function isValidDateTime($dateTime, $format = 'Y-m-d H:i:s')
    {
        return (\DateTime::createFromFormat($format, $dateTime) !== false);
    }

    /**
     * @param string $file
     */
    public static function touchFile($file)
    {
        fclose(fopen($file, 'a'));
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function randomPassword($length = 8)
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}