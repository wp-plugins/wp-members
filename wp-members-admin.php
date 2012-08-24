<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the administration panels.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2012  Chad Butler (email : plugins@butlerblog.com)
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2012
 */
 

add_filter( 'plugin_action_links', 'wpmem_admin_plugin_links', 10, 2 ); 
/**
 * filter to add link to settings from plugin panel
 *
 * @since 2.4
 *
 * @param  array  $links
 * @param  string $file
 * @static string $wpmem_plugin
 * @return array  $links
 */
function wpmem_admin_plugin_links( $links, $file )
{
	static $wpmem_plugin;
	if( !$wpmem_plugin ) $wpmem_plugin = plugin_basename( 'wp-members/wp-members.php' );
	if( $file == $wpmem_plugin ) {
		$settings_link = '<a href="options-general.php?page=wpmem-settings">' . __( 'Settings' ) . '</a>';
		$links = array_merge( array( $settings_link ), $links );
	}
	return $links;
}


/**
 * include contextual help
 * @todo finish writing the contextual help in wp-members-dialogs-admin.php
 */
// add_filter('contextual_help', 'wpmem_a_help_msg', 10, 2);
// include_once('wp-members-dialogs-admin.php');


/*****************************************************
	Manage User Detail Screen
*****************************************************/


add_action( 'show_user_profile', 'wpmem_admin_fields' );
add_action( 'edit_user_profile', 'wpmem_admin_fields' );
/**
 * add WP-Members fields to the WP user profile screen
 *
 * @since 2.1
 *
 * @global array $current_screen
 */
function wpmem_admin_fields()
{
	global $current_screen;
	if( $current_screen->id == 'profile' ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	} else {
		$user_id = $_REQUEST['user_id']; 
	} ?>

	<h3><?php _e( 'WP-Members Additional Fields', 'wp-members' ); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option( 'wpmembers_fields' );
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {

			/** determine which fields to show in the additional fields area */
			$show = false;
			if( $wpmem_fields[$row][6] == 'n' && $wpmem_fields[$row][2] != 'password' ) { $show = true; }
			if( $wpmem_fields[$row][1] == 'TOS' && $wpmem_fields[$row][4] != 'y' ) { $show = false; }
			
			if( $show ) { ?>  

				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><?php
						$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
						if( $wpmem_fields[$row][3] == 'checkbox' || $wpmem_fields[$row][3] == 'select' ) {
							$valtochk = $val; 
							$val = $wpmem_fields[$row][7];
						}
						echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val,$valtochk );
						$valtochk = ''; // empty for the next field in the loop
					?></td>
				</tr>

			<?php } 

		}

		// see if reg is moderated, and if the user has been activated
		if( WPMEM_MOD_REG == 1 ) { 
			$user_active_flag = get_user_meta( $user_id, 'active', 'true' );
			switch( $user_active_flag ) {
			
				case '':
					$label  = __( 'Activate this user?', 'wp-members' );
					$action = 1;
					break;
				
				case 0: 
					$label  = __( 'Reactivate this user?', 'wp-members' );
					$action = 1;
					break;
				
				case 1:
					$label  = __( 'Deactivate this user?', 'wp-members' );
					$action = 0;
					break;
				
			}?>

			<tr>
				<th><label><?php echo $label; ?></label></th>
				<td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="<?php echo $action; ?>" /></td>
			</tr>

		<?php }  

		// if using subscription model, show expiration
		// if registration is moderated, this doesn't show if user is not active yet.
		if( WPMEM_USE_EXP == 1 ) {
			if( ( WPMEM_MOD_REG == 1 &&  get_user_meta( $user_id, 'active', 'true' ) == 1 ) || ( WPMEM_MOD_REG != 1 ) ) { 
				wpmem_a_extenduser( $user_id );
			} 
		} ?>
		<tr>
			<th><label><?php _e( 'IP @ registration', 'wp-members' ); ?></label></th>
			<td><?php echo get_user_meta( $user_id, 'wpmem_reg_ip', 'true' ); ?></td>
		</tr>
	</table><?php
}


add_action('profile_update', 'wpmem_admin_update');
/**
 * updates WP-Members fields from the WP user profile screen
 *
 * @since 2.1
 */
function wpmem_admin_update()
{
	$user_id = $_REQUEST['user_id'];	
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = true; }
		if( $wpmem_fields[$row][6] == "n" && ! $chk_pass ) {
			update_user_meta( $user_id, $wpmem_fields[$row][2], $_POST[$wpmem_fields[$row][2]] );
		}
	}

	if (WPMEM_MOD_REG == 1) {
	
		$wpmem_activate_user = $_POST['activate_user'];
		if( $wpmem_activate_user == '' ) { $wpmem_activate_user = -1; }
		if( $wpmem_activate_user == 1 ) {
			wpmem_a_activate_user( $user_id, $chk_pass );
		} elseif( $wpmem_activate_user == 0 ) {
			wpmem_a_deactivate_user( $user_id );
		}
	}

	if( WPMEM_USE_EXP == 1 ) { 
		wpmem_a_extend_user( $user_id );
	}
}


/*****************************************************
	WP-Members Settings Screen
*****************************************************/


/**
 * builds the settings panel
 *
 * @param array $wpmem_settings
 */
