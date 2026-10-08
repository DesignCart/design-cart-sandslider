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

final class DC_Sand_Slider_Plugin {
    /** @var self|null */
    private static $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function activate(): void {
        self::instance()->register_post_types();
        DC_Sand_Slider_Sample::seed();
        flush_rewrite_rules(false);
    }

    public function boot(): void {
        add_action('init', [$this, 'register_post_types']);
        add_filter('plugin_action_links_' . DC_SAND_SLIDER_BASENAME, [$this, 'action_links']);

        DC_Sand_Slider_Admin::instance()->boot();
        DC_Sand_Slider_Frontend::instance()->boot();
    }

    public function register_post_types(): void {
        register_post_type(DC_Sand_Slider_Data::POST_TYPE, [
            'labels' => [
                'name' => __('Sand Slidery', 'design-cart-sandslider'),
                'singular_name' => __('Sand Slider', 'design-cart-sandslider'),
            ],
            'public' => false,
            'show_ui' => false,
            'supports' => ['title'],
        ]);
    }

    /**
     * @param array<int, string> $links
     * @return array<int, string>
     */
    public function action_links(array $links): array {
        array_unshift(
            $links,
            '<a href="' . esc_url(admin_url('admin.php?page=dc-sand-slider')) . '">' .
            esc_html__('Slidery', 'design-cart-sandslider') .
            '</a>'
        );

        return $links;
    }
}
