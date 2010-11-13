<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
DIALOG OUTPUT FUNCTIONS
*****************************************************/


function wpmem_inc_login($page="page")
{ 	
	global $wpmem_regchk;

	$wpmem_dialogs = get_option('wpmembers_dialogs');

	if($page == "page"){
	     if($wpmem_regchk!="success"){
		
		//this shown above blocked content ?>
		<p><?php echo stripslashes($wpmem_dialogs[0]); ?></p>

	<?php } 	
	} 

    $wpmem_login_form_arr = array(__('Existing users Login'), __('Username'), 'text', 'log', __('Password'), 'password', 'pwd', 'login', __('Login'));
    wpmem_login_form( $page, $wpmem_login_form_arr );
}


function wpmem_inc_changepassword()
{ 
	$wpmem_login_form_arr = array(__('Change Password'), __('New Password'), 'password', 'pass1', __('Repeat Password'), 'password', 'pass2', 'pwdchange', __('Update Password'));
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_inc_resetpassword()
{ 
	$wpmem_login_form_arr = array(__('Reset Forgotten Password'), __('Username'), 'text', 'user', __('Email'), 'text', 'email', 'pwdreset', __('Reset Password'));
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_login_form( $page, $wpmem_login_form_arr ) 
{ ?>	
	  <div class="wpmem_login">
		  <form name="form" method="post" action="<?php the_permalink();?>">
			  <table width="400" border="0" cellspacing="0" cellpadding="4">
				<tr align="left"> 
				  <td colspan="2"><h2><?php echo $wpmem_login_form_arr[0]; ?></h2></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[1]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[3], $wpmem_login_form_arr[2], '' ); ?></td>
				</tr>
				<tr> 
				  <td width="118" align="right"><?php echo $wpmem_login_form_arr[4]; ?></td>
				  <td width="166"><?php wpmem_create_formfield( $wpmem_login_form_arr[6], $wpmem_login_form_arr[5], '' ); ?></td>
				</tr>
			<?php if ( $wpmem_login_form_arr[7] == 'login' ) { ?>
				<tr>
				  <td width="118">&nbsp;</td>
				  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" />&nbsp;<?php _e('Remember me'); ?></td>
				</tr>
			<?php } ?>
				<tr> 
				  <td width="118">&nbsp;</td>
				  <td width="166">
					<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" /><?php
					if ( $wpmem_login_form_arr[7] != 'login' ) { wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
					wpmem_create_formfield( 'a', 'hidden', $wpmem_login_form_arr[7] ); ?>
					<input type="submit" name="Submit" value="<?php echo $wpmem_login_form_arr[8]; ?>" />
				  </td>
				</tr>
			<?php if ( $page == 'members' && $wpmem_login_form_arr[7] == 'login' ) { 
				$link = wpmem_chk_qstr(); ?>
				<tr>
				  <td colspan="2"><?php _e('Forgot password?'); ?>&nbsp;<a href="<?php echo $link; ?>a=pwdreset"><?php _e('Click here to reset'); ?></a></td>
				</tr>
			<?php } ?>
			  </table> 
		  </form>
	  </div><?php
}


function wpmem_inc_loginfailed() 
{ 
	/* 
	failed login message.  
	you can customize this to fit your theme, etc.

	You may edit below this line */?>

	<div align="center" id="wpmem_msg">
		<h2><?php _e('Login Failed!'); ?></h2>
		<p><?php _e('You entered an invalid username or password.'); ?></p>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'];?>"><?php _e('Click here to continue.'); ?></a></p>
	</div>

	<?php  // end edits for function wpmem_inc_loginfailed()
}


function wpmem_inc_registration($fields,$toggle = 'new',$heading = '')
{
	global $wpdb,$user_ID, $userdata,$securify,$wpmem_regchk,$username,$wpmem_fieldval_arr;

	if (!$heading) { $heading = "<h2>New Users Registration</h2>"; }
	if (is_user_logged_in()) { get_currentuserinfo(); }	?>

	<div class="wpmem_reg">
		<form name="form2" method="post" action="<?php the_permalink();//wpmem_chk_qstr();?>">

		  <table width="400" border="0" cellspacing="0" cellpadding="4">
			<tr align="left"> 
			  <td colspan="2"><?php echo $heading; ?></td>
			</tr>
			<?php if ($toggle == 'edit') { ?>
			<tr> 
			  <td width="49%" align="right"><?php _e('Username'); ?>:</td>
			  <td width="51%" align="left"><?php echo $userdata->user_login?></td>
			</tr>			
			<?php } else { ?>
			<tr> 
			  <td width="49%" align="right"><?php _e('Choose a Username'); ?><font color="red">*</font></td>
			  <td width="51%"><input name="log" type="text" value="<?php echo $username;?>" /></td>
			</tr>
			<?php } ?>
			<tr> 
			  <td colspan="2">&nbsp;</td>
			</tr>

			<?php
			$wpmem_fields = get_option('wpmembers_fields');
			for ($row = 0; $row < count($wpmem_fields); $row++)
			{ 
				if ($wpmem_fields[$row][4] == 'y') { ?>
					<tr<?php if( $wpmem_fields[$row][3] == 'textarea' ){ echo " valign=\"top\""; } ?>>
						<td align="right"><?php 
							echo $wpmem_fields[$row][1].":";
							if ($wpmem_fields[$row][5] == 'y') { ?><font color="red">*</font><?php } ?>
						</td>
						<td>
						<?php 
						if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
							switch ($wpmem_fields[$row][2]) {
							case('description'):
								$val = get_user_meta($user_ID,'description','true');
								break;

							case('user_email'):
								$val = $userdata->user_email;
								break;

							case('user_url'):
								$val = $userdata->user_url;
								break;

							default:				
								$val = get_user_meta($user_ID,$wpmem_fields[$row][2],'true');
								break;
							}

						} else {

							$val = $wpmem_fieldval_arr[$row]; 

						}

						wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$val,'');
						?>
						</td>
					</tr>
				<?php } 
			} ?>
		
		<?php 		
		if (WPMEM_CAPTCHA == 1) {
			
			$wpmem_captcha = get_option('wpmembers_captcha'); ?>
            
            <tr>
            	<td colspan="2" align="right">			
					<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
					<script type="text/javascript">
						function showRecaptcha(element) 
						{
							Recaptcha.create("<?php echo $wpmem_captcha[0]; ?>", element, {
								theme: "<?php echo $wpmem_captcha[2]; ?>",
								callback: Recaptcha.focus_response_field});
						}
					</script>
					<div id="recaptcha_div"></div>
					<script type="text/javascript">showRecaptcha('recaptcha_div');</script>
				</td>
            </tr>
            
        <?php } ?>
            
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr> 
			  <td align="right">&nbsp;</td>
			  <td>
			  <?php if ($toggle == 'edit') { ?>
				<input name="a" type="hidden" value="update" />
			  <?php } else { ?>
				<input name="a" type="hidden" value="register" />
			  <?php } ?>
				<input name="redirect_to" type="hidden" value="<?php the_permalink();?>" />
				<input name="Submit" type="submit" value="submit" /> 
				&nbsp;&nbsp; 
				<input name="Reset" type="reset" value="Clear Form" />
			  </td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td><font color="red">*</font> <?php _e('Required field'); ?></td>
			</tr>
			<tr>
			  <td>&nbsp;</td>
			  <td align="center"><!-- Attribution keeps this plugin free!! -->
				<small>Powered by <a href="http://butlerblog.com/wp-members" target="_blank">WP-Members</a><small>
				<?php
				/*
					Taking this out?  That's ok.  But please consider making a donation
					to support the further development of this plugin.  Many hours of
					work have gone into it.
					
					If you are a developer using this for a client site, you see 
					value in not having to do this from scratch.
					Please consider a larger amount.
					
					If you are a donor, I thank you for your support!  
				*/
				?>
			  </td>
			</tr>
		  </table>
		</form>
	</div>
	<?php
}


