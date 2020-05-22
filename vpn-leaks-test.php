<?php

/*
Plugin Name: VPN Leaks Test Package
Plugin URI: https://bash.ws/
Description: The plugin contains the leaks test package: DNS leak test, WEB rtc IP leak test, Email IP leak test. You can integrate these tests into your website.
Version: 1.0.0
Author: macvk
Author URI: https://github.com/macvk
License: GPLv2 or later
*/

if ( !defined( 'ABSPATH' ) ) {
	die( 'An attempt to call the vltp plugin directly...' );
}

define('VLTP_NONCE','vpn-leaks-nonce');
define('VLTP_FILE',__FILE__);

require_once dirname( __FILE__ ) . '/vltp-init.php';

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once dirname( __FILE__ ) . '/vltp-init-admin.php';
}

