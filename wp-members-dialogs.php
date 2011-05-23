<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2011  Chad Butler (email : plugins@butlerblog.com)
	
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

    $wpmem_login_form_arr = array(__('Existing users Login', 'wp-members'), __('Username', 'wp-members'), 'text', 'log', __('Password', 'wp-members'), 'password', 'pwd', 'login', __('Login', 'wp-members'), 'username', 'password');
    wpmem_login_form( $page, $wpmem_login_form_arr );
}


function wpmem_inc_changepassword()
{ 
	$wpmem_login_form_arr = array(__('Change Password', 'wp-members'), __('New Password', 'wp-members'), 'password', 'pass1', __('Repeat Password', 'wp-members'), 'password', 'pass2', 'pwdchange', __('Update Password', 'wp-members'), 'password', 'password');
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_inc_resetpassword()
{ 
	$wpmem_login_form_arr = array(__('Reset Forgotten Password', 'wp-members'), __('Username', 'wp-members'), 'text', 'user', __('Email', 'wp-members'), 'text', 'email', 'pwdreset', __('Reset Password', 'wp-members'), 'username', 'textbox');
    wpmem_login_form( 'page', $wpmem_login_form_arr );
}


function wpmem_login_form_OLD ( $page, $wpmem_login_form_arr ) 
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
				  <td width="166"><input name="rememberme" type="checkbox" id="rememberme" value="forever" />&nbsp;<?php _e('Remember me', 'wp-members'); ?></td>
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
			<?php if ( ( WPMEM_MSURL != null || $page == 'members' ) && $wpmem_login_form_arr[7] == 'login' ) { 
				$link = wpmem_chk_qstr( WPMEM_MSURL ); ?>
				<tr>
				  <td colspan="2"><?php _e('Forgot password?', 'wp-members'); ?>&nbsp;<a href="<?php echo $link; ?>a=pwdreset"><?php _e('Click here to reset', 'wp-members'); ?></a></td>
				</tr>
			<?php } ?>
			<?php if ( WPMEM_REGURL != null && $wpmem_login_form_arr[7] == 'login' ) { 
				$link = wpmem_chk_qstr( WPMEM_REGURL ); ?>
				<tr>
				  <td colspan="2"><?PHP _e('New User?', 'wp-members'); ?>&nbsp;<a href="<?php echo $link; ?>"><?php _e('Click here to register', 'wp-members'); ?></a></td>
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
		<h2><?php _e('Login Failed!', 'wp-members'); ?></h2>
		<p><?php _e('You entered an invalid username or password.', 'wp-members'); ?></p>
		<p><a href="<?php echo $_SERVER['REQUEST_URI'];?>"><?php _e('Click here to continue.', 'wp-members'); ?></a></p>
	</div>

	<?php  // end edits for function wpmem_inc_loginfailed()
}


function wpmem_inc_registration_OLD($fields,$toggle = 'new',$heading = '')
{
	global $wpdb,$user_ID,$userdata,$securify,$wpmem_regchk,$username,$wpmem_fieldval_arr; // can maybe ditch $user_ID if using userdata or current_user

	if (!$heading) { $heading = "<h2>".__('New Users Registration', 'wp-members')."</h2>"; }
	if (is_user_logged_in()) { global $current_user; get_currentuserinfo(); } // do we need this AND $userdata? re-evaluate to reduce db calls ?>

	<div class="wpmem_reg">
		<form name="form2" method="post" action="<?php the_permalink();//wpmem_chk_qstr();?>">

		  <table width="400" border="0" cellspacing="0" cellpadding="4">
			<tr align="left"> 
			  <td colspan="2"><?php echo $heading; ?></td>
			</tr>
			<?php if ($toggle == 'edit') { ?>
			<tr> 
			  <td width="49%" align="right"><?php _e('Username', 'wp-members'); ?>:</td>
			  <td width="51%" align="left"><?php echo $userdata->user_login?></td>
			</tr>			
			<?php } else { ?>
			<tr> 
			  <td width="49%" align="right"><?php _e('Choose a Username', 'wp-members'); ?><font color="red">*</font></td>
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
				$do_row = true;
				if ($wpmem_fields[$row][2] == 'tos' && $toggle == 'edit' && (get_user_meta($userdata->ID, 'tos', true))) { 
					// makes tos field hidden on user edit page, unless they haven't got a value for tos
					$do_row = false; 
					wpmem_create_formfield($wpmem_fields[$row][2], 'hidden', get_user_meta($userdata->ID, 'tos', true));
				}			
				if ($wpmem_fields[$row][4] == 'y' && $do_row == true ) {
				?>
					<tr<?php if( $wpmem_fields[$row][3] == 'textarea' || $wpmem_fields[$row][2] == 'tos' ) { echo " valign=\"top\""; } ?>>
						<td align="right"><?php 
						if ($wpmem_fields[$row][2] == 'tos') {
							
							if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
								$chk_tos;  // HUH?
							} else {
								$val = $wpmem_fieldval_arr[$row];
							}
							
							// should be checked by default? and only if form hasn't been submitted
							if(!$_POST && $wpmem_fields[$row][8] == 'y') { $val = $wpmem_fields[$row][7]; }
							
							wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$wpmem_fields[$row][7],$val);
							
						} else {
						
							echo $wpmem_fields[$row][1].":";
							if ($wpmem_fields[$row][5] == 'y') { ?><font color="red">*</font><?php } 
							
						} ?>
						</td>
						<td<?php if ($wpmem_fields[$row][2] == 'tos' || $wpmem_fields[$row][3] == 'checkbox') { echo " align=\"left\""; }?>>
						<?php 
						if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) { 
						
							if (WPMEM_DEBUG == true) { echo $wpmem_fields[$row][2]."&nbsp;"; }
						
							switch ($wpmem_fields[$row][2]) {
							case('description'):
								$val = get_user_meta($userdata->ID,'description','true');
								break;

							case('user_email'):
								$val = $userdata->user_email;
								break;

							case('user_url'):
								$val = $userdata->user_url;
								break;

							default:
								$val = get_user_meta($userdata->ID,$wpmem_fields[$row][2],'true');
								break;
							}

						} else {

							$val = $wpmem_fieldval_arr[$row];

						}
						
						if ($wpmem_fields[$row][2] == 'tos') { 
						
							if ($wpmem_fields[$row][5] == 'y') { echo "<font color=\"red\">*</font>"; }
							
							$tos_pop = "<a href=\"#\" onClick=\"window.open('".WP_PLUGIN_URL."/wp-members/wp-members-tos.php','mywindow');\">";
							printf( __('Please indicate that you have read and agree to the %s Terms of Service %s', 'wp-members'), $tos_pop, '</a>');
						
						} else {
						
							/*  for possible checkbox inclusion in the future.  needs to be tested */
							if ($wpmem_fields[$row][3] == 'checkbox') { 
								$valtochk = $val;
								$val = $wpmem_fields[$row][7]; 
								
								// if it should it be checked by default (& only if form not submitted), then override above...
								if (!$_POST && $wpmem_fields[$row][8] == 'y') { $val = $valtochk = $wpmem_fields[$row][7]; }
							} 
							
							wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$val,$valtochk);
						} ?>
						</td>
					</tr>
				<?php }
			} ?>
		
		<?php 		
		if (WPMEM_CAPTCHA == 1) {
			
			$wpmem_captcha = get_option('wpmembers_captcha'); 
			if ( $wpmem_captcha[0] && $wpmem_captcha[1] ) { ?>
            
            <tr>
            	<td colspan="2" align="right">			
					<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
					<script type="text/javascript">
						function showRecaptcha(element) 
						{
							Recaptcha.create("<?php echo $wpmem_captcha[0]; ?>", element, {
								theme: "<?php echo $wpmem_captcha[2]; ?>",
								// callback: Recaptcha.focus_response_field
								});
						}
					</script>
					<div id="recaptcha_div"></div>
					<script type="text/javascript">showRecaptcha('recaptcha_div');</script>
				</td>
            </tr>
            
        <?php } 
		
		} ?>
            
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
			  <td><font color="red">*</font> <?php _e('Required field', 'wp-members'); ?></td>
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
		$str  = "<ul>\n<li><a href=\"".$link."a=edit\">".__('Edit My Information', 'wp-members')."</a></li>\n
				<li><a href=\"".$link."a=pwdchange\">".__('Change Password', 'wp-members')."</a></li>\n</ul>";
	} else {		
		$str = "<p>".__('You are logged in.', 'wp-members')."</p>
			<ul>
				<li><a href=\"".$link."a=logout\">".__('Click here to logout.', 'wp-members')."</a></li>
				<li><a href=\"".get_option('siteurl')."\">".__('Begin using the site.', 'wp-members')."</a></li>
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
			<p><b><?php echo $themsg; ?></b></p>
			<p>&nbsp;</p>
		</div>

	<?php }		
}


