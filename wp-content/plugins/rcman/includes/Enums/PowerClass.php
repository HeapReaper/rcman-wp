<?php

namespace RCMan\Enums;

defined('ABSPATH') || exit;

enum PowerClass: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
    case NONE = 4;

    public function label(): string
    {
        return match ($this) {
            self::LOW    => '< 300W',
            self::MEDIUM => '300W - 1200W',
            self::HIGH   => '1200W - 3000W',
            self::NONE   => '0W',
        };
    }

    public static function labelFor(int $value): string
    {
        return self::tryFrom($value)?->label() ?? __('Onbekend', 'rcman');
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        $out = [];

        foreach ([self::NONE, self::LOW, self::MEDIUM, self::HIGH] as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
