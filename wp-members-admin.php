<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)
	
	WP-Members(tm) is a trademark of butlerblog.com
*/


/*****************************************************
BEGIN ADMIN FEATURES
*****************************************************/


add_action('edit_user_profile', 'wpmem_admin_fields');
function wpmem_admin_fields()
{
	$user_id = $_REQUEST['user_id']; ?>
	
	<h3><?php _e('WP-Members Additional Fields'); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option('wpmembers_fields');
		for ($row = 0; $row < count($wpmem_fields); $row++) {
		
			if($wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][4] == "y") { ?>    
			
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><input id="<?php echo $wpmem_fields[$row][2]; ?>" type="text" class="input" name="<?php echo $wpmem_fields[$row][2]; ?>" value="<?php echo get_user_meta($user_id,$wpmem_fields[$row][2],'true');?>" size="25" /></td>
				</tr>
			
			<?php } 
		
		}

		// see if reg is moderated, and if the user has been activated		
		if (WPMEM_MOD_REG == 1) { 
			if (get_user_meta($user_id,'active','true') != 1) { ?>

				<tr>
					<th><label><?php _e('Activate this user?'); ?></label></th>
					<td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="1" /></td>
				</tr>

			<?php }
		} ?>
	</table><?php
}


add_action('profile_update', wpmem_admin_update);
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		// new in 2.3.3 - does not include custom fields that are not used (note: WP does include it's own fields even if empty)
		if ($wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][6] == "y") {update_user_meta($user_id,$wpmem_fields[$row][2],$_POST[$wpmem_fields[$row][2]]);}
	}
	if (WPMEM_MOD_REG == 1) {

		$wpmem_activate_user = $_POST['activate_user'];
		if ($wpmem_activate_user == 1) {
			$new_pass = substr( md5( uniqid( microtime() ) ), 0, 7);
			$hashpassword = md5($new_pass);

			global $wpdb;
			$wpdb->update( $wpdb->users, array( 'user_pass' => $hashpassword ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );

			require_once('wp-members-email.php');

			wpmem_inc_regemail($user_id,$new_pass,2);
			update_user_meta($user_id,'active',$wpmem_activate_user); 
		}
	}
}


function wpmem_a_build_options($wpmem_settings)
{ ?>
	<h3><?php _e('Manage Options'); ?></h3>
		<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-settings'); } ?>
		<table class="form-table">
		<?php $arr = array(
			array(__('Block Posts by default'),'wpmem_settings_block_posts',__('Note: Posts can still be individually blocked or unblocked at the article level')),
			array(__('Block Pages by default'),'wpmem_settings_block_pages',__('Note: Pages can still be individually blocked or unblocked at the article level')),
			array(__('Show excerpts'),'wpmem_settings_show_excerpts',__('Shows excerpted content above the login/registration on both Posts and Pages')),
			array(__('Notify admin'),'wpmem_settings_notify',__('Sends email to admin for each new registration?')),
			array(__('Moderate registration'),'wpmem_settings_moderate',__('Holds new registrations for admin approval')),
			array(__('Use reCAPTCHA'),'wpmem_settings_captcha',__('Turns on CAPTCHA for registration')),
			array(__('Turn off registration'),'wpmem_settings_turnoff',__('Turns off the registration process, only allows login')),	
			array(__('Time-based expiration'),'wpmem_settings_time_exp',__('Allows for access to expire')),
			array(__('Trial period','wpmem_settings_trial'),__('Allows for a trial period')),
			array(__('Ignore warning messages'),'wpmem_settings_ignore_warnings',__('Ignores WP-Members warning messages in the admin panel'))
			); ?>
		<?php for ($row = 0; $row < count($arr); $row++) { ?>
		<?php if ($row < 7 || $row > 8) {  // this is here until we finish time based expiration ?>
		  <tr valign="top">
			<th align="left" scope="row"><?php echo $arr[$row][0]; ?></th>
			<td><?php if (WPMEM_DEBUG == true) { echo $wpmem_settings[$row+1]; } ?>
				<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if ($wpmem_settings[$row+1]==1) {echo "checked";}?> />
				<?php if($arr[$row][2]) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
			</td>
		  </tr>
		  <?php } ?>
		  <?php } ?>
		  <tr valign="top">
			<td>&nbsp;</td>
			<td><input type="hidden" name="wpmem_admin_a" value="update_settings">
				<input type="submit" name="UpdateSettings"  class="button-primary" value="Update Settings &raquo;" /> 
			</td>
		  </tr>
		</table>
	</form>
	<?php
}


