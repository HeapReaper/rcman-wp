<?php
/**
 * @var \RCMan\Admin\Pages\FlightReportsPage $page
 * @var int   $year
 * @var int[] $years
 * @var array{items: object[], total: int} $reports
 * @var int   $paged
 * @var bool  $pdf_available
 */

use RCMan\Support\Format;
use RCMan\Support\View;

defined('ABSPATH') || exit;

$postUrl = admin_url('admin-post.php');
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('Vluchtrapporten', 'rcman'); ?></h1>

    <?php if (!$pdf_available) : ?>
        <?php View::render('admin/partials/pdf-missing'); ?>
    <?php endif; ?>

    <div class="rcman-panel">
        <h2><?php esc_html_e('Nieuw rapport genereren', 'rcman'); ?></h2>
        <form method="post" action="<?php echo esc_url($postUrl); ?>" class="rcman-inline-form">
            <input type="hidden" name="action" value="rcman_generate_flight_report">
            <?php wp_nonce_field('rcman_generate_flight_report'); ?>
            <label>
                <?php esc_html_e('Van', 'rcman'); ?>
                <input type="date" name="start_date" value="<?php echo esc_attr($year . '-01-01'); ?>" required>
            </label>
            <label>
                <?php esc_html_e('Tot en met', 'rcman'); ?>
                <input type="date" name="end_date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>" required>
            </label>
            <?php submit_button(__('Genereren', 'rcman'), 'primary', 'submit', false, $pdf_available ? [] : ['disabled' => 'disabled']); ?>
        </form>
    </div>

    <?php View::render('admin/partials/year-filter', ['page' => $page, 'year' => $year, 'years' => $years]); ?>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Bestand', 'rcman'); ?></th>
                <th><?php esc_html_e('Periode', 'rcman'); ?></th>
                <th><?php esc_html_e('Aangemaakt op', 'rcman'); ?></th>
                <th><?php esc_html_e('Door', 'rcman'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$reports['items']) : ?>
                <tr><td colspan="5"><?php esc_html_e('Geen rapporten gevonden.', 'rcman'); ?></td></tr>
            <?php endif; ?>
            <?php foreach ($reports['items'] as $report) : ?>
                <?php
                $id       = (int) $report->id;
                $download = wp_nonce_url(add_query_arg(['action' => 'rcman_download_flight_report', 'id' => $id], $postUrl), 'rcman_download_flight_report_' . $id);
                $delete   = wp_nonce_url(add_query_arg(['action' => 'rcman_delete_flight_report', 'id' => $id], $postUrl), 'rcman_delete_flight_report_' . $id);
                ?>
                <tr>
                    <td><?php echo esc_html($report->file); ?></td>
                    <td><?php echo esc_html(Format::date($report->report_start_date) . ' – ' . Format::date($report->report_end_date)); ?></td>
                    <td><?php echo esc_html(Format::date($report->date)); ?></td>
                    <td><?php echo esc_html($report->made_by); ?></td>
                    <td class="rcman-actions">
                        <a class="button button-small" href="<?php echo esc_url($download); ?>"><?php esc_html_e('Download', 'rcman'); ?></a>
                        <a class="button button-small button-link-delete" href="<?php echo esc_url($delete); ?>"
                           onclick="return confirm('<?php echo esc_js(__('Rapport verwijderen?', 'rcman')); ?>');"><?php esc_html_e('Verwijderen', 'rcman'); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php View::render('admin/partials/pagination', ['total' => $reports['total'], 'paged' => $paged, 'per_page' => 10]); ?>
</div>
