<?php declare(strict_types=1);
/**
 * @package    Berryfrog
 * @copyright  Copyright (c) 2024 by muckiware
 */
namespace App\Core;

class Defaults
{
    const DEFINITION_TEMP_SUMMER_DAY_START = '>= 25';
    const DEFINITION_TEMP_SUMMER_DAY_END = '<= 29';
    const DEFINITION_TEMP_HOT_DAY_START = '>= 30';
    const DEFINITION_TEMP_HOT_DAY_END = '<= 34';
    const DEFINITION_TEMP_DESERT_DAY_START = '>= 35';
    const DEFINITION_TEMP_DESERT_DAY_END = '< 60';
    const DEFINITION_TEMP_TROPICAL_NIGHT_START = '>=20';
    const DEFINITION_TEMP_TROPICAL_NIGHT_END = '< 40';
}