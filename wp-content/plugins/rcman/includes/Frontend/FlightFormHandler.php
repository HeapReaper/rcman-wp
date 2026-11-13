<?php

namespace RCMan\Frontend;

use RCMan\Enums\ModelType;
use RCMan\Enums\PowerClass;
use RCMan\Repositories\FlightRepository;
use RCMan\Repositories\MemberRepository;
use RCMan\Settings;
use RCMan\Support\Logger;
use RCMan\Support\Request;

defined('ABSPATH') || exit;

/**
 * Handles the public flight registration form (port of FlightsController@store).
 */
final class FlightFormHandler
{
    public const ACTION = 'rcman_register_flight';
    private const FORM = 'flight-form';

    public function register(): void
    {
        add_action('admin_post_nopriv_' . self::ACTION, [$this, 'handle']);
        add_action('admin_post_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void
    {
        $old = [
            'name'        => Request::text('name'),
            'date'        => Request::text('date'),
            'start_time'  => Request::text('start_time'),
            'end_time'    => Request::text('end_time'),
            'model_type'  => Request::text('model_type'),
            'power_class' => Request::text('power_class'),
        ];

        if (!wp_verify_nonce(Request::text('_rcman_nonce'), self::ACTION)) {
            FormState::redirect(self::FORM, 'error', __('Het formulier is verlopen, probeer het opnieuw.', 'rcman'), $old);
        }

        // Honeypot field: must stay empty.
        if (Request::text('website') !== '') {
            FormState::redirect(self::FORM, 'error', __('Er ging iets mis!', 'rcman'));
        }

        if (trim(Request::text('anti_bot')) !== trim((string) Settings::get('anti_bot_answer'))) {
            Logger::info('Vlucht aanmelden - verkeerd antwoord anti-bot vraag', [
                'name' => $old['name'],
                'code' => Request::text('anti_bot'),
            ], Logger::CHANNEL_USER_ERROR);

            FormState::redirect(self::FORM, 'error', __('Het antwoord op de anti-bot vraag is onjuist.', 'rcman'), $old);
        }

        $date       = Request::date('date');
        $startTime  = Request::time('start_time');
        $endTime    = Request::time('end_time');
        $modelType  = ModelType::tryFrom(Request::int('model_type'));
        $powerClass = PowerClass::tryFrom(Request::int('power_class'));

        if ($old['name'] === '' || !$date || !$startTime || !$endTime || !$modelType || !$powerClass) {
            FormState::redirect(self::FORM, 'error', __('Niet alle velden zijn correct ingevuld.', 'rcman'), $old);
        }

        $member = (new MemberRepository())->findByName($old['name']);

        if (!$member) {
            Logger::info('Vlucht aanmelden - naam niet gevonden', ['name' => $old['name']], Logger::CHANNEL_USER_ERROR);
            FormState::redirect(self::FORM, 'error', __('Ik kon je naam niet vinden, heb je hem goed getypt?', 'rcman'), $old);
        }

        $id = (new FlightRepository())->create([
            'member_id'   => (int) $member->id,
            'date'        => $date,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'model_type'  => $modelType->value,
            'power_class' => $powerClass->value,
        ]);

        if (!$id) {
            Logger::error('Vlucht opslaan mislukt', ['name' => $old['name']]);
            FormState::redirect(self::FORM, 'error', __('Er ging iets mis!', 'rcman'), $old);
        }

        FormState::redirect(self::FORM, 'success', __('Vlucht is aangemeld!', 'rcman'), ['name' => $old['name']]);
    }
}
