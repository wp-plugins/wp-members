<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the plugin options tab.
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


/**
 * builds the settings panel
 *
 * @since 2.2.2
 *
 * @param array $wpmem_settings
 */
function wpmem_a_build_options( $wpmem_settings )
{ ?>
	<div class="metabox-holder has-right-sidebar">
	
		<div class="inner-sidebar">
			<?php wpmem_a_meta_box(); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
				<div class="inside">
					<strong><i>See the <a href="http://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/options/" target="_blank">Users Guide on plugin options</a>.</i></strong>
				</div>
			</div>
			<?php wpmem_a_rss_box(); ?>
		</div> <!-- .inner-sidebar -->	

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<h3><span><?php _e( 'Manage Options', 'wp-members' ); ?></span></h3>
					<div class="inside">
						<form name="updatesettings" id="updatesettings" method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
						<?php wp_nonce_field( 'wpmem-update-settings' ); ?>
							<table class="form-table">
							<?php $arr = array(
								array(__('Block Posts by default','wp-members'),'wpmem_settings_block_posts',__('Note: Posts can still be individually blocked or unblocked at the article level','wp-members')),
								array(__('Block Pages by default','wp-members'),'wpmem_settings_block_pages',__('Note: Pages can still be individually blocked or unblocked at the article level','wp-members')),
								array(__('Show excerpts','wp-members'),'wpmem_settings_show_excerpts',__('Shows excerpted content above the login/registration on both Posts and Pages','wp-members')),
								array(__('Notify admin','wp-members'),'wpmem_settings_notify',__('Sends email to admin for each new registration?','wp-members')),
								array(__('Moderate registration','wp-members'),'wpmem_settings_moderate',__('Holds new registrations for admin approval','wp-members')),
								array(__('Use reCAPTCHA','wp-members'),'wpmem_settings_captcha',__('Turns on CAPTCHA for registration','wp-members')),
								array(__('Turn off registration','wp-members'),'wpmem_settings_turnoff',__('Turns off the registration process, only allows login','wp-members')),
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
									<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if( $wpmem_settings[$row+1] == 1 ) { echo "checked"; }?> />&nbsp;&nbsp;
									<?php if( $arr[$row][2] ) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
								</td>
							  </tr>
							  <?php } ?>
							  <?php } ?>
							<?php $attribution = get_option( 'wpmembers_attrib' ); ?>
							  <tr valign="top">
								<th align="left" scope="row"><?php _e( 'Attribution', 'wp-members' ); ?></th>
								<td><input name="attribution" type="checkbox" id="attribution" value="1" <?php if( $attribution == 1 ) { echo "checked"; }?> />&nbsp;&nbsp;
									<span class="description"><?php _e( 'Attribution is appreciated!  Display "powered by" link on register form?', 'wp-members' ); ?></span>
								</td>
							  </tr>
							  <?php $wpmem_msurl = get_option( 'wpmembers_msurl' );
							  if( ! $wpmem_msurl ) { $wpmem_msurl = "http://"; } ?>
							  <tr>
								<th align="left" scope="row"><?php _e( 'User Profile Page:', 'wp-members' ); ?></th>
								<td>
									<select name="wpmem_settings_mspage">
										<?php wpmem_admin_page_list( $wpmem_msurl ); ?>
									</select>&nbsp;<?php _e( 'custom URL:', 'wp-members' ); ?>&nbsp;
									<input type="text" name="wpmem_settings_msurl" value="<?php echo $wpmem_msurl; ?>" size="50" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?></span></td>
							  </tr>
							  <?php $wpmem_regurl = get_option( 'wpmembers_regurl' );
							  if( ! $wpmem_regurl ) { $wpmem_regurl = "http://"; } ?>
							  <tr>
								<th align="left" scope="row"><?php _e( 'Register Page:', 'wp-members' ); ?></th>
								<td>
									<select name="wpmem_settings_regpage">
										<?php wpmem_admin_page_list( $wpmem_regurl ); ?>
									</select>&nbsp;<?php _e( 'custom URL:', 'wp-members' ); ?>&nbsp;	
									<input type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?></span></td>
							  </tr>
							  <?php $wpmem_style = get_option( 'wpmembers_style' ); ?>
							  <tr>
							    <th align="left" scope="row"><?php _e( 'Stylesheet:', 'wp-members' ); ?></th>
								<td><select name="wpmem_settings_style">
								<?php wpmem_admin_style_list(); ?>
								  </select>&nbsp;<span class="description"><?php _e( 'Select a stylesheet or specify a custom stylesheet below', 'wp-members' ); ?></span>
								</td>
							  </tr>							  
							  <?php $wpmem_cssurl = get_option( 'wpmembers_cssurl' );
							  if( ! $wpmem_cssurl ) { $wpmem_cssurl = "http://"; } ?>
							  <tr>
								<th align="left" scope="row"><?php _e( 'Custom Stylesheet:', 'wp-members' ); ?></th>
								<td><input type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" size="50" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?></span></td>
							  </tr>
							  <?php $auto_ex = get_option( 'wpmembers_autoex' ); ?>
							  <tr>
							    <th align="left" scope="row"><?php _e( 'Auto Excerpt:', 'wp-members' ); ?></th>
								<td><input type="checkbox" name="wpmem_autoex" value="1" <?php if( $auto_ex['auto_ex'] == 1 ) { echo "checked"; } ?> />&nbsp;&nbsp;&nbsp;&nbsp;<?php _e( 'Number of words in excerpt:', 'wp-members' ); ?> <input name="wpmem_autoex_len" type="text" size="5" value="<?php if( $auto_ex['auto_ex_len'] ) { echo $auto_ex['auto_ex_len']; } ?>" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?>. <?php _e( 'Automatically creates an excerpt', 'wp-members' ); ?></span></td>
							  </tr>
							  <tr valign="top">
								<td>&nbsp;</td>
								<td><input type="hidden" name="wpmem_admin_a" value="update_settings">
									<input type="submit" name="UpdateSettings"  class="button-primary" value="<?php _e( 'Update Settings', 'wp-members' ); ?> &raquo;" /> 
								</td>
							  </tr>
							</table>
						</form>
					</div><!-- .inside -->
				</div>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->
	</div><!-- .metabox-holder -->
	<?php
}


