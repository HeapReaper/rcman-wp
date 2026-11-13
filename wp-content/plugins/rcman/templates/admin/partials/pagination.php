<?php
/**
 * Simple pagination links for non-WP_List_Table pages.
 *
 * @var int $total
 * @var int $paged
 * @var int $per_page
 */

defined('ABSPATH') || exit;

$pages = (int) ceil($total / max(1, $per_page));

if ($pages <= 1) {
    return;
}
?>
<div class="tablenav bottom">
    <div class="tablenav-pages">
        <?php
        echo wp_kses_post(paginate_links([
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '',
            'current'   => $paged,
            'total'     => $pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ]));
        ?>
    </div>
</div>