function wpmem_a_build_options( $wpmem_settings )
{ ?>
	<h3><?php _e( 'Manage Options', 'wp-members' ); ?></h3>
		<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
		<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-settings'); } ?>
		<table class="form-table">
		<?php $arr = array(
			array(__('Block Posts by default','wp-members'),'wpmem_settings_block_posts',__('Note: Posts can still be individually blocked or unblocked at the article level','wp-members')),
			array(__('Block Pages by default','wp-members'),'wpmem_settings_block_pages',__('Note: Pages can still be individually blocked or unblocked at the article level','wp-members')),
			array(__('Show excerpts','wp-members'),'wpmem_settings_show_excerpts',__('Shows excerpted content above the login/registration on both Posts and Pages','wp-members')),
			array(__('Notify admin','wp-members'),'wpmem_settings_notify',__('Sends email to admin for each new registration?','wp-members')),
			array(__('Moderate registration','wp-members'),'wpmem_settings_moderate',__('Holds new registrations for admin approval','wp-members')),
			array(__('Use reCAPTCHA','wp-members'),'wpmem_settings_captcha',__('Turns on CAPTCHA for registration','wp-members')),
			array(__('Turn off registration','wp-members'),'wpmem_settings_turnoff',__('Turns off the registration process, only allows login','wp-members')),
			// NEW in 2.5.1 - legacy forms
			array(__('Legacy forms','wp-members'),'wpmem_settings_legacy',__('Uses the pre-2.5.1 table-based forms (leave off to use CSS table-less forms)','wp-members')),
			array(__('Time-based expiration','wp-members'),'wpmem_settings_time_exp',__('Allows for access to expire','wp-members')),
			array(__('Trial period','wp-members'),'wpmem_settings_trial',__('Allows for a trial period','wp-members')),
			array(__('Ignore warning messages','wp-members'),'wpmem_settings_ignore_warnings',__('Ignores WP-Members warning messages in the admin panel','wp-members'))
			); ?>
		<?php for( $row = 0; $row < count( $arr ); $row++ ) { ?>
		<?php if( ( $row < 8 || $row > 9 ) || ( WPMEM_EXP_MODULE == true ) ) { ?>
		  <tr valign="top">
			<th align="left" scope="row"><?php echo $arr[$row][0]; ?></th>
			<td><?php if (WPMEM_DEBUG == true) { echo $wpmem_settings[$row+1]; } ?>
				<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if( $wpmem_settings[$row+1] == 1 ) { echo "checked"; }?> />
				<?php if( $arr[$row][2] ) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
			</td>
		  </tr>
		  <?php } ?>
		  <?php } ?>
		  
		  <?php // new in 2.5
		  $wpmem_msurl = get_option('wpmembers_msurl');
		  if (!$wpmem_msurl) { $wpmem_msurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Members Area URL:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_msurl" value="<?php echo $wpmem_msurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr><?php // new in 2.5.1
		  $wpmem_regurl = get_option('wpmembers_regurl');
		  if (!$wpmem_regurl) { $wpmem_regurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Register Page URL:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr><?php // new in 2.5.1
		  $wpmem_cssurl = get_option('wpmembers_cssurl');
		  if (!$wpmem_cssurl) { $wpmem_cssurl = "http://"; } ?>
		  <tr>
			<th align="left" scope="row"><?php _e('Custom CSS:', 'wp-members'); ?></th>
			<td><input type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" size="50" />&nbsp;<span class="description"><?php _e('Optional', 'wp-members'); ?></span></td>
		  </tr>
		  <tr valign="top">
			<td>&nbsp;</td>
			<td><input type="hidden" name="wpmem_admin_a" value="update_settings">
				<input type="submit" name="UpdateSettings"  class="button-primary" value="<?php _e('Update Settings', 'wp-members'); ?> &raquo;" /> 
			</td>
		  </tr>
		</table>
	</form>
	<?php
}


/**
 * Builds the fields panel
 *
 * @since 2.2.2
 *
 * @param string $wpmem_fields
 */
function wpmem_a_build_fields( $wpmem_fields ) 
{ ?>
	<h3><?php _e( 'Manage Fields', 'wp-members' ); ?></h3>
	<p><?php _e( 'Determine which fields will display and which are required.  This includes all fields, both native WP fields and WP-Members custom fields.', 'wp-members' ); ?>
		&nbsp;<strong><?php _e( '(Note: Email is always mandatory. and cannot be changed.)', 'wp-members' ); ?></strong></p>
	<form name="updatefieldform" id="updatefieldform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
	<?php if( function_exists('wp_nonce_field') ) { wp_nonce_field( 'wpmem-update-fields' ); } ?>
	<table class="widefat" id="wpmem-fields">
		<thead><tr class="head">
			<th scope="col"><?php _e( 'Add/Delete',  'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Field Label', 'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Option Name', 'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Field Type',  'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Display?',    'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Required?',   'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'Checked?',    'wp-members' ) ?></th>
			<th scope="col"><?php _e( 'WP Native?',  'wp-members' ) ?></th>
		</tr></thead>
	<?php
	// order, label, optionname, input type, display, required, native
	$class = '';
	for( $row = 0; $row < count($wpmem_fields); $row++ ) {
		$class = ( $class == 'alternate' ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class; ?>" valign="top" id="<?php echo $wpmem_fields[$row][0];?>">
			<td width="80"><?php 
				if( $wpmem_fields[$row][6] != 'y' ) {  ?><input type="checkbox" name="<?php echo "del_".$wpmem_fields[$row][2]; ?>" value="delete" /> <?php _e( 'Delete', 'wp-members' ); } ?></td>
			<td width="180"><?php 
				echo $wpmem_fields[$row][1];
				if( $wpmem_fields[$row][5] == 'y' ){ ?><font color="red">*</font><?php }
				?>
			</td>
			<td width="180"><?php echo $wpmem_fields[$row][2]; ?></td>
			<td width="80"><?php echo $wpmem_fields[$row][3]; ?></td>
		  <?php if( $wpmem_fields[$row][2]!='user_email' ) { ?>
			<td width="80"><?php echo wpmem_create_formfield( $wpmem_fields[$row][2]."_display", 'checkbox', 'y', $wpmem_fields[$row][4] ); ?></td>
			<td width="80"><?php echo wpmem_create_formfield( $wpmem_fields[$row][2]."_required",'checkbox', 'y', $wpmem_fields[$row][5] ); ?></td>
		  <?php } else { ?>
			<td colspan="2" width="160"><small><i><?php _e( '(Email cannot be removed)', 'wp-members' ); ?></i></small></td>
		  <?php } ?>
			<td width="80"><?php if( $wpmem_fields[$row][3] == 'checkbox' ) { 
				echo wpmem_create_formfield( $wpmem_fields[$row][2]."_checked", 'checkbox', 'y', $wpmem_fields[$row][8]); } ?>
			</td>
			<td width="80"><?php if( $wpmem_fields[$row][6] == 'y' ) { echo "yes"; } ?></td>
		</tr><?php
	}	?>
	</table><br />
	<table class="widefat">	
		<tr>
			<td width="80"><input type="checkbox" name="add_field" value="add" /> <?php _e( 'Add', 'wp-members' ); ?></td>
			<td width="180"><input type="text" name="add_name" value="New field label" /></td>
			<td width="180"><input type="text" name="add_option" value="new_option_name" /></td>
			<td width="80">
				<select name="add_type">
					<option value="text"><?php     _e( 'text', 'wp-members' );     ?></option>
					<option value="textarea"><?php _e( 'textarea', 'wp-members' ); ?></option>
					<option value="checkbox"><?php _e( 'checkbox', 'wp-members' ); ?></option>
					<option value="select"><?php   _e( 'dropdown', 'wp-members' ); ?></option>
					<option value="password"><?php _e( 'password', 'wp-members' ); ?></option>
				</select>
			</td>
			<td width="80"><?php echo wpmem_create_formfield( 'add_display', 'checkbox', 'y' ); ?></td>
			<td width="80"><?php echo wpmem_create_formfield( 'add_required','checkbox', 'y' ); ?></td>
			<td width="80"><input type="checkbox" name="add_checked_default" value="y" /></td>
			<td width="80">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="right"><?php _e( 'For checkbox, stored value if checked:', 'wp-members' ); ?></td>
			<td><input type="text" name="add_checked_value" value="value" class="small-text" /></td>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" align="right">
				<?php _e( 'For dropdown, array of values:', 'wp-members' ); ?><br />
				<span class="description"><?php _e( 'Options should be Option Name|option_value,', 'wp-members' ); ?><br />
				<a href="http://butlerblog.com/wp-members/users-guide/add-fields/"><?php _e( 'Visit plugin site for more information', 'wp-members' ); ?></a></span>
			</td>
			<td colspan="2"><textarea name="add_dropdown_value" rows="5" cols="40"><---- Select One ---->|, 
Choice One|choice1value, 
Choice Two|choice_two_value, 
|, 
Example After Spacer|after_spacer</textarea></td>
			<td colspan="3">&nbsp;</td>
		</tr>

	</table><br />
	<input type="hidden" name="wpmem_admin_a" value="update_fields" />
	<input type="submit" name="save"  class="button-primary" value="<?php _e( 'Update Fields', 'wp-members' ); ?> &raquo;" /> 
	</form>
	<?php
}


/**
 * builds the dialogs panel
 *
 * @param array $wpmem_dialogs
 */
function wpmem_a_build_dialogs( $wpmem_dialogs )
{ 
	$wpmem_dialog_title_arr = array(
    	__("Restricted post (or page), displays above the login/registration form", 'wp-members'),
        __("Username is taken", 'wp-members'),
        __("Email is registered", 'wp-members'),
        __("Registration completed", 'wp-members'),
        __("User update", 'wp-members'),
        __("Passwords did not match", 'wp-members'),
        __("Password changes", 'wp-members'),
        __("Username or email do not exist when trying to reset forgotten password", 'wp-members'),
        __("Password reset", 'wp-members') 
    ); ?>
	<h3>WP-Members <?php _e('Dialogs and Error Messages', 'wp-members'); ?></h3>
	<p><?php printf(__('You can customize the text for dialogs and error messages. Simple HTML is allowed %s etc.', 'wp-members'), '- &lt;p&gt;, &lt;b&gt;, &lt;i&gt;,'); ?></p>
	<form name="updatedialogform" id="updatedialogform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-dialogs'); } ?>
		<table class="form-table">        
        <?php for ($row = 0; $row < count($wpmem_dialog_title_arr); $row++) { ?>
			<tr valign="top"> 
				<th scope="row"><?php echo $wpmem_dialog_title_arr[$row]; ?></th> 
				<td><textarea name="<?php echo "dialogs_".$row; ?>" rows="3" cols="50" id="" class="large-text code"><?php echo stripslashes($wpmem_dialogs[$row]); ?></textarea></td> 
			</tr>
		<?php } ?>
		
		<?php $wpmem_tos = stripslashes( get_option( 'wpmembers_tos' ) ); ?>
			<tr valign="top"> 
				<th scope="row"><?php _e('Terms of Service (TOS)', 'wp-members'); ?></th> 
				<td><textarea name="dialogs_tos" rows="3" cols="50" id="" class="large-text code"><?php echo $wpmem_tos; ?></textarea></td> 
			</tr>		
			<tr valign="top"> 
				<th scope="row">&nbsp;</th> 
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_dialogs" />
                    <input type="submit" name="save" class="button-primary" value="<?php _e('Update Dialogs', 'wp-members'); ?> &raquo;" />
				</td> 
			</tr>	
		</table> 
	</form>
	<?php
}


/**
 * primary admin function
 *
 * @todo check for duplicate field names in the add field process
 */
function wpmem_admin()
{
	$wpmem_settings = get_option('wpmembers_settings');
	$wpmem_fields   = get_option('wpmembers_fields');
	$wpmem_dialogs  = get_option('wpmembers_dialogs');
	
	if (WPMEM_EXP_MODULE == true) {
		$wpmem_experiod = get_option('wpmembers_experiod');
	}
	
	$did_update         = false;
	$active_tab         = ''; 
	$show_recaptcha     = ''; 
	$show_subscriptions = ''; 
	//$show_paypal        = '';
	$chkreq             = '';
	$add_field_err_msg  = '';

	if( isset( $_POST['wpmem_admin_a'] ) ) {
	
		switch ($_POST['wpmem_admin_a']) {

		case ("update_settings"):

			//check nonce
			check_admin_referer('wpmem-update-settings');

			//keep things clean
			$post_arr = array(
				'WPMEM_VERSION',
				'wpmem_settings_block_posts',
				'wpmem_settings_block_pages',
				'wpmem_settings_show_excerpts',
				'wpmem_settings_notify',
				'wpmem_settings_moderate',
				'wpmem_settings_captcha',
				'wpmem_settings_turnoff',
				'wpmem_settings_legacy',
				'wpmem_settings_time_exp',
				'wpmem_settings_trial',
				'wpmem_settings_ignore_warnings'
				);
				
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
				
				if (WPMEM_DEBUG == true) {
					echo $post_arr[$row]." ".$_POST[$post_arr[$row]]."<br />";
				}
				
				/* 	
					if we are setting registration to be moderated, 
					check to see if the current admin has been 
					activated so they don't accidentally lock themselves
					out later 
				*/
				if ($row == 5) {
					if ($_POST[$post_arr[$row]] == 1) {
						global $current_user;
						get_currentuserinfo();
						$user_ID = $current_user->ID;
						update_user_meta( $user_ID, 'active', 1 );
					}
				}			
			}
			
			// new in 2.5
			$wpmem_settings_msurl = $_POST['wpmem_settings_msurl'];
			if( $wpmem_settings_msurl != 'http://' ) {
				update_option( 'wpmembers_msurl', trim( $wpmem_settings_msurl ) );
			}
			$wpmem_settings_regurl = $_POST['wpmem_settings_regurl'];
			if( $wpmem_settings_regurl != 'http://' ) {
				update_option( 'wpmembers_regurl', trim( $wpmem_settings_regurl ) );
			} 
			$wpmem_settings_cssurl = $_POST['wpmem_settings_cssurl'];
			if( $wpmem_settings_cssurl != 'http://' ) {
				update_option( 'wpmembers_cssurl', trim( $wpmem_settings_cssurl ) );
			}

			update_option( 'wpmembers_settings', $wpmem_newsettings );
			$wpmem_settings = $wpmem_newsettings;
			$did_update = __( 'WP-Members settings were updated', 'wp-members' );
			
			// sets the options tab as active - can remove if we change to another layout
			$active_tab = 'options';
			
			break;

		case ("update_fields"):

			//check nonce
			check_admin_referer('wpmem-update-fields');

			// rebuild the array, don't touch user_email - it's always mandatory
			$nrow = 0;
			for( $row = 0; $row < count($wpmem_fields); $row++ ) {

				// check to see if the field is checked for deletion, and if not, add it to the new array.
				$delete_field = "del_".$wpmem_fields[$row][2];
				$delete_field = $_POST[$delete_field];
				if( $delete_field != "delete" ) {

					for( $i = 0; $i < 4; $i++ ) {
						$wpmem_newfields[$nrow][$i] = $wpmem_fields[$row][$i];
					}
					
					$wpmem_newfields[$nrow][0] = $nrow + 1;
		
					$display_field = $wpmem_fields[$row][2]."_display"; 
					$require_field = $wpmem_fields[$row][2]."_required";
					$checked_field = $wpmem_fields[$row][2]."_checked";
		
					if( $wpmem_fields[$row][2] != 'user_email' ){
						//if ($_POST[$display_field] == "on") {$wpmem_newfields[$row][4] = 'y';}
						//if ($_POST[$require_field] == "on") {$wpmem_newfields[$row][5] = 'y';}
						$wpmem_newfields[$nrow][4] = $_POST[$display_field];
						$wpmem_newfields[$nrow][5] = $_POST[$require_field];
					} else {
						$wpmem_newfields[$nrow][4] = 'y';
						$wpmem_newfields[$nrow][5] = 'y';		
					}
		
					if( $wpmem_newfields[$nrow][4] != 'y' && $wpmem_newfields[$nrow][5] == 'y' ) { $chkreq = "err"; }
					$wpmem_newfields[$nrow][6] = $wpmem_fields[$row][6];
					if( $wpmem_fields[$row][7] ) { $wpmem_newfields[$nrow][7] = $wpmem_fields[$row][7]; }
					if( $wpmem_fields[$row][3] == 'checkbox' ) { 
						if( $_POST[$checked_field] == 'y' ) { echo "checked: " . $_POST[$checked_field];
							$wpmem_newfields[$nrow][8] = 'y';
						} else {
							$wpmem_newfields[$nrow][8] = 'n';
						}
					}
				
					$nrow = $nrow + 1;
				}
				
			}
			
			if( $_POST['add_field'] == 'add' ) {
			
				// error check that field label and option name are included and unique
				if( ! $_POST['add_name'] )   { $add_field_err_msg = __( 'Field Label is required for adding a new field. Nothing was updated.', 'wp-members' ); }
				if( ! $_POST['add_option'] ) { $add_field_err_msg = __( 'Option Name is required for adding a new field. Nothing was updated.', 'wp-members' ); }
				// @todo check for duplicate field names
			
				// error check option name for spaces and replace with underscores
				$us_option = $_POST['add_option'];
				$us_option = preg_replace("/ /", '_', $us_option);
					
				$wpmem_newfields[$nrow][0] = $nrow + 1;
				$wpmem_newfields[$nrow][1] = stripslashes( $_POST['add_name'] );
				$wpmem_newfields[$nrow][2] = $us_option;
				$wpmem_newfields[$nrow][3] = $_POST['add_type'];
				$wpmem_newfields[$nrow][4] = $_POST['add_display'];
				$wpmem_newfields[$nrow][5] = $_POST['add_required'];
				$wpmem_newfields[$nrow][6] = 'n';
				
				if( $_POST['add_type'] == 'checkbox' ) { 
					$wpmem_newfields[$nrow][7] = $_POST['add_checked_value'];
					$wpmem_newfields[$nrow][8] = $_POST['add_checked_default'];
				}
				
				if( $_POST['add_type'] == 'select' ) {
					// get the values
					$str = stripslashes( $_POST['add_dropdown_value'] );
					// remove linebreaks
					$str = trim( str_replace( array("\r", "\r\n", "\n"), '', $str ) );
					// create array
					$wpmem_newfields[$nrow][7] = explode( ',', $str );
				}
			}
			
			if( WPMEM_DEBUG == true ) { echo "<pre>"; print_r($wpmem_newfields); echo "</pre>"; }
			
			if( ! $add_field_err_msg ) {
				update_option('wpmembers_fields',$wpmem_newfields);
				$wpmem_fields = $wpmem_newfields; 
				$did_update = __('WP-Members fields were updated', 'wp-members');
			} else {
				$did_update = $add_field_err_msg;
			}
			
			// sets the fields tab as active - can remove if we change to another layout
			$active_tab = 'fields';
			
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
			
			// new in 2.4 for Terms of Service
			update_option('wpmembers_tos', $_POST['dialogs_tos']);		
			
			$did_update = __('WP-Members dialogs were updated', 'wp-members');
			
			// sets the dialogs tab as active - can remove if we change to another layout
			$active_tab = 'dialogs';
					
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
			$did_update = __('reCAPTCHA was updated for WP-Members', 'wp-members');
			
			// sets the captcha tab as active - can remove if we change to another layout
			$active_tab = 'captcha';
			
			break;

		case ("update_exp"):
		
			//check nonce
			check_admin_referer('wpmem-update-exp');
			
			$wpmem_newexperiod = wpmem_a_newexperiod();
			update_option('wpmembers_experiod',$wpmem_newexperiod);
			
			$wpmem_newpaypal = wpmem_a_newpaypal();
			update_option( 'wpmembers_paypal', $wpmem_newpaypal ); 
			
			$wpmem_experiod = $wpmem_newexperiod; if (WPMEM_DEBUG == true) { var_dump($wpmem_experiod); }
			$did_update = __('WP-Members expiration periods were updated', 'wp-members');
			
			// sets the exp tab as active - can remove if we change to another layout
			$active_tab = 'exp';
			
			break;
			
		case( "update_emails" ):
			
			//check nonce
			check_admin_referer( 'wpmem-update-emails' );
			
			// update the email address (if applicable)
			if( $_POST['wp_mail_from'] ) { 
				update_option( 'wpmembers_email_wpfrom', $_POST['wp_mail_from'] ); 
			} else {
				delete_option( 'wpmembers_email_wpfrom' );
			}
			if( $_POST['wp_mail_from_name'] ) { 
				update_option( 'wpmembers_email_wpname', $_POST['wp_mail_from_name'] ); 
			} else {
				delete_option( 'wpmembers_email_wpname' );
			}
			
			// update the various emails being used
			if( $wpmem_settings[5] == 0 ) {
				$arr = array( 'wpmembers_email_newreg' );
			} else {
				$arr = array( 'wpmembers_email_newmod', 'wpmembers_email_appmod' );
			}
			array_push( $arr, 'wpmembers_email_repass' );
			if( $wpmem_settings[4] == 1 ) {
				array_push( $arr, 'wpmembers_email_notify' );
			}
			array_push(	$arr, 'wpmembers_email_footer' );
			
			for( $row = 0; $row < ( count( $arr ) - 1 ); $row++ ) {
				$arr2 = array( 
					"subj" => $_POST[$arr[$row] . '_subj'],
					"body" => $_POST[$arr[$row] . '_body']
				);
				update_option( $arr[$row], $arr2, false );
				$arr2 = '';
			}
			
			// updated the email footer
			update_option( $arr[$row], $_POST[$arr[$row] . '_body'], false );
			
			
			$did_update = __('WP-Members emails were updated', 'wp-members');
			
			$active_tab = 'emails';
			
			break;

		}
	}
	?>
    <div class="wrap">
	<?php screen_icon( 'options-general' ); ?>
    <h2>WP-Members <?php _e('Settings', 'wp-members'); ?></h2>

    <?php
	if ($did_update != false) {

		if ($chkreq == "err") { ?>
			<div class="error"><p><strong><?php _e('Settings were saved, but you have required fields that are not set to display!', 'wp-members'); ?></strong><br /><br />
				<?php _e('Note: This will not cause an error for the end user, as only displayed fields are validated.  However, you should still check that your displayed and required fields match up.  Mismatched fields are highlighted below.', 'wp-members'); ?></p></div>
		<?php } elseif( $add_field_err_msg ) { ?>
        	<div class="error"><p><strong><?php echo $add_field_err_msg; ?></p></div>
        <?php } else { ?>
			<div id="message" class="updated fade"><p><strong><?php echo $did_update; ?></strong></p></div>
		<?php }

	}


	/*************************************************************************
		WARNING MESSAGES
	**************************************************************************/

	// settings allow anyone to register
	if ( get_option('users_can_register') != 0 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(1);
	}

	// settings allow anyone to comment
	if ( get_option('comment_registration') !=1 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(2);
	} 
	
	// rss set to full text feeds
	if ( get_option('rss_use_excerpt') !=1 && $wpmem_settings[11] == 0 ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(3);
	} 

	// holding registrations but haven't changed default successful registration message
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[5] == 1 && $wpmem_dialogs[3] == 'Congratulations! Your registration was successful.<br /><br />You may now login using the password that was emailed to you.' ) { 
		include_once('wp-members-dialogs-admin.php');
		wpmem_a_warning_msg(4);
	}  

	// turned off registration but also have set to moderate and/or email new registrations
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[7] == 1 ) { 
		if ( $wpmem_settings[5] == 1 || $wpmem_settings[4] ==1 ) { 
			include_once('wp-members-dialogs-admin.php');
			wpmem_a_warning_msg(5);
		}  
	}
	
	// haven't entered recaptcha api keys
	if ( $wpmem_settings[11] == 0 && $wpmem_settings[6] == 1 ) {
		$wpmem_captcha = get_option('wpmembers_captcha');
		if ( !$wpmem_captcha[0]  || !$wpmem_captcha[1] ) {
			include_once('wp-members-dialogs-admin.php');
			wpmem_a_warning_msg(6);
		}
	}
	
	/*************************************************************************
		END WARNING MESSAGES
	**************************************************************************/	?>


	<p><strong><a href="http://rocketgeek.com" target="_blank">WP-Members</a> <?php _e('Version:', 'wp-members'); echo "&nbsp;".WPMEM_VERSION; ?></strong>
		[ <a href="http://rocketgeek.com/plugins/wp-members/quick-start-guide/">Quick Start Guide</a> | <a href="http://rocketgeek.com/plugins/wp-members/users-guide/">Online User Guide</a> | <a href="http://rocketgeek.com/plugins/wp-members/users-guide/faqs/">FAQs</a> ] 
		[ <?php _e('Follow', 'wp-members'); ?> ButlerBlog: <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a> ]<br />
	<?php if( ! defined( 'WPMEM_REMOVE_ATTR' ) ) { ?>
		<br /><a href="http://rocketgeek.com/about/site-membership-subscription/">Find out how to get access to WP-Members private members forum, premium code snippets, tutorials, and more!</a>
	<?php } ?>
	</p>
	
	<?php 
	// check for which admin tabs need to be included
	if( $wpmem_settings[6] == 1 ) { $show_recaptcha = true; }
	if( WPMEM_EXP_MODULE == true && ( $wpmem_settings[9] == 1 || $wpmem_settings[10] == 1 ) ) {
		//$show_paypal = true;
		$show_subscriptions = true;
	}
	?>
	
	<ul class="tabs">
		<li<?php if( $active_tab == 'options' || ! $active_tab ) { echo ' class="active"'; } ?>><a href="#tab1"><?php _e( 'Options', 'wp-members' ); ?></a></li>
		<li<?php if( $active_tab == 'fields' ) { echo ' class="active"'; } ?>><a href="#tab2"><?php _e( 'Fields', 'wp-members' ); ?></a></li>
		<li<?php if( $active_tab == 'dialogs' ) { echo ' class="active"'; } ?>><a href="#tab3"><?php _e( 'Dialogs', 'wp-members' ); ?></a></li>
		<li<?php if( $active_tab == 'emails' ) { echo ' class="active"'; } ?>><a href="#tab4"><?php _e( 'Emails', 'wp-members' ); ?></a></li>
		<?php if( $show_recaptcha == true ) { ?>
		<li<?php if( $active_tab == 'captcha' ) { echo ' class="active"'; } ?>><a href="#tab5"><?php _e( 'reCAPTCHA', 'wp-members' ); ?></a></li>
		<?php }
		if( $show_subscriptions == true ) { ?> 
		<li<?php if( $active_tab == 'exp' ) { echo ' class="active"'; } ?>><a href="#tab6"><?php _e( 'Subscriptions', 'wp-members' ); ?></a></li>
		<?php }
		//if( $show_paypal == true ) { ?>
		<!--<li><a href="#tab7"><?php _e( 'PayPal Settings', 'wp-members' ); ?></a></li>-->
		<?php //} ?>
	</ul>

	<div class="tab_container">

		<div id="tab1" class="tab_content<?php if( $active_tab == 'options' || ! $active_tab ) { echo ' active'; } ?>">
			<?php wpmem_a_build_options($wpmem_settings); ?>
		</div>

		<div id="tab2" class="tab_content<?php if( $active_tab == 'fields' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_fields($wpmem_fields); ?>
		</div>

		<div id="tab3" class="tab_content<?php if( $active_tab == 'dialogs' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_dialogs($wpmem_dialogs); ?>	
		</div>
		
		<div id="tab4" class="tab_content<?php if( $active_tab == 'emails' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_emails( $wpmem_settings ); ?>	
		</div>
		
		<?php if ($show_recaptcha == true ) { ?>
		<div id="tab5" class="tab_content<?php if( $active_tab == 'captcha' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_captcha_options(); ?>
		</div>
		<?php } 
		
		if ($show_subscriptions == true ) { ?>
		<div id="tab6" class="tab_content<?php if( $active_tab == 'exp' ) { echo ' active'; } ?>">
			<?php wpmem_a_build_expiration( $wpmem_experiod, $wpmem_settings[10], $wpmem_settings[9] ); ?>
		</div>
		<?php }
		
		//if ($show_paypal == true) { ?>
		<!--<div id="tab7" class="tab_content">
			<?php //wpmem_a_build_paypal(); ?>
		</div>-->
		<?php //} ?>

	</div>
	<p>&nbsp;</p>
		<p><i>
		<?php printf(__('Thank you for using WP-Members! You are using version %s', 'wp-members'), WPMEM_VERSION); ?>.<br />
		WP-Members is copyright &copy; 2006-<?php echo date("Y"); ?>  by Chad Butler, <a href="http://butlerblog.com">butlerblog.com</a> | 
		  <a href="http://feeds.butlerblog.com/butlerblog" target="_blank">RSS</a> | <a href="http://www.twitter.com/butlerblog" target="_blank">Twitter</a><br />
		WP-Members is a trademark of <a href="http://butlerblog.com">butlerblog.com</a><br />
		Premium support and installation service <a href="http://rocketgeek.com/about/site-membership-subscription/">available at rocketgeek.com</a>.
		</i></p>
		<p>&nbsp;</p>
	</div>
<?php
}


/**
 * builds the captcha options
 */
function wpmem_a_build_captcha_options()
{ 
	$wpmem_captcha = get_option('wpmembers_captcha');
	$url           = home_url();
	?>

	<h3><?php _e( 'Manage reCAPTCHA Options', 'wp-members' ); ?></h3>
    	<form name="updatecaptchaform" id="updatecaptchaform" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>"> 
	<?php if ( function_exists('wp_nonce_field') ) { wp_nonce_field('wpmem-update-captcha'); } ?>
	<table class="form-table">
		<tr>
			<td colspan="2">
            	<p><?php _e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog.', 'wp-members' ); ?></p>
				<p><?php printf(__('reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot while also correcting the automatic scans of old books. So you get less spam, and the world gets accurately digitized books. Everybody wins! For details, visit the %s reCAPTCHA website%s', 'wp-members'), '<a href="http://recaptcha.net/" target="_blank">', '</a>'); ?>.</p>
                <p>
            </td>
		</tr>        
		<tr valign="top"> 
			<th scope="row"><?php _e( 'reCAPTCHA Keys', 'wp-members' ); ?></th> 
			<td>
            	<?php printf( __( 'reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a %s free reCAPTCHA key%s', 'wp-members' ), "<a href=\"http://recaptcha.net/api/getkey?domain=$url&amp;app=wordpress\" target=\"_blank\">", '</a>' ); ?>.<br />
            	<?php _e( 'Public Key', 'wp-members' ); ?>:&nbsp;&nbsp;&nbsp;<input type="text" name="wpmem_captcha_publickey" size="50" value="<?php echo $wpmem_captcha[0]; ?>" /><br />
                <?php _e( 'Private Key', 'wp-members' ); ?>:&nbsp;<input type="text" name="wpmem_captcha_privatekey" size="50" value="<?php echo $wpmem_captcha[1]; ?>" />
             </td> 
		</tr>
        <tr valign="top">
        	<th scope="row"><?php _e( 'Choose Theme', 'wp-members' ); ?></th>
            <td>
            	<select name="wpmem_captcha_theme">
					<!--<?php echo wpmem_create_formfield( __( 'WP-Members', 'wp-members' ), 'option', 'custom', $wpmem_captcha[2] ); ?>--><?php
					echo wpmem_create_formfield( __( 'Red', 'wp-members' ), 'option', 'red', $wpmem_captcha[2] ); 
					echo wpmem_create_formfield( __( 'White', 'wp-members' ), 'option', 'white', $wpmem_captcha[2] );
					echo wpmem_create_formfield( __( 'Black Glass', 'wp-members' ), 'option', 'blackglass', $wpmem_captcha[2] ); 
					echo wpmem_create_formfield( __( 'Clean', 'wp-members' ), 'option', 'clean', $wpmem_captcha[2] ); ?>
					<!--<?php echo wpmem_create_formfield( __( 'Custom', 'wp-members' ), 'option', 'custom', $wpmem_captcha[2] ); ?>-->
                </select>
            </td>
        </tr><!--
		<tr valign="top"> 
			<th scope="row">Custom reCAPTCHA theme</th> 
			<td><input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?></span></td> 
		</tr>-->
		<tr valign="top"> 
			<th scope="row">&nbsp;</th> 
			<td>
				<input type="hidden" name="wpmem_admin_a" value="update_captcha" />
                <input type="submit" name="save"  class="button-primary" value="<?php _e( 'Update reCAPTCHA Settings', 'wp-members' ); ?> &raquo;" />
			</td> 
		</tr> 
	</table> 
	</form>
	<?php 
}


/*****************************************************
	End WP-Members Settings Screen
*****************************************************/


/*****************************************************
	Bulk User Management Screen
*****************************************************/

/**
 * User management panel
 *
 * Creates the bulk user management panel for the
 * Users > WP-Members page.
 *
 * @since 2.4
 */
function wpmem_admin_users()
{	
	// define variables
	$col_phone = ''; $col_country = ''; $user_action_msg = '';
	
	// check to see if we need phone and country columns
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ )
	{ 
		if( $wpmem_fields[$row][2] == 'country' && $wpmem_fields[$row][4] == 'y' ) { $col_country = true; }
		if( $wpmem_fields[$row][2] == 'phone1' && $wpmem_fields[$row][4] == 'y' ) { $col_phone = true; }
	}
	
	// should run other checks for expiration, activation, etc...
	
	// here is where we handle actions on the table...
	
	if( $_POST ) { 
	
		if( $_POST['action'] ) { 
			$action = $_POST['action']; 
		} elseif( $_POST['action2'] ) { 
			$action = $_POST['action2']; 
		}	
		
		$users = $_POST['users'];

		switch( $action ) {
		
		case "activate":
			// find out if we need to set passwords
			$wpmem_fields = get_option( 'wpmembers_fields' );
			for ( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
				if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = true; }
			}
			$x = 0;
			foreach( $users as $user ) {
				// check to see if the user is already activated, if not, activate
				if( ! get_user_meta( $user, 'active', true ) ) {
					wpmem_a_activate_user( $user, $chk_pass );
					$x++;
				}
			}
			$user_action_msg = sprintf( __( '%d users were activated.', 'wp-members' ), $x );
			break;
			
		case "export":
			update_option( 'wpmembers_export', $users );
			$user_action_msg = sprintf( __( 'Users ready to export, %s click here %s to generate and download a CSV.', 'wp-members' ),  '<a href="' . WP_PLUGIN_URL . '/wp-members/wp-members-export.php" target="_blank">', '</a>' );
			break;
		
		}
		
	} ?>

	<div class="wrap">

		<div id="icon-users" class="icon32"><br /></div>
		<h2><?php _e( 'WP-Members Users', 'wp-members' ); ?>  <a href="user-new.php" class="button add-new-h2"><?php _e( 'Add New', 'wp-members' ); ?></a></h2>
		
	<?php if( $user_action_msg ) { ?>

		<div id="message" class="updated fade"><p><strong><?php echo $user_action_msg; ?></strong></p></div>

	<?php } ?>

		<form id="posts-filter" action="<?php echo $_SERVER['REQUEST_URI']?>" method="post">
	
		<div class="filter">
			<ul class="subsubsub">
			
			<?php
			
			// For now, I don't see a good way of working this for localization without a 
			// huge amount of additional programming (like a multi-dimensional array)
			$show = $lcas = $curr = '';
			$tmp  = array( "All", "Not Active", "Trial", "Subscription", "Expired", "Not Exported" );
			if( isset( $_GET['show'] ) ) { $show = $_GET['show']; }
			for( $row = 0; $row < count( $tmp ); $row++ )
			{
				
				$link = "users.php?page=wpmem-users";
				if( $row != 0 ) {
				
					$lcas = strtolower( $tmp[$row] );
					$lcas = str_replace( " ", "", $lcas );
					$link.= "&#038;show=";
					$link.= $lcas;
					
					$curr = "";
						if( $show == $lcas ) { $curr = ' class="current"'; }
					
				} else {
				
					if( ! $show ) { $curr = ' class="current"'; }
					
				}
				
				$end = "";
				if( $row != 5 ) { $end = " |"; }

				$echolink = true;
				if( $lcas == "notactive" && WPMEM_MOD_REG != 1 ) { $echolink = false; }
				if( $lcas == "trial"     && WPMEM_USE_TRL != 1 ) { $echolink = false; }
				
				if( ( $lcas == "subscription" || $lcas == "expired" ) && WPMEM_USE_EXP != 1 ) { $echolink = false; }
				
				if( $echolink ) { echo "<li><a href=\"$link\"$curr>$tmp[$row] <span class=\"count\"></span></a>$end</li>"; }
			}

			?>
			</ul>
		</div>

		<?php // NOT YET... ?><!--
			<p class="search-box">
				<label class="screen-reader-text" for="user-search-input">Search Users:</label>
				<input type="text" id="user-search-input" name="usersearch" value="" />

				<input type="submit" value="Search Users" class="button" />
			</p>-->
	<?php
	
		// done with the action items, now build the page
		$users_per_page = 10;

		// workout the different queries, etc...
		if( ! $show ) {

			$result = count_users();

			if( isset( $_REQUEST['paged'] ) ) { 
				$paged = $_REQUEST['paged']; 
			} else {
				$paged = 1;
			}

			$arr = array(
				'show' => 'Total Users',
				'show_num' => $users_per_page,
				'total_users' =>  $result['total_users'],
				'pages' => ( ceil( ( $result['total_users'] ) / $users_per_page ) ),
				'paged' => $paged,
				'link' => "users.php?page=wpmem-users"
				);

			if( $paged == 1 ) { 
				$offset = 0;
			} else {
				$offset = ( ( $arr['paged'] * $users_per_page ) - $users_per_page );
			}
			
			$args = array(
				'offset' => $offset,
				'number' => $users_per_page,
				'fields' => 'all'
				);
		
		} elseif( $show == 'notactive' ) {

			/*global $wpdb;
			
			$sql = "SELECT user_id FROM `wp32`.`wp_usermeta` WHERE meta_key = 'active' AND meta_value = 1;";
			$user = $wpdb->get_results($sql, OBJECT);
			
			*/

		} elseif( $show == 'notexported' ) {

		}
		// http://codex.wordpress.org/Function_Reference/get_users
			
		$users = get_users( $args ); // get_users_of_blog(); ?>		


		<?php wpmem_a_build_user_action( true, $arr ); ?>

		<table class="widefat fixed" cellspacing="0">
			<thead>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</thead>

			<tfoot>
			<?php //$colspan = wpmem_a_build_user_tbl_head( $col_phone, $col_country ); ?>
			<?php $colspan = wpmem_a_build_user_tbl_head( array( 'phone'=>$col_phone, 'country'=>$col_country ) ); ?>
			</tfoot>

			<tbody id="users" class="list:user user-list">

			<?php	
			if( WPMEM_DEBUG == true ) { echo "<pre>\n"; print_r( $users ); echo "</pre>\n"; }
			$x=0; $class = '';
			foreach( $users as $user )
			{
				// are we filtering results? (active, trials, etc...)
				
				$chk_show = false; 
				switch( $show ) {
				case "notactive":
					if( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) { $chk_show = true; }
					break;
				case "trial":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					if( $chk_exp_type == 'trial' ) { $chk_show = true; }
					break;
				case "subscription":
					$chk_exp_type = get_user_meta( $user->ID, 'exp_type', 'true' );
					if( $chk_exp_type == 'subscription' ) { $chk_show = true; }
					break;
				case "expired":
					if( wpmem_chk_exp( $user->ID ) ) { $chk_show = true; }
					break;
				case "notexported":
					if( get_user_meta( $user->ID, 'exported', 'true' ) != 1 ) { $chk_show = true; }
					break;
				}

				if( !$show || $chk_show == true ) {
					
					$class = ( $class == 'alternate' ) ? '' : 'alternate';

					echo "<tr id=\"{$user->ID}\" class=\"$class\">\n";
					echo "	<th scope='row' class='check-column'><input type='checkbox' name='users[]' id=\"user_{$user->ID}\" class='administrator' value=\"{$user->ID}\" /></th>\n";
					echo "	<td class=\"username column-username\" nowrap>\n";
					echo "		<strong><a href=\"user-edit.php?user_id={$user->ID}&#038;" . esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ) . "\">" . $user->user_login . "</a></strong><br />\n";
					echo "	</td>\n";
					echo "	<td class=\"name column-name\" nowrap>" . get_user_meta( $user->ID, 'first_name', 'true' ) . "&nbsp" . get_user_meta( $user->ID, 'last_name', 'true' ) . "</td>\n";
					echo "	<td class=\"email column-email\" nowrap><a href='mailto:" . $user->user_email . "' title='E-mail: " . $user->user_email . "'>" . $user->user_email . "</a></td>\n";
					
					if( $col_phone == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . get_user_meta( $user->ID, 'phone1', 'true' ) . "</td>\n";
					}
					
					if( $col_country == true ) {
						echo "	<td class=\"email column-email\" nowrap>" . get_user_meta( $user->ID, 'country', 'true' ) . "</td>\n";
					}
					
					if( WPMEM_MOD_REG == 1 ) { 
						echo "	<td class=\"role column-role\" nowrap>";
						if( get_user_meta( $user->ID, 'active', 'true' ) != 1 ) { _e( 'No', 'wp-members' ); }
						echo "</td>\n";
					}
					
					if( WPMEM_USE_EXP == 1 ) {
						if( WPMEM_USE_TRL == 1 ) {
							echo "	<td class=\"email column-email\" nowrap>"; echo ucfirst( get_user_meta( $user->ID, 'exp_type', true ) ); echo "</td>\n";
						}
						echo "	<td class=\"email column-email\" nowrap>"; echo get_user_meta( $user->ID, 'expires', true ); echo "</td>\n";
					}
					echo "</tr>\n"; $x++;
				}
			} 
			
			if( $x == 0 ) { echo "<tr><td colspan=\"$colspan\">"; _e( 'No users matched your criteria', 'wp-members' ); echo "</td></tr>"; } ?>

		</table>
		
		<?php wpmem_a_build_user_action( false, $arr ); ?>

		</form>
		
		<br />
		<p>This button exports the full user list. To export based on other criteria, use the form above.</p>
		<form method="link" action="../wp-content/plugins/wp-members/wp-members-export-full.php">
			<input type="submit" class="button-secondary" value="Export All Users">
		</form>
	</div>
