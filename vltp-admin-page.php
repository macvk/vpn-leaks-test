<?php

function vltp_admin_page() {

	$add_url = add_query_arg(array( 'page' => 'vltp-admin-page', 'action'=>'add'), admin_url( 'admin.php' ));
	$edit_url = add_query_arg(array( 'page' => 'vltp-admin-page', 'action'=>'edit', 'id'=>'1'), admin_url( 'admin.php' ));
	
	
?>
<div class="wrap">
	<div class="wrap">
<?php
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$test_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	
	if (($action != 'edit' && $action != 'add') || !empty( $_POST[VLTP_NONCE] )) {
?>
		<h1 class="wp-heading-inline"><?php echo __( 'VPN leaks test configuration', 'vpn-leaks-test' ) ?></h1>
		<a href="<?php echo $add_url; ?>" class="page-title-action">Add New VPN Test</a>

		<hr class="wp-header-end">
<?php
	}
?>
		<div class="vltp-admin">
<?php

	if ( ! empty( $_POST[VLTP_NONCE] ) ) {
	
		global $wpdb;
		
		$row = array();
		$row['vltp_type'] = esc_sql($_POST['vltp_type']);
		
		$options = array();
		$options['vltp_progress_image'] = intval( $_POST['vltp_progress_image'] );
		$options['vltp_start'] = isset( $_POST['vltp_start'] ) ? $_POST['vltp_start'] : '';
		$options['vltp_result'] = isset( $_POST['vltp_result'] ) ? $_POST['vltp_result'] : '';
		$options['vltp_conclusion'] = isset( $_POST['vltp_conclusion'] ) ? $_POST['vltp_conclusion'] : '';
		
		$row['vltp_options'] = esc_sql(serialize($options));
		
		if ($test_id) {
			$wpdb->update( $wpdb->prefix.'vltp', $row, array( 'vltp_id' => $test_id ) );
			vltp_test_updated();
		}
		else {
			$wpdb->insert( $wpdb->prefix.'vltp', $row );

			vltp_test_inserted();
		}
		
		vltp_admin_page_list();
	}
	else if ($action == 'add') {
		vltp_admin_page_edit( 0 );
	}
	else if ($action == 'edit') {
		vltp_admin_page_edit( $test_id );
	}
	else if ($action == 'delete') {
		vltp_admin_page_delete( $test_id );
		vltp_admin_page_list();
	}
	else {
		vltp_admin_page_list();
	}
?>
		</div>
	</div>
</div>
<?php	
}

