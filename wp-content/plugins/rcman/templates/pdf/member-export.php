<?php
/**
 * @var string   $club_name
 * @var object[] $members
 * @var array<int, string[]> $roles
 * @var array<int, string[]> $licences
 * @var string[] $included_roles
 */

use RCMan\Enums\ClubRole;
use RCMan\Support\Format;

defined('ABSPATH') || exit;
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 8pt; color: #222; }
        h1 { font-size: 16pt; margin: 0 0 4pt; }
        h2 { font-size: 10pt; font-weight: normal; margin: 0 0 10pt; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 3pt 4pt; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #f2f2f2; }
        .footer { margin-top: 10pt; color: #777; font-size: 7pt; }
    </style>
</head>
<body>
    <h1><?php echo esc_html(sprintf(__('Ledenlijst %s', 'rcman'), $club_name)); ?></h1>
    <?php /* translators: %s: list of membership types */ ?>
    <h2><?php echo esc_html(sprintf(__('Lidmaatschappen: %s', 'rcman'), implode(', ', $included_roles))); ?></h2>

    <table>
        <thead>
            <tr>
                <th><?php esc_html_e('Naam', 'rcman'); ?></th>
                <th><?php esc_html_e('Geboortedatum', 'rcman'); ?></th>
                <th><?php esc_html_e('Adres', 'rcman'); ?></th>
                <th><?php esc_html_e('Telefoon', 'rcman'); ?></th>
                <th><?php esc_html_e('E-mail', 'rcman'); ?></th>
                <th><?php esc_html_e('KNVvL', 'rcman'); ?></th>
                <th><?php esc_html_e('RDW', 'rcman'); ?></th>
                <th><?php esc_html_e('Lidmaatschap', 'rcman'); ?></th>
                <th><?php esc_html_e('Brevetten', 'rcman'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member) : ?>
                <?php $id = (int) $member->id; ?>
                <tr>
                    <td><?php echo esc_html($member->name); ?></td>
                    <td><?php echo esc_html(Format::date($member->birthdate)); ?></td>
                    <td><?php echo esc_html(trim($member->address . ', ' . $member->zip_code . ' ' . $member->city, ', ')); ?></td>
                    <td><?php echo esc_html((string) $member->mobile_phone); ?></td>
                    <td><?php echo esc_html($member->email); ?></td>
                    <td><?php echo esc_html((string) $member->knvvl); ?></td>
                    <td><?php echo esc_html((string) $member->rdw_number); ?></td>
                    <td><?php echo esc_html(implode(', ', array_map([ClubRole::class, 'labelFor'], $roles[$id] ?? []))); ?></td>
                    <td><?php echo esc_html(implode(', ', $licences[$id] ?? [])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php /* translators: 1: number of members, 2: date */ ?>
    <p class="footer"><?php echo esc_html(sprintf(__('%1$d leden · gegenereerd op %2$s', 'rcman'), count($members), current_time('d-m-Y H:i'))); ?></p>
</body>
</html>
