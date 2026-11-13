<?php
/**
 * @var string $name
 * @var string $club_name
 * @var bool   $has_attachment
 */

defined('ABSPATH') || exit;
?>
<div style="font-family: Arial, sans-serif; font-size: 15px; color: #222;">
    <?php /* translators: %s: member name */ ?>
    <h1 style="font-size: 20px;"><?php echo esc_html(sprintf(__('Welkom %s!', 'rcman'), $name)); ?></h1>
    <?php /* translators: %s: club name */ ?>
    <p><?php echo esc_html(sprintf(__('Bedankt voor je aanmelding bij %s. We nemen spoedig contact met je op.', 'rcman'), $club_name)); ?></p>
    <?php if ($has_attachment) : ?>
        <p><?php esc_html_e('In de bijlage vind je de welkomstbrief.', 'rcman'); ?></p>
    <?php endif; ?>
</div>