/**
 * Updates the plugin options
 *
 * @since 2.8.0
 *
 * @return string The options updated message
 */
function wpmem_update_options()
{
	//check nonce
	check_admin_referer( 'wpmem-update-settings' );

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
	for( $row = 0; $row < count( $post_arr ); $row++ ) {
		if( $post_arr == 'WPMEM_VERSION' ) {
			$wpmem_newsettings[$row] = 'WPMEM_VERSION';
		} else {
			if( isset( $_POST[$post_arr[$row]] ) != 1 ) {
				$wpmem_newsettings[$row] = 0;
			} else {
				$wpmem_newsettings[$row] = $_POST[$post_arr[$row]];
			}
		}
		
		if( WPMEM_DEBUG == true ) {
			echo $post_arr[$row] . ' ' . $_POST[$post_arr[$row]] . '<br />';
		}
		
		/* 	
			if we are setting registration to be moderated, 
			check to see if the current admin has been 
			activated so they don't accidentally lock themselves
			out later 
		*/
		if( $row == 5 ) {
			if( isset( $_POST[$post_arr[$row]] ) == 1) {
				global $current_user;
				get_currentuserinfo();
				$user_ID = $current_user->ID;
				update_user_meta( $user_ID, 'active', 1 );
			}
		}			
	}
	
	$wpmem_attribution = ( isset( $_POST['attribution'] ) ) ? 1 : 0;
	update_option( 'wpmembers_attrib', $wpmem_attribution );

	$wpmem_settings_msurl  = $_POST['wpmem_settings_msurl'];
	$wpmem_settings_mspage = $_POST['wpmem_settings_mspage'];
	if( $wpmem_settings_mspage ) { update_option( 'wpmembers_msurl', $wpmem_settings_mspage ); }
	if( $wpmem_settings_msurl != 'http://' && ! $wpmem_settings_mspage ) {
		update_option( 'wpmembers_msurl', trim( $wpmem_settings_msurl ) );
	}

	$wpmem_settings_regurl  = $_POST['wpmem_settings_regurl'];
	$wpmem_settings_regpage = $_POST['wpmem_settings_regpage'];
	if( $wpmem_settings_regpage ) { update_option( 'wpmembers_regurl', $wpmem_settings_regpage ); }
	if( $wpmem_settings_regurl != 'http://' && ! $wpmem_settings_regpage ) {
		update_option( 'wpmembers_regurl', trim( $wpmem_settings_regurl ) );
	}
	
	
	$wpmem_settings_cssurl = $_POST['wpmem_settings_cssurl'];
	if( $wpmem_settings_cssurl != 'http://' ) {
		update_option( 'wpmembers_cssurl', trim( $wpmem_settings_cssurl ) );
	}
	
	// new in 2.8
	$wpmem_settings_style = ( isset( $_POST['wpmem_settings_style'] ) ) ? $_POST['wpmem_settings_style'] : false;
	update_option( 'wpmembers_style', $wpmem_settings_style, false );
	
	// new in 2.8
	$wpmem_autoex = array (
		'auto_ex'     => isset( $_POST['wpmem_autoex'] ) ? $_POST['wpmem_autoex'] : 0,
		'auto_ex_len' => isset( $_POST['wpmem_autoex_len'] ) ? $_POST['wpmem_autoex_len'] : ''
	);
	update_option( 'wpmembers_autoex', $wpmem_autoex, false );
	
	update_option( 'wpmembers_settings', $wpmem_newsettings );
	$wpmem_settings = $wpmem_newsettings;
	
	
	return __( 'WP-Members settings were updated', 'wp-members' );
}


