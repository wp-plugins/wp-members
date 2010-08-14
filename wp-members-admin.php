<?php
/*
	This file is part of the WP-Members plugin by Chad Butler
	
	You can find out more about this plugin at http://butlerblog.com/wp-members
  
	Copyright (c) 2006-2010  Chad Butler (email : plugins@butlerblog.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	You may also view the license here:
	http://www.gnu.org/licenses/gpl.html
*/


/*****************************************************
BEGIN ADMIN FEATURES
*****************************************************/


add_action('edit_user_profile', 'wpmem_admin_fields');
function wpmem_admin_fields()
{
	$user_id = $_REQUEST['user_id']; ?>
	
	<h3>WP-Members Additional Fields</h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option('wpmembers_fields');
		for ($row = 0; $row < count($wpmem_fields); $row++) {
		
			if($wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][4] == "y") { ?>    
			
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><input id="<?php echo $wpmem_fields[$row][2]; ?>" type="text" class="input" name="<?php echo $wpmem_fields[$row][2]; ?>" value="<?php echo get_usermeta($user_id,$wpmem_fields[$row][2]);?>" size="25" /></td>
				</tr>
			
			<?php } 
		
		}

		// see if reg is moderated, and if the user has been activated		
		if (WPMEM_MOD_REG == 1) { 
			if (get_usermeta($user_id, 'active') != 1) { ?>

				<tr>
					<th><label>Activate this user?</label></th>
					<td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="1" /></td>
				</tr>

			<?php }
		}
		
		// if using subscription model, show expiration
		// if registration is moderated, this doesn't show if user is not active yet.		
		if (WPMEM_USE_EXP == 1) {
			if ( (WPMEM_MOD_REG == 1 &&  get_user_meta($user_id, 'active', 'true') == 1) || (WPMEM_MOD_REG != 1) ) { ?>

				<tr>
					<th><label><?php echo ucfirst( get_user_meta($user_id, 'exp_type', 'true') ); ?> expires:</label></th>
					<td><?php echo get_user_meta($user_id, 'expires', 'true'); ?></td>
				</tr>
				<tr>
					<th><label>Extend user:</label></th>
					<td>
					<select name="wpmem_extend">
						<option value="" selected>--</option>
						<?php for ($i = 1; $i < 13; $i++) { wpmem_create_formfield($i, 'option', $i); } ?>
					</select>
					<?php 
						$tmp = get_option('wpmembers_experiod'); 
						switch ( $tmp['subscription_per'] ) {
						case 'd':
							echo "Day(s)";
							break;
						case 'w':
							echo "Week(s)";
							break;
						case 'm':
							echo "Month(s)";
							break;
						case 'y':
							echo "Year(s)";
							break;
						}
					?>
					</td>
				</tr>
				<?php
			} 
		} ?>
	</table><?php
}


add_action('profile_update', wpmem_admin_update);
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option('wpmembers_fields');
	for ($row = 0; $row < count($wpmem_fields); $row++) {
		if ($wpmem_fields[$row][6] == "n") {update_user_meta($user_id,$wpmem_fields[$row][2],$_POST[$wpmem_fields[$row][2]]);}
	}
	if (WPMEM_MOD_REG == 1) {

		$wpmem_activate_user = $_POST['activate_user'];
		if ($wpmem_activate_user == 1) {
			$new_pass = substr( md5( uniqid( microtime() ) ), 0, 7);
			$hashpassword = md5($new_pass);

			global $wpdb;
			$wpdb->update( $wpdb->users, array( 'user_pass' => $hashpassword ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );
			
			// new in 2.3 for user expiration
			if (WPMEM_USE_EXP == 1) { wpmem_set_exp($user_id); }

			require_once('wp-members-email.php');

			wpmem_inc_regemail($user_id,$new_pass,2);
			update_user_meta($user_id,'active',$wpmem_activate_user); 
		}
	}
	
	// new in 2.3 for user expiration
	//		this is a truncated version of wpmem_set_exp in wp-members-core.php until i can rework that function accordingly...
	if (WPMEM_USE_EXP == 1) { 
	
		if ($_POST['wpmem_extend'] > 0) {

			// get the expiration periods
			$exp_arr = get_option('wpmembers_experiod');

			$exp_num = $_POST['wpmem_extend'];
			$exp_per = $exp_arr["subscription_per"];

			$wpmem_exp = wpmem_exp_date( $exp_num, $exp_per ); 
			update_user_meta( $user_id, 'expires', $wpmem_exp );
			update_user_meta( $user_id, 'exp_type', 'subscription');
		}
	}
}


