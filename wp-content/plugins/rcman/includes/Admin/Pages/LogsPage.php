<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Support\Logger;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Log viewer with purge (port of the Logging module).
 */
final class LogsPage extends AbstractPage
{
    public function slug(): string
    {
        return 'rcman-logs';
    }

    public function title(): string
    {
        return __('Logs', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Logs', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::VIEW_LOGS;
    }

    public function actions(): array
    {
        return ['rcman_purge_logs' => 'handlePurge'];
    }

    /**
     * @return array<string, string>
     */
    private function channels(): array
    {
        return [
            Logger::CHANNEL_SYSTEM     => __('Systeem', 'rcman'),
            Logger::CHANNEL_USER_ERROR => __('Gebruikersfouten', 'rcman'),
            Logger::CHANNEL_ACCESS     => __('Toegang', 'rcman'),
        ];
    }

    public function render(): void
    {
        $channels = $this->channels();
        $channel  = isset($_GET['channel']) ? sanitize_key($_GET['channel']) : Logger::CHANNEL_SYSTEM; // phpcs:ignore WordPress.Security.NonceVerification

        if (!isset($channels[$channel])) {
            $channel = Logger::CHANNEL_SYSTEM;
        }

        View::render('admin/logs', [
            'page'     => $this,
            'channels' => $channels,
            'channel'  => $channel,
            'logs'     => Logger::paginate($channel, $this->currentPage()),
            'paged'    => $this->currentPage(),
        ]);
    }

    public function handlePurge(): void
    {
        $this->authorize('rcman_purge_logs');

        Logger::purge();

        $this->back(__('Logs zijn geleegd.', 'rcman'));
    }
}