<?php
}


/**
 * Activates a user
 *
 * If registration is moderated, sets the activated flag 
 * in the usermeta. Flag prevents login when WPMEM_MOD_REG
 * is true (1). Function is fired from bulk user edit or
 * user profile update.
 *
 * @since 2.4
 *
 * @param int  $user_id
 * @param bool $chk_pass
 * @uses $wpdb WordPress Database object
 */
function wpmem_a_activate_user( $user_id, $chk_pass = false )
{
	// define new_pass
	$new_pass = '';
	
	// If passwords are user defined skip this
	if( ! $chk_pass ) {
		// generates a password to send the user
		$new_pass = wp_generate_password();
		$new_hash = wp_hash_password( $new_pass );
		
		// update the user with the new password
		global $wpdb;
		$wpdb->update( $wpdb->users, array( 'user_pass' => $new_hash ), array( 'ID' => $user_id ), array( '%s' ), array( '%d' ) );
	}
	
	// if subscriptions can expire, set the user's expiration date
	if( WPMEM_USE_EXP == 1 ) { wpmem_set_exp( $user_id ); }

	// generate and send user approved email to user
	require_once( 'wp-members-email.php' );
	wpmem_inc_regemail( $user_id, $new_pass, 2 );
	
	// set the active flag in usermeta
	update_user_meta( $user_id, 'active', 1 );
}