function wpmem_inc_memberlinks($page = 'members') 
{
	$link = wpmem_chk_qstr();
	
	if ($page == 'members') {
		$str  = "<ul>\n<li><a href=\"".$link."a=edit\">Edit My Information</a></li>\n
				<li><a href=\"".$link."a=pwdchange\">Change Password</a></li>\n</ul>";
	} else {		
		$str = "<p>You are logged in.</p>
			<ul>
				<li><a href=\"".$link."a=logout\">Click here to logout.</a></li>
				<li><a href=\"".get_option('siteurl')."\">Begin using the site.</a></li>
			</ul>";
	}
	
	return $str;
}


function wpmem_inc_regmessage($toggle,$themsg='')
{ 
	$wpmem_dialogs = get_option('wpmembers_dialogs');
	$wpmem_dialogs_toggle = array('user','email','success','editsuccess','pwdchangerr','pwdchangesuccess','pwdreseterr','pwdresetsuccess');

	for ($row = 0; $row < count($wpmem_dialogs_toggle); $row++) {

		if ($toggle == $wpmem_dialogs_toggle[$row]) { ?>

			<div class="wpmem_msg" align="center">
				<p>&nbsp;</p>
				<p><b><?php echo $wpmem_dialogs[$row+1]; ?></b></p>
				<p>&nbsp;</p>
			</div>

			<?php
			$didtoggle = "true";
		}	
	}

	if ($didtoggle != "true") { ?>

		<div class="wpmem_msg" align="center">
			<p>&nbsp;</p>
			<p><b><?php _e('Sorry,'); ?>&nbsp;<?php echo $themsg; ?></b></p>
			<p>&nbsp;</p>
		</div>

	<?php }		
}


/* 
function wpmem_inc_dialog_title() //this may be deprecated
{
	$wpmem_dialog_title_arr = array(
    	__("Restricted post (or page), displays above the login/registration form"),
        __("Username is taken"),
        __("Email is registered"),
        __("Registration completed"),
        __("User update"),
        __("Passwords did not match"),
        __("Password changes"),
        __("Username or email do not exist when trying to reset forgotten password"),
        __("Password reset")  
    );
	return $wpmem_dialog_title_arr;
} 
*/


/*****************************************************
END DIALOG OUTPUT FUNCTIONS
*****************************************************/
?>