<?php
/**
 * @var \RCMan\Admin\Pages\FlightsPage $page
 * @var object|null $flight
 * @var object[]    $members
 * @var array<int, string> $model_types
 * @var array<int, string> $power_classes
 */

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1><?php echo $flight ? esc_html__('Vlucht bewerken', 'rcman') : esc_html__('Vlucht toevoegen', 'rcman'); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="rcman_save_flight">
        <input type="hidden" name="id" value="<?php echo esc_attr((string) ($flight->id ?? 0)); ?>">
        <?php wp_nonce_field('rcman_save_flight'); ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="member_id"><?php esc_html_e('Piloot', 'rcman'); ?></label></th>
                <td>
                    <select name="member_id" id="member_id" required>
                        <option value=""><?php esc_html_e('Selecteer', 'rcman'); ?></option>
                        <?php foreach ($members as $member) : ?>
                            <option value="<?php echo esc_attr((string) $member->id); ?>" <?php selected((int) ($flight->member_id ?? 0), (int) $member->id); ?>>
                                <?php echo esc_html($member->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="date"><?php esc_html_e('Datum', 'rcman'); ?></label></th>
                <td><input type="date" name="date" id="date" value="<?php echo esc_attr($flight->date ?? current_time('Y-m-d')); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="start_time"><?php esc_html_e('Starttijd', 'rcman'); ?></label></th>
                <td><input type="time" name="start_time" id="start_time" value="<?php echo esc_attr(substr($flight->start_time ?? '', 0, 5)); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="end_time"><?php esc_html_e('Eindtijd', 'rcman'); ?></label></th>
                <td><input type="time" name="end_time" id="end_time" value="<?php echo esc_attr(substr($flight->end_time ?? '', 0, 5)); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="model_type"><?php esc_html_e('Modeltype', 'rcman'); ?></label></th>
                <td>
                    <select name="model_type" id="model_type" required>
                        <?php foreach ($model_types as $value => $label) : ?>
                            <option value="<?php echo esc_attr((string) $value); ?>" <?php selected((int) ($flight->model_type ?? 0), $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="power_class"><?php esc_html_e('Vermogen', 'rcman'); ?></label></th>
                <td>
                    <select name="power_class" id="power_class" required>
                        <?php foreach ($power_classes as $value => $label) : ?>
                            <option value="<?php echo esc_attr((string) $value); ?>" <?php selected((int) ($flight->power_class ?? 0), $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Opslaan', 'rcman')); ?>
        <a href="<?php echo esc_url($page->url()); ?>"><?php esc_html_e('Annuleren', 'rcman'); ?></a>
    </form>
</div>