/**
 * Deactivates a user
 *
 * Reverses the active flag from the activation process
 * preventing login when registration is moderated.
 *
 * @since 2.7.1
 *
 * @param int $user_id
 */
function wpmem_a_deactivate_user( $user_id )
{
	update_user_meta( $user_id, 'active', 0 );
}


/**
 * builds the user action dropdown
 *
 * @param bool  $top
 * @param array $arr
 *
 * @since 2.4
 */
function wpmem_a_build_user_action( $top, $arr )
{ ?>
	<div class="tablenav<?php if( $top ){ echo ' top'; }?>">
		<div class="alignleft actions">
			<select name="action<?php if( !$top ) { echo '2'; } ?>">
				<option value="" selected="selected"><?php _e('Bulk Actions', 'wp-members'); ?></option>
			<?php if (WPMEM_MOD_REG == 1) { ?>
				<option value="activate"><?php _e('Activate', 'wp-members'); ?></option>
			<?php } ?>
				<option value="export"><?php _e('Export', 'wp-members'); ?></option>
			</select>
			<input type="submit" value="<?php _e('Apply', 'wp-members'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		<?php if( $arr['show'] == 'Total Users' ) { ?>
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $arr['show'] . ': ' . $arr['total_users']; ?></span>
		<?php if( $arr['total_users'] > $arr['show_num'] ) { ?>
			<span class="pagination-links">
				<a class="first-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the first page" href="<?php echo $arr['link']; ?>">&laquo;</a>
				<a class="prev-page<?php if( $arr['paged'] == 1 ){ echo ' disabled'; } ?>" title="Go to the previous page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] - 1 ); ?>">&lsaquo;</a>
				
				<span class="paging-input"><input class="current-page" title="Current page" type="text" name="paged" value="<?php echo $arr['paged']; ?>" size="1" /> of <span class="total-pages"><?php echo $arr['pages']; ?></span></span>
				
				<a class="next-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the next page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo ( $arr['paged'] + 1 ); ?>">&rsaquo;</a>
				<a class="last-page<?php if( $arr['paged'] == $arr['pages'] ){ echo ' disabled'; } ?>" title="Go to the last page" href="<?php echo $arr['link']; ?>&#038;paged=<?php echo $arr['pages']; ?>">&raquo;</a>
			</span>
		<?php } ?>
		</div>
		<?php } ?>
		<br class="clear" />
	</div>
