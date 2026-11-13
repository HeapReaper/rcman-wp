<?php

namespace RCMan\Enums;

defined('ABSPATH') || exit;

/**
 * Club membership roles (from Kiyomi's RoleSeeder). These describe the membership type
 * of a club member; WordPress roles/capabilities decide who may use the admin pages.
 */
enum ClubRole: string
{
    case MANAGEMENT = 'management';
    case JUNIOR_MEMBER = 'junior_member';
    case ASPIRANT_MEMBER = 'aspirant_member';
    case MEMBER = 'member';
    case DONOR = 'donor';
    case NOT_PAID = 'not_paid';

    public function label(): string
    {
        return match ($this) {
            self::MANAGEMENT      => __('Bestuur', 'rcman'),
            self::JUNIOR_MEMBER   => __('Junior lid', 'rcman'),
            self::ASPIRANT_MEMBER => __('Aspirant lid', 'rcman'),
            self::MEMBER          => __('Lid', 'rcman'),
            self::DONOR           => __('Donateur', 'rcman'),
            self::NOT_PAID        => __('Nieuw / niet betaald', 'rcman'),
        };
    }

    public static function labelFor(string $value): string
    {
        return self::tryFrom($value)?->label() ?? $value;
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
     * @return string[] Only valid role slugs.
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
