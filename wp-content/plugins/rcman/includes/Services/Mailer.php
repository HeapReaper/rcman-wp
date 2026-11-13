<?php

namespace RCMan\Services;

use RCMan\Settings;
use RCMan\Support\Logger;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * All outgoing e-mail of the plugin, sent through wp_mail().
 */
final class Mailer
{
    /**
     * @param string[] $attachments Absolute file paths.
     */
    public function sendHtml(string $to, string $subject, string $html, array $attachments = []): bool
    {
        $sent = wp_mail($to, $subject, $html, ['Content-Type: text/html; charset=UTF-8'], $attachments);

        if (!$sent) {
            Logger::error('E-mail niet afgeleverd', ['to' => $to, 'subject' => $subject]);
        }

        return $sent;
    }

    public function sendWelcome(string $email, string $name): bool
    {
        if (!Settings::get('send_welcome_email')) {
            return true;
        }

        $attachments = [];
        $attachmentId = (int) Settings::get('welcome_attachment_id');

        if ($attachmentId) {
            $path = get_attached_file($attachmentId);
            if ($path && is_file($path)) {
                $attachments[] = $path;
            }
        }

        $clubName = (string) Settings::get('club_name');

        return $this->sendHtml(
            $email,
            /* translators: %s: club name */
            sprintf(__('Welkom bij %s!', 'rcman'), $clubName),
            View::capture('email/welcome', ['name' => $name, 'club_name' => $clubName, 'has_attachment' => (bool) $attachments]),
            $attachments
        );
    }

    public function sendNewMemberNotification(string $name, int $memberId): bool
    {
        $to = (string) Settings::get('email_new_members');

        if (!is_email($to)) {
            return true;
        }

        return $this->sendHtml(
            $to,
            __('Nieuwe lid aanmelding', 'rcman'),
            View::capture('email/new-member', [
                'name' => $name,
                'url'  => admin_url('admin.php?page=rcman-members&action=view&id=' . $memberId),
            ])
        );
    }

    public function sendTest(string $email): bool
    {
        return $this->sendHtml(
            $email,
            __('RC Manager test e-mail', 'rcman'),
            '<p>' . esc_html__('Test e-mail van RC Manager. Als je dit leest werkt het versturen van e-mail.', 'rcman') . '</p>'
        );
    }
}
