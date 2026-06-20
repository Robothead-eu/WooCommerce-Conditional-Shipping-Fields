<?php
/**
 * Plugin Name: WooCommerce Conditional Shipping Fields
 * Plugin URI:  https://github.com/robothead/woocommerce-conditional-shipping-fields
 * Description: Streamline checkout by hiding irrelevant address fields based on the chosen shipping method. When a customer selects a parcel machine or pickup point, street address and city fields serve no purpose — this plugin removes them automatically, reducing friction and the chance of abandonment. Configure which fields to hide directly on each shipping method inside WooCommerce shipping zone settings.
 * Version:     1.0.0
 * Author:      Robothead
 * Author URI:  https://robothead.eu
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woocommerce-conditional-shipping-fields
 * Domain Path: /languages
 *
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * WC requires at least: 7.0
 * WC tested up to:      9.9
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCSF_VERSION', '1.0.0' );
define( 'WCSF_FILE', __FILE__ );
define( 'WCSF_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCSF_URL', plugin_dir_url( __FILE__ ) );

// PSR-4 autoloader — superseded by Composer's if vendor/autoload.php exists.
if ( file_exists( WCSF_DIR . 'vendor/autoload.php' ) ) {
	require_once WCSF_DIR . 'vendor/autoload.php';
} else {
	spl_autoload_register( static function ( string $class ): void {
		$prefix   = 'Robothead\\WcConditionalFields\\';
		$base_dir = WCSF_DIR . 'src/';

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );
}

add_action( 'before_woocommerce_init', static function (): void {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCSF_FILE, true );
	}
} );

add_action( 'plugins_loaded', static function (): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', static function (): void {
			echo '<div class="notice notice-error"><p>'
				. esc_html__( 'WooCommerce Conditional Shipping Fields requires WooCommerce to be active.', 'woocommerce-conditional-shipping-fields' )
				. '</p></div>';
		} );
		return;
	}

	Robothead\WcConditionalFields\Plugin::instance()->init();
} );
