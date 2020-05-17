<?php

include dirname(__FILE__).'/vltp-shortcode.php';

add_action( 'init', 'vltp_init');

add_action( 'wp_ajax_nopriv_vltp_test_email_check', 'vltp_test_email_check' );
add_action( 'wp_ajax_vltp_test_email_check', 'vltp_test_email_check' );

add_action( 'wp_ajax_nopriv_vltp_test_webrtc', 'vltp_test_webrtc' );
add_action( 'wp_ajax_vltp_test_webrtc', 'vltp_test_webrtc' );

/**
 * Initialization hook
 */
function vltp_init() {
	wp_register_style( 'vltp-css', plugins_url( '/include/vltp.css', __FILE__ ), array(), '1.0.0', 'all' );
	wp_register_script( 'vltp-js', plugins_url( '/include/vltp.js', __FILE__ ), array('jquery'), '1.0.0', 'all' );
	add_shortcode('vltp', 'vltp_shortcode');
}

/**
 * Default progress image 
 */
function vltp_default_progress_image() {
	return '<img id="vltp_progress_image_preview" src="'.plugin_dir_url( __FILE__ ).'include/ajax-loader.gif" />';
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'vltp_test_email_check') {
	do_action( 'wp_ajax_vltp_test_email_check' );
	do_action( 'wp_ajax_nopriv_vltp_test_email_check' );
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'vltp_test_webrtc') {
	do_action( 'wp_ajax_vltp_test_webrtc' );
	do_action( 'wp_ajax_nopriv_vltp_test_webrtc' );
}

