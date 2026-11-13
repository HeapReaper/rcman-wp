<?php

namespace RCMan\Enums;

defined('ABSPATH') || exit;

enum InstructorType: string
{
    case PLANE = 'plane';
    case GLIDER = 'glider_plane';
    case HELICOPTER = 'helicopter';

    public function label(): string
    {
        return match ($this) {
            self::PLANE      => __('Motorvliegtuig', 'rcman'),
            self::GLIDER     => __('Zweefvliegtuig', 'rcman'),
            self::HELICOPTER => __('Helikopter', 'rcman'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];

        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /**
     * @param mixed $values
     * @return string[]
     */
    public static function sanitize($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $valid = array_map(static fn (self $c) => $c->value, self::cases());

        return array_values(array_intersect(array_map('sanitize_key', $values), $valid));
    }
}
