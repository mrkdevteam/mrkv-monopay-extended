<?php
/**
 * Plugin Name: Morkva Monobank Extended
 * Description: Monobank Payment Gateway with callback by Morkva
 * Version: 0.3.1
 * Tested up to: 6.3
 * Requires at least: 5.2
 * Requires PHP: 7.1
 * Author: MORKVA
 * Author URI: https://morkva.co.ua
 * Text Domain: morkva-monobank-extended
 */

# This prevents a public user from directly accessing your .php files
if (! defined('ABSPATH')) 
{
    # Exit if accessed directly
    exit;
}

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

# Include monopay to menu Wordpress
require_once plugin_dir_path(__FILE__) . 'includes/class-morkva-monobank-menu.php';

# Create page and show in menu
new MorkvaMonopayMenu();

# Define constant of plugin direction and path
define('MORKVAMONOGATEWAY_DIR', plugin_dir_path(__FILE__));
define('MORKVAMONOGATEWAY_PATH', plugin_dir_url(__FILE__));

# Add payment method to site
add_action( 'plugins_loaded', 'mrkv_mono_init_mono_gateway_class', 11 );
add_action( 'plugins_loaded', 'mrkv_mono_true_load_plugin_textdomain', 11 );
add_filter( 'woocommerce_payment_gateways', 'mrkv_mono_add_mono_gateway_class' );

/**
 * Load translate 
 * */
function mrkv_mono_true_load_plugin_textdomain() 
{
    # Get languages path
    $plugin_path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
    # Load languages
    load_plugin_textdomain( 'morkva-monobank-extended', false, $plugin_path );
}

/**
 * Include gateway morkva monopay class
 * 
 * */
function mrkv_mono_init_mono_gateway_class() 
{
    # Require monopay class
    require_once MORKVAMONOGATEWAY_DIR . 'includes/class-wc-morkva-mono-gateway.php';
}

/**
 * Add Morkva monopay Gateway to Woocommerce
 * @param array All exist methods
 * @return array All exist methods
 * 
 * */
function mrkv_mono_add_mono_gateway_class( $methods ) 
{
    # Include Morkva Monopay
    $methods[] = 'WC_Gateway_Morkva_Mono';

    # Return all methods
    return $methods;
}

/**
 * Load all classes monopay connection
 * 
 * */
function mrkv_mono_loadMonoLibrary() 
{
    require_once MORKVAMONOGATEWAY_DIR . 'includes/classes/Morkva_Mono_Payment.php';
    require_once MORKVAMONOGATEWAY_DIR . 'includes/classes/Morkva_Mono_Order.php';
    require_once MORKVAMONOGATEWAY_DIR . 'includes/classes/Morkva_Mono_Response.php';
}

