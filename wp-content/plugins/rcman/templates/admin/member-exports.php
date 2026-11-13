<?php
/**
 * @var \RCMan\Admin\Pages\MemberExportsPage $page
 * @var int   $year
 * @var int[] $years
 * @var array{items: object[], total: int} $exports
 * @var int   $paged
 * @var array<string, string> $roles
 * @var bool  $pdf_available
 */

use RCMan\Support\Format;
use RCMan\Support\View;

defined('ABSPATH') || exit;

$postUrl = admin_url('admin-post.php');
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('Leden export', 'rcman'); ?></h1>

    <?php if (!$pdf_available) : ?>
        <?php View::render('admin/partials/pdf-missing'); ?>
    <?php endif; ?>

    <div class="rcman-panel">
        <h2><?php esc_html_e('Nieuwe export maken', 'rcman'); ?></h2>
        <form method="post" action="<?php echo esc_url($postUrl); ?>">
            <input type="hidden" name="action" value="rcman_generate_member_export">
            <?php wp_nonce_field('rcman_generate_member_export'); ?>
            <p><?php esc_html_e('Welke leden wil je opnemen?', 'rcman'); ?></p>
            <fieldset>
                <?php foreach ($roles as $role => $label) : ?>
                    <label class="rcman-check"><input type="checkbox" name="roles[]" value="<?php echo esc_attr($role); ?>"> <?php echo esc_html($label); ?></label>
                <?php endforeach; ?>
            </fieldset>
            <?php submit_button(__('Export maken', 'rcman'), 'primary', 'submit', true, $pdf_available ? [] : ['disabled' => 'disabled']); ?>
        </form>
    </div>

    <?php View::render('admin/partials/year-filter', ['page' => $page, 'year' => $year, 'years' => $years]); ?>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Bestand', 'rcman'); ?></th>
                <th><?php esc_html_e('Gemaakt op', 'rcman'); ?></th>
                <th><?php esc_html_e('Door', 'rcman'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$exports['items']) : ?>
                <tr><td colspan="4"><?php esc_html_e('Geen exports gevonden.', 'rcman'); ?></td></tr>
            <?php endif; ?>
            <?php foreach ($exports['items'] as $export) : ?>
                <?php
                $id       = (int) $export->id;
                $download = wp_nonce_url(add_query_arg(['action' => 'rcman_download_member_export', 'id' => $id], $postUrl), 'rcman_download_member_export_' . $id);
                $delete   = wp_nonce_url(add_query_arg(['action' => 'rcman_delete_member_export', 'id' => $id], $postUrl), 'rcman_delete_member_export_' . $id);
                ?>
                <tr>
                    <td><?php echo esc_html($export->file_name); ?></td>
                    <td><?php echo esc_html(Format::date($export->made_on)); ?></td>
                    <td><?php echo esc_html($export->made_by); ?></td>
                    <td class="rcman-actions">
                        <a class="button button-small" href="<?php echo esc_url($download); ?>"><?php esc_html_e('Download', 'rcman'); ?></a>
                        <a class="button button-small button-link-delete" href="<?php echo esc_url($delete); ?>"
                           onclick="return confirm('<?php echo esc_js(__('Export verwijderen?', 'rcman')); ?>');"><?php esc_html_e('Verwijderen', 'rcman'); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php View::render('admin/partials/pagination', ['total' => $exports['total'], 'paged' => $paged, 'per_page' => 10]); ?>
</div>
