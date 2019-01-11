<?php

/*
 * Plugin Name: WP Product Labels
 * Plugin URI: https://github.com/nikolays93
 * Description: Этот плагин хорошо демонстрирует работу класса wp-post-metabox
 * Version: 0.1.2
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-product-labels
 * Domain Path: /languages/
 */

namespace NikolayS93\WP_Product_Labels;

use NikolayS93\WP_Post_Metabox as Metabox;

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');

require_once ABSPATH . "wp-admin/includes/plugin.php";

if (version_compare(PHP_VERSION, '5.3') < 0) {
    throw new \Exception('Plugin requires PHP 5.3 or above');
}

class Plugin
{
    protected static $data;

    private function __construct() {}
    private function __clone() {}

    /**
     * Define required plugin data
     */
    public static function define()
    {
        self::$data = get_plugin_data(__FILE__);

        if( !defined(__NAMESPACE__ . '\DOMAIN') )
            define(__NAMESPACE__ . '\DOMAIN', self::$data['TextDomain']);

        if( !defined(__NAMESPACE__ . '\PLUGIN_DIR') )
            define(__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
    }

    /**
     * include required files
     */
    public static function initialize()
    {
        load_plugin_textdomain( DOMAIN, false, basename(PLUGIN_DIR) . '/languages/' );

        $autoload = PLUGIN_DIR . '/vendor/autoload.php';
        if( file_exists($autoload) ) include $autoload;
    }

    public static function execute()
    {
        $Metabox = new Metabox( __('Labels', DOMAIN), true );

        $Metabox->set_type(apply_filters('todo', 'product'));

        $Metabox->set_field(apply_filters('todo', 'sale-price'));
        $Metabox->set_field(apply_filters('todo', 'sale-new'));

        $Metabox->set_content( function() {
            ?>
            <ul>
                <li>
                    <label>
                        <?php printf('<input type="checkbox" name="sale-price" value="1" %s> %s',
                            checked( '1', get_post_meta( get_the_ID(), $key = 'sale-price', $single = true ), false ),
                            __('Show "Sale" label', DOMAIN)
                        ) ?>
                    </label>
                </li>
                <li>
                    <label>
                        <?php printf('<input type="checkbox" name="sale-new" value="1" %s> %s',
                            checked( '1', get_post_meta( get_the_ID(), $key = 'sale-new', $single = true ), false ),
                            __('Show "New" label', DOMAIN)
                        ) ?>
                    </label>
                </li>
            </ul>
        <?php } );
    }
}

Plugin::define();

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'initialize' ), 10 );
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'execute' ), 20 );

add_filter('woocommerce_product_is_on_sale', __NAMESPACE__ . '\_sale_flash_filter', 10, 2);
function _sale_flash_filter($issale, $class) {
    if( get_post_meta( get_the_ID(), 'sale-price', true ) ) {
        $issale = true;
    }

    return $issale;
}

add_action( 'woocommerce_before_shop_loop_item_title', __NAMESPACE__ . '\_new_flash', 10, 5 );
function _new_flash() {
    if( get_post_meta( get_the_ID(), 'sale-new', true ) ) {
        echo '<span class="onnew">' . esc_html__( 'Новинка', 'woocommerce' ) . '</span>';
    }
}
