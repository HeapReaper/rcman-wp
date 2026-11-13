<?php
defined('ABSPATH') || exit;
?>
<div class="notice notice-warning inline">
    <p>
        <?php
        printf(
            /* translators: %s: command */
            esc_html__('PDF genereren is niet beschikbaar. Voer %s uit in de map van de plugin om dompdf te installeren.', 'rcman'),
            '<code>composer install --no-dev</code>'
        );
        ?>
    </p>
</div>
