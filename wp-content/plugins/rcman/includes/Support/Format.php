<?php

namespace RCMan\Support;

defined('ABSPATH') || exit;

/**
 * Date/time formatting helpers (port of Kiyomi's refactorDate helper).
 */
final class Format
{
    public static function date(?string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }

        $timestamp = strtotime($date);

        return $timestamp ? gmdate('d-m-Y', $timestamp) : '';
    }

    public static function time(?string $time): string
    {
        return $time ? substr($time, 0, 5) : '';
    }

    /**
     * @return array<int, string>
     */
    public static function monthNames(): array
    {
        return [
            1  => __('Januari', 'rcman'),
            2  => __('Februari', 'rcman'),
            3  => __('Maart', 'rcman'),
            4  => __('April', 'rcman'),
            5  => __('Mei', 'rcman'),
            6  => __('Juni', 'rcman'),
            7  => __('Juli', 'rcman'),
            8  => __('Augustus', 'rcman'),
            9  => __('September', 'rcman'),
            10 => __('Oktober', 'rcman'),
            11 => __('November', 'rcman'),
            12 => __('December', 'rcman'),
        ];
    }
}
