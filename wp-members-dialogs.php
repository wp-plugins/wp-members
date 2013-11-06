<?php
/**
 * WP-Members Dialog Functions
 *
 * Handles functions that output front-end dialogs to end users.
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


if ( ! function_exists( 'wpmem_inc_login' ) ):
/**
 * Login Dialog
 *
 * Loads the login form for user login
 *
 * @since 1.8
 *
 * @uses apply_filters Calls wpmem_restricted_msg filter to change the restricted post message content
 * @uses wpmem_login_form()
 *
 * @param  string $page
 * @return string $str the generated html for the login form
 */
function wpmem_inc_login( $page="page" )
{ 	
	global $wpmem_regchk;

	$str = '';
	$arr = get_option( 'wpmembers_dialogs' );

	if( $page == "page" ){
	     if( $wpmem_regchk!="success" ){
		
			// this shown above blocked content
			$str = '<p>' . stripslashes($arr[0]) . '</p>';
			
			// filter blocked content message
			$str = apply_filters( 'wpmem_restricted_msg', $str );

		} 	
	} 

    $arr = array( __( 'Existing Users Login', 'wp-members' ), __( 'Username', 'wp-members' ), 'text', 'log', __( 'Password', 'wp-members' ), 'password', 'pwd', 'login', __( 'Login', 'wp-members' ), 'username', 'password' );
	
	$str = $str . wpmem_login_form( $page, $arr );
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_changepassword' ) ):
/**
 * Change Password Dialog
 *
 * Loads the form for changing password.
 *
 * @since 2.0
 *
 * @uses wpmem_login_form()
 *
 * @return string $str the generated html for the change password form
 */
function wpmem_inc_changepassword()
{ 
	$arr = array(__('Change Password', 'wp-members'), __('New Password', 'wp-members'), 'password', 'pass1', __('Repeat Password', 'wp-members'), 'password', 'pass2', 'pwdchange', __('Update Password', 'wp-members'), 'password', 'password');
    $str = wpmem_login_form( 'page', $arr );
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_resetpassword' ) ):
/**
 * Reset Password Dialog
 *
 * Loads the form for resetting password.
 *
 * @since 2.1
 *
 * @uses wpmem_login_form()
 *
 * @return string $str the generated html fo the reset password form
 */
function wpmem_inc_resetpassword()
{ 
	$arr = array(__('Reset Forgotten Password', 'wp-members'), __('Username', 'wp-members'), 'text', 'user', __('Email', 'wp-members'), 'text', 'email', 'pwdreset', __('Reset Password', 'wp-members'), 'username', 'textbox');
    $str = wpmem_login_form( 'page', $arr );
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_loginfailed' ) ):
/**
 * Login Failed Dialog
 *
 * Returns the login failed error message.
 *
 * @since 1.8
 *
 * @uses apply_filters Calls wpmem_login_failed filter to change the failed login message
 *
 * @return string $str the generated html for the login failed message
 */
function wpmem_inc_loginfailed() 
{ 
	$str = '<div align="center" id="wpmem_msg">
		<h2>' . __('Login Failed!', 'wp-members') . '</h2>
		<p>' . __('You entered an invalid username or password.', 'wp-members') . '</p>
		<p><a href="' . $_SERVER['REQUEST_URI'] . '">' . __('Click here to continue.', 'wp-members') . '</a></p>
	</div>';
	
	$str = apply_filters( 'wpmem_login_failed', $str );

	return $str;
}
endif;


if( ! function_exists( 'wpmem_inc_memberlinks' ) ):
/**
 * Member Links Dialog
 *
 * Outputs the links used on the members area.
 *
 * @since 2.0
 *
 * @uses apply_filters Calls 'wpmem_logout_link' filter to change the logout link
 * @uses apply_filters Calls 'wpmem_member_links' filter to change the links shown on the logged in state of the user profile page (user-profile/members-area shortcode page)
 * @uses apply_filters Calls 'wpmem_register_links' filter to change the links shown on the logged in state of the registration form (shortcode page)
 * @uses apply_filters Calls 'wpmem_login_links' filter to change the links shown on the logged in state of the login form (shortcode page)
 *
 * @param  string $page
 * @return string $str
 */
function wpmem_inc_memberlinks( $page = 'members' ) 
{
	global $user_login; 
	
	$link = wpmem_chk_qstr();
	
	$logout = apply_filters( 'wpmem_logout_link', $link . 'a=logout' );
	
	switch( $page ) {
	
	case 'members':
		$str  = '<ul><li><a href="'  .$link . 'a=edit">' . __( 'Edit My Information', 'wp-members' ) . '</a></li>
				<li><a href="' . $link . 'a=pwdchange">' . __( 'Change Password', 'wp-members' ) . '</a></li>';
		if( WPMEM_USE_EXP == 1 && function_exists( 'wpmem_user_page_detail' ) ) { $str .= wpmem_user_page_detail(); }
		$str.= '</ul>';
		$str = apply_filters( 'wpmem_member_links', $str );
		break;
		
	case 'register':	
		$str = '<p>' . sprintf( __( 'You are logged in as %s', 'wp-members' ), $user_login ) . '</p>
			<ul>
				<li><a href="' . $logout . '">' . __( 'Click here to logout.', 'wp-members' ) . '</a></li>
				<li><a href="' . get_option('home') . '">' . __( 'Begin using the site.', 'wp-members' ) . '</a></li>
			</ul>';
		$str = apply_filters( 'wpmem_register_links', $str );
		break;	
	
	case 'login':

		$str = '<p>
		  	' . sprintf( __( 'You are logged in as %s', 'wp-members' ), $user_login ) . '<br />
		  	<a href="' . $logout . '">' . __( 'click here to logout', 'wp-members' ) . '</a>
			</p>';
		$str = apply_filters( 'wpmem_login_links', $str );
		break;	
			
	case 'status':
		$str ='<p>
			' . sprintf( __( 'You are logged in as %s', 'wp-members' ), $user_login ) . '  | 
			<a href="' . $logout . '">' . __( 'click here to logout', 'wp-members' ) . '</a>
			</p>';
		break;
	
	}
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_inc_regmessage' ) ):
/**
 * Message Dialog
 *
 * Returns various dialogs and error messages.
 *
 * @since 1.8
 *
 * @uses apply_filters Calls the wpmem_msg_dialog filter to filter the message dialog
 *
 * @param  string $toggle error message toggle to look for specific error messages
 * @param  string $msg a message that has no toggle that is passed directly to the function
 * @return string $str
 */
function wpmem_inc_regmessage( $toggle, $msg='' )
{ 
	$wpmem_dialogs = get_option('wpmembers_dialogs');
	$arr = array( 'user', 'email', 'success', 'editsuccess', 'pwdchangerr', 'pwdchangesuccess', 'pwdreseterr', 'pwdresetsuccess' );

	$str = '<div class="wpmem_msg" align="center">
		<p>&nbsp;</p>
		<p><b>';

	for( $r = 0; $r < count( $arr ); $r++ ) {

		if( $toggle == $arr[$r] ) {

			$str = $str . stripslashes( $wpmem_dialogs[$r+1] ) . '</b></p>
				<p>&nbsp;</p>
			</div>';
			
			$str = apply_filters( 'wpmem_msg_dialog', $str );

			return $str;
		}
	}

	$str = $str . stripslashes( $msg ) . '</b></p>
		<p>&nbsp;</p>
		</div>';

	$str = apply_filters( 'wpmem_msg_dialog', $str );

	return $str;

}
endif;


if ( ! function_exists( 'wpmem_inc_registration' ) ):
/**
 * Registration Form Include
 *
 * Calls the appropriate set of forms and passes back string containing the form
 *
 * @since 2.5.1
 *
 * @uses wpmem_inc_registration_NEW()
 * @uses wpmem_inc_registration_OLD()
 *
 * @param  var    $toggle
 * @param  string $heading
 * @return string
 */
function wpmem_inc_registration( $toggle = 'new', $heading = '' )
{
	if ( WPMEM_OLD_FORMS != 1 ) { 
		$str = wpmem_inc_registration_NEW( $toggle, $heading );
	} else {
		include_once( 'wp-members-deprecated.php' );
		$str = wpmem_inc_registration_OLD( $toggle, $heading );
	}
	
	return $str;
}
endif;


if ( ! function_exists( 'wpmem_login_form' ) ):
/**
 * Login Form Include
 *
 * Calls the appropriate set of forms.
 *
 * @since 2.5.1
 *
 * @uses wpmem_login_form_NEW()
 * @uses wpmem_login_form_OLD()
 *
 * @param  string $page 
 * @param  array  $arr array of the login form pieces
 * @return string $str the html of the form in $str
 */
function wpmem_login_form( $page, $arr ) 
{
	if ( WPMEM_OLD_FORMS != 1 ) { 
		$str = wpmem_login_form_NEW( $page, $arr );
	} else {
		include_once( 'wp-members-deprecated.php' );
		$str = wpmem_login_form_OLD( $page, $arr );
	}
	
	return $str;
}
endif;


if( ! function_exists( 'wpmem_inc_registration_NEW' ) ):
/**
 * Registration Form Dialog
 *
 * Outputs the table-less form for new user
 * registration and existing user edits.
 *
 * @since 2.5.1
 *
 * @uses apply_filters Calls 'wpmem_register_form_before' filter to add any string to the beginning of the registration form
 * @uses apply_filters Calls 'wpmem_register_form' filter to change any element of the generated HTML string of the registration form
 * @uses apply_filters Calls 'wpmem_register_heading' filter to change the heading of the registration form
 * @uses apply_filters Calls 'wpmem_tos_link_txt' filter to change the text (and link) for the TOS (Terms of Service) acknowledgement
 *
 * @param  string $toggle
 * @param  string $heading
 * @global string $wpmem_regchk
 * @global array  $userdata
 * @return string $form
 */
function wpmem_inc_registration_NEW( $toggle = 'new', $heading = '' )
{
	// fix the wptexturize
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_content', 'wptexturize' );
	add_filter( 'the_content', 'wpmem_texturize', 99 );
	
	global $wpmem_regchk, $userdata; 

	$heading = ( !$heading ) ? apply_filters( 'wpmem_register_heading', __( 'New Users Registration', 'wp-members' ) ) : $heading;

	$form = apply_filters( 'wpmem_register_form_before', '' );

	$form.= '[wpmem_txt]<div id="wpmem_reg">
		<a name="register"></a>
	<form name="form" method="post" action="' . get_permalink() . '" class="form">'; 

	$form = ( defined( 'WPMEM_USE_NONCE' ) ) ? $form . wp_nonce_field( 'wpmem-validate-submit', 'wpmem-form-submit' ) : $form;
	
	$form.= '	<fieldset>
			<legend>' . $heading . '</legend>';

	if( $toggle == 'edit' ) {

		$form = $form . '<label for="username" class="text">' . __( 'Username', 'wp-members' ) . '</label>
			<div class="div_text"><p class="noinput">' . $userdata->user_login . '</p></div>';

	} else { 

		$val  = ( $_POST ) ? stripslashes( $_POST['log'] ) : '';
		$form = $form . '<label for="username" class="text">' . __( 'Choose a Username', 'wp-members' ) . '<font class="req">*</font></label>
			<div class="div_text">
				<input name="log" type="text" value="' . $val . '" class="username" id="username" />
			</div>';

	}

	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ )
	{ 
		$do_row = ( $toggle == 'edit' && $wpmem_fields[$row][2] == 'password' ) ? false : true;
		
		if( $wpmem_fields[$row][2] == 'tos' && $toggle == 'edit' && ( get_user_meta($userdata->ID, 'tos', true ) ) ) { 
			// makes tos field hidden on user edit page, unless they haven't got a value for tos
			$do_row = false; 
			$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], 'hidden', get_user_meta( $userdata->ID, 'tos', true ) );
		}

		if( $wpmem_fields[$row][4] == 'y' && $do_row == true ) {

			if( $wpmem_fields[$row][2] != 'tos' ) {

				$class = ( $wpmem_fields[$row][3] == 'password' ) ? 'text' : $wpmem_fields[$row][3];
				
				$form = $form . '<label for="' . $wpmem_fields[$row][2] . '" class="' . $class . '">' . $wpmem_fields[$row][1];
				$form = ( $wpmem_fields[$row][5] == 'y' ) ? $form . '<font class="req">*</font>' : $form;
				$form = $form . '</label>';

			} 

			$form = $form . '<div class="div_' . $class . '">';

			if( ( $toggle == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) { 

				$form = ( WPMEM_DEBUG == true ) ? $form . $wpmem_fields[$row][2] . "&nbsp;" : $form;

				switch( $wpmem_fields[$row][2] ) {
					case( 'description' ):
						$val = htmlspecialchars( get_user_meta( $userdata->ID, 'description', 'true' ) );
						break;

					case( 'user_email' ):
						$val = $userdata->user_email;
						break;

					case( 'user_url' ):
						$val = esc_url( $userdata->user_url );
						break;

					default:
						$val = htmlspecialchars( get_user_meta( $userdata->ID, $wpmem_fields[$row][2], 'true' ) );
						break;
				}

			} else {

				$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';

			}

			if( $wpmem_fields[$row][2] == 'tos' ) { 

				if( ( $toggle == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) {
					$chk_tos;  // HUH?
				} else {
					$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
				}

				// should be checked by default? and only if form hasn't been submitted
				$val = ( ! $_POST && $wpmem_fields[$row][8] == 'y' ) ? $wpmem_fields[$row][7] : $val;

				$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );

				$form = ( $wpmem_fields[$row][5] == 'y' ) ? $form . '<font class="req">*</font>' : $form;

				// determine if TOS is a WP page or not...
				$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
				if( stristr( $tos_content, '[wp-members page="tos"' ) ) {
					
					$tos_content = " " . $tos_content;
					$ini = strpos( $tos_content, 'url="' );
					$ini += strlen( 'url="' );
					$len = strpos( $tos_content, '"]', $ini ) - $ini;
					$link = substr( $tos_content, $ini, $len );
					$tos_pop = '<a href="' . $link . '" target="_blank">';

				} else { 
					$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WP_PLUGIN_URL . "/wp-members/wp-members-tos.php','mywindow');\">";
				}
				$form.= apply_filters( 'wpmem_tos_link_txt', sprintf( __( 'Please indicate that you agree to the %s TOS %s', 'wp-members' ), $tos_pop, '</a>' ) );

			} else {

				// for checkboxes
				if( $wpmem_fields[$row][3] == 'checkbox' ) { 
					$valtochk = $val;
					$val = $wpmem_fields[$row][7]; 
					// if it should it be checked by default (& only if form not submitted), then override above...
					if( $wpmem_fields[$row][8] == 'y' && ( ! $_POST && $toggle != 'edit' ) ) { $val = $valtochk = $wpmem_fields[$row][7]; }
				}

				// for dropdown select
				if( $wpmem_fields[$row][3] == 'select' ) {
					$valtochk = $val;
					$val = $wpmem_fields[$row][7];
				}
				
				if( ! isset( $valtochk ) ) { $valtochk = ''; }

				$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk );
			}

			$form = $form . '</div>'; 
		}
	}

	if( WPMEM_CAPTCHA == 1 && $toggle != 'edit' ) { // don't show on edit page!

		$wpmem_captcha = get_option('wpmembers_captcha'); 
		if( $wpmem_captcha[0] && $wpmem_captcha[1] ) {

			$form = $form . '<div class="clear"></div>
				<div align="right" class="captcha">';
			$form = $form . wpmem_inc_recaptcha( $wpmem_captcha[0], $wpmem_captcha[2] );
			$form = $form . '</div>';
		} 
	}

	$var  = ( $toggle == 'edit' ) ? 'update' : 'register';
	$form.= '<input name="a" type="hidden" value="' . $var . '" />';

	$form = $form . '<input name="redirect_to" type="hidden" value="' . get_permalink() . '" />
		<div class="button_div">
			<input name="reset" type="reset" value="' . __( 'Clear Form', 'wp-members' ) . '" class="buttons" />
			<input name="submit" type="submit" value="' . __( 'Submit', 'wp-members' ) . '" class="buttons" />
		</div>';

	$form = $form . '<div class="req-text"><font class="req">*</font>' . __( 'Required field', 'wp-members' ) . '</div>			

	</fieldset></form>';
	$form = $form . wpmem_inc_attribution();
	$form = $form . '</div>[/wpmem_txt]';
	
	$form = apply_filters( 'wpmem_register_form', $form );

	return $form;
}
endif;


if ( ! function_exists( 'wpmem_login_form_NEW' ) ):
/**
 * Login Form Dialog
 *
 * Builds the table-less form used for
 * login, change password, and reset password.
 *
 * @since 2.5.1
 *
 * @uses apply_filters Calls 'wpmem_login_form_before' filter to add any string to the beginning of the login form
 * @uses apply_filters Calls 'wpmem_forgot_link' filter to change the link for resetting a forgotten password
 * @uses apply_filters Calls 'wpmem_reg_link' filter to change the link to the registration page
 * @uses apply_filters Calls 'wpmem_login_form' filter to change any element in the generated string of HTML for the login form
 *
 * @param  string $page
 * @param  array  $arr
 * @return string $form
 */
function wpmem_login_form_NEW( $page, $arr ) 
{
	$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : get_permalink();

	// fix the wptexturize
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_content', 'wptexturize' );
	add_filter('the_content', 'wpmem_texturize', 99); 
	
	$form = apply_filters( 'wpmem_login_form_before', '' );
	
	$form.= '[wpmem_txt]<div id="wpmem_login">
		<a name="login"></a>
		<form action="' . get_permalink() . '" method="POST" class="form">
		<fieldset>
			<legend>' . $arr[0] . '</legend>
				
			<label for="username">' . $arr[1] . '</label>
			<div class="div_text">
				' . wpmem_create_formfield( $arr[3], $arr[2], '', '', $arr[9] ) . '
			</div>
			
			<label for="password">' . $arr[4] . '</label>
			<div class="div_text">
				' . wpmem_create_formfield( $arr[6], $arr[5], '', '', $arr[10] ) . '
			</div>
				
			<input type="hidden" name="redirect_to" value="' . $redirect_to . '" />';
	if ( $arr[7] != 'login' ) { $form = $form . wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
	
	$form = $form . wpmem_create_formfield( 'a', 'hidden', $arr[7] );
				
	$form = $form . '<div class="button_div">';
	
	if ( $arr[7] == 'login' ) {
		$form = $form . '<input name="rememberme" type="checkbox" id="rememberme" value="forever" />&nbsp;' . __('Remember me', 'wp-members') . '&nbsp;&nbsp;<input type="submit" name="Submit" value="' . $arr[8] . '" class="buttons" />';
	} else {
		$form = $form . '<input type="submit" name="Submit" value="' . $arr[8] . '" class="buttons" />';
	}
	
	$form = $form . '</div>

			<div class="clear"></div>
			<div align="right">';
				
	if ( ( WPMEM_MSURL != null || $page == 'members' ) && $arr[7] == 'login' ) { 
		
		$link = apply_filters( 'wpmem_forgot_link', wpmem_chk_qstr( WPMEM_MSURL ) . 'a=pwdreset' );	
		$form = $form . __('Forgot password?', 'wp-members') . '&nbsp;<a href="' . $link . '">' . __('Click here to reset', 'wp-members') . '</a>';

	}
	
	$form = $form . '</div>
			<div align="right">';
 			
	if ( ( WPMEM_REGURL != null ) && $arr[7] == 'login' ) { 

		$link = apply_filters( 'wpmem_reg_link', WPMEM_REGURL );
		$form = $form . __('New User?', 'wp-members') . '&nbsp;<a href="' . $link . '">' . __('Click here to register', 'wp-members') . '</a>';

	}			
	
	$form = $form. '</div>	
			<div class="clear"></div>
		</fieldset></form>
	</div>[/wpmem_txt]';
	
	$form = apply_filters( 'wpmem_login_form', $form );
	
	return $form;
}
endif;

if ( ! function_exists( 'wpmem_inc_recaptcha' ) ):
/**
 * Create reCAPTCHA form
 *
 * @since  2.6.0
 *
 * @uses apply_filters Calls 'wpmem_recaptcha' filter to change the reCAPTCHA; used for implementing custom reCAPTCHA theme/layout
 *
 * @param  string $key
 * @param  string $theme
 * @return string $str
 */
function wpmem_inc_recaptcha( $key, $theme )
{
	$str = '<script type="text/javascript">
			var RecaptchaOptions = { theme : \''. $theme . '\' };
		</script>
		<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=' . $key . '"></script>
		<noscript>
			<iframe src="http://www.google.com/recaptcha/api/noscript?k=' . $key . '" height="300" width="500" frameborder="0"></iframe><br/>
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
		</noscript>';
		
	$str = apply_filters( 'wpmem_recaptcha', $str );
	
	return $str;
}
endif;


/**
 * Create an attribution link in the form
 *
 * @since 2.6.0
 * @return string $str
 */
function wpmem_inc_attribution()
{
	/*		
		thanks for your support!  
	*/
	
	$show_attribution = get_option( 'wpmembers_attrib' );

	if( WPMEM_OLD_FORMS != 1 && $show_attribution ) { // NEW FORMS
	
		$str = '
		<div align="center">
			<small>Powered by <a href="http://rocketgeek.com" target="_blank">WP-Members</a></small>
		</div>';
		
		return $str;

	} elseif( $show_attribution ) {  // LEGACY FORMS

		$str = '
			<tr>
			  <td>&nbsp;</td>
			  <td align="center">
				<small>Powered by <a href="http://rocketgeek.com" target="_blank">WP-Members</a></small>
			  </td>
			</tr>';
			
		return $str;
			
	}
	
	return;
	
}


if ( ! function_exists( 'wpmem_page_pwd_reset' ) ):
/**
 * Password reset forms
 *
 * This function creates both password reset and forgotten
 * password forms for page=password shortcode.
 *
 * @since 2.7.6
 *
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_pwd_reset( $wpmem_regchk, $content )
{
	if( is_user_logged_in() ) {
	
		switch( $wpmem_regchk ) { 
				
		case "pwdchangempty":
			$content = wpmem_inc_regmessage( $wpmem_regchk, __( 'Password fields cannot be empty', 'wp-members' ) );
			$content = $content . wpmem_inc_changepassword();
			break;

		case "pwdchangerr":
			$content = wpmem_inc_regmessage( $wpmem_regchk );
			$content = $content . wpmem_inc_changepassword();
			break;

		case "pwdchangesuccess":
			$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
			break;

		default:
			$content = $content . wpmem_inc_changepassword();
			break;				
		}
	
	} else {
	
		switch( $wpmem_regchk ) {

		case "pwdreseterr":
			$content = $content 
				. wpmem_inc_regmessage( $wpmem_regchk )
				. wpmem_inc_resetpassword();
			$wpmem_regchk = ''; // clear regchk
			break;

		case "pwdresetsuccess":
			$content = $content . wpmem_inc_regmessage( $wpmem_regchk );
			$wpmem_regchk = ''; // clear regchk
			break;

		default:
			$content = $content . wpmem_inc_resetpassword();
			break;
		}
		
	}
	
	return $content;

}
endif;


if ( ! function_exists( 'wpmem_page_user_edit' ) ):
/**
 * Creates a user edit page
 *
 * @since 2.7.6
 *
 * @uses apply_filters Calls 'wpmem_user_edit_heading' filter to change the user profile edit heading for the user edit shortcode page
 *
 * @param  string $wpmem_regchk
 * @param  string $content
 * @return string $content
 */
function wpmem_page_user_edit( $wpmem_regchk, $content )
{
	global $wpmem_a, $wpmem_themsg;
	
	$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );
	
	if( $wpmem_a == "update") { $content.= wpmem_inc_regmessage( $wpmem_regchk, $wpmem_themsg ); }
	$content = $content . wpmem_inc_registration( 'edit', $heading );
	
	return $content;
}
endif;

/** End of File **/