function vltp_admin_page_delete( $id ) {
	global $wpdb;
	$wpdb->delete( $wpdb->prefix.'vlt',array( 'vltp_id'=>$id ) );
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test '.intval($id).' deleted successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vltp_test_updated() {
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test '.intval($_POST['vltp_id']).' updated successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vltp_test_inserted() {
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test inserted successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vltp_admin_page_list() {
	global $wpdb;

	$total = $wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'vltp');

	if (!$total) {
		echo '<br>'.__( 'No tests was added. Please use "<b>Add New VPN test</b>" button to add new test.', 'vpn-leaks-test' );
		return;
	}

	$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'vltp',ARRAY_A);
	
	echo '<br>';
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<tr>';
	echo '<th><b>ID</b></th><th><b>Type</b></th><th><b>Shortcode</b></th>';
	echo '</tr>';
	foreach ($results as $row) {
		echo '<tr>';
		echo '<td class="title column-title has-row-actions column-primary page-title">';
		echo $row['vltp_id'];
		echo '<div class="row-actions">';
		echo '<span class="edit"><a href="'.add_query_arg(array( 'page' => 'vltp-admin-page', 'action'=>'edit', 'id'=>$row['vltp_id']), admin_url( 'admin.php' )).'">Edit</a> | </span>';
		echo '<span class="trash"><a href="'.add_query_arg(array( 'page' => 'vltp-admin-page', 'action'=>'delete', 'id'=>$row['vltp_id']), admin_url( 'admin.php' )).'" class="submitdelete">Trash</a></span>';
		echo '</div>';
		echo '</td>';
		echo '<td>'.vltp_test_type_name($row['vltp_type']).'</td><td><span style="background:#b6f7b6;padding:0 10px 2px;color:#000">[vltp id='.$row['vltp_id'].']<span></td>';
		echo '</tr>';
	}
	echo '</table>';
	
}

function vltp_test_type_name($type) {
	$names = array();
	$names['dns'] = 'DNS Leak Test';
	$names['email'] = 'Email IP Leak Test';
	$names['webrtc'] = 'WebRTC Leak Test';
	
	return isset($names[$type]) ? $names[$type] : '';
}

function vltp_admin_page_edit($id) {

	global $wpdb;

	$id = intval($id);
	
	$types = array('dns'=>'DNS leak test','webrtc'=>'WebRTC leak test','email'=>'Email IP leak test');
	
	$row = array();	
	
	if ($id) {
		echo '<h2>'.__( 'VPN Test', 'vpn-leaks-test' ).' #'.$id.'</h2>';
		$row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'vltp WHERE vltp_id='.$id,ARRAY_A);
	}
	else {
		echo '<h2>'.__( 'New VPN Test', 'vpn-leaks-test' ).'</h2>';
	}
	
	if ($row) {
		$options = unserialize(stripslashes($row['vltp_options']));
		
		$row['vltp_progress_image'] = stripslashes($options['vltp_progress_image']);
		$row['vltp_start'] = stripslashes($options['vltp_start']);
		$row['vltp_result'] = stripslashes($options['vltp_result']);	
		$row['vltp_conclusion'] = stripslashes($options['vltp_conclusion']);
	}
	else {
		$row['vltp_start'] = 'Start test';
		$row['vltp_progress_image'] = 0;
		$row['vltp_type'] = 'dns';
		$row['vltp_result'] = '<div>%ip <img src="%flag" /> %country_name %asn</div>';
		$row['vltp_conclusion'] = '<div>%text</div>';
	}	
	
	$type_select = '<select id="vltp_type" name="vltp_type">';
	
	foreach ($types as $k=>$v) {
		if ($k == $row['vltp_type']) {
			$type_select .= '<option selected="selected" value="'.$k.'">'.__( $v, 'vpn-leaks-test' ).'</option>';
		}
		else {
			$type_select .= '<option value="'.$k.'">'.__( $v, 'vpn-leaks-test' ).'</option>';
		}
	}
	
	$type_select.= '</select>';
	
	$image_id = $row['vltp_progress_image'];
	
	$progress_image_input = '';
	
	if( intval( $image_id ) > 0 ) {
		$progress_image_input .= wp_get_attachment_image( $image_id, 'medium', false, array( 'id' => 'vltp_progress_image_preview' ) );
	} 
	else {
		$progress_image_input .= vltp_default_progress_image();
	}
	
	$progress_image_input.= '<input type="hidden" name="vltp_progress_image" id="vltp_progress_image" value="'.esc_attr( $image_id ).'" class="regular-text" />';
	$progress_image_input.= '<input type="button" class="button-primary" value="'.__( 'Select image', 'vpn-leaks-test' ).'" id="vltp_progress_image_manager"/>';
	
	$id_input = '<input id="vltp_id" name="vltp_id" type="hidden" value="'.$id.'" />';
	$start_input = '<input id="vltp_start" name="vltp_start" type="text" value="'.esc_attr($row['vltp_start']).'" />';
	$result_input = '<textarea id="vltp_result" name="vltp_result" spellcheck="false">'.esc_attr($row['vltp_result']).'</textarea>';
	
	$result_help = '';
	$result_help .= 'Format characters available for "<strong>Test Results</strong>":<br>';
	$result_help .= '<br><b>%ip</b> IP address';
	$result_help .= '<br><b>%country_code</b> Country code of the IP address';
	$result_help .= '<br><b>%country_name</b> Country name of the IP address';
	$result_help .= '<br><b>%asn</b> ASN of the IP address';
	$result_help .= '<br><b>%flag</b> The URL to picture of IP Country Flag';
	
	$result_help = __( $result_help, 'vpn-leaks-test' );

	$conclusion_input = '<textarea id="vltp_conclusion" name="vltp_conclusion" spellcheck="false">'.esc_attr($row['vltp_conclusion']).'</textarea>';
	
	$conclusion_help = '';
	$conclusion_help .= 'The following format characters for "<strong>Test conclusion</strong>" are available:<br>';
	$conclusion_help .= '<br><b>%text</b> the conclusion text';
	
	$conclusion_help = __( $conclusion_help, 'vpn-leaks-test' );
	
	echo '<form id="addtest" method="post" action="" class="validate">';
	echo $id_input;
	echo wp_nonce_field( -1, VLTP_NONCE );
	echo '<table class="form-table">';
	echo vltp_admin_page_edit_input('Leak Test Type',$type_select,__( 'Please choose the test type from the list', 'vpn-leaks-test' ),'vltp_type');
	echo vltp_admin_page_edit_input('Progress image',$progress_image_input,__( 'Select an image for progress bar', 'vpn-leaks-test' ),'vltp_progress_image_manager');
	echo vltp_admin_page_edit_input('Start button caption',$start_input,__( 'Type a text for <b>Start test</b> button', 'vpn-leaks-test' ),'vltp_start');
	echo vltp_admin_page_edit_input_ex('Test Results line by line HTML',$result_input,__( 'This HTML is used for formatting the test results line by line. '.$result_help, 'vpn-leaks-test' ),'vltp_start');
	echo vltp_admin_page_edit_input_ex('Test Conclusion HTML',$conclusion_input,__( 'This HTML is used for formatting the tests conclusion line by line. '.$conclusion_help, 'vpn-leaks-test' ),'vltp_conclusion');
	echo '</table>';

	if ($id) {	
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Update Test', 'vpn-leaks-test').'">';
		echo '<a href="'.add_query_arg(array( 'page' => 'vltp-admin-page'), admin_url( 'admin.php' )).'">Cancel</a></p>';
	}
	else {
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Add New Test', 'vpn-leaks-test').'">';
		echo '<a href="'.add_query_arg(array( 'page' => 'vltp-admin-page'), admin_url( 'admin.php' )).'">Cancel</a></p>';
	}
	echo '</form>';
	
}


function vltp_admin_page_edit_input($label,$input,$note,$id) {
	return '<tr class="form-field"><th><label for="'.$id.'">'.$label.'</label></th><td>'.$input.'<div class="description">'.$note.'</div></td></tr>';
}

function vltp_admin_page_edit_input_ex($label,$input,$note,$id) {
	$content= '<tr class="form-field form-field-help"><th><label for="'.$id.'">'.$label.'</label></th><td>'.$input.'</td></tr>';
	$content.='<tr class="help"><th></th><td>'.$note.'</td></tr>';
	return $content;
}