function wpmem_a_build_fields ($wpmem_fields) 
{ ?>

	<h3><?php _e('Manage Fields'); ?></h3>
    <p><?php _e('Determine which fields will display and which are required.  This includes all fields, both native WP fields and WP-Members custom fields.'); ?>
		&nbsp;<strong><?php _e('(Note: Email is always mandatory. and cannot be changed.)'); ?></strong></p>
    <form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-fields'); } ?>
	<table class="widefat">
		<thead><tr class="head">
        	<th scope="col" align="right"><?php _e('Field Label') ?></th>
			<th scope="col" align="center"><?php _e('Display?') ?></th>
            <th scope="col" align="center"><?php _e('Required?') ?></th>
            <th scope="col" align="center"><?php _e('WP Native?') ?></th>
        </tr></thead>
	<?php
	// order, label, optionname, input type, display, required, native
	$class = '';
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($chkreq == "err" && $wpmem_fields[$row][5] == 'y' && $wpmem_fields[$row][4] != 'y') {
			$class = "updated fade";
		} else {
			$class = ($class == 'alternate') ? '' : 'alternate';
		}
		?><tr class="<?php echo $class; ?>" valign="top">
        	<td><?php 
				echo $wpmem_fields[$row][1];
				if ($wpmem_fields[$row][5] == 'y'){ ?><font color="red">*</font><?php }
				?>
            </td>
		  <?php if ($wpmem_fields[$row][2]!='user_email'){ ?>
			<td><?php wpmem_create_formfield($wpmem_fields[$row][2]."_display",'checkbox', 'y', $wpmem_fields[$row][4]); ?></td>
            <td><?php wpmem_create_formfield($wpmem_fields[$row][2]."_required",'checkbox', 'y', $wpmem_fields[$row][5]); ?></td>
		  <?php } else { ?>
			<td colspan="2"><small><i><?php _e('(The email field is mandatory and cannot be removed)'); ?></i></small></td>
		  <?php } ?>
			<td><?php if ($wpmem_fields[$row][6] == 'y') { echo "yes"; }?></td>
          </tr><?php
	}	?>
    	<tr>
        	<td colspan="6">
            	<input type="hidden" name="wpmem_admin_a" value="update_fields" />
                <input type="submit" name="save"  class="button-primary" value="<?php _e('Update Fields'); ?> &raquo;" /> 
            </td>
        </tr>
    </table>
    </form>
	<?php
}


function wpmem_a_build_dialogs($wpmem_dialogs)
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
    ); ?>
	<h3>WP-Members <?php _e('Dialogs and Error Messages'); ?></h3>
	<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-dialogs'); } ?>
		<table class="form-table">
		<tr>
			<td colspan="2"><?php _e('You can customize the following text.  Simple HTML is allowed'); ?> - &lt;p&gt;, &lt;b&gt;, &lt;i&gt;, <?php _e('etc.');?></td>
		</tr>        
        <?php for ($row = 0; $row < count($wpmem_dialog_title_arr); $row++) { ?>
			<tr valign="top"> 
				<th scope="row"><?php echo $wpmem_dialog_title_arr[$row]; ?></th> 
				<td><textarea name="<?php echo "dialogs_".$row; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo stripslashes($wpmem_dialogs[$row]); ?></textarea></td> 
			</tr>
		<?php } ?>
			<tr valign="top"> 
				<th scope="row">&nbsp;</th> 
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_dialogs" />
                    <input type="submit" name="save"  class="button-primary" value="<?php _e('Update Dialogs'); ?> &raquo;" />
				</td> 
			</tr>				
		</table> 
	</form>
	<?php
}


