<?php

/*
Plugin Name: VPN leaks test
Plugin URI: https://bash.ws/
Description: The plugin contains the leaks test package: DNS leak test, WEB rtc IP leak test, Email IP leak test. You can integrate these tests into your website.
Version: 1.0.0
Author: macvk
Author URI: https://github.com/macvk
License: GPLv2 or later
*/

define('VLT_NONCE','vpn-leaks-nonce');
define('VLT_FILE',__FILE__);

if ( !function_exists( 'add_action' ) ) {
	die();
}

require_once dirname( __FILE__ ) . '/vlt-init.php';

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once dirname( __FILE__ ) . '/vlt-init-admin.php';
}

