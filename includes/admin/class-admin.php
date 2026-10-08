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

final class DC_Sand_Slider_Admin {
    public const PAGE_LIST = 'dc-sand-slider';
    public const PAGE_EDIT = 'dc-sand-slider-edit';

    /** @var self|null */
    private static $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('admin_body_class', [$this, 'admin_body_class']);
        DC_Sand_Slider_Admin_Handlers::boot();
    }

    public function register_menu(): void {
        add_menu_page(
            __('Sand Slider', 'design-cart-sandslider'),
            __('Sand Slider', 'design-cart-sandslider'),
            'manage_options',
            self::PAGE_LIST,
            ['DC_Sand_Slider_Admin_List', 'render'],
            'dashicons-images-alt',
            27
        );

        add_submenu_page(
            self::PAGE_LIST,
            __('Lista sliderów', 'design-cart-sandslider'),
            __('Lista sliderów', 'design-cart-sandslider'),
            'manage_options',
            self::PAGE_LIST,
            ['DC_Sand_Slider_Admin_List', 'render']
        );

        add_submenu_page(
            self::PAGE_LIST,
            __('Dodaj / edytuj', 'design-cart-sandslider'),
            __('Dodaj slider', 'design-cart-sandslider'),
            'manage_options',
            self::PAGE_EDIT,
            ['DC_Sand_Slider_Admin_Form', 'render']
        );
    }

    public function admin_body_class(string $classes): string {
        if ($this->is_plugin_page()) {
            $classes .= ' dc-sand-slider-admin';
        }

        return $classes;
    }

    public function enqueue_assets(string $hook): void {
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        $base = DC_SAND_SLIDER_URL . 'assets/admin/';
        $css = $base . 'css/';
        $js = $base . 'js/';
        $ver = DC_SAND_SLIDER_VERSION;

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_style(
            'dc-sand-slider-font-awesome',
            $base . 'vendor/font-awesome/font-awesome.min.css',
            [],
            '4.7.0'
        );

        // Enqueue each DC Interface sheet directly (avoid CSS @import quirks in WP admin).
        $sheets = [
            'dc-tokens',
            'dc-hero',
            'dc-layout',
            'dc-tabs',
            'dc-accordion',
            'dc-switch',
            'dc-dimension',
            'dc-forms',
            'dc-colorpicker',
        ];
        $prev = 'dc-sand-slider-font-awesome';
        foreach ($sheets as $sheet) {
            $handle = 'dc-sand-slider-' . $sheet;
            wp_enqueue_style($handle, $css . $sheet . '.css', [$prev], $ver);
            $prev = $handle;
        }

        wp_enqueue_style(
            'dc-sand-slider-admin',
            $css . 'admin-overrides.css',
            [$prev],
            $ver
        );

        wp_enqueue_script('dc-sand-slider-colorpicker', $js . 'dc-colorpicker.js', [], $ver, true);
        wp_enqueue_script('dc-sand-slider-dimension', $js . 'dc-dimension.js', [], $ver, true);
        wp_enqueue_script(
            'dc-sand-slider-interface',
            $js . 'dc-interface.js',
            ['dc-sand-slider-colorpicker', 'dc-sand-slider-dimension'],
            $ver,
            true
        );
        wp_enqueue_script(
            'dc-sand-slider-admin',
            $js . 'admin.js',
            ['jquery', 'jquery-ui-sortable', 'dc-sand-slider-interface'],
            $ver,
            true
        );

        wp_localize_script('dc-sand-slider-admin', 'dcSandSliderAdmin', [
            'i18n' => [
                'selectImage' => __('Wybierz obraz', 'design-cart-sandslider'),
                'useImage' => __('Użyj obrazu', 'design-cart-sandslider'),
                'confirmDelete' => __('Usunąć slider?', 'design-cart-sandslider'),
                'confirmRemoveSlide' => __('Usunąć ten slajd?', 'design-cart-sandslider'),
                'slide' => __('Slajd', 'design-cart-sandslider'),
            ],
        ]);
    }

    /**
     * Detect list / edit screens by query arg or admin hook.
     */
    private function is_plugin_page(string $hook = ''): bool {
        $page = DC_Sand_Slider_Admin_Helpers::query_text('page');
        if (in_array($page, [self::PAGE_LIST, self::PAGE_EDIT], true)) {
            return true;
        }

        if ($hook === '') {
            return false;
        }

        return strpos($hook, self::PAGE_LIST) !== false;
    }
}
