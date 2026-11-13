<?php

namespace RCMan\Enums;

defined('ABSPATH') || exit;

/**
 * Default licences seeded into the licences table (from Kiyomi's LicenceSeeder).
 */
final class Licence
{
    public const PLANE = 'RC motorplane';
    public const HELICOPTER = 'RC helicopter';
    public const GLIDER = 'RC glider';
    public const MULTICOPTER = 'RC multicopter';
    public const DRONE_A1 = 'Drone A1';
    public const DRONE_A2 = 'Drone A2';
    public const DRONE_A3 = 'Drone A3';

    /**
     * @return string[]
     */
    public static function defaults(): array
    {
        return [
            self::PLANE,
            self::HELICOPTER,
            self::GLIDER,
            self::MULTICOPTER,
            self::DRONE_A1,
            self::DRONE_A2,
            self::DRONE_A3,
        ];
    }
}
