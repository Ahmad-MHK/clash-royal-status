 
<?php
/**
 * Plugin Name: Clash Royale Player Status
 * Plugin URI:  https://example.com
 * Description: Zoek Clash Royale spelers en toon player info, upcoming chests en battle log via de officiële Clash Royale API.
 * Version:     1.0.0
 * Author:      Ahmad Mahouk
 * Text Domain: clash-royale-status
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Plugin constants
define( 'CR_STATUS_VERSION', '1.0.0' );
define( 'CR_STATUS_PATH', plugin_dir_path( __FILE__ ) );
define( 'CR_STATUS_URL',  plugin_dir_url( __FILE__ ) );

// Option keys
define( 'CR_STATUS_OPTION_API_TOKEN',    'cr_status_api_token' );
define( 'CR_STATUS_OPTION_SHOW_CHESTS',  'cr_status_show_chests' );
define( 'CR_STATUS_OPTION_SHOW_BATTLES', 'cr_status_show_battles' );

// Includes
require_once CR_STATUS_PATH . 'includes/class-cr-assets.php';
require_once CR_STATUS_PATH . 'includes/class-cr-api.php';
require_once CR_STATUS_PATH . 'includes/class-cr-admin.php';
require_once CR_STATUS_PATH . 'includes/class-cr-shortcode.php';

function cr_status_init() {
    $assets = new CR_Assets( CR_STATUS_PATH, CR_STATUS_URL );
    $api    = new CR_API( CR_STATUS_OPTION_API_TOKEN );

    // Register admin settings and shortcode
    new CR_Admin( $assets );
    new CR_Shortcode( $api, $assets );
}
add_action( 'init', 'cr_status_init' );
