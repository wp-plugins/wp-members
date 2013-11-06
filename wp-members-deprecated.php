<?php
/**
 * WP-Members Deprecated Functions
 *
 * These functions will be deprecated. If you are using any of these
 * as pluggable functions, or if you are using the old table-based
 * forms, you will want to bring your WP-Members installation 
 * up-to-date in order to be able to upgrade.
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


if( ! function_exists( 'wpmem_inc_registration_OLD' ) ):
/**
 * Registration Form Dialog (Legacy)
 *
 * Outputs the table-based form for new user
 * registration and existing user edits. Broken out
 * as a separate function in 2.5.1
 *
 * @since 2.5.1
 *
 * @param  string $toggle
 * @param  string $heading
 * @return string $form
 */
function wpmem_inc_registration_OLD( $toggle = 'new', $heading = '' )
{
	global $userdata, $wpmem_regchk;

	if( !$heading ) { $heading = "<h2>" . __( 'New Users Registration', 'wp-members' ) . "</h2>"; }

	$form = '<div class="wpmem_reg">
		<a name="register"></a>
		<form name="form2" method="post" action="' . get_permalink() . '">'; 

	$form.= ( WPMEM_USE_NONCE == 1 ) ? wp_nonce_field( 'wpmem-validate-submit', 'wpmem-form-submit' ) : '';
	
	$form.= '	<table width="400" border="0" cellspacing="0" cellpadding="4">
			<tr align="left"> 
			  <td colspan="2">' . $heading . '</td>
			</tr>';

	if( $toggle == 'edit' ) {
		$form = $form . '<tr> 
			  <td width="49%" align="right">' . __( 'Username', 'wp-members' ) . ':</td>
			  <td width="51%" align="left">' . $userdata->user_login . '</td>
			</tr>';
	} else {
		$form = $form . '<tr> 
			  <td width="49%" align="right">' . __( 'Choose a Username', 'wp-members' ) . '<font color="red">*</font></td>
			  <td width="51%"><input name="log" type="text" value="' . stripslashes( $_POST['log'] ) . '" /></td>
			</tr>';
	}

	$form = $form . '<tr> 
			  <td colspan="2">&nbsp;</td>
			</tr>';

	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count($wpmem_fields); $row++ )
	{ 
		$do_row = true;
		
		if( $toggle == 'edit' && $wpmem_fields[$row][2] == 'password' ) { $do_row = false; }
		
		if( $wpmem_fields[$row][2] == 'tos' && $toggle == 'edit' && ( get_user_meta( $userdata->ID, 'tos', true ) ) ) { 
			// makes tos field hidden on user edit page, unless they haven't got a value for tos
			$do_row = false; 
			$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], 'hidden', get_user_meta( $userdata->ID, 'tos', true ) );
		}
		if( $wpmem_fields[$row][4] == 'y' && $do_row == true ) {

			$form = $form . '<tr'; if( $wpmem_fields[$row][3] == 'textarea' || $wpmem_fields[$row][2] == 'tos' ) { $form = $form . ' valign="top"'; } $form = $form . '>';
			$form = $form . '<td align="right">';
			if( $wpmem_fields[$row][2] == 'tos' ) {

				if( ( $toggle == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) {
					$chk_tos;  // HUH?
				} else {
					$val = $wpmem_fieldval_arr[$row];
				}

				// should be checked by default? and only if form hasn't been submitted
				if( ! $_POST && $wpmem_fields[$row][8] == 'y' ) { $val = $wpmem_fields[$row][7]; }

				$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );

			} else {

				$form = $form . $wpmem_fields[$row][1].":";
				if( $wpmem_fields[$row][5] == 'y' ) { $form = $form . '<font color="red">*</font>'; } 

			} 
			
			$form = $form . '</td>
			<td'; if( $wpmem_fields[$row][2] == 'tos' || $wpmem_fields[$row][3] == 'checkbox' ) { $form = $form . ' align="left"'; } $form = $form . '>';

			if( ( $toggle == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) { 

				//if (WPMEM_DEBUG == true) { $form = $form . $wpmem_fields[$row][2]."&nbsp;"; }

				switch( $wpmem_fields[$row][2] ) {
				case( 'description' ):
					$val = htmlspecialchars( get_user_meta($userdata->ID,'description','true') );
					break;

				case( 'user_email' ):
					$val = $userdata->user_email;
					break;

				case( 'user_url' ):
					$val = esc_url( $userdata->user_url );
					break;

				default:
					$val = htmlspecialchars( get_user_meta($userdata->ID,$wpmem_fields[$row][2],'true') );
					break;
				}

			} else {

				$val = $_POST[ $wpmem_fields[$row][2] ];

			}

			if( $wpmem_fields[$row][2] == 'tos' ) { 

				if( $wpmem_fields[$row][5] == 'y' ) { $form = $form . '<font color="red">*</font>'; }
				
				// determine if TOS is a WP page or not...
				$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
				if( strstr( $tos_content, '[wp-members page="tos"' ) ) {
					
					$tos_content = " " . $tos_content;
					$ini = strpos( $tos_content, 'url="' );
					$ini += strlen( 'url="' );
					$len = strpos( $tos_content, '"]', $ini ) - $ini;
					$link = substr( $tos_content, $ini, $len );
					$tos_pop = '<a href="' . $link . '" target="_blank">';

				} else { 
					$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WP_PLUGIN_URL . "/wp-members/wp-members-tos.php','mywindow');\">";
				}
				$form = $form . sprintf( __( 'Please indicate that you agree to the %s TOS %s', 'wp-members' ), $tos_pop, '</a>');

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

				$form = $form . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk );
			}

			$form = $form . '</td>
					</tr>';
		}
	}

	if( WPMEM_CAPTCHA == 1 && $toggle != 'edit' ) {

		$wpmem_captcha = get_option( 'wpmembers_captcha' ); 
		if( $wpmem_captcha[0] && $wpmem_captcha[1] ) {
		
			$form = $form . '<tr>
				<td colspan="2" align="right">';
			$form = $form . wpmem_inc_recaptcha( $wpmem_captcha[0], $wpmem_captcha[2] );
			$form = $form . '</td>
			</tr>';
            
		} 

	} 
            
	$form = $form . '<tr><td colspan="2">&nbsp;</td></tr>
			<tr> 
			  <td align="right">&nbsp;</td>
			  <td>';
	if( $toggle == 'edit' ) {
		$form = $form . '<input name="a" type="hidden" value="update" />';
	} else {
		$form = $form . '<input name="a" type="hidden" value="register" />';
	}
	$form = $form . '
				<input name="redirect_to" type="hidden" value="' . get_permalink() . '" />
				<input name="Submit" type="submit" value="' . __( 'Submit', 'wp-members' ) . '" /> 
				&nbsp;&nbsp; 
				<input name="Reset" type="reset" value="' . __( 'Clear Form', 'wp-members' ) . '" />
			  </td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td><font color="red">*</font> ' . __('Required field', 'wp-members') . '</td>
			</tr>';
	
	$form = $form . wpmem_inc_attribution();

	$form = $form . '
		  </table>
		</form>
	</div>';

	return $form;

}
endif;


