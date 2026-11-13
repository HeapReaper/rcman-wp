<?php
/**
 * @var \RCMan\Admin\Pages\MembersPage            $page
 * @var \RCMan\Admin\ListTables\MembersListTable $table
 */

use RCMan\Capabilities;

defined('ABSPATH') || exit;
?>
<div class="wrap rcman">
    <h1 class="wp-heading-inline"><?php esc_html_e('Leden', 'rcman'); ?></h1>
    <?php if (current_user_can(Capabilities::EDIT_MEMBERS)) : ?>
        <a href="<?php echo esc_url($page->url(['action' => 'new'])); ?>" class="page-title-action"><?php esc_html_e('Lid toevoegen', 'rcman'); ?></a>
    <?php endif; ?>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($page->slug()); ?>">
        <?php
        $table->search_box(__('Zoek leden', 'rcman'), 'rcman-member-search');
        $table->display();
        ?>
    </form>
</div>
