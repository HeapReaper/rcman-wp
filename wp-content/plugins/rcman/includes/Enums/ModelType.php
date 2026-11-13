<?php

namespace RCMan\Enums;

defined('ABSPATH') || exit;

enum ModelType: int
{
    case PLANE = 1;
    case GLIDER = 2;
    case HELICOPTER = 3;
    case DRONE = 4;
    case MOTORGLIDER = 5;

    public function label(): string
    {
        return match ($this) {
            self::PLANE       => __('Motorvliegtuig', 'rcman'),
            self::GLIDER      => __('Zweefvliegtuig', 'rcman'),
            self::HELICOPTER  => __('Helikopter', 'rcman'),
            self::DRONE       => __('Drone', 'rcman'),
            self::MOTORGLIDER => __('Motorzweefvliegtuig', 'rcman'),
        };
    }

    public static function labelFor(int $value): string
    {
        return self::tryFrom($value)?->label() ?? __('Onbekend', 'rcman');
    }

    /**
     * @return array<int, string> Options in the order shown in the flight form.
     */
    public static function options(): array
    {
        $order = [self::PLANE, self::GLIDER, self::MOTORGLIDER, self::HELICOPTER, self::DRONE];
        $out = [];

        foreach ($order as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
