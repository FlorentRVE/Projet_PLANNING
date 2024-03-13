<?php

namespace App\Helper;

abstract class TimeHelper
{
    public static function convertSecondsToHoursMinutes($seconds): string
    {

        // Calcul des heures et des minutes
        $hours = floor($seconds / 60);
        $minutes = $seconds % 60;
        $formattedMinutes = sprintf("%02d", $minutes);

        // Retourner le résultat sous forme de chaîne
        return "$hours:$formattedMinutes";

    }

}