<?php 	
}


/**
 * builds the user management table heading
 *
 * @since 2.4
 *
 * @param array $args
 */
function wpmem_a_build_user_tbl_head( $args )
{
	$arr = array( 'Username', 'Name', 'E-mail', 'Phone', 'Country', 'Activated?', 'Subscription', 'Expires' ); ?>

	<tr class="thead">
		<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<?php $c = 1; 
	foreach( $arr as $val ) { 

		$showcol = false;
		switch ($val) {
		case "Phone":
			if( $args['phone'] == true ) { $showcol = true; $c++; }
			break;
		case "Country":
			if( $args['country'] == true ) { $showcol = true; $c++; }
			break;
		case "Activated?":
			if( WPMEM_MOD_REG == 1 ) { $showcol = true; $c++; }
			break;
		case "Subscription":
			if( WPMEM_USE_EXP == 1 && WPMEM_USE_TRL == true ) { $showcol = true; $c++; }
			break;
		case "Expires":
			if( WPMEM_USE_EXP == 1 ) { $showcol = true; $c++; }
			break;
		default:
			$showcol = true; $c++; 
			break;
		} 		
		if( $showcol == true ) { ?>
		<th scope="col" class="manage-column" style=""><?php echo $val ?></th>
	<?php } 
	} ?>
	</tr><?php 
	return $c;
}


