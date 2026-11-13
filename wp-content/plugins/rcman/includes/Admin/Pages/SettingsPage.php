<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Settings;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Plugin settings (port of SettingsController).
 */
final class SettingsPage extends AbstractPage
{
    public function slug(): string
    {
        return 'rcman-settings';
    }

    public function title(): string
    {
        return __('Instellingen', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Instellingen', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::MANAGE_SETTINGS;
    }

    public function actions(): array
    {
        return ['rcman_save_settings' => 'handleSave'];
    }

    public function onLoad(): void
    {
        add_action('admin_enqueue_scripts', static function (): void {
            wp_enqueue_media();
            wp_enqueue_script('rcman-media-picker', RCMAN_URL . 'assets/js/media-picker.js', ['jquery'], RCMAN_VERSION, true);
        });
    }

    public function render(): void
    {
        $settings     = Settings::all();
        $attachmentId = (int) $settings['welcome_attachment_id'];

        View::render('admin/settings', [
            'page'            => $this,
            'settings'        => $settings,
            'attachment_name' => $attachmentId ? basename((string) get_attached_file($attachmentId)) : '',
        ]);
    }

    public function handleSave(): void
    {
        $this->authorize('rcman_save_settings');

        $email = Request::email('email_new_members');

        if ($email !== '' && !is_email($email)) {
            $this->back(null, __('Het e-mailadres voor nieuwe leden is ongeldig.', 'rcman'));
        }

        Settings::update([
            'club_name'                => Request::text('club_name') ?: get_bloginfo('name'),
            'email_new_members'        => $email,
            'send_welcome_email'       => Request::bool('send_welcome_email') ? 1 : 0,
            'welcome_attachment_id'    => Request::int('welcome_attachment_id'),
            'anti_bot_answer'          => Request::text('anti_bot_answer') ?: '4',
            'mail_batch_size'          => min(500, max(1, Request::int('mail_batch_size', 25))),
            'delete_data_on_uninstall' => Request::bool('delete_data_on_uninstall') ? 1 : 0,
        ]);

        $this->back(__('Instellingen opgeslagen!', 'rcman'));
    }
}
