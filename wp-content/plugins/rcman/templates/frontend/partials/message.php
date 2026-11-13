<?php
/**
 * @var array{type: string, message: string}|null $state
 */

defined('ABSPATH') || exit;

if (empty($state)) {
    return;
}
?>
<div class="rcman-alert rcman-alert--<?php echo esc_attr($state['type'] === 'success' ? 'success' : 'error'); ?>" role="alert">
    <?php echo esc_html($state['message']); ?>
</div>
