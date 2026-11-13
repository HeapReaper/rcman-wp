<?php

namespace RCMan\Frontend;

use RCMan\Enums\ModelType;
use RCMan\Enums\PowerClass;
use RCMan\Repositories\MemberRepository;
use RCMan\Support\View;

defined('ABSPATH') || exit;

/**
 * Public shortcodes:
 *  [rcman_flight_form]   – register a flight (port of FlightsController@create)
 *  [rcman_member_signup] – sign up as new member (port of NewMemberController@create)
 */
final class Shortcodes
{
    public function register(): void
    {
        add_shortcode('rcman_flight_form', [$this, 'flightForm']);
        add_shortcode('rcman_member_signup', [$this, 'memberSignup']);

        (new FlightFormHandler())->register();
        (new MemberSignupHandler())->register();

        add_action('template_redirect', [$this, 'preventCaching']);
    }

    /**
     * Pages with our forms must never be cached: nonces and status messages are per request.
     */
    public function preventCaching(): void
    {
        $post = get_queried_object();

        if (!$post instanceof \WP_Post) {
            return;
        }

        if (has_shortcode($post->post_content, 'rcman_flight_form') || has_shortcode($post->post_content, 'rcman_member_signup')) {
            if (!defined('DONOTCACHEPAGE')) {
                define('DONOTCACHEPAGE', true);
            }

            nocache_headers();
        }
    }

    /**
     * @param array<string, string>|string $atts
     */
    public function flightForm($atts = []): string
    {
        $atts = shortcode_atts([
            'title' => __('Registratie aanvang modelvliegen', 'rcman'),
            'image' => '',
        ], $atts, 'rcman_flight_form');

        $this->enqueueAssets('flight-form');

        return View::capture('frontend/flight-form', [
            'title'         => $atts['title'],
            'image'         => $atts['image'],
            'state'         => FormState::get('flight-form'),
            'model_types'   => ModelType::options(),
            'power_classes' => PowerClass::options(),
            'glider'        => ModelType::GLIDER->value,
            'no_power'      => PowerClass::NONE->value,
        ]);
    }

    /**
     * @param array<string, string>|string $atts
     */
    public function memberSignup($atts = []): string
    {
        $atts = shortcode_atts([
            'title' => __('Aanmelden als nieuw lid', 'rcman'),
        ], $atts, 'rcman_member_signup');

        $this->enqueueAssets();

        return View::capture('frontend/member-signup', [
            'title'    => $atts['title'],
            'state'    => FormState::get('member-signup'),
            'licences' => (new MemberRepository())->allLicences(),
        ]);
    }

    private function enqueueAssets(string $script = ''): void
    {
        wp_enqueue_style('rcman-frontend', RCMAN_URL . 'assets/css/frontend.css', [], RCMAN_VERSION);

        if ($script !== '') {
            wp_enqueue_script('rcman-' . $script, RCMAN_URL . 'assets/js/' . $script . '.js', [], RCMAN_VERSION, true);
        }
    }
}
