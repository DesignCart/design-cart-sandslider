<?php
/**
 * Design Cart Sand Slider
 * Autor: Paweł Nosko
 * Firma: Design Cart
 * Adres: https://www.designcart.pl
 *
 * Plugin Name:       Design Cart Sand Slider
 * Plugin URI:        https://www.designcart.pl/laboratorium/378-pokaz-slajdow-z-efektem-ruchu-piasku-na-wydmie.html
 * Description:       Create sand-particle slideshows with Three.js. Manage multiple sliders and embed them with a shortcode.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Paweł Nosko
 * Author URI:        https://www.designcart.pl/pawel-nosko.html
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       design-cart-sandslider
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DC_SAND_SLIDER_VERSION', '1.0.0');
define('DC_SAND_SLIDER_FILE', __FILE__);
define('DC_SAND_SLIDER_DIR', plugin_dir_path(__FILE__));
define('DC_SAND_SLIDER_URL', plugin_dir_url(__FILE__));
define('DC_SAND_SLIDER_BASENAME', plugin_basename(__FILE__));

require_once DC_SAND_SLIDER_DIR . 'includes/class-data.php';
require_once DC_SAND_SLIDER_DIR . 'includes/sample/class-sample.php';
require_once DC_SAND_SLIDER_DIR . 'includes/admin/class-admin-helpers.php';
require_once DC_SAND_SLIDER_DIR . 'includes/admin/class-admin-handlers.php';
require_once DC_SAND_SLIDER_DIR . 'includes/admin/class-admin-list.php';
require_once DC_SAND_SLIDER_DIR . 'includes/admin/class-admin-form.php';
require_once DC_SAND_SLIDER_DIR . 'includes/admin/class-admin.php';
require_once DC_SAND_SLIDER_DIR . 'includes/frontend/class-frontend.php';
require_once DC_SAND_SLIDER_DIR . 'includes/class-plugin.php';

register_activation_hook(__FILE__, ['DC_Sand_Slider_Plugin', 'activate']);

DC_Sand_Slider_Plugin::instance()->boot();
