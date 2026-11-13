<?php
/**
 * @var \RCMan\Admin\Pages\MembersPage $page
 * @var object   $member
 * @var string[] $roles
 * @var object[] $licences
 * @var string[] $instructor
 */

use RCMan\Capabilities;
use RCMan\Enums\ClubRole;
use RCMan\Enums\InstructorType;
use RCMan\Support\Format;

defined('ABSPATH') || exit;

$instructorLabels = array_map(static fn ($type) => InstructorType::tryFrom($type)?->label() ?? $type, $instructor);

$rows = [
    __('E-mail', 'rcman')         => $member->email,
    __('Geboortedatum', 'rcman')  => Format::date($member->birthdate),
    __('Adres', 'rcman')          => $member->address,
    __('Postcode', 'rcman')       => $member->zip_code,
    __('Woonplaats', 'rcman')     => $member->city,
    __('Telefoon', 'rcman')       => $member->mobile_phone,
    __('KNVvL nummer', 'rcman')   => $member->knvvl,
    __('RDW nummer', 'rcman')     => $member->rdw_number,
    __('Lidmaatschap', 'rcman')   => implode(', ', array_map([ClubRole::class, 'labelFor'], $roles)),
    __('Brevetten', 'rcman')      => implode(', ', wp_list_pluck($licences, 'name')),
    __('Instructeur', 'rcman')    => implode(', ', $instructorLabels),
    __('In memoriam', 'rcman')    => (int) $member->in_memoriam ? __('Ja', 'rcman') : __('Nee', 'rcman'),
    __('Lid sinds', 'rcman')      => Format::date($member->created_at),
];
?>
<div class="wrap rcman">
    <h1 class="wp-heading-inline"><?php echo esc_html($member->name); ?></h1>
    <?php if (current_user_can(Capabilities::EDIT_MEMBERS)) : ?>
        <a href="<?php echo esc_url($page->url(['action' => 'edit', 'id' => (int) $member->id])); ?>" class="page-title-action"><?php esc_html_e('Bewerken', 'rcman'); ?></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <table class="form-table rcman-details" role="presentation">
        <?php foreach ($rows as $label => $value) : ?>
            <tr>
                <th scope="row"><?php echo esc_html($label); ?></th>
                <td><?php echo esc_html((string) $value ?: '—'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="<?php echo esc_url($page->url()); ?>">&larr; <?php esc_html_e('Terug naar leden', 'rcman'); ?></a></p>
</div>