/*****************************************************
	End Bulk User Management Screen
*****************************************************/


/*****************************************************
	New features associated with field management
*****************************************************/


/**
 * loads the admin javascript file
 */
function wpmem_load_admin_js()
{
	// queue up admin ajax and styles 
	$plugin_path = plugin_dir_url ( __FILE__ );
	wp_enqueue_script( 'wpmem-admin-js',  $plugin_path.'js/wp-members-admin.js', '', WPMEM_VERSION ); 
	wp_enqueue_style ( 'wpmem-admin-css', $plugin_path.'css/wp-members-styles-admin.css', '', WPMEM_VERSION );
}


add_action( 'wp_ajax_wpmem_a_field_reorder', 'wpmem_a_field_reorder' );
/**
 * reorders the fields on DnD
 */
function wpmem_a_field_reorder()
{
	// start fresh
	$new_order = $wpmem_old_fields = $wpmem_new_fields = $key = $row = '';

	$new_order = $_REQUEST['orderstring'];
	$new_order = explode("&", $new_order);	
	
	// loop through $new_order to create new field array
	$wpmem_old_fields = get_option('wpmembers_fields');
	for ( $row = 0; $row < count( $new_order ); $row++ )  {
		if ($row > 0) {
			$key = $new_order[$row];
			$key = substr($key, 15); //echo $key.", ";
			
			for ( $x = 0; $x < count( $wpmem_old_fields ); $x++ )  {
				
				if ( $wpmem_old_fields[$x][0] == $key ) {
					$wpmem_new_fields[$row - 1] = $wpmem_old_fields[$x];
				}
			}
		}
	}
	
	update_option('wpmembers_fields', $wpmem_new_fields); 

	die(); // this is required to return a proper result
}