if ( ! function_exists( 'wpmem_login_form_OLD' ) ):
/**
 * Login Form Dialog (Legacy)
 *
 * Builds the table-based form used for
 * login, change password, and reset password.
 *
 * @param string $page
 * @param array $arr
 * @return string $form
 */
function wpmem_login_form_OLD ( $page, $arr ) 
{ 
	$form = '<div class="wpmem_login">
	<a name="login"></a>
	<form name="form" method="post" action="' . get_permalink() . '">
	  <table width="400" border="0" cellspacing="0" cellpadding="4">
		<tr align="left"> 
		  <td colspan="2"><h2>' . $arr[0] . '</h2></td>
		</tr>
		<tr> 
		  <td width="118" align="right">' . $arr[1] . '</td>
		  <td width="166">' . wpmem_create_formfield( $arr[3], $arr[2], '' ) . '</td>
		</tr>
		<tr> 
		  <td width="118" align="right">' . $arr[4] . '</td>
		  <td width="166">' . wpmem_create_formfield( $arr[6], $arr[5], '' ) . '</td>
		</tr>';
	
	if ( $arr[7] == 'login' ) {
		$form = $form . '<tr>
		  <td width="118">&nbsp;</td>
		  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" />&nbsp;' . __('Remember me', 'wp-members') . '</td>
		</tr>';
	}
	
	$form = $form . '<tr> 
		<td width="118">&nbsp;</td>
		<td width="166">
			<input type="hidden" name="redirect_to" value="' . get_permalink() . '" />';
	
	if ( $arr[7] != 'login' ) { $form = $form . wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
	
	$form = $form . wpmem_create_formfield( 'a', 'hidden', $arr[7] ) . '
			<input type="submit" name="Submit" value="' . $arr[8] . '" />
		  </td>
		</tr>';
	
	if ( ( WPMEM_MSURL != null || $page == 'members' ) && $arr[7] == 'login' ) { 

		$link = wpmem_chk_qstr( WPMEM_MSURL );
		$form = $form . '<tr>
		  <td colspan="2">' . __('Forgot password?', 'wp-members') . '&nbsp;<a href="' . $link . 'a=pwdreset">' . __('Click here to reset', 'wp-members') . '</a></td>
		</tr>';
	
	}
	
	if ( WPMEM_REGURL != null && $arr[7] == 'login' ) { 
	
		$form = $form . '<tr>
			<td colspan="2">' . __('New User?', 'wp-members') . '&nbsp;<a href="'. WPMEM_REGURL . '">' . __('Click here to register', 'wp-members') . '</a></td>
		</tr>';
	}
	
	$form = $form . '</table> 
	  </form>
	</div>';
	
	return $form;
}
endif;


/**
 * Old form sidebar login
 *
 * @since 2.8.0
 *
 * @param string $post_to
 */
function wpmem_old_forms_sidebar( $post_to )
{ ?>
	<ul>
	<?php if( $wpmem_regchk == 'loginfailed' && $_POST['slog'] == 'true' ) { ?>
		<p><?php _e( 'Login Failed!<br />You entered an invalid username or password.', 'wp-members' ); ?></p>
	<?php }?>
		<p><?php _e( 'You are not currently logged in.', 'wp-members' ); ?><br />
			<form name="form" method="post" action="<?php echo $post_to; ?>">
			<?php _e( 'Username', 'wp-members' ); ?><br />
			<input type="text" name="log" style="font:10px verdana,sans-serif;" /><br />
			<?php _e( 'Password', 'wp-members' ); ?><br />
			<input type="password" name="pwd" style="font:10px verdana,sans-serif;" /><br />
			<input type="hidden" name="rememberme" value="forever" />
			<input type="hidden" name="redirect_to" value="<?php echo $post_to; ?>" />
			<input type="hidden" name="a" value="login" />
			<input type="hidden" name="slog" value="true" />
			<input type="submit" name="Submit" value="<?php _e( 'login', 'wp-members' ); ?>" style="font:10px verdana,sans-serif;" />
			<?php 			
				if( WPMEM_MSURL != null ) { 
					$link = wpmem_chk_qstr( WPMEM_MSURL ); ?>
					<a href="<?php echo $link; ?>a=pwdreset"><?php _e( 'Forgot?', 'wp-members' ); ?></a>&nbsp;
				<?php } 			
				if( WPMEM_REGURL != null ) { ?>
					<a href="<?php echo WPMEM_REGURL; ?>"><?php _e( 'Register', 'wp-members' ); ?></a>

				<?php } ?>
			</form>
		</p>
	</ul>
<?php }

/** End of File **/