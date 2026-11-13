<?php

namespace RCMan\Frontend;

defined('ABSPATH') || exit;

/**
 * Carries the result of a public form submit (status message + old input) across the
 * POST -> redirect -> GET cycle, using a short-lived transient referenced in the URL.
 */
final class FormState
{
    private const QUERY_ARG = 'rcman_state';

    /**
     * @param array<string, mixed> $old
     */
    public static function redirect(string $form, string $type, string $message, array $old = []): void
    {
        $token = wp_generate_password(20, false);

        set_transient('rcman_state_' . $token, [
            'form'    => $form,
            'type'    => $type,
            'message' => $message,
            'old'     => $old,
        ], 10 * MINUTE_IN_SECONDS);

        $target = wp_get_referer() ?: home_url('/');
        $target = remove_query_arg(self::QUERY_ARG, $target);

        wp_safe_redirect(add_query_arg(self::QUERY_ARG, $token, $target) . '#rcman-' . $form);
        exit;
    }

    /**
     * @return array{type: string, message: string, old: array<string, mixed>}|null
     */
    public static function get(string $form): ?array
    {
        static $cache = [];

        // phpcs:ignore WordPress.Security.NonceVerification
        $token = isset($_GET[self::QUERY_ARG]) ? sanitize_key(wp_unslash($_GET[self::QUERY_ARG])) : '';

        if ($token === '') {
            return null;
        }

        if (!array_key_exists($token, $cache)) {
            $cache[$token] = get_transient('rcman_state_' . $token) ?: null;
        }

        $state = $cache[$token];

        return $state && $state['form'] === $form ? $state : null;
    }
}
