<?php
/**
 * Year select that reloads the current admin page.
 *
 * @var \RCMan\Admin\Pages\AbstractPage $page
 * @var int   $year
 * @var int[] $years
 * @var array<string, scalar> $extra Extra query args to keep (optional).
 */

defined('ABSPATH') || exit;

$extra = $extra ?? [];
?>
<form method="get" class="rcman-year-filter">
    <input type="hidden" name="page" value="<?php echo esc_attr($page->slug()); ?>">
    <?php foreach ($extra as $key => $value) : ?>
        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $value); ?>">
    <?php endforeach; ?>
    <label for="rcman-year"><?php esc_html_e('Jaar', 'rcman'); ?></label>
    <select name="year" id="rcman-year" onchange="this.form.submit()">
        <?php foreach ($years as $option) : ?>
            <option value="<?php echo esc_attr((string) $option); ?>" <?php selected($year, $option); ?>><?php echo esc_html((string) $option); ?></option>
        <?php endforeach; ?>
    </select>
    <noscript><?php submit_button(__('Toon', 'rcman'), 'secondary', '', false); ?></noscript>
</form>
