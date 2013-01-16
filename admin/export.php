<?php
/**
 * WP-Members Export Functions
 *
 * Mananges exporting the user table to a CSV file.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2013  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2013
 */


/** 
 * WordPress Administration Bootstrap 
 */
include('../../../../wp-load.php');
include('../../../../wp-admin/includes/admin.php');


if ( !current_user_can('list_users') )
	wp_die(__('Cheatin&#8217; uh?'));

if ( !get_option('wpmembers_export') ) {
	wp_die(__('there was an error and no users were exported', 'wp-members'));
}


/**
 * Output needs to be buffered, start the buffer
 */
ob_start();


$user_arr = get_option('wpmembers_export');

header("Content-type: application/octet-stream");

// generate a filename based on date of export
$today = date("m-d-y"); 
$filename = "wp-members-user-export-".$today.".csv";
header("Content-Disposition: attachment; filename=\"$filename\"");


// get the fields
$wpmem_fields = get_option('wpmembers_fields');

// do the header row
$hrow = "User ID,Username,";
for ($row = 0; $row < count($wpmem_fields); $row++) {
		$hrow.= $wpmem_fields[$row][1].",";
}

if (WPMEM_MOD_REG == 1) {
	$hrow.= __('Activated?', 'wp-members').",";
}
if (WPMEM_USE_EXP == 1) {
	$hrow.= __('Subscription', 'wp-members').",".__('Expires', 'wp-members').",";
}

$hrow.= __('Registered', 'wp-members').",";
$hrow.= __('IP', 'wp-members');
$data = $hrow."\r\n";

// we used the fields array once,
// rewind so we can use it again
reset($wpmem_fields);

// build the data, delimit by commas, use \n switch for new line
foreach ($user_arr as $user) {

	$user_info = get_userdata($user);

 	$data.= $user_info->ID.",".$user_info->user_login.",";
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($wpmem_fields[$row][2] == 'user_email') {
			$data.= $user_info->user_email.",";
		} else {
			$data.= get_user_meta($user, $wpmem_fields[$row][2], true).",";
		}
	}
	
	if (WPMEM_MOD_REG == 1) {
		if (get_user_meta($user, 'active', 1)) {
			$data.= __('Yes', 'wp-members').",";
		} else {
			$data.= __('No', 'wp-members').",";
		}
	}

	if (WPMEM_USE_EXP ==1) {
		$data.= get_user_meta($user, "exp_type", true).",";
		$data.= get_user_meta($user, "expires", true).",";
	}
	
	$data.= $user_info->user_registered.",";
	$data.= get_user_meta($user, "wpmem_reg_ip", true);
	$data.= "\r\n";
	
	// update the user record as being exported
	update_user_meta($user, 'exported', 1);
}



echo $data; 

update_option('wpmembers_export', '');

/**
 * Clear the buffer 
 */
ob_flush();
?>