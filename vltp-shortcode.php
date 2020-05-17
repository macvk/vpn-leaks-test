<?php

/**
 * Process the [vltp] shortcode.
 *
 * @param array $attrs {
 *     Shortcode attributes. 
 *
 *     @type int $id  The VPN test ID
 * }
 *
 * @return string Content with shortcode parsed
*/
function vltp_shortcode($attrs) {

	global $wpdb;

	extract( shortcode_atts( array(
		'id' => 1,
	), $attrs ) );

	if ( !$id ) {
		return '';
	}	

	$id = intval( $id );
	
	// Retrives the Test configuration
	$row = $wpdb->get_row( 'SELECT * FROM '.$wpdb->prefix.'vltp WHERE vltp_id='.$id, ARRAY_A );
	
	if ( !$row ) {
		return '';
	}
	
	if ( !in_array( $row['vltp_type'], array( 'dns', 'email', 'webrtc' ) ) ) {
		return '';
	}
	
	$content = '';	
	
	// 
	$options = @unserialize( stripslashes( $row['vltp_options'] ) );
	
	$row['vltp_progress_image'] = isset( $options['vltp_progress_image'] ) ? stripslashes( $options['vltp_progress_image'] ) : '';
	$row['vltp_start'] = isset( $options['vltp_start'] ) ? stripslashes( $options['vltp_start'] ) : '';
	$row['vltp_result'] = isset( $options['vltp_result'] ) ? stripslashes( $options['vltp_result'] ) : '';
	$row['vltp_conclusion'] = isset( $options['vltp_conclusion'] ) ? stripslashes( $options['vltp_conclusion'] ) : '';
	
	wp_enqueue_style( 'vltp-css' );
	wp_enqueue_script( 'vltp-js' );
	
	$image = '';
	if ( $row['vltp_progress_image'] ) {
		$arr = wp_get_attachment_image_src( $row['vltp_progress_image'], 'medium', false );
		if ( isset( $arr[0] ) ) {
			$image = $arr[0];
		}
	}
	
	if ( !$image ) {
		$image = plugin_dir_url( __FILE__ ).'include/ajax-loader.gif';
	}
	
	global $wp;
	
	$script_array = array( );
	$script_array['vltp_progress_image'] = $image;
	
	$script_array['vltp_test_id'] = mt_rand( 100000000,999999999 );
	$script_array['vltp_url'] = home_url( add_query_arg( array( 'vltp_test_id'=>$script_array['vltp_test_id'] ), $wp->request ) );
	$script_array['vltp_ajax_url'] = admin_url( 'admin-ajax.php' );
	
	if ( $row['vltp_type'] == 'email' ) {
		$m = $script_array['vltp_test_id'].'@bash.ws';
		$script_array['vltp_email_message'] = sprintf( __('Please send an email to <a href="mailto:%s">%s</a>. The email subject and body doesn\'t matter. Do not refresh the page.', 'vpn-leaks-test') ,$m, $m );
	}
	
	wp_localize_script( 'vltp-js', 'vltp_settings', $script_array );
	
	$content = '<div class="vltp-test">';
	$content.= '<div class="vltp-start" data-type="'.$row['vltp_type'].'">'.$row['vltp_start'].'</div>';

	if ( isset( $_REQUEST['vltp_test_id'] ) ) {
	
		$content .= vltp_test_result( $row );
	}

	$content .= '</div>';
	
	return $content;

}

