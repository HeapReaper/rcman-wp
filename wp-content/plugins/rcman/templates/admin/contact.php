<?php
/**
 * @var \RCMan\Admin\Pages\ContactPage $page
 * @var array<string, string> $roles
 * @var int $pending
 */

use RCMan\Admin\Pages\ContactPage;

defined('ABSPATH') || exit;

$postUrl = admin_url('admin-post.php');
?>
<div class="wrap rcman">
    <h1><?php esc_html_e('Leden mailen', 'rcman'); ?></h1>

    <?php if ($pending) : ?>
        <div class="notice notice-info inline">
            <?php /* translators: %d: number of e-mails */ ?>
            <p><?php echo esc_html(sprintf(__('Er staan nog %d e-mails in de wachtrij.', 'rcman'), $pending)); ?></p>
        </div>
    <?php endif; ?>

    <div class="rcman-panel">
        <form method="post" action="<?php echo esc_url($postUrl); ?>">
            <input type="hidden" name="action" value="rcman_send_newsletter">
            <?php wp_nonce_field('rcman_send_newsletter'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Versturen naar', 'rcman'); ?></th>
                    <td>
                        <fieldset>
                            <label class="rcman-check"><input type="checkbox" name="send_to[]" value="<?php echo esc_attr(ContactPage::TO_YOURSELF); ?>"> <strong><?php esc_html_e('Alleen naar mezelf (test)', 'rcman'); ?></strong></label>
                            <?php foreach ($roles as $role => $label) : ?>
                                <label class="rcman-check"><input type="checkbox" name="send_to[]" value="<?php echo esc_attr($role); ?>"> <?php echo esc_html($label); ?></label>
                            <?php endforeach; ?>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="subject"><?php esc_html_e('Onderwerp', 'rcman'); ?></label></th>
                    <td><input type="text" class="large-text" name="subject" id="subject" maxlength="255" required></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Bericht', 'rcman'); ?></th>
                    <td><?php wp_editor('', 'rcman_content', ['textarea_name' => 'content', 'textarea_rows' => 14, 'media_buttons' => true]); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Bijlagen', 'rcman'); ?></th>
                    <td class="rcman-media-picker" data-multiple="1">
                        <input type="hidden" name="attachment_ids" value="" class="rcman-media-picker__input">
                        <button type="button" class="button rcman-media-picker__select"><?php esc_html_e('Bijlagen kiezen', 'rcman'); ?></button>
                        <button type="button" class="button-link rcman-media-picker__clear"><?php esc_html_e('Wissen', 'rcman'); ?></button>
                        <p class="rcman-media-picker__names description"></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Versturen', 'rcman')); ?>
        </form>
    </div>

    <div class="rcman-panel">
        <h2><?php esc_html_e('Test e-mail', 'rcman'); ?></h2>
        <p><?php esc_html_e('Controleer of de website e-mail kan versturen.', 'rcman'); ?></p>
        <form method="post" action="<?php echo esc_url($postUrl); ?>" class="rcman-inline-form">
            <input type="hidden" name="action" value="rcman_send_test_mail">
            <?php wp_nonce_field('rcman_send_test_mail'); ?>
            <input type="email" name="email_test" class="regular-text" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required>
            <?php submit_button(__('Verstuur test', 'rcman'), 'secondary', 'submit', false); ?>
        </form>
    </div>
</div>
