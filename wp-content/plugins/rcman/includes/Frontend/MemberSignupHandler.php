<?php

namespace RCMan\Frontend;

use RCMan\Enums\ClubRole;
use RCMan\Repositories\MemberRepository;
use RCMan\Services\Mailer;
use RCMan\Services\MemberFormData;
use RCMan\Settings;
use RCMan\Support\Logger;
use RCMan\Support\Request;

defined('ABSPATH') || exit;

/**
 * Handles the public new member sign-up form (port of NewMemberController@store).
 */
final class MemberSignupHandler
{
    public const ACTION = 'rcman_member_signup';
    private const FORM = 'member-signup';

    public function register(): void
    {
        add_action('admin_post_nopriv_' . self::ACTION, [$this, 'handle']);
        add_action('admin_post_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void
    {
        $form = MemberFormData::fromRequest(true);
        $old  = array_merge($form->data, ['licences' => $form->licenceIds]);

        if (!wp_verify_nonce(Request::text('_rcman_nonce'), self::ACTION)) {
            FormState::redirect(self::FORM, 'error', __('Het formulier is verlopen, probeer het opnieuw.', 'rcman'), $old);
        }

        if (Request::text('website') !== '' || trim(Request::text('anti_bot')) !== trim((string) Settings::get('anti_bot_answer'))) {
            Logger::info('Lid aanmelden - anti-bot controle mislukt', ['name' => $form->data['name']], Logger::CHANNEL_USER_ERROR);
            FormState::redirect(self::FORM, 'error', __('Ik gok dat je een bot bent!', 'rcman'), $old);
        }

        if (!$form->isValid()) {
            FormState::redirect(self::FORM, 'error', implode(' ', $form->errors), $old);
        }

        $members  = new MemberRepository();
        $memberId = $members->create($form->data, [ClubRole::NOT_PAID->value], $form->licenceIds);

        if (!$memberId) {
            Logger::error('Nieuw lid opslaan mislukt', ['name' => $form->data['name']]);
            FormState::redirect(self::FORM, 'error', __('Er ging iets mis! Probeer het later opnieuw.', 'rcman'), $old);
        }

        Logger::info('Nieuwe lid aanmelding', ['member_id' => $memberId, 'name' => $form->data['name']]);

        $mailer = new Mailer();
        $mailer->sendWelcome($form->data['email'], $form->data['name']);
        $mailer->sendNewMemberNotification($form->data['name'], $memberId);

        FormState::redirect(self::FORM, 'success', __('Je formulier is verstuurd! We nemen spoedig contact op.', 'rcman'));
    }
}
