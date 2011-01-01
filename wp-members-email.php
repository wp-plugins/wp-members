<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
EMAIL FUNCTIONS
*****************************************************/


function wpmem_inc_regemail($user_id,$password,$toggle)
{
	$user          = new WP_User($user_id);
	$user_login    = stripslashes($user->user_login);
	$user_email    = stripslashes($user->user_email);
	$blogname      = get_option('blogname');
	$the_permalink = $_REQUEST['redirect_to'];
	
	// NEW in 2.4 for expirations
	if (WPMEM_USE_EXP == 1) {
		$exp_type = get_user_meta($user_id, 'exp_type', 'true');
		$exp_date = get_user_meta($user_id, 'expires', 'true');
	}

	switch ($toggle) {
	
	case 0: 
		//this is a new registration
		$subj = sprintf(__('Your registration info for %s', 'wp-members'), $blogname);
		
		$body = sprintf(__('Thank you for registering for %s', 'wp-members'), $blogname)." \r\n\r\n";
		$body.= sprintf(__('Your registration information is below.', 'wp-members'))."\r\n\r\n";
		$body.= sprintf(__('You may wish to retain a copy for your records.', 'wp-members'))."\r\n\r\n";
		$body.= sprintf(__('username: %s', 'wp-members'), $user_login)."\r\n";
		$body.= sprintf(__('password: %s', 'wp-members'), $password)."\r\n\r\n";
		
		if (WPMEM_USE_EXP == 1) { $body.= "Your $exp_type will expire $exp_date \r\n\r\n"; }
		
		$body.= sprintf(__('You may login here:', 'wp-members'))."\r\n";
		$body.= "$the_permalink \r\n\r\n";
		break;
		
	case 1:
		//registration is moderated
		$subj = sprintf(__('Thank you for registering for %s', 'wp-members'), $blogname);
		
		$body = "Thank you for registering for $blogname \r\n\r\n";
		$body.= "Your registration has been received\r\n";
		$body.= "and is pending approval.\r\n\r\n";
		$body.= "You will receive login instructions\r\n";
		$body.= "upon approval of your account\r\n\r\n";
		break;

	case 2:
		//registration is moderated, user is approved
		$url  = get_option('siteurl');
		$subj = sprintf(__('Your registration for %s has been approved', 'wp-members'), $blogname);
		
		$body = "Your registration for $blogname has been approved\r\n\r\n";
		$body.= sprintf(__('Your registration information is below.', 'wp-members'))."\r\n\r\n";
		$body.= sprintf(__('You may wish to retain a copy for your records.', 'wp-members'))."\r\n\r\n";
		$body.= sprintf(__('username: %s', 'wp-members'), $user_login)."\r\n";
		$body.= sprintf(__('password: %s', 'wp-members'), $password)."\r\n\r\n";
		
		if (WPMEM_USE_EXP == 1) { $body.= "Your $exp_type will expire $exp_date \r\n\r\n"; }
		
		$body.= sprintf(__('You may login at: %s', 'wp-members'), $url)."\r\n\r\n";
		
		// new in 2.4
		$orig = get_user_meta($user_id,'wpmem_reg_url');
				// not sure about deleting this... it could be useful for some
				// delete_user_meta($user_id, 'wpmem_reg_url');
		$body.= sprintf(__('You originally registered at:', 'wp-members'))."\r\n";
		$body.= $orig[0]."\r\n\r\n";
		
		break;

	case 3:
		//this is a password reset
		$subj = sprintf(__('Password reset for %s', 'wp-members'), $blogname);
		
		$body = "Your password has been reset for $blogname \r\n\r\n";
		$body.= "Your new password is included below.\r\n\r\n";
		$body.= "You may wish to retain a copy for your records.\r\n\r\n";	
		$body.= "password: $password \r\n\r\n";
		break;
		
	}
	
	$body.= "-----------------------------------\r\n";
	$body.= "This is an automated message \r\n";
	$body.= "from $blogname\r\n";
	$body.= "Please do not reply to this address\r\n";

	// end edits for function wpmem_inc_regemail()

	wp_mail($user_email, $subj, $body, $headers = '');

}


function wpmem_notify_admin($user_id, $wpmem_fields)
{
	$user			= new WP_User($user_id);
	$blogname		= get_option('blogname');
	$the_permalink	= $_REQUEST['redirect_to'];  //NEW for 2.4
	
	$subj = sprintf(__('New user registration for %s', 'wp-members'), $blogname);
	
	$body = sprintf(__('The following user registered for %s', 'wp-members'), $blogname)."\r\n";
	
	if (WPMEM_MOD_REG == 1) { $body.= sprintf(__('and is pending admin approval', 'wp-members')."\r\n"); } 	
	
	$body.= "\r\n";
	$body.= "username: ".$user->user_login."\r\n";
	$body.= "email:    ".$user->user_email."\r\n\r\n";
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($wpmem_fields[$row][4] == 'y') {
			$name = $wpmem_fields[$row][1];
			
			if ($wpmem_fields[$row][2] != 'user_email') {
				if ($wpmem_fields[$row][2] == 'user_url') {
					$val  = $user->user_url;
				} else {
					$val  = get_user_meta($user_id,$wpmem_fields[$row][2],'true');
				}
			}
			
			$body.= "$name: $val \r\n";
		}
	}
	
	$body.= "user registered at: $the_permalink \r\n\r\n";  //NEW for 2.4
	
	if (WPMEM_MOD_REG == 1) { 
		$body.= "\r\n"."activate user: ".get_bloginfo( 'wpurl' )."/wp-admin/user-edit.php?user_id=".$user_id."\r\n"; 
	}
	
	$body.= "\r\n";
	$body.= "-----------------------------------\r\n";
	$body.= "This is an automated message \r\n";
	$body.= "from $blogname\r\n";
	$body.= "Please do not reply to this address\r\n";
	$subj = "New user registration for $blogname";
	
	$admin_email = get_option('admin_email');
	wp_mail($admin_email, $subj, $body, $headers = '');
}

?>