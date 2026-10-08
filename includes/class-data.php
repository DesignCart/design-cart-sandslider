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

final class DC_Sand_Slider_Data {
    public const POST_TYPE = 'dc_sand_slider';
    public const META_SETTINGS = '_dc_sand_slider_settings';
    public const META_SLIDES = '_dc_sand_slider_slides';

    /**
     * @return array<string, mixed>
     */
    public static function default_settings(): array {
        return [
            'height' => '70vh',
            'minHeight' => 420,
            'holdMs' => 3500,
            'maxLongSide' => 720,
            'releaseSpan' => 1.9,
            'background' => '#050608',
            'fit' => 'contain',
            'fitPaddingTop' => 24,
            'fitPaddingRight' => 24,
            'fitPaddingBottom' => 48,
            'fitPaddingLeft' => 24,
            'autoplay' => 1,
            'loop' => 1,
            'nav' => 1,
            'pagination' => 1,
            'dimUnder' => 1,
            'dimAmount' => 55,
            'dimFadeMs' => 900,
            'startIndex' => 0,
            'navColor' => '#f3eefc',
            'navBg' => '#140a28',
            'navBorder' => '#a78bfa',
            'navHoverColor' => '#ffffff',
            'navHoverBg' => '#7c3aed',
            'navHoverBorder' => '#c4b5fd',
            'pagDot' => '#c4b5fd',
            'pagDotHover' => '#ddd6fe',
            'pagDotActive' => '#8b5cf6',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function default_slide(): array {
        return [
            'image_id' => 0,
            'image_url' => '',
            'header' => '',
            'subheader' => '',
            'cta_label' => '',
            'cta_href' => '',
            'cta_target' => '',
        ];
    }

    /**
     * @return array{title:string,settings:array<string,mixed>,slides:array<int,array<string,mixed>>}
     */
    public static function get_slider(int $id = 0): array {
        $settings = self::default_settings();
        $slides = [];
        $title = '';

        if ($id > 0) {
            $post = get_post($id);
            if ($post && $post->post_type === self::POST_TYPE) {
                $title = $post->post_title;
                $raw_settings = get_post_meta($id, self::META_SETTINGS, true);
                $raw_slides = get_post_meta($id, self::META_SLIDES, true);
                $settings = wp_parse_args(is_array($raw_settings) ? $raw_settings : [], $settings);
                $slides = is_array($raw_slides) ? array_values($raw_slides) : [];
            }
        }

        return [
            'title' => $title,
            'settings' => $settings,
            'slides' => $slides,
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public static function sanitize_settings(array $settings): array {
        $defaults = self::default_settings();
        $clean = [];

        $clean['height'] = isset($settings['height'])
            ? self::sanitize_css_size((string) $settings['height'], (string) $defaults['height'])
            : $defaults['height'];
        $clean['minHeight'] = isset($settings['minHeight'])
            ? max(0, (int) $settings['minHeight'])
            : (int) $defaults['minHeight'];
        $clean['holdMs'] = isset($settings['holdMs'])
            ? max(500, (int) $settings['holdMs'])
            : (int) $defaults['holdMs'];
        $clean['maxLongSide'] = isset($settings['maxLongSide'])
            ? max(120, min(1600, (int) $settings['maxLongSide']))
            : (int) $defaults['maxLongSide'];
        $clean['releaseSpan'] = isset($settings['releaseSpan'])
            ? max(0.2, min(6, (float) $settings['releaseSpan']))
            : (float) $defaults['releaseSpan'];

        $bg = isset($settings['background']) ? sanitize_hex_color((string) $settings['background']) : '';
        $clean['background'] = $bg ? $bg : $defaults['background'];

        $fit = isset($settings['fit']) ? sanitize_key((string) $settings['fit']) : $defaults['fit'];
        $clean['fit'] = in_array($fit, ['contain', 'cover'], true) ? $fit : 'contain';

        foreach (['fitPaddingTop', 'fitPaddingRight', 'fitPaddingBottom', 'fitPaddingLeft'] as $key) {
            $clean[$key] = isset($settings[$key]) ? max(0, (int) $settings[$key]) : (int) $defaults[$key];
        }

        foreach (['autoplay', 'loop', 'nav', 'pagination', 'dimUnder'] as $toggle) {
            $clean[$toggle] = !empty($settings[$toggle]) ? 1 : 0;
        }

        $clean['dimAmount'] = isset($settings['dimAmount'])
            ? max(0, min(100, (int) $settings['dimAmount']))
            : (int) $defaults['dimAmount'];
        $clean['dimFadeMs'] = isset($settings['dimFadeMs'])
            ? max(0, (int) $settings['dimFadeMs'])
            : (int) $defaults['dimFadeMs'];
        $clean['startIndex'] = isset($settings['startIndex'])
            ? max(0, (int) $settings['startIndex'])
            : 0;

        $color_keys = [
            'navColor',
            'navBg',
            'navBorder',
            'navHoverColor',
            'navHoverBg',
            'navHoverBorder',
            'pagDot',
            'pagDotHover',
            'pagDotActive',
        ];
        foreach ($color_keys as $key) {
            $color = isset($settings[$key]) ? sanitize_hex_color((string) $settings[$key]) : '';
            $clean[$key] = $color ? $color : $defaults[$key];
        }

        return $clean;
    }

    /**
     * CSS custom properties for chrome (nav + pagination).
     *
     * @param array<string, mixed> $settings
     * @return array<string, string>
     */
    public static function chrome_css_vars(array $settings): array {
        $settings = wp_parse_args($settings, self::default_settings());

        return [
            '--dc-ss-nav-color' => (string) $settings['navColor'],
            '--dc-ss-nav-bg' => (string) $settings['navBg'],
            '--dc-ss-nav-border' => (string) $settings['navBorder'],
            '--dc-ss-nav-hover-color' => (string) $settings['navHoverColor'],
            '--dc-ss-nav-hover-bg' => (string) $settings['navHoverBg'],
            '--dc-ss-nav-hover-border' => (string) $settings['navHoverBorder'],
            '--dc-ss-pag-dot' => (string) $settings['pagDot'],
            '--dc-ss-pag-dot-hover' => (string) $settings['pagDotHover'],
            '--dc-ss-pag-dot-active' => (string) $settings['pagDotActive'],
        ];
    }

    /**
     * @param array<int, mixed> $slides
     * @return array<int, array<string, mixed>>
     */
    public static function sanitize_slides(array $slides): array {
        $clean = [];

        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            $item = self::default_slide();
            $item['image_id'] = isset($slide['image_id']) ? absint($slide['image_id']) : 0;
            $item['image_url'] = isset($slide['image_url']) ? esc_url_raw((string) $slide['image_url']) : '';
            $item['header'] = isset($slide['header']) ? sanitize_text_field((string) $slide['header']) : '';
            $item['subheader'] = isset($slide['subheader']) ? sanitize_text_field((string) $slide['subheader']) : '';
            $item['cta_label'] = isset($slide['cta_label']) ? sanitize_text_field((string) $slide['cta_label']) : '';
            $item['cta_href'] = isset($slide['cta_href']) ? esc_url_raw((string) $slide['cta_href']) : '';
            $target = isset($slide['cta_target']) ? sanitize_text_field((string) $slide['cta_target']) : '';
            $item['cta_target'] = in_array($target, ['_blank', '_self'], true) ? $target : '';

            if ($item['image_id'] <= 0 && $item['image_url'] === '') {
                continue;
            }

            $clean[] = $item;
        }

        return $clean;
    }

    /**
     * Resolve public image URL for a slide.
     *
     * @param array<string, mixed> $slide
     */
    public static function slide_image_url(array $slide): string {
        $id = isset($slide['image_id']) ? (int) $slide['image_id'] : 0;
        if ($id > 0) {
            $url = wp_get_attachment_image_url($id, 'full');
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return isset($slide['image_url']) ? (string) $slide['image_url'] : '';
    }

    /**
     * Convert hex color (#rrggbb) to Three.js integer.
     */
    public static function hex_to_int(string $hex): int {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return 0x050608;
        }

        return (int) hexdec($hex);
    }

    private static function sanitize_css_size(string $value, string $fallback): string {
        $value = trim($value);
        if ($value === '') {
            return $fallback;
        }

        if (preg_match('/^\d+(\.\d+)?(px|vh|vw|%)$/i', $value)) {
            return $value;
        }

        if (preg_match('/^\d+$/', $value)) {
            return $value . 'px';
        }

        return $fallback;
    }
}
