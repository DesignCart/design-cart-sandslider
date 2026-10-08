<?php
/**
 * Design Cart Sand Slider
 * Autor: Paweł Nosko
 * Firma: Design Cart
 * Adres: https://www.designcart.pl
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$dc_sand_slider_ids = get_posts([
    'post_type' => 'dc_sand_slider',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'fields' => 'ids',
]);

foreach ($dc_sand_slider_ids as $dc_sand_slider_id) {
    wp_delete_post((int) $dc_sand_slider_id, true);
}

delete_option('dc_sand_slider_sample_seeded');
