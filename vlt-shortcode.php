<?php

function vlt_shortcode($attrs) {

	global $wpdb;

	extract(shortcode_atts(array(
		'id' => 1,
	), $attrs));

	if (!$id) {
		return '';
	}	

	$id = intval($id);

	$row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'vlt WHERE vlt_id='.$id,ARRAY_A);

	if (!$row) {
		return '';
	}

	if (!in_array($row['vlt_type'], array('dns','email','webrtc'))) {
		return '';
	}

	$content = '';	
	
	$options = unserialize(stripslashes($row['vlt_options']));
	
	$row['vlt_progress_image'] = stripslashes($options['vlt_progress_image']);
	$row['vlt_start'] = stripslashes($options['vlt_start']);
	$row['vlt_result'] = stripslashes($options['vlt_result']);	
	$row['vlt_conclusion'] = stripslashes($options['vlt_conclusion']);

	wp_enqueue_style( 'vlt-css' );
	wp_enqueue_script( 'vlt-js' );
	
	$image = '';
	if ($row['vlt_progress_image']) {
		$arr = wp_get_attachment_image_src( $row['vlt_progress_image'], 'medium', false);
		if (isset($arr[0])) {
			$image = $arr[0];
		}
	}
	
	if (!$image) {
		$image = plugin_dir_url(__FILE__).'include/ajax-loader.gif';
	}
	

	global $wp;

	$script_array = array( );
	$script_array['vlt_progress_image'] = $image;

	$script_array['vlt_test_id'] = mt_rand(100000000,999999999);
	$script_array['vlt_url'] = home_url(add_query_arg(array('vlt_test_id'=>$script_array['vlt_test_id']), $wp->request));
	$script_array['vlt_ajax_url'] = admin_url( 'admin-ajax.php' );
	
	if ($row['vlt_type'] == 'email') {
		$m = $script_array['vlt_test_id'].'@bash.ws';
		$script_array['vlt_email_message'] = __('Please send an email to <a href="mailto:'.$m.'">'.$m.'</a>. The email subject and body doesn\'t matter. Do not refresh the page.','vpn-leaks-test');
	}
	
	wp_localize_script( 'vlt-js', 'vlt_settings', $script_array );
	
	$content = '<div class="vlt-test">';
	$content.= '<div class="vlt-start" data-type="'.$row['vlt_type'].'">'.$row['vlt_start'].'</div>';

	if (isset($_REQUEST['vlt_test_id'])) {

		$content .= vlt_test_result($row);
	}

	$content.= '</div>';
	
	return $content;

}

function vlt_test_result($row) {
	$content = '';

	if ($row['vlt_type'] == 'dns') {
		$url = 'https://bash.ws/dnsleak/test/'.intval($_REQUEST['vlt_test_id']);
	}
	else if ($row['vlt_type'] == 'email') {
		$url = 'https://bash.ws/email-leak-test/test/'.intval($_REQUEST['vlt_test_id']);
	}
	else if ($row['vlt_type'] == 'webrtc') {
		$url = 'https://bash.ws/webrtc-leak-test/test/'.intval($_REQUEST['vlt_test_id']);
	}
	else {
		return '';
	}

	$ip = isset($_REQUEST['ip']) ? $_REQUEST['ip'] : '';

	if (!$ip) {
		$ip = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:'';
	}
		
	if (!$ip) {
		$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'';
	}

	if (!$ip) {
		$ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	}

	$data = array('ip' => $ip);

	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$context  = stream_context_create($options);
	
	$results = @file_get_contents($url.'?json',false,$context);

	$results = @json_decode($results,true);	

	if (isset($results['error'])) {
		$results= array();
	}

	if ($results) {
		

		$total  = 0;
		foreach ($results as $k=>$v) {

			if (!isset($v['type'])) {
				continue;
			}

			if ($row['vlt_type'] == 'dns') {
				if ($v['type'] != 'dns') {
					continue;
				}
			}
			else if ($row['vlt_type'] == 'email') {
				if ($v['type'] != 'mail') {
					continue;
				}
			}
			else if ($row['vlt_type'] == 'webrtc') {
				if ($v['type'] != 'webrtc') {
					continue;
				}
			}
			$total ++;
		}

		$content .= '<div class="vlt-title">';
		if ($row['vlt_type'] == 'dns') {
			$content .= 'You use '.$total.' DNS servers';
		}
		else if ($row['vlt_type'] == 'email') {
			$content .= 'Your email contains '.$total.' IPs';
		}
		else if ($row['vlt_type'] == 'webrtc') {
			$content .= 'WebRTC is able to see '.$total.' IPs';
		}
		$content .= '</div>';
		$content .= '<div class="vlt-results">';
		foreach ($results as $k=>$v) {

			if (!isset($v['type'])) {
				continue;
			}
		
			if ($row['vlt_type'] == 'dns') {
				if ($v['type'] != 'dns') {
					continue;
				}
			}
			else if ($row['vlt_type'] == 'email') {
				if ($v['type'] != 'mail') {
					continue;
				}
			}
			else if ($row['vlt_type'] == 'webrtc') {
				if ($v['type'] != 'webrtc') {
					continue;
				}
			}

			$r = array();
			$r['%country_code'] = $v['country'];
			$r['%country_name'] = $v['country_name'];
			$r['%asn'] = $v['asn'];
			$r['%ip'] = $v['ip'];
			$r['%flag'] = plugin_dir_url(__FILE__).'include/flags/'.$v['country'].'.png';
			$content .= str_replace(array_keys($r),array_values($r),$row['vlt_result']);
		}
		$content .= '</div>';
		$content .= '<div class="vlt-conclusion">';


		foreach ($results as $k=>$v) {

			if (!isset($v['type'])) {
				continue;
			}

			if ($v['type'] != 'conclusion') {
				continue;
			}

			$r = array();
			$r['%text'] = $v['ip'];
			$content .= str_replace(array_keys($r),array_values($r),$row['vlt_conclusion']);
		}
		$content .= '</div>';
	}
	else {
		$content.= '<div class="vlt-results">'.__('The Test failed, please try again...','vpn-leaks-test').'</div>';
	}
	return $content;

}

function vlt_test_webrtc() {

	$ips = isset($_REQUEST['ips']) ? $_REQUEST['ips'] : array();
	$ip = isset($_REQUEST['ip']) ? $_REQUEST['ip'] : '';

	if (!$ip) {
		$ip = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:'';
	}
		
	if (!$ip) {
		$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'';
	}

	if (!$ip) {
		$ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
	}

	$data = array('ajax' => '1', 'ips'=>$ips, 'ip'=>$ip);
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$context  = stream_context_create($options);
	$content = @file_get_contents('https://bash.ws/webrtc-leak-test/test/'.intval($_REQUEST['vlt_test_id']), false, $context);
	echo $content;
	die();
}

function vlt_test_email_check() {

	$data = array('ajax' => '1');

	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);

	$context  = stream_context_create($options);
	$content = @file_get_contents('https://bash.ws/email-leak-test/test/'.intval($_REQUEST['vlt_test_id']), false, $context);
	echo $content;
	die();
}