/**
 * Create the stylesheet dropdown selection
 *
 * @since 2.8
 *
 * @uses apply_filters Calls wpmem_admin_style_list can hook into the list to include your own stylesheets.
 */
function wpmem_admin_style_list()
{
	$val  = get_option( 'wpmembers_style', null );
	$list = array(
		'Twenty Ten'      => WPMEM_DIR . 'css/wp-members.css',
		'Twenty Eleven'   => WPMEM_DIR . 'css/wp-members-2011.css',
		'Twenty Twelve'   => WPMEM_DIR . 'css/wp-members-2012.css',
		'Twenty Thirteen' => WPMEM_DIR . 'css/wp-members-2013.css',
		'Kubrick'         => WPMEM_DIR . 'css/wp-members-kubrick.css'
	);
	$list = apply_filters( 'wpmem_admin_style_list', $list );

	foreach( $list as $name => $location ) {
		echo '<option value="' . $location . '" ' . wpmem_selected( $location, $val, 'select' ) . '>' . $name . "</option>\n";
	}
	
	return;
}


/**
 * Create a dropdown selection of pages
 *
 * @since 2.8.1
 *
 * @param string $val
 */
function wpmem_admin_page_list( $val )
{
	echo '<option value="">'; echo esc_attr( __( 'Select a page' ) ); echo '</option>';
	$pages = get_pages(); 
	foreach ( $pages as $page ) {
		$option = '<option value="' . get_page_link( $page->ID ) . '" ' . wpmem_selected( get_page_link( $page->ID ), $val, 'select' ) . '>';
		$option .= $page->post_title;
		$option .= '</option>';
		echo $option;
	}
	echo '<option value="">'; _e( 'USE CUSTOM URL ---->', 'wp-members' ); echo '</option>';
}

/** End of File **/