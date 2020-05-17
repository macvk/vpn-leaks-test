<?php

/**
 * Add help to the VPN leaks test admin page
 *
 * @return null
 */
function vltp_admin_help()
{
	$current_screen = get_current_screen();
	
	// Screen Content
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}
	
	if ( !isset( $_GET['page'] ) || $_GET['page'] != 'vltp-admin-page' ) {
		return;
	}

	//setup page
	$current_screen->add_help_tab(
		array(
			'id'		=> 'vltp-admin-page-help-quick',
			'title'		=> __( 'Quick start' , 'vpn-leaks-test'),
			'content'	=>
				'<p><strong>' . __( 'Quick setup guide' , 'vpn-leaks-test') . '</strong></p>' .
				'<p>' . __( '1. Click "<b>Add New VPN test</b>" and choose the test you want to add. Fill in the form and save the test.' , 'vpn-leaks-test') . '</p>' .
				'<p>' . __( '2. Create the page using Wordpress common interface and Copy and paste test shortcode into the page content. Shortcode format: <b style="padding:2px 10px 3px;background:#b6f7b6">[vltp id=YOUR_TEST_ID]</b>' , 'vpn-leaks-test') . '</p>'.
				'<p>' . __( '3. All the tests use the same interface. It consists from the <b>Start button</b> and the test results. The test result is displayed as a table of IPs and conclusion message.' , 'vpn-leaks-test') . '</p>',
		)
	);

	$current_screen->add_help_tab(
		array(
			'id'		=> 'vltp-admin-page-help-dns',
			'title'		=> __( 'DNS leak test' , 'vpn-leaks-test'),
			'content'	=>
				'<p><strong>' . __( 'Setup DNS leak test' , 'vpn-leaks-test') . '</strong></p>' .
				'<p>' . __( '1. Please Add new test and choose DNS leak test as a test type.' , 'vpn-leaks-test') . '</p>' .
				'<p>' . __( '2. Fill in the rest of the form and use the shortcut code for integration.' , 'vpn-leaks-test') . '</p>'.
				'<p>' . __( '3. The Dns leak test is very simple to use. To start the test user needs to click the "Start test" button. Dns queries will be generated and send to the specified domain. The IPs from which was DNS queries received will be displayed as a result. More information can be found <a target=_blank href="https://bash.ws/dnsleak">here</a>' , 'vpn-leaks-test') . '</p>',
		)
	);

	$current_screen->add_help_tab(
		array(
			'id'		=> 'vltp-admin-page-help-email',
			'title'		=> __( 'Email leak test' , 'vpn-leaks-test'),
			'content'	=>
				'<p><strong>' . __( 'Setup Email IP leak test' , 'vpn-leaks-test') . '</strong></p>' .
				'<p>' . __( '1. Please Add new test and choose Email leak test as a test type.' , 'vpn-leaks-test') . '</p>' .
				'<p>' . __( '2. Fill in the rest of the form and use the shortcut code for integration.' , 'vpn-leaks-test') . '</p>'.
				'<p>' . __( '3. The Email leak test requires the user to send an email to the specified email address. The email header will be analyzed for IP leaks. More information is available <a target=_blank href="https://bash.ws/email-leak-test">here</a>.' , 'vpn-leaks-test') . '</p>',
		)
	);

	$current_screen->add_help_tab(
		array(
			'id'		=> 'vltp-admin-page-help-webrtc',
			'title'		=> __( 'WebRTC leak test' , 'vpn-leaks-test'),
			'content'	=>
				'<p><strong>' . __( 'Setup WebRTC leak test' , 'vpn-leaks-test') . '</strong></p>' .
				'<p>' . __( '1. Please Add new test and choose WebRTC leak test as a test type.' , 'vpn-leaks-test') . '</p>' .
				'<p>' . __( '2. Fill in the rest of the form and use the shortcut code for integration.' , 'vpn-leaks-test') . '</p>'.
				'<p>' . __( '3. The WebRTC leak test requires the user to click the "Start test" button. The test is very quick and simple. Only javascript is used to do the test (source code is included). The Internet Browser will be tested for WebRTC IP leak during the test. More information is available <a target=_blank href="https://bash.ws/webrtc-leak-test">here</a>.' , 'vpn-leaks-test') . '</p>',
		)
	);
}
