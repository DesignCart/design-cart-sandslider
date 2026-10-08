<?php
/**
 * Design Cart Sand Slider
 * Autor: Paweł Nosko
 * Firma: Design Cart
 * Adres: https://www.designcart.pl
 */

if (!defined('ABSPATH')) {
    exit;
}

final class DC_Sand_Slider_Admin_Handlers {
    public static function boot(): void {
        add_action('admin_post_dc_sand_slider_save', [__CLASS__, 'save_slider']);
        add_action('admin_post_dc_sand_slider_delete', [__CLASS__, 'delete_slider']);
        add_action('admin_post_dc_sand_slider_copy', [__CLASS__, 'copy_slider']);
    }

    public static function save_slider(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-sandslider'));
        }
        check_admin_referer('dc_sand_slider_save');

        $slider_id = DC_Sand_Slider_Admin_Helpers::posted_id('slider_id');
        $title = DC_Sand_Slider_Admin_Helpers::posted_text('title');
        $settings = DC_Sand_Slider_Data::sanitize_settings(
            DC_Sand_Slider_Admin_Helpers::posted_array('settings')
        );
        $slides = DC_Sand_Slider_Data::sanitize_slides(
            DC_Sand_Slider_Admin_Helpers::posted_array('slides')
        );

        $post_data = [
            'post_type' => DC_Sand_Slider_Data::POST_TYPE,
            'post_title' => $title !== '' ? $title : __('Nowy Sand Slider', 'design-cart-sandslider'),
            'post_status' => 'publish',
        ];

        if ($slider_id > 0) {
            $post_data['ID'] = $slider_id;
            $slider_id = wp_update_post($post_data, true);
        } else {
            $slider_id = wp_insert_post($post_data, true);
        }

        if (is_wp_error($slider_id)) {
            wp_die(esc_html($slider_id->get_error_message()));
        }

        update_post_meta((int) $slider_id, DC_Sand_Slider_Data::META_SETTINGS, $settings);
        update_post_meta((int) $slider_id, DC_Sand_Slider_Data::META_SLIDES, $slides);

        wp_safe_redirect(
            admin_url('admin.php?page=dc-sand-slider-edit&id=' . (int) $slider_id . '&updated=1')
        );
        exit;
    }

    public static function delete_slider(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-sandslider'));
        }

        $id = DC_Sand_Slider_Admin_Helpers::query_id('id');
        check_admin_referer('dc_sand_slider_delete_' . $id);

        if ($id > 0) {
            wp_delete_post($id, true);
        }

        wp_safe_redirect(admin_url('admin.php?page=dc-sand-slider&deleted=1'));
        exit;
    }

    public static function copy_slider(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Brak uprawnień.', 'design-cart-sandslider'));
        }

        $id = DC_Sand_Slider_Admin_Helpers::query_id('id');
        check_admin_referer('dc_sand_slider_copy_' . $id);
        $post = get_post($id);

        if ($post && $post->post_type === DC_Sand_Slider_Data::POST_TYPE) {
            $new_id = wp_insert_post([
                'post_type' => DC_Sand_Slider_Data::POST_TYPE,
                'post_status' => 'publish',
                'post_title' => $post->post_title . ' ' . __('(Kopia)', 'design-cart-sandslider'),
            ]);

            if ($new_id && !is_wp_error($new_id)) {
                update_post_meta(
                    $new_id,
                    DC_Sand_Slider_Data::META_SETTINGS,
                    get_post_meta($id, DC_Sand_Slider_Data::META_SETTINGS, true)
                );
                update_post_meta(
                    $new_id,
                    DC_Sand_Slider_Data::META_SLIDES,
                    get_post_meta($id, DC_Sand_Slider_Data::META_SLIDES, true)
                );
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=dc-sand-slider&copied=1'));
        exit;
    }
}