/*****************************************************
	New features associated with custom emails
*****************************************************/


/**
 * builds the emails panel
 *
 * @since 2.7
 *
 * @param array $wpmem_settings
 */
function wpmem_a_build_emails( $wpmem_settings )
{ 

	if( $wpmem_settings[5] == 0 ) {
		$wpmem_email_title_arr = array(
			array( __( "New Registration", 'wp-members' ), 'wpmembers_email_newreg' )
		);
	} else {
        $wpmem_email_title_arr = array(
			array( __( "Registration is Moderated", 'wp-members' ), 'wpmembers_email_newmod' ),
			array( __( "Registration is Moderated, User is Approved", 'wp-members' ), 'wpmembers_email_appmod' )
		);
	}
	array_push( 
		$wpmem_email_title_arr,
        array( __( "Password Reset", 'wp-members' ), 'wpmembers_email_repass' )
	);
	if( $wpmem_settings[4] == 1 ) {
		array_push(
			$wpmem_email_title_arr,
			array( __( "Admin Notification", 'wp-members' ), 'wpmembers_email_notify' )
		);
	}
	array_push(
		$wpmem_email_title_arr,
		array( __( "Email Signature", 'wp-members' ), 'wpmembers_email_footer' )
    ); ?>
	
	<h3>WP-Members <?php _e( 'Email Messages', 'wp-members' ); ?></h3>
	<p>
	<?php _e( 'You can customize the content of the emails sent by the plugin.', 'wp-members' ); ?><br />
	<a href="http://butlerblog.com/wp-members/users-guide/shortcodes/" target="_blank">
	<?php _e( 'A list of shortcodes is available here.', 'wp-members' ); ?></a>
	</p>
	<hr />
	<form name="updateemailform" id="updateemailform" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
	<?php if( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wpmem-update-emails' ); } ?>
		<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e( 'Set a custom email address', 'wp-members' ); ?></th> 
				<td><input type="text" name="wp_mail_from" size="40" value="<?php echo get_option( 'wpmembers_email_wpfrom' ); ?>" />&nbsp;<span class="description"><?php _e( '(optional)', 'wp-members' ); ?> email@yourdomain.com</span></td> 
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e( 'Set a custom email name', 'wp-members' ); ?></th> 
				<td><input type="text" name="wp_mail_from_name" size="40" value="<?php echo get_option( 'wpmembers_email_wpname' ); ?>" />&nbsp;<span class="description"><?php _e( '(optional)', 'wp-members' ); ?> John Smith</span></td>
			</tr>
			<tr><td colspan="2"><hr /></td></tr>
        
		<?php for( $row = 0; $row < ( count( $wpmem_email_title_arr ) - 1 ); $row++ ) { 
		
			$arr = get_option( $wpmem_email_title_arr[$row][1] );
		?>
			<tr valign="top"><td colspan="2"><strong><?php echo $wpmem_email_title_arr[$row][0]; ?></strong></td></tr>
			<tr valign="top"> 
				<th scope="row"><?php _e( 'Subject', 'wp-members' ); ?></th> 
				<td><input type="text" name="<?php echo $wpmem_email_title_arr[$row][1] . '_subj'; ?>" size="80" value="<?php echo stripslashes( $arr['subj'] ); ?>"></td> 
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Body', 'wp-members' ); ?></th>
				<td><textarea name="<?php echo $wpmem_email_title_arr[$row][1] . '_body'; ?>" rows="12" cols="50" id="" class="large-text code"><?php echo stripslashes( $arr['body'] ); ?></textarea></td>
			</tr>
			<tr><td colspan="2"><hr /></td></tr>
		<?php } 
		
			$arr = get_option( $wpmem_email_title_arr[$row][1] ); ?>
		
			<tr valign="top">
				<th scope="row"><strong><?php echo $wpmem_email_title_arr[$row][0]; ?></strong> <span class="description"><?php _e( '(optional)', 'wp-members' ); ?></span></th>
				<td><textarea name="<?php echo $wpmem_email_title_arr[$row][1] . '_body'; ?>" rows="10" cols="50" id="" class="large-text code"><?php echo stripslashes( $arr ); ?></textarea></td>
			</tr>
			<tr><td colspan="2"><hr /></td></tr>			
			<tr valign="top"> 
				<th scope="row">&nbsp;</th> 
				<td>
					<input type="hidden" name="wpmem_admin_a" value="update_emails" />
                    <input type="submit" name="save" class="button-primary" value="<?php _e( 'Update Emails', 'wp-members' ); ?> &raquo;" />
				</td> 
			</tr>	
		</table> 
	</form>
	<?php
}

// end of the admin features...
?>