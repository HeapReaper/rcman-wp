<?php
/**
 * @var string   $club_name
 * @var object[] $flights
 * @var string   $start_date
 * @var string   $end_date
 */

use RCMan\Enums\ModelType;
use RCMan\Enums\PowerClass;
use RCMan\Support\Format;

defined('ABSPATH') || exit;
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; color: #222; }
        h1 { font-size: 18pt; margin: 0 0 4pt; }
        h2 { font-size: 12pt; font-weight: normal; margin: 0 0 12pt; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 4pt 6pt; border-bottom: 1px solid #ddd; }
        th { background: #f2f2f2; }
        tr:nth-child(even) td { background: #fafafa; }
        .footer { margin-top: 12pt; color: #777; font-size: 8pt; }
    </style>
</head>
<body>
    <h1><?php echo esc_html($club_name); ?></h1>
    <?php /* translators: 1: start date, 2: end date */ ?>
    <h2><?php echo esc_html(sprintf(__('Vluchten van %1$s tot en met %2$s', 'rcman'), $start_date, $end_date)); ?></h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th><?php esc_html_e('Piloot', 'rcman'); ?></th>
                <th><?php esc_html_e('Datum', 'rcman'); ?></th>
                <th><?php esc_html_e('Start', 'rcman'); ?></th>
                <th><?php esc_html_e('Einde', 'rcman'); ?></th>
                <th><?php esc_html_e('Model', 'rcman'); ?></th>
                <th><?php esc_html_e('Vermogen', 'rcman'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($flights as $i => $flight) : ?>
                <tr>
                    <td><?php echo esc_html((string) ($i + 1)); ?></td>
                    <td><?php echo esc_html($flight->member_name ?? '—'); ?></td>
                    <td><?php echo esc_html(Format::date($flight->date)); ?></td>
                    <td><?php echo esc_html(Format::time($flight->start_time)); ?></td>
                    <td><?php echo esc_html(Format::time($flight->end_time)); ?></td>
                    <td><?php echo esc_html(ModelType::labelFor((int) $flight->model_type)); ?></td>
                    <td><?php echo esc_html(PowerClass::labelFor((int) $flight->power_class)); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php /* translators: 1: number of flights, 2: date */ ?>
    <p class="footer"><?php echo esc_html(sprintf(__('%1$d vluchten · gegenereerd op %2$s', 'rcman'), count($flights), current_time('d-m-Y H:i'))); ?></p>
</body>
</html>
