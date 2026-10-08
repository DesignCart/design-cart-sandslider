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

final class DC_Sand_Slider_Admin_List {
    public static function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $query = new WP_Query([
            'post_type' => DC_Sand_Slider_Data::POST_TYPE,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        DC_Sand_Slider_Admin_Helpers::page_start(__('Lista sliderów', 'design-cart-sandslider'));
        DC_Sand_Slider_Admin_Helpers::render_hero(
            __('Sand Slider', 'design-cart-sandslider'),
            [
                [
                    'href' => admin_url('admin.php?page=dc-sand-slider-edit'),
                    'label' => __('Dodaj slider', 'design-cart-sandslider'),
                    'icon' => 'fa fa-plus',
                    'class' => 'dc-btn dc-btn--light',
                ],
            ],
            [
                ['href' => admin_url('plugins.php'), 'label' => __('Wtyczki', 'design-cart-sandslider')],
                ['label' => __('Design Cart Sand Slider', 'design-cart-sandslider')],
                ['label' => __('Lista', 'design-cart-sandslider')],
            ]
        );

        echo '<div class="dc-page__wrap"><div class="dc-form-card"><div class="dc-interface">';
        DC_Sand_Slider_Admin_Helpers::render_notices();
        echo '<div class="dc-form-card__body">';
        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-sliders',
            __('Twoje slidery', 'design-cart-sandslider'),
            __('Shortcode: [dc_sand_slider id="ID"]', 'design-cart-sandslider')
        );

        if ($query->have_posts()) {
            echo '<div class="dc-sand-slider-list">';
            while ($query->have_posts()) {
                $query->the_post();
                $id = (int) get_the_ID();
                $data = DC_Sand_Slider_Data::get_slider($id);
                $count = count($data['slides']);
                $edit = admin_url('admin.php?page=dc-sand-slider-edit&id=' . $id);
                $copy = wp_nonce_url(
                    admin_url('admin-post.php?action=dc_sand_slider_copy&id=' . $id),
                    'dc_sand_slider_copy_' . $id
                );
                $delete = wp_nonce_url(
                    admin_url('admin-post.php?action=dc_sand_slider_delete&id=' . $id),
                    'dc_sand_slider_delete_' . $id
                );

                echo '<div class="dc-sand-slider-item">';
                echo '<div class="dc-sand-slider-item__main">';
                echo '<strong class="dc-sand-slider-item__title">' . esc_html(get_the_title()) . '</strong>';
                echo '<code class="dc-sand-slider-item__shortcode">[dc_sand_slider id="' . esc_html((string) $id) . '"]</code>';
                echo '<span class="dc-sand-slider-item__meta">' .
                    esc_html(sprintf(
                        /* translators: %d: number of slides */
                        _n('%d slajd', '%d slajdów', $count, 'design-cart-sandslider'),
                        $count
                    )) .
                    '</span>';
                echo '</div>';
                echo '<div class="dc-sand-slider-item__actions">';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($edit) . '" title="' . esc_attr__('Edytuj', 'design-cart-sandslider') . '"><i class="fa fa-pencil"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary" href="' . esc_url($copy) . '" title="' . esc_attr__('Kopiuj', 'design-cart-sandslider') . '"><i class="fa fa-files-o"></i></a>';
                echo '<a class="dc-btn dc-btn--secondary dc-sand-slider-delete" href="' . esc_url($delete) . '" title="' . esc_attr__('Usuń', 'design-cart-sandslider') . '"><i class="fa fa-trash"></i></a>';
                echo '</div></div>';
            }
            echo '</div>';
            wp_reset_postdata();
        } else {
            echo '<p class="dc-hint">' . esc_html__('Brak sliderów. Dodaj pierwszy lub aktywuj wtyczkę ponownie, aby wczytać przykład.', 'design-cart-sandslider') . '</p>';
        }

        DC_Sand_Slider_Admin_Helpers::section_end();
        echo '</div></div></div></div>';
        DC_Sand_Slider_Admin_Helpers::page_end();
    }
}
