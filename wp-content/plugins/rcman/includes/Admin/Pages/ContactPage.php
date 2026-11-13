<?php

namespace RCMan\Admin\Pages;

use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Repositories\MemberRepository;
use RCMan\Services\MailQueue;
use RCMan\Services\Mailer;
use RCMan\Support\Logger;
use RCMan\Support\Request;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Newsletter / contact members by membership type, plus test e-mail
 * (port of UsersContactController and the Mail module).
 */
final class ContactPage extends AbstractPage
{
    public const TO_YOURSELF = 'to_yourself';

    public function slug(): string
    {
        return 'rcman-contact';
    }

    public function title(): string
    {
        return __('Leden mailen', 'rcman');
    }

    public function menuTitle(): string
    {
        return __('Nieuwsbrief', 'rcman');
    }

    public function capability(): string
    {
        return Capabilities::CONTACT_MEMBERS;
    }

    public function actions(): array
    {
        return [
            'rcman_send_newsletter' => 'handleSend',
            'rcman_send_test_mail'  => 'handleTestMail',
        ];
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
        View::render('admin/contact', [
            'page'    => $this,
            'roles'   => ClubRole::options(),
            'pending' => (new MailQueue())->pendingCount(),
        ]);
    }

    public function handleSend(): void
    {
        $this->authorize('rcman_send_newsletter');

        $sendTo      = array_map('sanitize_key', Request::array('send_to'));
        $subject     = Request::text('subject');
        $content     = Request::html('content');
        $attachments = array_filter(array_map('absint', explode(',', Request::text('attachment_ids'))));

        if (!$sendTo || $subject === '' || trim(wp_strip_all_tags($content)) === '') {
            $this->back(null, __('Vul ontvangers, onderwerp en inhoud in.', 'rcman'));
        }

        if ($sendTo === [self::TO_YOURSELF]) {
            $recipients = [wp_get_current_user()->user_email];
        } else {
            $members    = (new MemberRepository())->withRoles(ClubRole::sanitize($sendTo));
            $recipients = wp_list_pluck($members, 'email');
        }

        $recipients = array_values(array_filter($recipients, 'is_email'));

        if (!$recipients) {
            $this->back(null, __('Er zijn geen ontvangers met een geldig e-mailadres gevonden.', 'rcman'));
        }

        (new MailQueue())->enqueue($recipients, $subject, $content, $attachments);

        Logger::info('Nieuwsbrief ingepland', [
            'subject'    => $subject,
            'recipients' => count($recipients),
            'by'         => $this->currentUserName(),
        ]);

        $this->redirect([], sprintf(
            /* translators: %d: number of recipients */
            __('De e-mails (%d) zijn aan de wachtrij toegevoegd en worden zo verwerkt...', 'rcman'),
            count($recipients)
        ));
    }

    public function handleTestMail(): void
    {
        $this->authorize('rcman_send_test_mail');

        $email = Request::email('email_test');

        if (!is_email($email)) {
            $this->back(null, __('Vul een geldig e-mailadres in.', 'rcman'));
        }

        if (!(new Mailer())->sendTest($email)) {
            $this->back(null, __('Ik kon geen test e-mail versturen. Bekijk de logs.', 'rcman'));
        }

        $this->back(__('Test e-mail is verstuurd.', 'rcman'));
    }
}