function wpmem_admin()
{
	$wpmem_settings = get_option('wpmembers_settings');
	$wpmem_fields   = get_option('wpmembers_fields');
	$wpmem_dialogs  = get_option('wpmembers_dialogs');

	switch ($_POST['wpmem_admin_a']) {

	case ("update_settings"):

		//check nonce
		check_admin_referer('wpmem-update-settings');

		//keep things clean
		$post_arr = array('WPMEM_VERSION','wpmem_settings_block_posts','wpmem_settings_block_pages','wpmem_settings_show_excerpts','wpmem_settings_notify','wpmem_settings_moderate','wpmem_settings_captcha','wpmem_settings_turnoff','wpmem_settings_time_exp','wpmem_settings_trial','wpmem_settings_ignore_warnings');
		$wpmem_newsettings = array();
		for ($row = 0; $row < count($post_arr); $row++) {
			if ($post_arr == 'WPMEM_VERSION') {
				$wpmem_newsettings[$row] = 'WPMEM_VERSION';
			} else {
				if ($_POST[$post_arr[$row]] != 1) {
					$wpmem_newsettings[$row] = 0;
				} else {
					$wpmem_newsettings[$row] = $_POST[$post_arr[$row]];
				}
			}
		}

		update_option('wpmembers_settings',$wpmem_newsettings);
		$wpmem_settings = $wpmem_newsettings;
		$did_update = "true";
		break;

	case ("update_fields"):

		//check nonce
		check_admin_referer('wpmem-update-fields');

		//rebuild the array, don't touch user_email - it's always mandatory
		for ($row = 0; $row < count($wpmem_fields); $row++) {

			for ($i = 0; $i < 4; $i++) {
				$wpmem_newfields[$row][$i] = $wpmem_fields[$row][$i];
			}

			$display_field = $wpmem_fields[$row][2]."_display"; 
			$require_field = $wpmem_fields[$row][2]."_required";

			if ($wpmem_fields[$row][2]!='user_email'){
				if ($_POST[$display_field] == "on") {$wpmem_newfields[$row][4] = 'y';}
				if ($_POST[$require_field] == "on") {$wpmem_newfields[$row][5] = 'y';}
			} else {
				$wpmem_newfields[$row][4] = 'y';
				$wpmem_newfields[$row][5] = 'y';		
			}

			if ($wpmem_newfields[$row][4] != 'y' && $wpmem_newfields[$row][5] == 'y') { $chkreq = "err"; }
			$wpmem_newfields[$row][6] = $wpmem_fields[$row][6];
		}

		update_option('wpmembers_fields',$wpmem_newfields);
		$wpmem_fields = $wpmem_newfields;
		$did_update = "true";
		break;

	case ("update_dialogs"):

		//check nonce
		check_admin_referer('wpmem-update-dialogs');

		for ($row = 0; $row < count($wpmem_dialogs); $row++) {
			$dialog = "dialogs_".$row;
			$wpmem_newdialogs[$row] = $_POST[$dialog];
		}

		update_option('wpmembers_dialogs',$wpmem_newdialogs);
		$wpmem_dialogs = $wpmem_newdialogs;
		$did_update = "true";		
		break;
		
	case ("update_captcha"):
	
		//check nonce
		check_admin_referer('wpmem-update-captcha');
		
		$wpmem_captcha = array(
			$_POST['wpmem_captcha_publickey'],
			$_POST['wpmem_captcha_privatekey'],
			$_POST['wpmem_captcha_theme']
			);
		
		update_option('wpmembers_captcha',$wpmem_captcha);
		$did_update = "true";
		break;

	}

	?>
    <div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
    <h2>WP-Members <?php _e('Settings'); ?></h2>

    <?php
	if ($did_update == "true") {

		if ($chkreq == "err") { ?>
			<div class="error"><p><strong><?php _e('Settings were saved, but you have required field that are not set to display!'); ?></strong><br /><br />
				<?php _e('Note: This will not cause an error for the end user, as only displayed fields are validated.  However, you should still check that 
				your displayed and required fields match up.  Mismatched fields are highlighted below.'); ?></p></div>
		<?php } else { ?>
			<div id="message" class="updated fade"><p><strong><?php _e('Settings saved.'); ?></strong></p></div>
		<?php }

	}


	/*************************************************************************
		WARNING MESSAGES
	**************************************************************************/

	// settings allow anyone to register
	if ( get_option('users_can_register') != 0 && $wpmem_settings[10] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(1);
	}

	// settings allow anyone to comment
	if ( get_option('comment_registration') !=1 && $wpmem_settings[10] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(2);
	} 
	
	// rss set to full text feeds
	if ( get_option('rss_use_excerpt') !=1 && $wpmem_settings[10] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(3);
	} 

	// holding registrations but haven't changed default successful registration message
	if ( $wpmem_settings[10] == 0 && $wpmem_settings[5] == 1 && $wpmem_dialogs[3] == 'Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you.' ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(4);
	}  

	// turned off registration but also have set to moderate and/or email new registrations
	if ( $wpmem_settings[10] == 0 && $wpmem_settings[7] == 1 ) { 
		if ( $wpmem_settings[5] == 1 || $wpmem_settings[4] ==1 ) { 
			include_once('wp-members-dialogs-admin.php');
			wpmem_a_warning_msg(5);
		}  
	}

	
	/*************************************************************************
		END WARNING MESSAGES
	**************************************************************************/	?>


	<p><strong><a href="http://butlerblog.com/wp-members/" target="_blank">WP-Members</a> <?php _e('Version:'); echo "&nbsp;".WPMEM_VERSION; ?></strong>
		[ <?php _e('Follow'); ?> ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a> ]
		<br />
		<?php _e('If you find this plugin useful,'); ?> <br /><?php _e('please consider making a donation'); ?> <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="QC2W6AM9WUZML">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	</p>

	<?php wpmem_a_build_options($wpmem_settings); ?>

	<p>&nbsp;</p>
    
    <?php if ($wpmem_settings[6] == 1) { ?>
    
    <?php wpmem_a_build_captcha_options(); ?>
    
    <p>&nbsp;</p>
    
    <?php } ?>

	<?php wpmem_a_build_fields($wpmem_fields); ?>

	<p>&nbsp;</p>

	<?php wpmem_a_build_dialogs($wpmem_dialogs); ?>	
	
	<p>&nbsp;</p>
	<p><i>
	<?php _e('Thank you for using WP-Members! You are using version'); ?> <?php echo WPMEM_VERSION; ?>.
	<?php _e('If you find this plugin useful, please consider a'); ?> <a href="http://butlerblog.com/wp-members">
	<?php _e('donation'); ?></a>.<br />
	<?php _e('WP-Members is copyright'); ?> &copy; 2006-2010 by Chad Butler, <a href="http://butlerblog.com">butlerblog.com</a> | 
	  <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a><br />
	<?php _e('WP-Members is a trademark of'); ?> <a href="http://butlerblog.com">butlerblog.com</a>
	</i></p>
	<p>&nbsp;</p>
</div>
<?php
}


function wpmem_a_build_captcha_options()
{ 
	$wpmem_captcha = get_option('wpmembers_captcha');
	?>

	<h3><?php _e('Manage reCAPTCHA Options'); ?></h3>
    	<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-captcha'); } ?>
	<table class="form-table">
		<tr>
			<td colspan="2">
            	<p><?php _e('reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.'); ?></p>
				<p><?php _e('reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot while also correcting the automatic scans of old books. So you get less spam, and the world gets accurately digitized books. Everybody wins! For details, visit the'); ?> <a href="http://recaptcha.net/" target="_blank"><?php _e('reCAPTCHA website'); ?></a>.</p>
                <p>
            </td>
		</tr>        
		<tr valign="top"> 
			<th scope="row"><?php _e('reCAPTCHA Keys'); ?></th> 
			<td>
            	<?php _e('reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a'); ?> <a href="http://recaptcha.net/api/getkey?domain=wp3&amp;app=wordpress" target="_blank"><?php _e('free reCAPTCHA key'); ?></a>.<br />
            	<?php _e('Public Key'); ?>:&nbsp;&nbsp;&nbsp;<input type="text" name="wpmem_captcha_publickey" size="50" value="<?php echo $wpmem_captcha[0]; ?>" /><br />
                <?php _e('Private Key'); ?>:&nbsp;<input type="text" name="wpmem_captcha_privatekey" size="50" value="<?php echo $wpmem_captcha[1]; ?>" />
             </td> 
		</tr>
        <tr valign="top">
        	<th scope="row"><?php _e('Choose Theme'); ?></th>
            <td>
            	<select name="wpmem_captcha_theme"><?php 
					wpmem_create_formfield(__('Red'), 'option', 'red', $wpmem_captcha[2]); 
					wpmem_create_formfield(__('White'), 'option', 'white', $wpmem_captcha[2]);
					wpmem_create_formfield(__('Black Glass'), 'option', 'blackglass', $wpmem_captcha[2]); 
					wpmem_create_formfield(__('Clean'), 'option', 'clean', $wpmem_captcha[2]); ?>
					<!--<?php wpmem_create_formfield(__('Custom'), 'option', 'custom', $wpmem_captcha[2]); ?>-->
                </select>
            </td>
        </tr>
		<tr valign="top"> 
			<th scope="row">&nbsp;</th> 
			<td>
				<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
                <input type="submit" name="save"  class="button-primary" value="<?php _e('Update reCAPTCHA Settings'); ?> &raquo;" />
			</td> 
		</tr>				
	</table> 
	</form>
	<?php 
}


/*****************************************************
END ADMIN FEATURES
*****************************************************/
?>