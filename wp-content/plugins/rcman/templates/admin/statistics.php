<?php
/**
 * @var \RCMan\Admin\Pages\StatisticsPage $page
 * @var int   $year
 * @var int[] $years
 * @var array<string, array{type: string, title: string, data: array<string, int>}> $charts
 * @var int   $total_flights
 * @var int   $total_members
 */

use RCMan\Support\View;

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('Statistieken', 'rcman'); ?></h1>

    <?php View::render('admin/partials/year-filter', ['page' => $page, 'year' => $year, 'years' => $years]); ?>

    <div class="rcman-cards">
        <div class="rcman-card">
            <span class="rcman-card__value"><?php echo esc_html(number_format_i18n($total_flights)); ?></span>
            <?php /* translators: %d: year */ ?>
            <span class="rcman-card__label"><?php echo esc_html(sprintf(__('Vluchten in %d', 'rcman'), $year)); ?></span>
        </div>
        <div class="rcman-card">
            <span class="rcman-card__value"><?php echo esc_html(number_format_i18n($total_members)); ?></span>
            <span class="rcman-card__label"><?php esc_html_e('Leden totaal', 'rcman'); ?></span>
        </div>
    </div>

    <div class="rcman-charts">
        <?php foreach ($charts as $id => $chart) : ?>
            <div class="rcman-panel rcman-chart rcman-chart--<?php echo esc_attr($chart['type']); ?>">
                <h2><?php echo esc_html($chart['title']); ?></h2>
                <?php if (!$chart['data']) : ?>
                    <p class="description"><?php esc_html_e('Nog geen gegevens.', 'rcman'); ?></p>
                <?php else : ?>
                    <div class="rcman-chart__canvas">
                        <canvas id="rcman-chart-<?php echo esc_attr($id); ?>"
                                data-type="<?php echo esc_attr($chart['type']); ?>"
                                data-labels="<?php echo esc_attr(wp_json_encode(array_map('strval', array_keys($chart['data'])))); ?>"
                                data-values="<?php echo esc_attr(wp_json_encode(array_values($chart['data']))); ?>"
                                aria-label="<?php echo esc_attr($chart['title']); ?>" role="img"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
