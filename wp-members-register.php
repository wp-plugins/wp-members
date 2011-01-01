<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
REGISTRATION FUNCTIONS
*****************************************************/


function wpmem_registration($toggle)
{
	// make sure native WP registration functions are loaded
	require_once( ABSPATH . WPINC . '/registration-functions.php');

	global $wpdb,$user_ID,$userdata,$wpmem_regchk,$wpmem_themsg,$username,$user_email,$wpmem_fieldval_arr;

	if($toggle=='register'){ $username = $_POST['log']; }
	$user_email = $_POST['user_email'];

	// build array of the posts
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		$wpmem_fieldval_arr[$row] = $_POST[$wpmem_fields[$row][2]];
	}

	// check for required fields	
	$wpmem_fields_rev = array_reverse($wpmem_fields);
	$wpmem_fieldval_arr_rev = array_reverse($wpmem_fieldval_arr);

	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ( $wpmem_fields_rev[$row][5] == 'y' ) {
			if ( !$wpmem_fieldval_arr_rev[$row] ) { $wpmem_themsg = $wpmem_fields_rev[$row][1]." ".__('is a required field')."."; }
		}
	}

	// if captcha is on, check for captcha
	if (WPMEM_CAPTCHA == 1) {
		$wpmem_captcha = get_option('wpmembers_captcha'); // get the captcha settings (api keys) 
		if ( $wpmem_captcha[0] && $wpmem_captcha[1] ) {   // if there is no api key, the captcha never displayed to the end user
			if (!$_POST["recaptcha_response_field"]) {
				$wpmem_themsg = __("you must complete the CAPTCHA form.");
			}
		}
	}
	
	// start from the assumption that it's not ok to register the user
	$ok_to_reg = false;

	switch($toggle) {

	case "register":
	
		// new in 2.3, toggle off registration
		if (WPMEM_NO_REG != 1) {

			if ( !$username ) { $wpmem_themsg = __('username is a required field', 'wp-members'); } 
			if ( $wpmem_themsg ) {

				$wpmem_regchk = "empty";

			} else {

				if (username_exists($username)) {

					$wpmem_regchk = "user";

				} else {

					$email_exists = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email = '$user_email'");
					if ( $email_exists) {

						$wpmem_regchk = "email";

					} else {

						// if captcha is on, check the captcha
							
						if (WPMEM_CAPTCHA == 1 && $wpmem_captcha[0] && $wpmem_captcha[1]) {
							
							require_once('lib/recaptchalib.php');

							$publickey  = $wpmem_captcha[0];
							$privatekey = $wpmem_captcha[1];

							// the response from reCAPTCHA
							$resp = null;
							// the error code from reCAPTCHA, if any
							$error = null;
							
							if ($_POST["recaptcha_response_field"]) {
								
								$resp = recaptcha_check_answer (
									$privatekey,
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]
								);
								
								if ($resp->is_valid) {

									$ok_to_reg = true;
			
								} else {
								
									// set the error code so that we can display it
									global $wpmem_captcha_err;
									$wpmem_captcha_err = $resp->error;
									$wpmem_captcha_err = wpmem_get_captcha_err($wpmem_captcha_err);
									$wpmem_regchk = "captcha";
																		
								}
							} 
							
							// end check recaptcha
						
						} else {
						
							$ok_to_reg = true;
						
						}
					}
				}
			}
		}
		
		// moved this in 2.3.3 to accomodate changes with captcha
		
		if ($ok_to_reg == true) {
		//everything checks out, so go ahead and insert

			//The main insertion process was taken from the WP core, the rest is modified to accomodate WP-Members user defined fields.

			$password = substr( md5( uniqid( microtime() ) ), 0, 7);
			$hashpassword = md5($password);
			$user_registered = gmdate('Y-m-d H:i:s');

			$query = "INSERT INTO $wpdb->users 
				(user_login, user_pass, user_email, user_registered, user_nicename, display_name) VALUES 
				('$username', '$hashpassword', '$user_email', '$user_registered', '$username', '$username')";

			$query = apply_filters('create_user_query', $query);
			$wpdb->query( $query );
			$user_id = $wpdb->insert_id;

			//Sets the user to the default role.
			$user = new WP_User($user_id);
			$user->set_role(get_option('default_role'));

			update_user_meta( $user_id, 'nickname', $username); // gotta have this whether it's used or not; if it's included w/ custom, value should be overwritten below.
			for ($row = 0; $row < count($wpmem_fields); $row++) {

				/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
				  they go into the wp_users table.  email is already done above, we need to then screen for putting in 
				  website, if used, and screen out email, since it's already done. */
				if ($wpmem_fields[$row][2] == 'user_url') {
					$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_id) );
				} else {
					if ($wpmem_fields[$row][2] != 'user_email') {
						// new in 2.4 - code improvement
						// check to see if we are using this field or not
						if ( $wpmem_fields[$row][4] == 'y' ) {
							update_user_meta( $user_id, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);
						}
					}
				}
			} 
			
			// new in 2.4 - capture IP address of user at registration
			update_user_meta( $user_id, 'wpmem_reg_ip', $_SERVER['REMOTE_ADDR'] );
			
			// NEW in 2.4 - if registration is moderated, we will store
			// the registration url for sending when approved
			if ( WPMEM_MOD_REG == 1 ) {
				$the_permalink = $_REQUEST['redirect_to'];
				update_user_meta( $user_id, 'wpmem_reg_url', $the_permalink );
			}

			// new in 2.4 for user expiration
			if ( WPMEM_USE_EXP == 1 && WPMEM_MOD_REG != 1 ) { wpmem_set_exp($user_id); }
			
			require_once('wp-members-email.php');

			//if this was successful, and you have email properly
			//configured, send a notification email to the user
			wpmem_inc_regemail($user_id,$password,WPMEM_MOD_REG);
			
			//notify admin of new reg, if needed;
			if (WPMEM_NOTIFY_ADMIN == 1) { wpmem_notify_admin($user_id, $wpmem_fields); }

			// successful registration message
			$wpmem_regchk = "success";

		}

		break;

	case "update":

		if ( $wpmem_themsg ) {

			$wpmem_regchk = "updaterr";

		} else {

			for ($row = 0; $row < count($wpmem_fields); $row++) {

				/*there are two native wp fields that throw a sticky wicket into our clean array - email and website.
				  they go into the wp_users table.  we need to then screen for these and put them in a different way*/
				switch ($wpmem_fields[$row][2]) {

				case ('user_url'):
					$wpdb->update( $wpdb->users, array('user_url'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				case ('user_email'):
					$wpdb->update( $wpdb->users, array('user_email'=>$wpmem_fieldval_arr[$row]), array('ID'=>$user_ID) );
					break;

				default:
					update_user_meta( $user_ID, $wpmem_fields[$row][2], $wpmem_fieldval_arr[$row]);
					break;
				}
			} 

			$wpmem_regchk = "editsuccess";

		}

		break;

	}

} // end registration function


// new captcha error function
function wpmem_get_captcha_err($wpmem_captcha_err)
{
	switch ($wpmem_captcha_err) {
	
	case "invalid-site-public-key":
		$wpmem_captcha_err = __('We were unable to validate the public key.', 'wp-members');
		break;
		
	case "invalid-site-public-key":
		$wpmem_captcha_err = __('We were unable to validate the private key.', 'wp-members');
		break;
	
	case "invalid-request-cookie":
		$wpmem_captcha_err = __('The challenge parameter of the verify script was incorrect.', 'wp-members');
		break;
		
	case "incorrect-captcha-sol":
		$wpmem_captcha_err = __('The CAPTCHA solution was incorrect.', 'wp-members');
		break;
	
	case "verify-params-incorrect":
		$wpmem_captcha_err = __('The parameters to verify were incorrect', 'wp-members');
		break;
		
	case "invalid-referrer":
		$wpmem_captcha_err = __('reCAPTCHA API keys are tied to a specific domain name for security reasons.', 'wp-members');
		break;
		
	case "recaptcha-not-reachable":
		$wpmem_captcha_err = __('The reCAPTCHA server was not reached.  Please try to resubmit.', 'wp-members');
		break;
	}
	
	return $wpmem_captcha_err;
}

?>