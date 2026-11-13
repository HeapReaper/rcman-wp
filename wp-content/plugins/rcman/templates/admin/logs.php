<?php
/**
 * @var \RCMan\Admin\Pages\LogsPage $page
 * @var array<string, string> $channels
 * @var string $channel
 * @var array{items: object[], total: int} $logs
 * @var int $paged
 */

use RCMan\Support\View;

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1 class="wp-heading-inline"><?php esc_html_e('Logs', 'rcman'); ?></h1>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="rcman-heading-form"
          onsubmit="return confirm('<?php echo esc_js(__('Alle logs legen?', 'rcman')); ?>');">
        <input type="hidden" name="action" value="rcman_purge_logs">
        <?php wp_nonce_field('rcman_purge_logs'); ?>
        <button type="submit" class="page-title-action"><?php esc_html_e('Logs legen', 'rcman'); ?></button>
    </form>
    <hr class="wp-header-end">

    <nav class="nav-tab-wrapper">
        <?php foreach ($channels as $key => $label) : ?>
            <a href="<?php echo esc_url($page->url(['channel' => $key])); ?>" class="nav-tab <?php echo $key === $channel ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
    </nav>

    <table class="widefat striped rcman-logs">
        <thead>
            <tr>
                <th><?php esc_html_e('Tijd', 'rcman'); ?></th>
                <th><?php esc_html_e('Niveau', 'rcman'); ?></th>
                <th><?php esc_html_e('Bericht', 'rcman'); ?></th>
                <th><?php esc_html_e('Context', 'rcman'); ?></th>
                <th><?php esc_html_e('IP', 'rcman'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$logs['items']) : ?>
                <tr><td colspan="5"><?php esc_html_e('Geen logregels.', 'rcman'); ?></td></tr>
            <?php endif; ?>
            <?php foreach ($logs['items'] as $log) : ?>
                <tr>
                    <td><?php echo esc_html(mysql2date('d-m-Y H:i:s', $log->created_at)); ?></td>
                    <td><span class="rcman-level rcman-level--<?php echo esc_attr($log->level); ?>"><?php echo esc_html($log->level); ?></span></td>
                    <td><?php echo esc_html($log->message); ?></td>
                    <td><code><?php echo esc_html((string) $log->context); ?></code></td>
                    <td><?php echo esc_html((string) $log->ip_address); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php View::render('admin/partials/pagination', ['total' => $logs['total'], 'paged' => $paged, 'per_page' => 50]); ?>
</div>
