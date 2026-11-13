<?php
/**
 * @var int      $year
 * @var int      $flights_year
 * @var int      $members_total
 * @var array<string, int> $top_pilots
 * @var object[] $recent_flights
 * @var int      $pending_mails
 * @var bool     $pdf_available
 */

use RCMan\Enums\ModelType;
use RCMan\Support\Format;

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('RC Manager', 'rcman'); ?></h1>

    <?php if (!$pdf_available) : ?>
        <?php \RCMan\Support\View::render('admin/partials/pdf-missing'); ?>
    <?php endif; ?>

    <div class="rcman-cards">
        <div class="rcman-card">
            <span class="rcman-card__value"><?php echo esc_html(number_format_i18n($flights_year)); ?></span>
            <?php /* translators: %d: year */ ?>
            <span class="rcman-card__label"><?php echo esc_html(sprintf(__('Vluchten in %d', 'rcman'), $year)); ?></span>
        </div>
        <div class="rcman-card">
            <span class="rcman-card__value"><?php echo esc_html(number_format_i18n($members_total)); ?></span>
            <span class="rcman-card__label"><?php esc_html_e('Leden', 'rcman'); ?></span>
        </div>
        <div class="rcman-card">
            <span class="rcman-card__value"><?php echo esc_html(number_format_i18n($pending_mails)); ?></span>
            <span class="rcman-card__label"><?php esc_html_e('E-mails in wachtrij', 'rcman'); ?></span>
        </div>
    </div>

    <div class="rcman-columns">
        <div class="rcman-panel">
            <h2><?php esc_html_e('Laatste vluchten', 'rcman'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Piloot', 'rcman'); ?></th>
                        <th><?php esc_html_e('Datum', 'rcman'); ?></th>
                        <th><?php esc_html_e('Tijd', 'rcman'); ?></th>
                        <th><?php esc_html_e('Model', 'rcman'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$recent_flights) : ?>
                        <tr><td colspan="4"><?php esc_html_e('Nog geen vluchten dit jaar.', 'rcman'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($recent_flights as $flight) : ?>
                        <tr>
                            <td><?php echo esc_html($flight->member_name ?? '—'); ?></td>
                            <td><?php echo esc_html(Format::date($flight->date)); ?></td>
                            <td><?php echo esc_html(Format::time($flight->start_time) . ' – ' . Format::time($flight->end_time)); ?></td>
                            <td><?php echo esc_html(ModelType::labelFor((int) $flight->model_type)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="rcman-panel">
            <?php /* translators: %d: year */ ?>
            <h2><?php echo esc_html(sprintf(__('Top 10 piloten %d', 'rcman'), $year)); ?></h2>
            <ol class="rcman-ranking">
                <?php foreach ($top_pilots as $name => $count) : ?>
                    <li><span><?php echo esc_html($name); ?></span> <strong><?php echo esc_html((string) $count); ?></strong></li>
                <?php endforeach; ?>
            </ol>
            <?php if (!$top_pilots) : ?>
                <p><?php esc_html_e('Nog geen gegevens.', 'rcman'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="rcman-panel">
        <h2><?php esc_html_e('Shortcodes', 'rcman'); ?></h2>
        <p><?php esc_html_e('Plaats deze shortcodes op een pagina om de openbare formulieren te tonen:', 'rcman'); ?></p>
        <ul>
            <li><code>[rcman_flight_form]</code> – <?php esc_html_e('Vlucht aanmelden', 'rcman'); ?></li>
            <li><code>[rcman_member_signup]</code> – <?php esc_html_e('Aanmelden als nieuw lid', 'rcman'); ?></li>
        </ul>
    </div>
</div>
