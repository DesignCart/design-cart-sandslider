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

final class DC_Sand_Slider_Admin_Form {
    public static function render(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $slider_id = DC_Sand_Slider_Admin_Helpers::query_id('id');
        $slider = DC_Sand_Slider_Data::get_slider($slider_id);
        $settings = $slider['settings'];
        $slides = $slider['slides'];
        $is_edit = $slider_id > 0;
        $page_title = $is_edit
            ? __('Edytuj slider', 'design-cart-sandslider')
            : __('Dodaj slider', 'design-cart-sandslider');

        DC_Sand_Slider_Admin_Helpers::page_start($page_title);
        DC_Sand_Slider_Admin_Helpers::render_hero(
            $page_title,
            [
                [
                    'form' => 'dc-sand-slider-form',
                    'label' => __('Zapisz', 'design-cart-sandslider'),
                    'icon' => 'fa fa-save',
                    'class' => 'dc-btn dc-btn--light',
                ],
                [
                    'href' => admin_url('admin.php?page=dc-sand-slider'),
                    'label' => '',
                    'icon' => 'fa fa-arrow-left',
                    'class' => 'dc-btn dc-btn--ghost',
                    'title' => __('Wróć do listy', 'design-cart-sandslider'),
                ],
            ],
            [
                ['href' => admin_url('plugins.php'), 'label' => __('Wtyczki', 'design-cart-sandslider')],
                ['href' => admin_url('admin.php?page=dc-sand-slider'), 'label' => __('Sand Slider', 'design-cart-sandslider')],
                ['label' => $page_title],
            ]
        );

        echo '<div class="dc-page__wrap"><div class="dc-form-card"><div class="dc-interface">';
        DC_Sand_Slider_Admin_Helpers::render_notices();

        echo '<form class="dc-form dc-form--full" id="dc-sand-slider-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="dc_sand_slider_save">';
        echo '<input type="hidden" name="slider_id" value="' . esc_attr((string) $slider_id) . '">';
        wp_nonce_field('dc_sand_slider_save');

        DC_Sand_Slider_Admin_Helpers::render_nav([
            'tab-slides' => ['fa fa-picture-o', __('Slajdy', 'design-cart-sandslider')],
            'tab-settings' => ['fa fa-cog', __('Ustawienia', 'design-cart-sandslider')],
        ]);

        echo '<div class="dc-form-card__body">';
        self::render_slides_tab($slider['title'], $slides);
        self::render_settings_tab($settings);

        echo '<div class="dc-actions">';
        echo '<button type="submit" class="dc-btn dc-btn--primary"><i class="fa fa-save"></i> ' .
            esc_html__('Zapisz slider', 'design-cart-sandslider') . '</button>';
        echo '</div></div></form></div></div></div>';

        self::render_slide_template();
        DC_Sand_Slider_Admin_Helpers::page_end();
    }

