<?php
/**
 * Public flight registration form. Shortcode: [rcman_flight_form title="..." image="https://..."]
 *
 * @var string $title
 * @var string $image
 * @var array{type: string, message: string, old: array<string, mixed>}|null $state
 * @var array<int, string> $model_types
 * @var array<int, string> $power_classes
 * @var int $glider
 * @var int $no_power
 */

use RCMan\Frontend\FlightFormHandler;
use RCMan\Support\View;

defined('ABSPATH') || exit;

$old = $state['old'] ?? [];
?>
<div class="rcman-form-wrapper" id="rcman-flight-form">
    <form class="rcman-form rcman-flight-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
          data-state="<?php echo esc_attr($state['type'] ?? ''); ?>" data-glider="<?php echo esc_attr((string) $glider); ?>" data-no-power="<?php echo esc_attr((string) $no_power); ?>">
        <input type="hidden" name="action" value="<?php echo esc_attr(FlightFormHandler::ACTION); ?>">
        <?php wp_nonce_field(FlightFormHandler::ACTION, '_rcman_nonce'); ?>

        <h2 class="rcman-form__title"><?php echo esc_html($title); ?></h2>

        <?php if ($image) : ?>
            <img src="<?php echo esc_url($image); ?>" alt="" class="rcman-form__image">
        <?php endif; ?>

        <?php View::render('frontend/partials/message', ['state' => $state]); ?>

        <div class="rcman-field">
            <label for="rcman-name"><?php esc_html_e('Naam modelvlieger', 'rcman'); ?></label>
            <input type="text" name="name" id="rcman-name" placeholder="<?php esc_attr_e('Volledige naam', 'rcman'); ?>"
                   value="<?php echo esc_attr((string) ($old['name'] ?? '')); ?>" autocomplete="name" required>
            <small class="rcman-help" id="rcman-name-help"><?php esc_html_e('Na de eerste keer succesvol invullen wordt je naam automatisch ingevuld.', 'rcman'); ?></small>
        </div>

        <div class="rcman-field">
            <label for="rcman-date"><?php esc_html_e('Datum', 'rcman'); ?></label>
            <input type="date" name="date" id="rcman-date" value="<?php echo esc_attr((string) ($old['date'] ?? '')); ?>" required>
            <button type="button" class="rcman-button rcman-button--small" data-rcman="today"><?php esc_html_e('Vandaag', 'rcman'); ?></button>
        </div>

        <div class="rcman-row">
            <div class="rcman-field">
                <label for="rcman-start-time"><?php esc_html_e('Starttijd', 'rcman'); ?></label>
                <input type="time" name="start_time" id="rcman-start-time" value="<?php echo esc_attr((string) ($old['start_time'] ?? '')); ?>" required>
                <button type="button" class="rcman-button rcman-button--small" data-rcman="start"><?php esc_html_e('Start vlucht', 'rcman'); ?></button>
            </div>
            <div class="rcman-field">
                <label for="rcman-end-time"><?php esc_html_e('Eindtijd', 'rcman'); ?></label>
                <input type="time" name="end_time" id="rcman-end-time" value="<?php echo esc_attr((string) ($old['end_time'] ?? '')); ?>" required>
                <button type="button" class="rcman-button rcman-button--small" data-rcman="end"><?php esc_html_e('Einde vlucht', 'rcman'); ?></button>
            </div>
        </div>

        <div class="rcman-row">
            <div class="rcman-field">
                <label for="rcman-model-type"><?php esc_html_e('Modeltype', 'rcman'); ?></label>
                <select name="model_type" id="rcman-model-type" required>
                    <option value="" disabled <?php selected(empty($old['model_type'])); ?>><?php esc_html_e('Selecteer', 'rcman'); ?></option>
                    <?php foreach ($model_types as $value => $label) : ?>
                        <option value="<?php echo esc_attr((string) $value); ?>" <?php selected((string) ($old['model_type'] ?? ''), (string) $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="rcman-field">
                <label for="rcman-power-class"><?php esc_html_e('Modelvermogen', 'rcman'); ?></label>
                <select name="power_class" id="rcman-power-class" required>
                    <option value="" disabled <?php selected(empty($old['power_class'])); ?>><?php esc_html_e('Selecteer', 'rcman'); ?></option>
                    <?php foreach ($power_classes as $value => $label) : ?>
                        <option value="<?php echo esc_attr((string) $value); ?>" <?php selected((string) ($old['power_class'] ?? ''), (string) $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="rcman-field" id="rcman-anti-bot">
            <label for="rcman-anti-bot-input"><?php esc_html_e('Anti-bot vraag: wat is 2 + 2?', 'rcman'); ?></label>
            <input type="text" name="anti_bot" id="rcman-anti-bot-input" inputmode="numeric" autocomplete="off" required>
        </div>

        <div class="rcman-hp" aria-hidden="true">
            <label for="rcman-website"><?php esc_html_e('Laat dit veld leeg', 'rcman'); ?></label>
            <input type="text" name="website" id="rcman-website" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit" class="rcman-button"><?php esc_html_e('Verzenden', 'rcman'); ?></button>
    </form>
</div>
