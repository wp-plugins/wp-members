<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
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

	switch ($toggle) {
	
	case 0: 
		//this is a new registration
		$subj = "Your registration info for $blogname";
		
		$body = "Thank you for registering for $blogname \r\n\r\n";
		$body.= "Your registration information is below.\r\n\r\n";
		$body.= "You may wish to retain a copy for your records.\r\n\r\n";
		$body.= "username: $user_login \r\n";
		$body.= "password: $password \r\n\r\n";
		$body.= "You may login here:\r\n";
		$body.= "$the_permalink \r\n\r\n";
		break;
		
	case 1:
		//registration is moderated
		$subj = "Thank you for registering for $blogname";
		
		$body = "Thank you for registering for $blogname \r\n\r\n";
		$body.= "Your registration has been received\r\n";
		$body.= "and is pending approval.\r\n\r\n";
		$body.= "You will receive login instructions\r\n";
		$body.= "upon approval of your account\r\n\r\n";
		break;

	case 2:
		//registration is moderated, user is approved
		$url  = get_option('siteurl');
		$subj = "Your registration for $blogname has been approved";
		
		$body = "Your registration for $blogname has been approved\r\n\r\n";
		$body.= "Your registration information is below.\r\n\r\n";
		$body.= "You may wish to retain a copy for your records.\r\n\r\n";
		$body.= "username: $user_login \r\n";
		$body.= "password: $password \r\n\r\n";
		$body.= "You may login at: $url \r\n\r\n";
		
		// new in 2.3.3
		$orig = get_user_meta($user_id,'wpmem_reg_url');
				delete_user_meta($user_id, 'wpmem_reg_url');
		$body.= "You originally registered at:\r\n";
		$body.= $orig[0]."\r\n\r\n";
		
		break;

	case 3:
		//this is a password reset
		$subj = "Password reset for $blogname";
		
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
	$the_permalink	= $_REQUEST['redirect_to'];  //NEW for 2.3.3
	
	$body = "The following user registered for $blogname\r\n";
	
	if (WPMEM_MOD_REG == 1) { $body.= "and is pending admin approval\r\n"; } 	
	
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
	
	$body.= "user registered at: $the_permalink \r\n\r\n";  //NEW for 2.3.3
	
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