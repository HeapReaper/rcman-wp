<?php
/**
 * @var \RCMan\Admin\Pages\SettingsPage $page
 * @var array<string, mixed> $settings
 * @var string $attachment_name
 */

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('Instellingen', 'rcman'); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="rcman_save_settings">
        <?php wp_nonce_field('rcman_save_settings'); ?>

        <h2><?php esc_html_e('Algemeen', 'rcman'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="club_name"><?php esc_html_e('Clubnaam', 'rcman'); ?></label></th>
                <td><input type="text" class="regular-text" name="club_name" id="club_name" value="<?php echo esc_attr((string) $settings['club_name']); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="anti_bot_answer"><?php esc_html_e('Antwoord anti-bot vraag', 'rcman'); ?></label></th>
                <td>
                    <input type="text" class="small-text" name="anti_bot_answer" id="anti_bot_answer" value="<?php echo esc_attr((string) $settings['anti_bot_answer']); ?>">
                    <p class="description"><?php esc_html_e('De openbare formulieren vragen "Wat is 2 + 2?". Standaard: 4.', 'rcman'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('E-mail', 'rcman'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="email_new_members"><?php esc_html_e('E-mailadres voor melding nieuwe leden', 'rcman'); ?></label></th>
                <td>
                    <input type="email" class="regular-text" name="email_new_members" id="email_new_members" value="<?php echo esc_attr((string) $settings['email_new_members']); ?>">
                    <p class="description"><?php esc_html_e('Leeg laten om geen melding te versturen.', 'rcman'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Welkomstmail', 'rcman'); ?></th>
                <td>
                    <label><input type="checkbox" name="send_welcome_email" value="1" <?php checked((int) $settings['send_welcome_email'], 1); ?>> <?php esc_html_e('Stuur nieuwe leden een welkomstmail', 'rcman'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Bijlage welkomstmail', 'rcman'); ?></th>
                <td class="rcman-media-picker" data-multiple="0">
                    <input type="hidden" name="welcome_attachment_id" class="rcman-media-picker__input" value="<?php echo esc_attr((string) $settings['welcome_attachment_id']); ?>">
                    <button type="button" class="button rcman-media-picker__select"><?php esc_html_e('Bestand kiezen', 'rcman'); ?></button>
                    <button type="button" class="button-link rcman-media-picker__clear"><?php esc_html_e('Wissen', 'rcman'); ?></button>
                    <p class="rcman-media-picker__names description"><?php echo esc_html($attachment_name); ?></p>
                    <p class="description"><?php esc_html_e('Bijvoorbeeld de kennismakingsfolder (PDF).', 'rcman'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="mail_batch_size"><?php esc_html_e('Nieuwsbrief batchgrootte', 'rcman'); ?></label></th>
                <td>
                    <input type="number" class="small-text" min="1" max="500" name="mail_batch_size" id="mail_batch_size" value="<?php echo esc_attr((string) $settings['mail_batch_size']); ?>">
                    <p class="description"><?php esc_html_e('Aantal e-mails dat per minuut verstuurd wordt.', 'rcman'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Verwijderen', 'rcman'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Gegevens bij verwijderen', 'rcman'); ?></th>
                <td>
                    <label><input type="checkbox" name="delete_data_on_uninstall" value="1" <?php checked((int) $settings['delete_data_on_uninstall'], 1); ?>>
                        <?php esc_html_e('Verwijder alle leden, vluchten, rapporten en instellingen wanneer de plugin verwijderd wordt', 'rcman'); ?></label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Opslaan', 'rcman')); ?>
    </form>
</div>