/**
 * Receives and displays the results of testing from 3rd party service bash.ws
 * 
 * @param array $row {
 *     The VPN test configuration
 *
 *     @type string $vltp_type        VPN test type
 *     @type string $vltp_result      Line by line HTML to format the VPN test result
 *     @type string $vltp_conclusion  Line by line HTML to format the VPN test conclusion
 * }
 *
 * @return string The result of testing
*/
function vltp_test_result( $row ) {
	$content = '';
	
	$test_id = isset( $_REQUEST['vltp_test_id'] ) ? intval( $_REQUEST['vltp_test_id'] ) : 0;

	if ( !$test_id ) {
		return '';
	}

	if ($row['vltp_type'] == 'dns') {
		$url = 'https://bash.ws/dnsleak/test/'.$test_id;
	}
	else if ($row['vltp_type'] == 'email') {
		$url = 'https://bash.ws/email-leak-test/test/'.$test_id;
	}
	else if ($row['vltp_type'] == 'webrtc') {
		$url = 'https://bash.ws/webrtc-leak-test/test/'.$test_id;
	}
	else {
		return '';
	}
	
	$ip = isset( $_REQUEST['ip'] ) ? $_REQUEST['ip'] : '';

	if (!$ip) {
		$ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : '';
	}
		
	if ( !$ip ) {
		$ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
	}

	if ( !$ip ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	if ( !$ip ) {	
		return '';
	}

	$data = array('ip' => $ip);

	/**
	 * Receives JSON object from the 3rd party service bash.ws
	 *
	 * @param string $ip The user IP address
	 *
	 * @return array $results { 
	 * 
	 *     @type string $type         The item type (dns, webrtc, email, ip, conclusion, info)
	 *     @type string $ip           IP address
	 *     @type int    $country      Country code of IP address 
	 *     @type string $country_name Country name of IP address
	 *     @type string $asn          ASN name of IP address
	 *
	 * }
	 *
	*/
	
	$response = wp_remote_post( esc_url_raw( $url.'?json' ), $data );
	
	$results = array();
	if ( !is_wp_error( $response ) ) {
	
		$results = @json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $results['error'] ) ) {
			$results = array();
		}
	}
	
	if ( $results ) {

		$total  = 0;
		foreach ($results as $k=>$v) {

			if ( !isset( $v['type'] ) ) {
				continue;
			}
			
			if ( $row['vltp_type'] == 'dns' ) {
				if ( $v['type'] != 'dns' ) {
					continue;
				}
			}
			else if ($row['vltp_type'] == 'email') {
				if ($v['type'] != 'mail') {
					continue;
				}
			}
			else if ($row['vltp_type'] == 'webrtc') {
				if ($v['type'] != 'webrtc') {
					continue;
				}
			}
			$total ++;
		}

		$content .= '<div class="vltp-title">';

		if ( $row['vltp_type'] == 'dns' ) {
			$content .= sprintf( _n('You use %d DNS server', 'You use %d DNS servers', $total, 'vpn-leaks-test'), $total);
		}
		else if ( $row['vltp_type'] == 'email' ) {
			$content .= sprintf( _n('Your email contains %d IP', 'Your email contains %d IPs', $total, 'vpn-leaks-test'), $total );
		}
		else if ( $row['vltp_type'] == 'webrtc' ) {
			$content .= sprintf( _n( 'WebRTC is able to see %d IP', 'WebRTC is able to see %d IPs', $total, 'vpn-leaks-test' ), $total );
		}

		$content .= '</div>';
		$content .= '<div class="vltp-results">';

		foreach ( $results as $k=>$v ) {
		
			$check_fields = array( 'country', 'country_name', 'ip', 'type');
		
			foreach ( $check_fields as $field ) {
				if ( !isset( $v[$field] ) || !$v[$field] ) {
					continue;
				}
			}
		
			if ( $row['vltp_type'] == 'dns' ) {
				if ( $v['type'] != 'dns' ) {
					continue;
				}
			}
			else if ( $row['vltp_type'] == 'email' ) {
				if ( $v['type'] != 'mail' ) {
					continue;
				}
			}
			else if ( $row['vltp_type'] == 'webrtc' ) {
				if ( $v['type'] != 'webrtc' ) {
					continue;
				}
			}
			
			$r = array();

			$r['%country_code'] = $v['country'];
			$r['%country_name'] = $v['country_name'];
			$r['%asn'] = $v['asn'];
			$r['%ip'] = $v['ip'];
			$r['%flag'] = plugin_dir_url( __FILE__ ).'include/flags/'.$v['country'].'.png';
			
			$content .= str_replace( array_keys( $r ), array_values( $r ), $row['vltp_result'] );

		}
		$content .= '</div>';
		$content .= '<div class="vltp-conclusion">';

		
		foreach ( $results as $k=>$v ) {
		
			if ( !isset( $v['type'] ) ) {
				continue;
			}

			if ( $v['type'] != 'conclusion' ) {
				continue;
			}

			$r = array();
			$r['%text'] = $v['ip'];
			$content .= str_replace( array_keys($r), array_values($r), $row['vltp_conclusion'] );
		}
		$content .= '</div>';
	}
	else {
		$content.= '<div class="vltp-results">'.__('The Test failed, please try again...','vpn-leaks-test').'</div>';
	}

	return $content;	
}

/**
 * Checks to see if the IP for testing was received and analyzed 
 *
 * @return null
*/
function vltp_test_webrtc() {

	$ips = isset($_REQUEST['ips']) ? $_REQUEST['ips'] : array();
	$ip = isset($_REQUEST['ip']) ? $_REQUEST['ip'] : '';
	
	if (!$ip) {
		$ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : '';
	}
		
	if (!$ip) {
		$ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
	}

	if (!$ip) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	}
	
	$test_id = isset( $_REQUEST['vltp_test_id'] ) ? intval( $_REQUEST['vltp_test_id'] ) : 0;
	$url = 'https://bash.ws/webrtc-leak-test/test/'.$test_id;
	
	$data = array( 'ajax' => '1', 'ips'=>$ips, 'ip'=>$ip );
	$response = wp_remote_post( esc_url_raw( $url ), $data );

	if ( !is_wp_error( $response ) ) {
		echo wp_remote_retrieve_body( $response );
	}
	
	die();
}

/**
 * Checks to see if the email for testing was received and analyzed
 *
 * @return null
*/
function vltp_test_email_check() {

	$test_id = isset( $_REQUEST['vltp_test_id'] ) ? intval( $_REQUEST['vltp_test_id'] ) : 0;
	$url = 'https://bash.ws/email-leak-test/test/'.$test_id;
	
	$data = array( 'ajax' => '1' );
	$response = wp_remote_post( esc_url_raw( $url ), $data );
	
	if ( !is_wp_error( $response ) ) {
		echo $response;
	}
	die();
}