function wpmem_a_build_options($wpmem_settings)
{ ?>
	<h3>Manage Options</h3>
		<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-settings'); } ?>
		<table class="form-table">
		<?php $arr = array(
			array('Block Posts by default','wpmem_settings_block_posts','Note: Posts can still be individually blocked or unblocked at the article level'),
			array('Block Pages by default','wpmem_settings_block_pages','Note: Pages can still be individually blocked or unblocked at the article level'),
			array('Show excerpts','wpmem_settings_show_excerpts','Shows excerpted content above the login/registration on both Posts and Pages'),
			array('Notify admin','wpmem_settings_notify','Sends email to admin for each new registration?'),
			array('Moderate registration','wpmem_settings_moderate','Holds new registrations for admin approval'),
			array('Turn off registration','wpmem_settings_turnoff','Turns off the registration process, only allows login'),
			array('Time-based expiration','wpmem_settings_time_exp','Allows for access to expire'),
			array('Trial period','wpmem_settings_trial','Allows for a trial period'),
			array('Ignore warning messages','wpmem_settings_ignore_warnings','Ignores WP-Members warning messages in the admin panel') ); ?>
		<?php for ($row = 0; $row < count($arr); $row++) { ?>
		<?php if ($row < 6 || $row > 7) {  // this is here until we finish time based expiration ?>
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
    <form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php">
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
			<td colspan="2"><small><i>(The email field is mandatory and cannot be removed)</i></small></td>
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
    	"Restricted post (or page), displays above the login/registration form",
        "Username is taken",
        "Email is registered",
        "Registration completed",
        "User update",
        "Passwords did not match",
        "Password changes",
        "Username or email do not exist when trying to reset forgotten password",
        "Password reset"  
    ); ?>
	<h3>WP-Members Dialogs and Error Messages</h3>
	<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-dialogs'); } ?>
		<table class="form-table">
		<tr>
			<td colspan="2">You can customize the following text.  Simple HTML is allowed - &lt;p&gt;, &lt;b&gt;, &lt;i&gt;, etc.</td>
		</tr>        
        <?php for ($row = 0; $row < count($wpmem_dialog_title_arr); $row++) { ?>
			<tr valign="top"> 
				<th scope="row"><?php echo $wpmem_dialog_title_arr[$row]; ?></th>
				<td><textarea name="<?php echo "dialogs_".$row; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo $wpmem_dialogs[$row]; ?></textarea></td> 
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


function wpmem_a_build_expiration($wpmem_experiod, $trial, $expire)
{	?>
	<h3>Trial & Subscription Period</h3>
	<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['PHP_SELF']?>?page=wp-members.php">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-exp'); } ?>
		<table class="form-table">
		<?php 
			
			if ($trial == 1) { wpmem_a_build_exp_table($wpmem_experiod, 'Trial'); }
	
			if ($expire == 1) { wpmem_a_build_exp_table($wpmem_experiod, 'Subscription'); }
		
		?>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_exp" />
                    <input type="submit" name="save"  class="button-primary" value="<?php _e('Update'); ?> &raquo;" />
				</td>
			</tr>
		</table>
	</form>	
	<?php
    	
}


function wpmem_a_build_exp_table($wpmem_experiod, $title)
{ 
	$fld_name = strtolower($title);
	?>
	<tr valign="top">
		<th align="left" scope="row">Set <?php echo $title; ?> Period</th>
		<td><select name="<?php echo $fld_name; ?>_num">
        <?php for ($i = 1; $i < 13; $i++) {
			wpmem_create_formfield($i, 'option', $i, $wpmem_experiod[$fld_name."_num"]);
		} ?>
            </select>
			<select name="<?php echo $fld_name; ?>_period"><?php 
				wpmem_create_formfield('Day(s)','option','d',$wpmem_experiod[$fld_name."_per"]);
				wpmem_create_formfield('Week(s)','option','w',$wpmem_experiod[$fld_name."_per"]);
				wpmem_create_formfield('Month(s)','option','m',$wpmem_experiod[$fld_name."_per"]);
				wpmem_create_formfield('Year(s)','option','y',$wpmem_experiod[$fld_name."_per"]);
				?>
			</select>
		</td>
	</tr>
    <?php 
}


function wpmem_admin()
{
	$wpmem_settings = get_option('wpmembers_settings');
	$wpmem_fields   = get_option('wpmembers_fields');
	$wpmem_dialogs  = get_option('wpmembers_dialogs');
	$wpmem_experiod = get_option('wpmembers_experiod');

	switch ($_POST['wpmem_admin_a']) {

	case ("update_settings"):

		//check nonce
		check_admin_referer('wpmem-update-settings');

		//keep things clean
		$post_arr = array('WPMEM_VERSION','wpmem_settings_block_posts','wpmem_settings_block_pages','wpmem_settings_show_excerpts','wpmem_settings_notify','wpmem_settings_moderate','wpmem_settings_turnoff','wpmem_settings_time_exp','wpmem_settings_trial','wpmem_settings_ignore_warnings');
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
		
	case ("update_exp"):
	
		//check nonce
		check_admin_referer('wpmem-update-exp');
		
		$wpmem_newexperiod = array( 'subscription_num' => $_POST['subscription_num'],
									'subscription_per' => $_POST['subscription_period'],
									'trial_num' 	   => $_POST['trial_num'],
									'trial_per' 	   => $_POST['trial_period'],
									
								);
		update_option('wpmembers_experiod',$wpmem_newexperiod);
		$wpmem_experiod = $wpmem_newexperiod; if (WPMEM_DEBUG == true) { var_dump($wpmem_experiod); }
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
			<div class="error"><p><strong>Settings were saved, but you have required field that are not set to display!</strong><br /><br />Note: 
				This will not cause an error for the end user, as only displayed fields are validated.  However, you should still check that 
				your displayed and required fields match up.  Mismatched fields are highlighted below.</p></div>
		<?php } else { ?>
			<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>
		<?php }

	}


	/*************************************************************************
		WARNING MESSAGES
	**************************************************************************/

	if (get_option('users_can_register') != 0 && $wpmem_settings[9] == 0) { ?>

		<div class="error"><p><strong>Your WP settings allow anyone to register - this is not the recommended setting.</strong>  You can <a href="options-general.php">change this here</a> making sure the box next to "Anyone can register" is unchecked.</p> [<span title="This setting allows a link on the /wp-login.php page to register using the WP native registration process thus circumventing any registration you are using with WP-Members. In some cases, this may suit the users wants/needs, but most users should uncheck this option. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.">why is this?</span>]</div>

	<?php }

	if (get_option('comment_registration') !=1 && $wpmem_settings[9] == 0) { ?>

		<div class="error"><p><strong>Your WP settings allow anyone to comment - this is not the recommended setting.</strong>  You can <a href="options-discussion.php">change this here</a> by checking the box next to "Users must be registered and logged in to comment."</p> [<span title="This setting allows any users to comment, whether or not they are registered. Depending on how you are using WP-Members will determine whether you should change this setting or not. If you do not change this setting, you can choose to ignore these warning messages under WP-Members Settings.">why is this?]</div>

	<?php } 

	if ($wpmem_settings[9] == 0 && $wpmem_settings[5] == 1 && $wpmem_dialogs[3] == 'Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you.') { ?>

		<div class="error"><p><strong>You have set WP-Members to hold registrations for approval,</strong> but you have not changed the default message for "Registration Completed" under "WP-Members Dialogs and Error Messages."  You should change this message to let users know they are pending approval.</div>	
	<?php } 

	if ($wpmem_settings[9] == 0 && $wpmem_settings[6] == 1) { 
		if ($wpmem_settings[5] == 1 || $wpmem_settings[4] ==1) { ?>

		<div class="error"><p><strong>You have set WP-Members to turn off the registration process,</strong> but you also set to moderate and/or email admin new registrations.  Turning registrations off overrides the other two settings since no registrations are allowed.</div>	

	<?php }  

	} 
	/*************************************************************************
		END WARNING MESSAGES
	**************************************************************************/	?>


	<p><strong><a href="http://butlerblog.com/wp-members/" target="_blank">WP-Members</a> Version: <?php echo WPMEM_VERSION; ?></strong>
		[ Follow ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a> ]
		<br />
		If you find this plugin useful,<br />please consider making a donation <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="QC2W6AM9WUZML">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
	</p>

	<?php wpmem_a_build_options($wpmem_settings); ?>

	<p>&nbsp;</p>
    
    <?php
	if ($wpmem_settings[7] == 1 || $wpmem_settings[8] == 1) { ?>
    
    <?php wpmem_a_build_expiration( $wpmem_experiod, $wpmem_settings[8], $wpmem_settings[7] ); ?>
    
    <p>&nbsp;</p>
    
    <?php } ?>

	<?php wpmem_a_build_fields($wpmem_fields); ?>

	<p>&nbsp;</p>

	<?php wpmem_a_build_dialogs($wpmem_dialogs); ?>

	<p>&nbsp;</p>
	<p><i>Thank you for using WP-Members! You are using version <?php echo WPMEM_VERSION; ?>. If you find this plugin useful, please consider a <a href="http://butlerblog.com/wp-members">donation</a>.<br />
	  WP-Members is copyright &copy; 2006-2010 by Chad Butler, <a href="http://butlerblog.com">butlerblog.com</a> | 
	  <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a></i></p>
	<p>&nbsp;</p>
</div>
<?php
}


/*****************************************************
END ADMIN FEATURES
*****************************************************/
?>