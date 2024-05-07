<?php

namespace App\Helper;

abstract class FormatNameHelper
{
    public static function formatName($name): string
    {
        $trimedName = trim($name);
        $spaceReplacedName = preg_replace('/\s+/', ' ', $trimedName);

        return $spaceReplacedName;
    }
}