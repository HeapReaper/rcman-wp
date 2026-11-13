<?php
/**
 * @var \RCMan\Admin\Pages\MembersPage $page
 * @var object|null $member
 * @var string[]    $member_roles
 * @var int[]       $member_licences
 * @var string[]    $member_instructor
 * @var array<string, string> $all_roles
 * @var object[]    $all_licences
 * @var array<string, string> $instructor_types
 */

defined('ABSPATH') || exit;

$value = static fn (string $field): string => (string) ($member->{$field} ?? '');

$fields = [
    'name'         => ['label' => __('Naam', 'rcman'), 'type' => 'text', 'required' => true],
    'email'        => ['label' => __('E-mail', 'rcman'), 'type' => 'email', 'required' => true],
    'birthdate'    => ['label' => __('Geboortedatum', 'rcman'), 'type' => 'date', 'required' => false],
    'address'      => ['label' => __('Adres', 'rcman'), 'type' => 'text', 'required' => false],
    'zip_code'     => ['label' => __('Postcode', 'rcman'), 'type' => 'text', 'required' => false],
    'city'         => ['label' => __('Woonplaats', 'rcman'), 'type' => 'text', 'required' => false],
    'mobile_phone' => ['label' => __('Telefoon', 'rcman'), 'type' => 'tel', 'required' => false],
    'knvvl'        => ['label' => __('KNVvL nummer', 'rcman'), 'type' => 'text', 'required' => false],
    'rdw_number'   => ['label' => __('RDW nummer', 'rcman'), 'type' => 'text', 'required' => false],
];
?>
<div class="wrap rcman">
    <h1><?php echo $member ? esc_html__('Lid bewerken', 'rcman') : esc_html__('Lid toevoegen', 'rcman'); ?></h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="rcman_save_member">
        <input type="hidden" name="id" value="<?php echo esc_attr((string) ($member->id ?? 0)); ?>">
        <?php wp_nonce_field('rcman_save_member'); ?>

        <h2><?php esc_html_e('Persoonsgegevens', 'rcman'); ?></h2>
        <table class="form-table" role="presentation">
            <?php foreach ($fields as $name => $field) : ?>
                <tr>
                    <th scope="row"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($field['label']); ?></label></th>
                    <td>
                        <input type="<?php echo esc_attr($field['type']); ?>" class="regular-text" name="<?php echo esc_attr($name); ?>"
                               id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value($name)); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th scope="row"><?php esc_html_e('In memoriam', 'rcman'); ?></th>
                <td>
                    <label><input type="checkbox" name="in_memoriam" value="1" <?php checked((int) ($member->in_memoriam ?? 0), 1); ?>> <?php esc_html_e('Dit lid is overleden', 'rcman'); ?></label>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Club', 'rcman'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Lidmaatschap', 'rcman'); ?></th>
                <td>
                    <fieldset>
                        <?php foreach ($all_roles as $role => $label) : ?>
                            <label class="rcman-check"><input type="checkbox" name="roles[]" value="<?php echo esc_attr($role); ?>" <?php checked(in_array($role, $member_roles, true)); ?>> <?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Brevetten', 'rcman'); ?></th>
                <td>
                    <fieldset>
                        <?php foreach ($all_licences as $licence) : ?>
                            <label class="rcman-check"><input type="checkbox" name="licences[]" value="<?php echo esc_attr((string) $licence->id); ?>" <?php checked(in_array((string) $licence->id, array_map('strval', $member_licences), true)); ?>> <?php echo esc_html($licence->name); ?></label>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Instructeur voor', 'rcman'); ?></th>
                <td>
                    <fieldset>
                        <?php foreach ($instructor_types as $type => $label) : ?>
                            <label class="rcman-check"><input type="checkbox" name="instructor[]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $member_instructor, true)); ?>> <?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
        </table>

        <?php submit_button($member ? __('Lid bijwerken', 'rcman') : __('Lid aanmaken', 'rcman')); ?>
        <a href="<?php echo esc_url($page->url()); ?>"><?php esc_html_e('Annuleren', 'rcman'); ?></a>
    </form>
</div>
