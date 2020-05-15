<?php

include dirname(__FILE__).'/vlt-shortcode.php';

add_action( 'init', 'vlt_init');

add_action( 'wp_ajax_nopriv_vlt_test_email_check', 'vlt_test_email_check' );
add_action( 'wp_ajax_vlt_test_email_check', 'vlt_test_email_check' );

add_action( 'wp_ajax_nopriv_vlt_test_webrtc', 'vlt_test_webrtc' );
add_action( 'wp_ajax_vlt_test_webrtc', 'vlt_test_webrtc' );

function vlt_init() {
	wp_register_style( 'vlt-css', plugins_url( '/include/vlt.css', __FILE__ ), array(), '1.0.0', 'all' );
	wp_register_script( 'vlt-js', plugins_url( '/include/vlt.js', __FILE__ ), array('jquery'), '1.0.0', 'all' );
	add_shortcode('vlt', 'vlt_shortcode');
}

function vlt_default_progress_image() {
	return '<img id="vlt_progress_image_preview" src="'.plugin_dir_url(__FILE__).'include/ajax-loader.gif" />';
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'vlt_test_email_check') {
	do_action( 'wp_ajax_vlt_test_email_check' );
	do_action( 'wp_ajax_nopriv_vlt_test_email_check' );
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'vlt_test_webrtc') {
	do_action( 'wp_ajax_vlt_test_webrtc' );
	do_action( 'wp_ajax_nopriv_vlt_test_webrtc' );
}

