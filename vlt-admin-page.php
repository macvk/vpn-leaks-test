<?php

function vlt_admin_page() {

	$add_url = add_query_arg(array( 'page' => 'vlt-admin-page', 'action'=>'add'), admin_url( 'admin.php' ));
	$edit_url = add_query_arg(array( 'page' => 'vlt-admin-page', 'action'=>'edit', 'id'=>'1'), admin_url( 'admin.php' ));
	
	
?>
<div class="wrap">
	<div class="wrap">
<?php
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	
	if (($action != 'edit' && $action != 'add') || !empty( $_POST[VLT_NONCE] )) {
?>
		<h1 class="wp-heading-inline"><?php echo __( 'VPN leaks test configuration', 'vpn-leaks-test' ) ?></h1>
		<a href="<?php echo $add_url; ?>" class="page-title-action">Add New VPN Test</a>

		<hr class="wp-header-end">
<?php
	}
?>
		<div class="vlt-admin">
<?php

	if ( ! empty( $_POST[VLT_NONCE] ) ) {
	
		global $wpdb;
		
		$row = array();
		$row['vlt_type'] = esc_sql($_POST['vlt_type']);

		$options = array();
		$options['vlt_progress_image'] = intval($_POST['vlt_progress_image']);
		$options['vlt_start'] = $_POST['vlt_start'];
		$options['vlt_result'] = $_POST['vlt_result'];
		$options['vlt_conclusion'] = $_POST['vlt_conclusion'];
		
		$row['vlt_options'] = esc_sql(serialize($options));
		
		if (isset($_POST['vlt_id']) && $_POST['vlt_id']) {
			$wpdb->update($wpdb->prefix.'vlt',$row,array('vlt_id'=>intval($_POST['vlt_id'])));
			vlt_test_updated();
		}
		else {
			$wpdb->insert($wpdb->prefix.'vlt',$row);
			
			vlt_test_inserted();
		}

		vlt_admin_page_list();
	}
	else if ($action == 'add') {
		vlt_admin_page_edit(0);
	}
	else if ($action == 'edit') {
		vlt_admin_page_edit($id);
	}
	else if ($action == 'delete') {
		vlt_admin_page_delete($id);
		vlt_admin_page_list();
	}
	else {
		vlt_admin_page_list();
	}
?>
		</div>
	</div>
</div>
<?php	
}

function vlt_admin_page_delete($id) {
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'vlt',array('vlt_id'=>$id));
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test '.intval($id).' deleted successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vlt_test_updated() {
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test '.intval($_POST['vlt_id']).' updated successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vlt_test_inserted() {
	echo '<div class="notice notice-success is-dismissible"><p>';
	echo __('The VPN test inserted successfully', 'vpn-leaks-test');
	echo '</p></div>';
}

function vlt_admin_page_list() {
	global $wpdb;

	$total = $wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'vlt');

	if (!$total) {
		echo '<br>'.__( 'No tests was added. Please use "<b>Add New VPN test</b>" button to add new test.', 'vpn-leaks-test' );
		return;
	}

	$results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'vlt',ARRAY_A);
	
	echo '<br>';
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<tr>';
	echo '<th><b>ID</b></th><th><b>Type</b></th><th><b>Shortcode</b></th>';
	echo '</tr>';
	foreach ($results as $row) {
		echo '<tr>';
		echo '<td class="title column-title has-row-actions column-primary page-title">';
		echo $row['vlt_id'];
		echo '<div class="row-actions">';
		echo '<span class="edit"><a href="'.add_query_arg(array( 'page' => 'vlt-admin-page', 'action'=>'edit', 'id'=>$row['vlt_id']), admin_url( 'admin.php' )).'">Edit</a> | </span>';
		echo '<span class="trash"><a href="'.add_query_arg(array( 'page' => 'vlt-admin-page', 'action'=>'delete', 'id'=>$row['vlt_id']), admin_url( 'admin.php' )).'" class="submitdelete">Trash</a></span>';
		echo '</div>';
		echo '</td>';
		echo '<td>'.vlt_test_type_name($row['vlt_type']).'</td><td><span style="background:#b6f7b6;padding:0 10px 2px;color:#000">[vlt id='.$row['vlt_id'].']<span></td>';
		echo '</tr>';
	}
	echo '</table>';
	
}

function vlt_test_type_name($type) {
	if ($type == 'dns')
		return 'DNS Leak Test';

	if ($type == 'email')
		return 'Email IP Leak Test';

	if ($type == 'webrtc')
		return 'WebRTC Leak Test';

	return'';
}

