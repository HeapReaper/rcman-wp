<?php

namespace RCMan\Admin\Pages;

use RCMan\Admin\Notices;

defined('ABSPATH') || exit;

/**
 * Base class for admin pages: slug, capability, rendering and admin-post action handling.
 */
abstract class AbstractPage
{
    abstract public function slug(): string;

    abstract public function title(): string;

    abstract public function menuTitle(): string;

    abstract public function capability(): string;

    abstract public function render(): void;

    /**
     * admin-post.php actions handled by this page: action name => method name.
     *
     * @return array<string, string>
     */
    public function actions(): array
    {
        return [];
    }

    public function registerActions(): void
    {
        foreach ($this->actions() as $action => $method) {
            add_action('admin_post_' . $action, [$this, $method]);
        }
    }

    /**
     * Called on load-{hook} so pages can enqueue assets or handle screen options.
     */
    public function onLoad(): void
    {
    }

    /**
     * @param array<string, scalar> $args
     */
    public function url(array $args = []): string
    {
        return add_query_arg(array_merge(['page' => $this->slug()], $args), admin_url('admin.php'));
    }

    /**
     * Verifies capability + nonce for an admin-post action. Dies on failure.
     */
    protected function authorize(string $action, ?string $capability = null): void
    {
        if (!current_user_can($capability ?? $this->capability())) {
            wp_die(esc_html__('Je hebt geen toegang tot deze actie.', 'rcman'), '', ['response' => 403]);
        }

        check_admin_referer($action);
    }

    /**
     * @param array<string, scalar> $args
     */
    protected function redirect(array $args = [], ?string $success = null, ?string $error = null): void
    {
        if ($success !== null) {
            Notices::success($success);
        }

        if ($error !== null) {
            Notices::error($error);
        }

        wp_safe_redirect($this->url($args));
        exit;
    }

    protected function back(?string $success = null, ?string $error = null): void
    {
        if ($success !== null) {
            Notices::success($success);
        }

        if ($error !== null) {
            Notices::error($error);
        }

        wp_safe_redirect(wp_get_referer() ?: $this->url());
        exit;
    }

    protected function currentUserName(): string
    {
        $user = wp_get_current_user();

        return $user->display_name ?: $user->user_login;
    }

    protected function currentPage(): int
    {
        return max(1, isset($_GET['paged']) ? absint($_GET['paged']) : 1); // phpcs:ignore WordPress.Security.NonceVerification
    }

    protected function selectedYear(): int
    {
        $year = isset($_GET['year']) ? absint($_GET['year']) : 0; // phpcs:ignore WordPress.Security.NonceVerification

        return $year >= 1970 && $year <= 2100 ? $year : (int) current_time('Y');
    }
}
