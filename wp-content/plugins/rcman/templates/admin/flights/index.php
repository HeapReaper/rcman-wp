<?php
/**
 * @var \RCMan\Admin\Pages\FlightsPage            $page
 * @var \RCMan\Admin\ListTables\FlightsListTable $table
 * @var int   $year
 * @var int[] $years
 */

use RCMan\Capabilities;
use RCMan\Support\View;

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1 class="wp-heading-inline"><?php esc_html_e('Vluchten', 'rcman'); ?></h1>
    <?php if (current_user_can(Capabilities::EDIT_FLIGHTS)) : ?>
        <a href="<?php echo esc_url($page->url(['action' => 'new'])); ?>" class="page-title-action"><?php esc_html_e('Vlucht toevoegen', 'rcman'); ?></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <?php View::render('admin/partials/year-filter', ['page' => $page, 'year' => $year, 'years' => $years]); ?>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($page->slug()); ?>">
        <input type="hidden" name="year" value="<?php echo esc_attr((string) $year); ?>">
        <?php
        $table->search_box(__('Zoek piloot', 'rcman'), 'rcman-flight-search');
        $table->display();
        ?>
    </form>
</div>
