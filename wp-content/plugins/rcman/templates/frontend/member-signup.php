<?php
/**
 * Public new member sign-up form. Shortcode: [rcman_member_signup title="..."]
 *
 * @var string   $title
 * @var array{type: string, message: string, old: array<string, mixed>}|null $state
 * @var object[] $licences
 */

use RCMan\Frontend\MemberSignupHandler;
use RCMan\Support\View;

defined('ABSPATH') || exit;

$old         = $state['old'] ?? [];
$oldLicences = array_map('intval', (array) ($old['licences'] ?? []));
$value       = static fn (string $key): string => (string) ($old[$key] ?? '');

$fields = [
    'name'         => ['label' => __('Naam', 'rcman'), 'type' => 'text', 'placeholder' => __('Voornaam achternaam', 'rcman'), 'required' => true, 'autocomplete' => 'name'],
    'birthdate'    => ['label' => __('Geboortedatum', 'rcman'), 'type' => 'date', 'placeholder' => '', 'required' => true, 'autocomplete' => 'bday'],
    'address'      => ['label' => __('Adres', 'rcman'), 'type' => 'text', 'placeholder' => __('Straatnaam nummer', 'rcman'), 'required' => true, 'autocomplete' => 'street-address'],
    'zip_code'     => ['label' => __('Postcode', 'rcman'), 'type' => 'text', 'placeholder' => '1234AB', 'required' => true, 'autocomplete' => 'postal-code'],
    'city'         => ['label' => __('Woonplaats', 'rcman'), 'type' => 'text', 'placeholder' => '', 'required' => true, 'autocomplete' => 'address-level2'],
    'mobile_phone' => ['label' => __('Telefoonnummer', 'rcman'), 'type' => 'tel', 'placeholder' => '0612345678', 'required' => true, 'autocomplete' => 'tel'],
    'email'        => ['label' => __('E-mail', 'rcman'), 'type' => 'email', 'placeholder' => 'mail@provider.nl', 'required' => true, 'autocomplete' => 'email'],
    'rdw_number'   => ['label' => __('RDW nummer', 'rcman'), 'type' => 'text', 'placeholder' => __('Niet vereist', 'rcman'), 'required' => false, 'autocomplete' => 'off'],
    'knvvl'        => ['label' => __('KNVvL nummer', 'rcman'), 'type' => 'text', 'placeholder' => __('Niet vereist', 'rcman'), 'required' => false, 'autocomplete' => 'off'],
];
?>
<div class="rcman-form-wrapper" id="rcman-member-signup">
    <form class="rcman-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="<?php echo esc_attr(MemberSignupHandler::ACTION); ?>">
        <?php wp_nonce_field(MemberSignupHandler::ACTION, '_rcman_nonce'); ?>

        <h2 class="rcman-form__title"><?php echo esc_html($title); ?></h2>

        <?php View::render('frontend/partials/message', ['state' => $state]); ?>

        <?php foreach ($fields as $name => $field) : ?>
            <div class="rcman-field">
                <label for="rcman-<?php echo esc_attr($name); ?>"><?php echo esc_html($field['label']); ?><?php echo $field['required'] ? ' *' : ''; ?></label>
                <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($name); ?>" id="rcman-<?php echo esc_attr($name); ?>"
                       placeholder="<?php echo esc_attr($field['placeholder']); ?>" autocomplete="<?php echo esc_attr($field['autocomplete']); ?>"
                       value="<?php echo esc_attr($value($name)); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
            </div>
        <?php endforeach; ?>

        <?php if ($licences) : ?>
            <fieldset class="rcman-field">
                <legend><?php esc_html_e('Brevetten / certificaten', 'rcman'); ?></legend>
                <?php foreach ($licences as $licence) : ?>
                    <label class="rcman-check">
                        <input type="checkbox" name="licences[]" value="<?php echo esc_attr((string) $licence->id); ?>" <?php checked(in_array((int) $licence->id, $oldLicences, true)); ?>>
                        <?php echo esc_html($licence->name); ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>

        <div class="rcman-field">
            <label for="rcman-signup-anti-bot"><?php esc_html_e('Anti-bot vraag: wat is 2 + 2?', 'rcman'); ?> *</label>
            <input type="text" name="anti_bot" id="rcman-signup-anti-bot" inputmode="numeric" autocomplete="off" required>
        </div>

        <div class="rcman-hp" aria-hidden="true">
            <label for="rcman-signup-website"><?php esc_html_e('Laat dit veld leeg', 'rcman'); ?></label>
            <input type="text" name="website" id="rcman-signup-website" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit" class="rcman-button"><?php esc_html_e('Aanmelden', 'rcman'); ?></button>
    </form>
</div>
