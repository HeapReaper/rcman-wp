<?php
/**
 * @var string $name
 * @var string $url
 */

defined('ABSPATH') || exit;
?>
<div style="font-family: Arial, sans-serif; font-size: 15px; color: #222;">
    <h1 style="font-size: 20px;"><?php esc_html_e('Nieuwe lid aanmelding!', 'rcman'); ?></h1>
    <?php /* translators: %s: member name */ ?>
    <p><?php echo esc_html(sprintf(__('Naam: %s', 'rcman'), $name)); ?></p>
    <p><a href="<?php echo esc_url($url); ?>"><?php esc_html_e('Bekijk de aanmelding in WordPress', 'rcman'); ?></a></p>
</div>
