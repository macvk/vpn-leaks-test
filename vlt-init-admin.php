<?php

add_action( 'init', 'vlt_admin_init' );

include dirname(__FILE__).'/vlt-admin-page.php';
include dirname(__FILE__).'/vlt-admin-help.php';

register_activation_hook( VLT_FILE, 'vlt_activation' );
add_action( 'admin_enqueue_scripts', 'vlt_admin_media_files' );
add_action( 'wp_ajax_vlt_get_progress_image', 'vlt_get_progress_image' );

function vlt_get_progress_image() {
	if (isset($_REQUEST['id']) ){
		$image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'vlt_progress_image_preview' ) );
		$data = array(
			'image'    => $image,
		);
		wp_send_json_success( $data );
	} 
	else {
		wp_send_json_error();
	}
}

function vlt_admin_media_files( $page ) {

	if( strpos($page, 'vlt-admin-page') === false ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'vlt-admin-script' );
	
	$translation_array = array( 'default_progress_image' => vlt_default_progress_image() );
	wp_localize_script( 'vlt-admin-script', 'vlt_settings', $translation_array );

	wp_enqueue_style( 'vlt-admin.css' );
}

function vlt_admin_init()  {
	add_action( 'admin_menu', 'vlt_admin_menu' );
	wp_register_style( 'vlt-admin.css', plugin_dir_url( __FILE__ ) . '/include/vlt-admin.css' );
	wp_register_script( 'vlt-admin-script', plugins_url( 'include/vlt-admin.js' , __FILE__ ), array('jquery'), '0.1' );
}

function vlt_admin_menu()  {
	$hook = add_options_page( __( 'VPN leaks test', 'vpn-leaks-test' ), __( 'VPN leaks test', 'vpn-leaks-test' ),  'manage_options', 'vlt-admin-page',  'vlt_admin_page');
	
	if ($hook) {
		add_action( "load-$hook", 'vlt_admin_help' );
	}
}

function vlt_activation() {
	global $wpdb;
	
	$sql = 'CREATE TABLE '.$wpdb->prefix.'vlt (
		vlt_id int(11) NOT NULL AUTO_INCREMENT,
		vlt_type varchar(10) NOT NULL default "",
		vlt_options text NOT NULL default "",
		PRIMARY KEY (vlt_id)
		) '.$wpdb->get_charset_collate().';';
		
	$wpdb->query($sql);
	
	register_uninstall_hook( VLT_FILE, 'vlt_uninstall' );
}

function vlt_uninstall() {
	global $wpdb;
	
	$sql = 'DROP TABLE '.$wpdb->prefix.'vlt';
	
	$wpdb->query($sql);
}