/*****************************************************
END DIALOG OUTPUT FUNCTIONS
*****************************************************/


/*****************************************************
NEW in 2.5.1 - toggle between new table-less forms and legacy table-based forms
	- this will move into the code structure and possibly fine-tune the functions
	  at a later time.
	- we are trying to accomodate those users that want to upgrade, but have already
	  done work integrating the old forms into their site (and might not want to change)
*****************************************************/


function wpmem_inc_registration($fields,$toggle = 'new',$heading = '')
{
	if ( WPMEM_OLD_FORMS != 1 ) { 
		wpmem_inc_registration_NEW($fields,$toggle,$heading);
	} else {
		wpmem_inc_registration_OLD($fields,$toggle,$heading);
	}
}


function wpmem_login_form( $page, $wpmem_login_form_arr ) 
{
	if ( WPMEM_OLD_FORMS != 1 ) { 
		wpmem_login_form_NEW( $page, $wpmem_login_form_arr );
	} else {
		wpmem_login_form_OLD( $page, $wpmem_login_form_arr );
	}
}


function wpmem_inc_registration_NEW($fields,$toggle = 'new',$heading = '')
{
	global $wpdb,$user_ID,$userdata,$securify,$wpmem_regchk,$username,$wpmem_fieldval_arr; // can maybe ditch $user_ID if using userdata or current_user

	if (!$heading) { $heading = __('New Users Registration', 'wp-members'); }
	if (is_user_logged_in()) { global $current_user; get_currentuserinfo(); } // do we need this AND $userdata? re-evaluate to reduce db calls ?>


	<div id="wpmem_reg">
		<fieldset>
			<legend><?php echo $heading; ?></legend>
			<form name="form" method="post" action="<?php the_permalink();//wpmem_chk_qstr();?>" class="form">

			<?php if ($toggle == 'edit') { ?>
			
				<label for="username" class="text"><?php _e('Username', 'wp-members'); ?></label>
				<div class="div_text">
					<?php echo $userdata->user_login; ?>
				</div>
			
			<?php } else { ?>
			
				<label for="username" class="text"><?php _e('Choose a Username', 'wp-members'); ?><font class="req">*</font></label>
				<div class="div_text">
					<input name="log" type="text" value="<?php echo $username;?>" class="username" id="username" />
				</div>
			
			<?php } 
						
			$wpmem_fields = get_option('wpmembers_fields');
			for ($row = 0; $row < count($wpmem_fields); $row++)
			{ 
				$do_row = true;
				if ($wpmem_fields[$row][2] == 'tos' && $toggle == 'edit' && (get_user_meta($userdata->ID, 'tos', true))) { 
					// makes tos field hidden on user edit page, unless they haven't got a value for tos
					$do_row = false; 
					wpmem_create_formfield($wpmem_fields[$row][2], 'hidden', get_user_meta($userdata->ID, 'tos', true));
				}					
				
				if ($wpmem_fields[$row][4] == 'y' && $do_row == true ) {

				
						if ($wpmem_fields[$row][2] != 'tos') {

							echo "<label for=\"".$wpmem_fields[$row][2]."\" class=\"".$wpmem_fields[$row][3]."\">".$wpmem_fields[$row][1];
							if ($wpmem_fields[$row][5] == 'y') { ?><font class="req">*</font><?php } 
							echo "</label>\n";
						
						} 
						
						echo "<div class=\"div_".$wpmem_fields[$row][3]."\">\n";

						if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) { 
						
							if (WPMEM_DEBUG == true) { echo $wpmem_fields[$row][2]."&nbsp;"; }
						
							switch ($wpmem_fields[$row][2]) {
							case('description'):
								$val = get_user_meta($userdata->ID,'description','true');
								break;

							case('user_email'):
								$val = $userdata->user_email;
								break;

							case('user_url'):
								$val = $userdata->user_url;
								break;

							default:
								$val = get_user_meta($userdata->ID,$wpmem_fields[$row][2],'true');
								break;
							}

						} else {

							$val = $wpmem_fieldval_arr[$row];

						}

						if ($wpmem_fields[$row][2] == 'tos') { 
						
							if (($toggle == 'edit') && ($wpmem_regchk != 'updaterr')) {
								$chk_tos;  // HUH?
							} else {
								$val = $wpmem_fieldval_arr[$row];
							}
							
							// should be checked by default? and only if form hasn't been submitted
							if(!$_POST && $wpmem_fields[$row][8] == 'y') { $val = $wpmem_fields[$row][7]; }
							
							wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$wpmem_fields[$row][7],$val);
						
							if ($wpmem_fields[$row][5] == 'y') { echo "<font color=\"red\">*</font>"; }
							
							$tos_pop = "<a href=\"#\" onClick=\"window.open('".WP_PLUGIN_URL."/wp-members/wp-members-tos.php','mywindow');\">";
							printf( __('Please indicate that you agree to the %s TOS %s', 'wp-members'), $tos_pop, '</a>');
						
						} else {
						
							/*  for possible checkbox inclusion in the future.  needs to be tested */
							if ($wpmem_fields[$row][3] == 'checkbox') { 
								$valtochk = $val;
								$val = $wpmem_fields[$row][7]; 
								
								// if it should it be checked by default (& only if form not submitted), then override above...
								if (!$_POST && $wpmem_fields[$row][8] == 'y') { $val = $valtochk = $wpmem_fields[$row][7]; }
							}
							
							wpmem_create_formfield($wpmem_fields[$row][2],$wpmem_fields[$row][3],$val,$valtochk);
						} ?>
					
					</div><?php 
				}
			}
			
			
			if (WPMEM_CAPTCHA == 1) {
			
				$wpmem_captcha = get_option('wpmembers_captcha'); 
				if ( $wpmem_captcha[0] && $wpmem_captcha[1] ) { ?>
						
					<div class="clear"></div>
					<div align="right" >
						<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
						<script type="text/javascript">
							function showRecaptcha(element) 
							{
								Recaptcha.create("<?php echo $wpmem_captcha[0]; ?>", element, {
									theme: "<?php echo $wpmem_captcha[2]; ?>",
									//callback: Recaptcha.focus_response_field
									});
							}
						</script>
						<div id="recaptcha_div"></div>
						<script type="text/javascript">showRecaptcha('recaptcha_div');</script>
					</div>
            
				<?php } 
		
			} ?>

			<?php if ($toggle == 'edit') { ?>
				<input name="a" type="hidden" value="update" />
			 <?php } else { ?>
				<input name="a" type="hidden" value="register" />
			<?php } ?>
				<input name="redirect_to" type="hidden" value="<?php the_permalink();?>" />
				<div class="button_div">
					<input name="reset" type="reset" value="Clear Form" class="buttons" />
					<input name="submit" type="submit" value="Submit" class="buttons" />
				</div>
				
