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

final class DC_Sand_Slider_Frontend {
    /** @var self|null */
    private static $instance = null;

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_filter('script_loader_tag', [$this, 'module_script_tag'], 10, 3);
        add_shortcode('dc_sand_slider', [$this, 'render_shortcode']);
        add_shortcode('dc-sand-slider', [$this, 'render_shortcode']);
    }

    public function register_assets(): void {
        $base = DC_SAND_SLIDER_URL . 'assets/frontend/';

        wp_register_style(
            'dc-sand-slider-front',
            $base . 'css/front.css',
            [],
            DC_SAND_SLIDER_VERSION
        );

        wp_register_script(
            'dc-sand-slider-front',
            $base . 'js/front.js',
            [],
            DC_SAND_SLIDER_VERSION,
            true
        );
    }

    public function module_script_tag(string $tag, string $handle, string $src): string {
        if ($handle !== 'dc-sand-slider-front') {
            return $tag;
        }

        if (strpos($tag, 'type=') !== false) {
            $updated = preg_replace('/\stype=(["\']).*?\1/', ' type="module"', $tag);
            return is_string($updated) ? $updated : $tag;
        }

        return str_replace('<script ', '<script type="module" ', $tag);
    }

    /**
     * @param array<string, mixed>|string $atts
     */
    public function render_shortcode($atts): string {
        $atts = shortcode_atts(['id' => 0], $atts, 'dc_sand_slider');
        $slider_id = (int) $atts['id'];
        if ($slider_id <= 0) {
            return '';
        }

        $post = get_post($slider_id);
        if (!$post || $post->post_type !== DC_Sand_Slider_Data::POST_TYPE) {
            return '';
        }

        $data = DC_Sand_Slider_Data::get_slider($slider_id);
        $settings = $data['settings'];
        $raw_slides = $data['slides'];
        if (!$raw_slides) {
            return '';
        }

        $images = [];
        foreach ($raw_slides as $slide) {
            $src = DC_Sand_Slider_Data::slide_image_url($slide);
            if ($src === '') {
                continue;
            }

            $entry = ['src' => $src];
            if (!empty($slide['header'])) {
                $entry['header'] = (string) $slide['header'];
            }
            if (!empty($slide['subheader'])) {
                $entry['subheader'] = (string) $slide['subheader'];
            }
            if (!empty($slide['cta_label'])) {
                $entry['cta'] = [
                    'label' => (string) $slide['cta_label'],
                    'href' => !empty($slide['cta_href']) ? (string) $slide['cta_href'] : '#',
                ];
                if (!empty($slide['cta_target'])) {
                    $entry['cta']['target'] = (string) $slide['cta_target'];
                }
            }
            $images[] = $entry;
        }

        if (!$images) {
            return '';
        }

        wp_enqueue_style('dc-sand-slider-front');
        wp_enqueue_script('dc-sand-slider-front');

        $uid = 'dc-sand-slider-' . $slider_id . '-' . wp_unique_id();
        $height = (string) $settings['height'];
        $min_height = (int) $settings['minHeight'];
        $bg = (string) $settings['background'];

        $style_vars = array_merge(
            [
                '--dc-ss-height' => $height,
                '--dc-ss-min-height' => $min_height . 'px',
                '--dc-ss-bg' => $bg,
            ],
            DC_Sand_Slider_Data::chrome_css_vars($settings)
        );
        $style_attr = '';
        foreach ($style_vars as $prop => $value) {
            $style_attr .= $prop . ':' . $value . ';';
        }

        $config = [
            'images' => $images,
            'holdMs' => (int) $settings['holdMs'],
            'maxLongSide' => (int) $settings['maxLongSide'],
            'releaseSpan' => (float) $settings['releaseSpan'],
            'background' => DC_Sand_Slider_Data::hex_to_int($bg),
            'autoplay' => (int) $settings['autoplay'] === 1,
            'loop' => (int) $settings['loop'] === 1,
            'fit' => (string) $settings['fit'],
            'fitPadding' => [
                'top' => (int) $settings['fitPaddingTop'],
                'right' => (int) $settings['fitPaddingRight'],
                'bottom' => (int) $settings['fitPaddingBottom'],
                'left' => (int) $settings['fitPaddingLeft'],
            ],
            'nav' => (int) $settings['nav'] === 1,
            'pagination' => (int) $settings['pagination'] === 1,
            'dimUnder' => (int) $settings['dimUnder'] === 1,
            'dimAmount' => max(0, min(1, ((int) $settings['dimAmount']) / 100)),
            'dimFadeMs' => (int) $settings['dimFadeMs'],
            'startIndex' => (int) $settings['startIndex'],
        ];

        ob_start();
        ?>
        <div
            class="design-cart-sandslider"
            id="<?php echo esc_attr($uid); ?>"
            style="<?php echo esc_attr($style_attr); ?>"
            data-dc-sand-slider
            data-config="<?php echo esc_attr(wp_json_encode($config)); ?>"
        >
            <div class="dc-sand-slider__loader" data-dc-ss-loader><?php echo esc_html__('Ładowanie…', 'design-cart-sandslider'); ?></div>
            <div class="dc-sand-slider__stage" data-dc-ss-stage></div>
            <div class="dc-sand-slider__copy" data-dc-ss-copy hidden>
                <h2 class="dc-sand-slider__header" data-dc-ss-header></h2>
                <p class="dc-sand-slider__subheader" data-dc-ss-subheader></p>
                <a class="dc-sand-slider__cta" data-dc-ss-cta hidden></a>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
