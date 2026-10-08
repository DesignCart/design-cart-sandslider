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

final class DC_Sand_Slider_Sample {
    public static function seed(): void {
        if (get_option('dc_sand_slider_sample_seeded') === '1') {
            return;
        }

        $existing = get_posts([
            'post_type' => DC_Sand_Slider_Data::POST_TYPE,
            'posts_per_page' => 1,
            'post_status' => 'any',
            'fields' => 'ids',
        ]);

        if ($existing) {
            update_option('dc_sand_slider_sample_seeded', '1', false);
            return;
        }

        $demo = self::demo_slides();
        $slides = [];

        foreach ($demo as $item) {
            $attachment_id = self::sideload_plugin_image($item['file']);
            $slides[] = [
                'image_id' => $attachment_id > 0 ? $attachment_id : 0,
                'image_url' => $attachment_id > 0
                    ? ''
                    : DC_SAND_SLIDER_URL . 'assets/frontend/sample-images/' . $item['file'],
                'header' => $item['header'],
                'subheader' => $item['subheader'],
                'cta_label' => $item['cta_label'],
                'cta_href' => $item['cta_href'],
                'cta_target' => '',
            ];
        }

        $slider_id = wp_insert_post([
            'post_type' => DC_Sand_Slider_Data::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => __('Przykładowy Sand Slider', 'design-cart-sandslider'),
        ], true);

        if (is_wp_error($slider_id) || !$slider_id) {
            return;
        }

        update_post_meta(
            (int) $slider_id,
            DC_Sand_Slider_Data::META_SETTINGS,
            DC_Sand_Slider_Data::default_settings()
        );
        update_post_meta(
            (int) $slider_id,
            DC_Sand_Slider_Data::META_SLIDES,
            DC_Sand_Slider_Data::sanitize_slides($slides)
        );

        update_option('dc_sand_slider_sample_seeded', '1', false);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function demo_slides(): array {
        return [
            [
                'file' => 'sandpixel.webp',
                'header' => 'Sandpixel',
                'subheader' => 'The image breaks into sand and reveals the next slide.',
                'cta_label' => 'See the effect',
                'cta_href' => '#demo',
            ],
            [
                'file' => 'sbider-bot.webp',
                'header' => 'Sbider Bot',
                'subheader' => 'Automation and bots built for e‑commerce.',
                'cta_label' => 'Learn more',
                'cta_href' => '#sbider',
            ],
            [
                'file' => 'geomap.webp',
                'header' => 'Geomap',
                'subheader' => 'Maps and locations in one clear view.',
                'cta_label' => 'Open the map',
                'cta_href' => '#geomap',
            ],
            [
                'file' => 'opencart-3-speed.webp',
                'header' => 'OpenCart 3 Speed',
                'subheader' => 'A faster store without rebuilding from scratch.',
                'cta_label' => 'Speed up your store',
                'cta_href' => '#speed',
            ],
            [
                'file' => '8-may-2026.webp',
                'header' => '8 May 2026',
                'subheader' => 'The next update is on the horizon.',
                'cta_label' => 'Check the date',
                'cta_href' => '#may',
            ],
        ];
    }

    private static function sideload_plugin_image(string $filename): int {
        $source = DC_SAND_SLIDER_DIR . 'assets/frontend/sample-images/' . $filename;
        if (!is_readable($source)) {
            return 0;
        }

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $tmp = wp_tempnam($filename);
        if (!$tmp || !copy($source, $tmp)) {
            return 0;
        }

        $file_array = [
            'name' => $filename,
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, null, [
            'post_title' => pathinfo($filename, PATHINFO_FILENAME),
        ]);

        if (is_wp_error($attachment_id)) {
            wp_delete_file($tmp);
            return 0;
        }

        return (int) $attachment_id;
    }
}