<?php // find a place to put this ?>
<font class="req">*</font> <?php _e('Required field', 'wp-members'); ?>			

			</form>
		</fieldset>
		<!-- Attribution keeps this plugin free!! -->
		<div align="center">
			<small>Powered by <a href="http://butlerblog.com/wp-members" target="_blank">WP-Members</a></small>
		</div>
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
	</div>
	<?php
}


function wpmem_login_form_NEW( $page, $wpmem_login_form_arr ) 
{ ?>	
	<div id="wpmem_login">
		<fieldset>
			<legend><?php echo $wpmem_login_form_arr[0]; ?></legend>
			<form action="<?php the_permalink();?>" method="POST" class="form">
				
			<label for="username"><?php echo $wpmem_login_form_arr[1]; ?></label>
			<div class="div_text">
				<?php wpmem_create_formfield( $wpmem_login_form_arr[3], $wpmem_login_form_arr[2], '', '', $wpmem_login_form_arr[9] ); ?>
			</div>
			
			<label for="password"><?php echo $wpmem_login_form_arr[4]; ?></label>
			<div class="div_text">
				<?php wpmem_create_formfield( $wpmem_login_form_arr[6], $wpmem_login_form_arr[5], '', '', $wpmem_login_form_arr[10] ); ?>
			</div>
				
			<input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" />
			<?php if ( $wpmem_login_form_arr[7] != 'login' ) { wpmem_create_formfield( 'formsubmit', 'hidden', '1' ); }
				wpmem_create_formfield( 'a', 'hidden', $wpmem_login_form_arr[7] ); ?>
				
			<div class="button_div">
			<?php if ( $wpmem_login_form_arr[7] == 'login' ) { ?>
				<input name="rememberme" type="checkbox" id="rememberme" value="forever" />&nbsp;<?php _e('Remember me', 'wp-members'); ?>
			<?php } ?>
				<input type="submit" name="Submit" value="<?php echo $wpmem_login_form_arr[8]; ?>" class="buttons" />
			</div>

			<div class="clear"></div>
			<div align="right">
			<?php 	
			if ( ( WPMEM_MSURL != null || $page == 'members' ) && $wpmem_login_form_arr[7] == 'login' ) { 

				$link = wpmem_chk_qstr( WPMEM_MSURL );	
				_e('Forgot password?', 'wp-members'); ?>&nbsp;<a href="<?php echo $link; ?>a=pwdreset"><?php _e('Click here to reset', 'wp-members'); ?></a>

			<?php } ?>
			</div>
			<div align="right">
			<?php 			
			if ( ( WPMEM_REGURL != null ) && $wpmem_login_form_arr[7] == 'login' ) { 

				$link = wpmem_chk_qstr( WPMEM_REGURL );	
				_e('New User?', 'wp-members'); ?>&nbsp;<a href="<?php echo $link; ?>"><?php _e('Click here to register', 'wp-members'); ?></a>

			<?php } ?>			
			</div>	
				
			</form>
			<div class="clear"></div>
		</fieldset>
	</div><?php
}
?>