    /**
     * @param array<int, array<string, mixed>> $slides
     */
    private static function render_slides_tab(string $title, array $slides): void {
        echo '<div id="tab-slides" class="dc-tab-panel dc-active" role="tabpanel">';

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-tag',
            __('Nazwa', 'design-cart-sandslider'),
            __('Widoczna tylko w panelu administracyjnym', 'design-cart-sandslider')
        );
        DC_Sand_Slider_Admin_Helpers::text_field(
            'title',
            'slider-title',
            __('Nazwa slidera', 'design-cart-sandslider'),
            $title,
            true
        );
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-picture-o',
            __('Slajdy', 'design-cart-sandslider'),
            __('Dodawaj slajdy dynamicznie i ustawiaj kolejność metodą przeciągnij i upuść', 'design-cart-sandslider')
        );

        echo '<div class="dc-sand-slider-slides" id="dc-sand-slider-slides">';
        if ($slides) {
            foreach ($slides as $index => $slide) {
                self::render_slide_row((int) $index, $slide);
            }
        }
        echo '</div>';

        echo '<button type="button" class="dc-btn dc-btn--secondary" id="dc-sand-slider-add-slide">';
        echo '<i class="fa fa-plus"></i> ' . esc_html__('Dodaj slajd', 'design-cart-sandslider');
        echo '</button>';

        DC_Sand_Slider_Admin_Helpers::section_end();
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function render_settings_tab(array $settings): void {
        echo '<div id="tab-settings" class="dc-tab-panel" role="tabpanel">';

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-desktop',
            __('Kontener', 'design-cart-sandslider'),
            __('Wysokość i tło sceny Three.js', 'design-cart-sandslider')
        );
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::text_field(
            'settings[height]',
            'settings-height',
            __('Wysokość (np. 70vh, 600px)', 'design-cart-sandslider'),
            (string) $settings['height']
        );
        DC_Sand_Slider_Admin_Helpers::number_field(
            'settings[minHeight]',
            'settings-min-height',
            __('Min. wysokość (px)', 'design-cart-sandslider'),
            (int) $settings['minHeight'],
            0,
            2000
        );
        echo '</div>';
        DC_Sand_Slider_Admin_Helpers::color_field(
            'settings[background]',
            __('Kolor tła', 'design-cart-sandslider'),
            (string) $settings['background']
        );
        DC_Sand_Slider_Admin_Helpers::radio_group(
            'settings[fit]',
            __('Dopasowanie obrazu', 'design-cart-sandslider'),
            [
                'contain' => __('Zmieść (contain)', 'design-cart-sandslider'),
                'cover' => __('Wypełnij (cover)', 'design-cart-sandslider'),
            ],
            (string) $settings['fit']
        );
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-arrows',
            __('Marginesy ramki obrazu', 'design-cart-sandslider'),
            __('Odstęp od krawędzi kontenera (CSS px)', 'design-cart-sandslider')
        );
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::dimension_field('settings[fitPaddingTop]', __('Góra', 'design-cart-sandslider'), (int) $settings['fitPaddingTop'], 0, 200);
        DC_Sand_Slider_Admin_Helpers::dimension_field('settings[fitPaddingRight]', __('Prawo', 'design-cart-sandslider'), (int) $settings['fitPaddingRight'], 0, 200);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::dimension_field('settings[fitPaddingBottom]', __('Dół', 'design-cart-sandslider'), (int) $settings['fitPaddingBottom'], 0, 200);
        DC_Sand_Slider_Admin_Helpers::dimension_field('settings[fitPaddingLeft]', __('Lewo', 'design-cart-sandslider'), (int) $settings['fitPaddingLeft'], 0, 200);
        echo '</div>';
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-clock-o',
            __('Animacja i tempo', 'design-cart-sandslider'),
            __('Parametry efektu sandpixel', 'design-cart-sandslider')
        );
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::number_field('settings[holdMs]', 'settings-hold', __('Czas zatrzymania (ms)', 'design-cart-sandslider'), (int) $settings['holdMs'], 500, 20000);
        DC_Sand_Slider_Admin_Helpers::number_field('settings[maxLongSide]', 'settings-density', __('Gęstość siatki (maxLongSide)', 'design-cart-sandslider'), (int) $settings['maxLongSide'], 120, 1600);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::number_field('settings[releaseSpan]', 'settings-release', __('Release span (s)', 'design-cart-sandslider'), (float) $settings['releaseSpan'], 0.2, 6, '0.1');
        DC_Sand_Slider_Admin_Helpers::number_field('settings[startIndex]', 'settings-start', __('Slajd startowy', 'design-cart-sandslider'), (int) $settings['startIndex'], 0, 50);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::number_field('settings[dimAmount]', 'settings-dim', __('Przyciemnienie pod spodem (%)', 'design-cart-sandslider'), (int) $settings['dimAmount'], 0, 100);
        DC_Sand_Slider_Admin_Helpers::number_field('settings[dimFadeMs]', 'settings-dim-fade', __('Fade przyciemnienia (ms)', 'design-cart-sandslider'), (int) $settings['dimFadeMs'], 0, 5000);
        echo '</div>';
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-toggle-on',
            __('Zachowanie', 'design-cart-sandslider'),
            __('Nawigacja, pętla i autoplay', 'design-cart-sandslider')
        );
        DC_Sand_Slider_Admin_Helpers::toggle('settings[autoplay]', __('Autoplay', 'design-cart-sandslider'), (int) $settings['autoplay'] === 1);
        DC_Sand_Slider_Admin_Helpers::toggle('settings[loop]', __('Pętla', 'design-cart-sandslider'), (int) $settings['loop'] === 1);
        DC_Sand_Slider_Admin_Helpers::toggle('settings[nav]', __('Strzałki nawigacji', 'design-cart-sandslider'), (int) $settings['nav'] === 1);
        DC_Sand_Slider_Admin_Helpers::toggle('settings[pagination]', __('Paginacja (kropki)', 'design-cart-sandslider'), (int) $settings['pagination'] === 1);
        DC_Sand_Slider_Admin_Helpers::toggle('settings[dimUnder]', __('Przyciemniaj dolny slajd podczas przejścia', 'design-cart-sandslider'), (int) $settings['dimUnder'] === 1);
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-paint-brush',
            __('Wygląd strzałek', 'design-cart-sandslider'),
            __('Kolory przycisków prev / next', 'design-cart-sandslider')
        );
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navColor]', __('Kolor ikony', 'design-cart-sandslider'), (string) $settings['navColor']);
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navBg]', __('Tło', 'design-cart-sandslider'), (string) $settings['navBg']);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navBorder]', __('Obramowanie', 'design-cart-sandslider'), (string) $settings['navBorder']);
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navHoverColor]', __('Hover — ikona', 'design-cart-sandslider'), (string) $settings['navHoverColor']);
        echo '</div>';
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navHoverBg]', __('Hover — tło', 'design-cart-sandslider'), (string) $settings['navHoverBg']);
        DC_Sand_Slider_Admin_Helpers::color_field('settings[navHoverBorder]', __('Hover — obramowanie', 'design-cart-sandslider'), (string) $settings['navHoverBorder']);
        echo '</div>';
        DC_Sand_Slider_Admin_Helpers::section_end();

        DC_Sand_Slider_Admin_Helpers::section_start(
            'fa fa-ellipsis-h',
            __('Wygląd paginacji', 'design-cart-sandslider'),
            __('Kolory kropek pod slajdem', 'design-cart-sandslider')
        );
        echo '<div class="dc-row dc-row--2">';
        DC_Sand_Slider_Admin_Helpers::color_field('settings[pagDot]', __('Kropka', 'design-cart-sandslider'), (string) $settings['pagDot']);
        DC_Sand_Slider_Admin_Helpers::color_field('settings[pagDotHover]', __('Hover', 'design-cart-sandslider'), (string) $settings['pagDotHover']);
        echo '</div>';
        DC_Sand_Slider_Admin_Helpers::color_field(
            'settings[pagDotActive]',
            __('Aktywna kropka', 'design-cart-sandslider'),
            (string) $settings['pagDotActive']
        );
        DC_Sand_Slider_Admin_Helpers::section_end();

        echo '</div>';
    }

    /**
     * @param array<string, mixed> $slide
     */
    private static function render_slide_row(int $index, array $slide): void {
        $defaults = DC_Sand_Slider_Data::default_slide();
        $slide = wp_parse_args($slide, $defaults);
        $image_id = (int) $slide['image_id'];
        $preview = '';

        if ($image_id > 0) {
            $preview = wp_get_attachment_image_url($image_id, 'medium') ?: '';
        } elseif (!empty($slide['image_url'])) {
            $preview = (string) $slide['image_url'];
        }

        echo '<div class="dc-sand-slider-slide" data-index="' . esc_attr((string) $index) . '">';
        echo '<div class="dc-sand-slider-slide__bar">';
        echo '<span class="dc-sand-slider-slide__handle" title="' . esc_attr__('Przeciągnij', 'design-cart-sandslider') . '"><i class="fa fa-bars"></i></span>';
        echo '<strong class="dc-sand-slider-slide__label">' . esc_html__('Slajd', 'design-cart-sandslider') . ' <span class="dc-sand-slider-slide__num">' . esc_html((string) ($index + 1)) . '</span></strong>';
        echo '<button type="button" class="dc-btn dc-btn--ghost dc-sand-slider-slide__remove" title="' . esc_attr__('Usuń slajd', 'design-cart-sandslider') . '"><i class="fa fa-trash"></i></button>';
        echo '</div>';

        echo '<div class="dc-sand-slider-slide__body">';
        echo '<div class="dc-sand-slider-media">';
        echo '<input type="hidden" class="dc-sand-slider-media__id" name="slides[' . esc_attr((string) $index) . '][image_id]" value="' . esc_attr((string) $image_id) . '">';
        echo '<input type="hidden" class="dc-sand-slider-media__url" name="slides[' . esc_attr((string) $index) . '][image_url]" value="' . esc_attr((string) $slide['image_url']) . '">';
        echo '<div class="dc-sand-slider-media__preview">';
        if ($preview !== '') {
            echo '<img src="' . esc_url($preview) . '" alt="">';
        }
        echo '</div>';
        echo '<div class="dc-sand-slider-media__actions">';
        echo '<button type="button" class="dc-btn dc-btn--secondary dc-sand-slider-media__select"><i class="fa fa-picture-o"></i> ' . esc_html__('Obraz', 'design-cart-sandslider') . '</button>';
        echo '<button type="button" class="dc-btn dc-btn--ghost dc-sand-slider-media__clear">' . esc_html__('Wyczyść', 'design-cart-sandslider') . '</button>';
        echo '</div></div>';

        echo '<div class="dc-sand-slider-slide__fields">';
        echo '<div class="dc-field"><label class="dc-label">' . esc_html__('Nagłówek', 'design-cart-sandslider') . '</label>';
        echo '<input class="dc-input" type="text" name="slides[' . esc_attr((string) $index) . '][header]" value="' . esc_attr((string) $slide['header']) . '"></div>';
        echo '<div class="dc-field"><label class="dc-label">' . esc_html__('Podnagłówek', 'design-cart-sandslider') . '</label>';
        echo '<input class="dc-input" type="text" name="slides[' . esc_attr((string) $index) . '][subheader]" value="' . esc_attr((string) $slide['subheader']) . '"></div>';
        echo '<div class="dc-row dc-row--2">';
        echo '<div class="dc-field"><label class="dc-label">' . esc_html__('CTA — tekst', 'design-cart-sandslider') . '</label>';
        echo '<input class="dc-input" type="text" name="slides[' . esc_attr((string) $index) . '][cta_label]" value="' . esc_attr((string) $slide['cta_label']) . '"></div>';
        echo '<div class="dc-field"><label class="dc-label">' . esc_html__('CTA — link', 'design-cart-sandslider') . '</label>';
        echo '<input class="dc-input" type="text" name="slides[' . esc_attr((string) $index) . '][cta_href]" value="' . esc_attr((string) $slide['cta_href']) . '"></div>';
        echo '</div>';
        echo '<div class="dc-field"><label class="dc-label">' . esc_html__('CTA — target', 'design-cart-sandslider') . '</label>';
        echo '<select class="dc-select" name="slides[' . esc_attr((string) $index) . '][cta_target]">';
        echo '<option value=""' . selected((string) $slide['cta_target'], '', false) . '>' . esc_html__('Domyślny', 'design-cart-sandslider') . '</option>';
        echo '<option value="_self"' . selected((string) $slide['cta_target'], '_self', false) . '>_self</option>';
        echo '<option value="_blank"' . selected((string) $slide['cta_target'], '_blank', false) . '>_blank</option>';
        echo '</select></div>';
        echo '</div></div></div>';
    }

    private static function render_slide_template(): void {
        echo '<template id="dc-sand-slider-slide-template">';
        self::render_slide_row(0, DC_Sand_Slider_Data::default_slide());
        echo '</template>';
    }
}