function vlt_admin_page_edit($id) {

	global $wpdb;

	$id = intval($id);
	
	$types = array('dns'=>'DNS leak test','webrtc'=>'WebRTC leak test','email'=>'Email IP leak test');
	
	$row = array();	
	
	if ($id) {
		echo '<h2>'.__( 'Edit VPN Test', 'vpn-leaks-test' ).' #'.$id.'</h2>';
		$row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'vlt WHERE vlt_id='.$id,ARRAY_A);
	}
	else {
		echo '<h2>'.__( 'New VPN Test', 'vpn-leaks-test' ).'</h2>';
	}
	
	if ($row) {
		$options = unserialize(stripslashes($row['vlt_options']));
		
		$row['vlt_progress_image'] = stripslashes($options['vlt_progress_image']);
		$row['vlt_start'] = stripslashes($options['vlt_start']);
		$row['vlt_result'] = stripslashes($options['vlt_result']);	
		$row['vlt_conclusion'] = stripslashes($options['vlt_conclusion']);
	}
	else {
		$row['vlt_start'] = 'Start test';
		$row['vlt_progress_image'] = 0;
		$row['vlt_type'] = 'dns';
		$row['vlt_result'] = '<div>%ip <img src="%flag" /> %country_name %asn</div>';
		$row['vlt_conclusion'] = '<div>%text</div>';
	}	
	
	$type_select = '<select id="vlt_type" name="vlt_type">';
	
	foreach ($types as $k=>$v) {
		if ($k == $row['vlt_type']) {
			$type_select .= '<option selected="selected" value="'.$k.'">'.__( $v, 'vpn-leaks-test' ).'</option>';
		}
		else {
			$type_select .= '<option value="'.$k.'">'.__( $v, 'vpn-leaks-test' ).'</option>';
		}
	}
	
	$type_select.= '</select>';
	
	$image_id = $row['vlt_progress_image'];
	
	$progress_image_input = '';
	
	if( intval( $image_id ) > 0 ) {
		$progress_image_input .= wp_get_attachment_image( $image_id, 'medium', false, array( 'id' => 'vlt_progress_image_preview' ) );
	} 
	else {
		$progress_image_input .= vlt_default_progress_image();
	}
	
	$progress_image_input.= '<input type="hidden" name="vlt_progress_image" id="vlt_progress_image" value="'.esc_attr( $image_id ).'" class="regular-text" />';
	$progress_image_input.= '<input type="button" class="button-primary" value="'.__( 'Select image', 'vpn-leaks-test' ).'" id="vlt_progress_image_manager"/>';
	
	$id_input = '<input id="vlt_id" name="vlt_id" type="hidden" value="'.$id.'" />';
	$start_input = '<input id="vlt_start" name="vlt_start" type="text" value="'.esc_attr($row['vlt_start']).'" />';
	$result_input = '<textarea id="vlt_result" name="vlt_result" spellcheck="false">'.esc_attr($row['vlt_result']).'</textarea>';
	
	$result_help = '';
	$result_help .= 'Format characters available for "<strong>Test Results</strong>":<br>';
	$result_help .= '<br><b>%ip</b> IP address';
	$result_help .= '<br><b>%country_code</b> Country code of the IP address';
	$result_help .= '<br><b>%country_name</b> Country name of the IP address';
	$result_help .= '<br><b>%asn</b> ASN of the IP address';
	$result_help .= '<br><b>%flag</b> The URL to picture of IP Country Flag';
	
	$result_help = __( $result_help, 'vpn-leaks-test' );

	$conclusion_input = '<textarea id="vlt_conclusion" name="vlt_conclusion" spellcheck="false">'.esc_attr($row['vlt_conclusion']).'</textarea>';
	
	$conclusion_help = '';
	$conclusion_help .= 'The following format characters for "<strong>Test conclusion</strong>" are available:<br>';
	$conclusion_help .= '<br><b>%text</b> the conclusion text';
	
	$conclusion_help = __( $conclusion_help, 'vpn-leaks-test' );
	
	echo '<form id="addtest" method="post" action="" class="validate">';
	echo $id_input;
	echo wp_nonce_field( -1, VLT_NONCE );
	echo '<table class="form-table">';
	echo vlt_admin_page_edit_input('Leak Test Type',$type_select,__( 'Please choose the test type from the list', 'vpn-leaks-test' ),'vlt_type');
	echo vlt_admin_page_edit_input('Progress image',$progress_image_input,__( 'Select an image for progress bar', 'vpn-leaks-test' ),'vlt_progress_image_manager');
	echo vlt_admin_page_edit_input('Start button caption',$start_input,__( 'Type a text for <b>Start test</b> button', 'vpn-leaks-test' ),'vlt_start');
	echo vlt_admin_page_edit_input_ex('Test Results line by line HTML',$result_input,__( 'This HTML is used for formatting the test results line by line. '.$result_help, 'vpn-leaks-test' ),'vlt_start');
	echo vlt_admin_page_edit_input_ex('Test Conclusion HTML',$conclusion_input,__( 'This HTML is used for formatting the tests conclusion line by line. '.$conclusion_help, 'vpn-leaks-test' ),'vlt_conclusion');
	echo '</table>';

	if ($id) {	
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Update Test', 'vpn-leaks-test').'">';
		echo '<a href="'.add_query_arg(array( 'page' => 'vlt-admin-page'), admin_url( 'admin.php' )).'">Cancel</a></p>';
	}
	else {
		echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Add New Test', 'vpn-leaks-test').'">';
		echo '<a href="'.add_query_arg(array( 'page' => 'vlt-admin-page'), admin_url( 'admin.php' )).'">Cancel</a></p>';
	}
	echo '</form>';
	
}


function vlt_admin_page_edit_input($label,$input,$note,$id) {
	return '<tr class="form-field"><th><label for="'.$id.'">'.$label.'</label></th><td>'.$input.'<div class="description">'.$note.'</div></td></tr>';
}

function vlt_admin_page_edit_input_ex($label,$input,$note,$id) {
	$content= '<tr class="form-field form-field-help"><th><label for="'.$id.'">'.$label.'</label></th><td>'.$input.'</td></tr>';
	$content.='<tr class="help"><th></th><td>'.$note.'</td></tr>';
	return $content;
}


