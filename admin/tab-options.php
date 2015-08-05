<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the plugin options tab.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2015  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2015
 *
 * Functions included:
 * - wpmem_a_build_options
 * - wpmem_update_options
 * - wpmem_admin_style_list
 * - wpmem_admin_page_list
 */


/**
 * Builds the settings panel.
 *
 * @since 2.2.2
 */
function wpmem_a_build_options() {

	global $wpmem;

	$admin_email = apply_filters( 'wpmem_notify_addr', get_option( 'admin_email' ) );
	$chg_email   = __( sprintf( '%sChange%s or %sFilter%s this address', '<a href="' . site_url( 'wp-admin/options-general.php', 'admin' ) . '">', '</a>', '<a href="http://rocketgeek.com/plugins/wp-members/users-guide/filter-hooks/wpmem_notify_addr/">', '</a>' ), 'wp-members' );
	$help_link   = __( sprintf( 'See the %sUsers Guide on plugin options%s.', '<a href="http://rocketgeek.com/plugins/wp-members/users-guide/plugin-settings/options/" target="_blank">', '</a>' ), 'wp-members' );	

	// Build an array of post types
	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	$post_arr = array(
		'post' => 'Posts',
		'page' => 'Pages',
	);
	if ( $post_types ) {
		foreach ( $post_types  as $post_type ) { 
			$cpt_obj = get_post_type_object( $post_type );
			$post_arr[ $cpt_obj->name ] = $cpt_obj->labels->name;
		}
	} ?>
	
	<div class="metabox-holder has-right-sidebar">

		<div class="inner-sidebar">
			<?php wpmem_a_meta_box(); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Need help?', 'wp-members' ); ?></span></h3>
				<div class="inside">
					<strong><i><?php echo $help_link; ?></i></strong>
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
							<h3>Content</h3>
							<ul>
							<?php

							// Content Blocking option group.
							$i = 0;
							$len = count( $post_arr );
							foreach ( $post_arr as $key => $val ) { 
								if ( $key == 'post' || $key == 'page' ) { // @todo - holding off on CPT support. ?>
								<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
									<label><?php echo ( $i == 0 ) ? 'Content Blocking' : '&nbsp;'; ?></label>
									<select name="wpmem_block_<?php echo $key; ?>">
										<option value="0"<?php echo ( isset( $wpmem->block[ $key ] ) && $wpmem->block[ $key ] == 0 ) ? ' selected' : '';?>><?php _e( 'Do not block', 'wp-members' ); ?></option>
										<option value="1"<?php echo ( isset( $wpmem->block[ $key ] ) && $wpmem->block[ $key ] == 1 ) ? ' selected' : '';?>><?php _e( 'Block', 'wp-members' ); ?></option>
										<!--<option value="2"<?php echo ( isset( $wpmem->block[ $key ] ) && $wpmem->block[ $key ] == 2 ) ? ' selected' : '';?>><?php _e( 'Hide', 'wp-members' ); ?></option>-->
									</select>
									<span><?php echo $val; ?></span>
								</li>
								<?php $i++;
								}
							}

							// Show Excerpts, Login Form, and Registration Form option groups.

							$option_group_array = array( 
								'show_excerpt' => __( 'Show Excerpts', 'wp-members' ), 
								'show_login'   => __( 'Show Login Form', 'wp-members' ), 
								'show_reg'     => __( 'Show Registration Form', 'wp-members' ),
							);

							foreach ( $option_group_array as $item_key => $item_val ) {
								$i = 0;
								$len = count( $post_arr );
								foreach ( $post_arr as $key => $val ) {
									if ( $key == 'post' || $key == 'page' ) { // @todo - holding off on CPT support.
									$setting = ( isset( $wpmem->{$item_key}[ $key ] ) ) ? $wpmem->{$item_key}[ $key ] : 0;
									?>
									<li<?php echo ( $i == $len - 1 ) ? ' style="border-bottom:1px solid #eee;"' : ''; ?>>
										<label><?php echo ( $i == 0 ) ? $item_val : '&nbsp;'; ?></label>
										<input name="wpmem_<?php echo $item_key; ?>_<?php echo $key; ?>" type="checkbox" id="" value="1"<?php echo wpmem_selected( 1, $setting ); ?> /> <span><?php echo $val; ?></span>
									</li>
									<?php $i++;
									}
								}
							} ?>
							</ul>
							<?php
							if ( WPMEM_EXP_MODULE == true ) {
								$arr = array( 
									array(__('Time-based expiration','wp-members'),'wpmem_settings_time_exp',__('Allows for access to expire','wp-members'),'use_exp'),
									array(__('Trial period','wp-members'),'wpmem_settings_trial',__('Allows for a trial period','wp-members'),'use_trial'),
								); ?>
							<h3>Subscription Settings</h3>	
							<ul><?php
							for ( $row = 0; $row < count( $arr ); $row++ ) { ?>
							  <li>
								<label><?php echo $arr[$row][0]; ?></label>
								<?php if (WPMEM_DEBUG == true) { echo $wpmem->$arr[$row][3]; } ?>
								<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if ( $wpmem->$arr[$row][3] == 1 ) { echo "checked"; }?> />&nbsp;&nbsp;
								<?php if ( $arr[$row][2] ) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
							  </li>
							<?php } 
							}?></ul>
							<h3>Other Settings</h3>
							<ul>
							<?php $arr = array(
								array(__('Notify admin','wp-members'),'wpmem_settings_notify',sprintf(__('Notify %s for each new registration? %s','wp-members'),$admin_email,$chg_email),'notify'),
								array(__('Moderate registration','wp-members'),'wpmem_settings_moderate',__('Holds new registrations for admin approval','wp-members'),'mod_reg'),
								array(__('Ignore warning messages','wp-members'),'wpmem_settings_ignore_warnings',__('Ignores WP-Members warning messages in the admin panel','wp-members'),'warnings'),
							);
							for ( $row = 0; $row < count( $arr ); $row++ ) { ?>
							  <li>
								<label><?php echo $arr[$row][0]; ?></label>
								<?php if (WPMEM_DEBUG == true) { echo $wpmem->$arr[$row][3]; } ?>
								<input name="<?php echo $arr[$row][1]; ?>" type="checkbox" id="<?php echo $arr[$row][1]; ?>" value="1" <?php if ( $wpmem->$arr[$row][3] == 1 ) { echo "checked"; }?> />&nbsp;&nbsp;
								<?php if ( $arr[$row][2] ) { ?><span class="description"><?php echo $arr[$row][2]; ?></span><?php } ?>
							  </li>
							<?php } ?>
							<?php $attribution = $wpmem->attrib; ?>
							  <li>
								<label><?php _e( 'Attribution', 'wp-members' ); ?></label>
								<input name="attribution" type="checkbox" id="attribution" value="1" <?php if ( $attribution == 1 ) { echo "checked"; }?> />&nbsp;&nbsp;
								<span class="description"><?php _e( 'Attribution is appreciated!  Display "powered by" link on register form?', 'wp-members' ); ?></span>
							  </li>
							<?php $auto_ex = $wpmem->autoex; ?>
							  <li>
								<label><?php _e( 'Auto Excerpt:', 'wp-members' ); ?></label>
								<input type="checkbox" name="wpmem_autoex" value="1" <?php if ( $auto_ex['auto_ex'] == 1 ) { echo "checked"; } ?> />&nbsp;&nbsp;&nbsp;&nbsp;<?php _e( 'Number of words in excerpt:', 'wp-members' ); ?> <input name="wpmem_autoex_len" type="text" size="5" value="<?php if ( $auto_ex['auto_ex_len'] ) { echo $auto_ex['auto_ex_len']; } ?>" />&nbsp;<span class="description"><?php _e( 'Optional', 'wp-members' ); ?>. <?php _e( 'Automatically creates an excerpt', 'wp-members' ); ?></span>
							  </li>
							  <li>
								<label><?php _e( 'Enable CAPTCHA', 'wp-members' ); ?></label>
								<select name="wpmem_settings_captcha">
									<option value="0"<?php echo ( $wpmem->captcha == 0 ) ? ' selected ' : ''; ?>><?php _e( 'None', 'wp-members' ); ?></option>
									<option value="1"<?php echo ( $wpmem->captcha == 1 ) ? ' selected ' : ''; ?>>reCAPTCHA</option>
									<option value="3"<?php echo ( $wpmem->captcha == 3 ) ? ' selected ' : ''; ?>>reCAPTCHA v2</option>
									<option value="2"<?php echo ( $wpmem->captcha == 2 ) ? ' selected ' : ''; ?>>Really Simple CAPTCHA</option>
								</select>
							  </li>
							<h3><?php _e( 'Pages' ); ?></h3>
							  <?php $wpmem_logurl = $wpmem->user_pages['login'];
							  if ( ! $wpmem_logurl ) { $wpmem_logurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'Login Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_logpage" id="wpmem_logpage_select">
								<?php wpmem_admin_page_list( $wpmem_logurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'Specify a login page (optional)', 'wp-members' ); ?></span><br />
								<div id="wpmem_logpage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_logurl" value="<?php echo $wpmem_logurl; ?>" size="50" />
								</div>
							  </li>
							  <?php $wpmem_regurl = $wpmem->user_pages['register'];
							  if ( ! $wpmem_regurl ) { $wpmem_regurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'Register Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_regpage" id="wpmem_regpage_select">
									<?php wpmem_admin_page_list( $wpmem_regurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'For creating a register link in the login form', 'wp-members' ); ?></span><br />
								<div id="wpmem_regpage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_regurl" value="<?php echo $wpmem_regurl; ?>" size="50" />
								</div>
							  </li>
							  <?php $wpmem_msurl = $wpmem->user_pages['profile'];
							  if ( ! $wpmem_msurl ) { $wpmem_msurl = wpmem_use_ssl(); } ?>
							  <li>
								<label><?php _e( 'User Profile Page:', 'wp-members' ); ?></label>
								<select name="wpmem_settings_mspage" id="wpmem_mspage_select">
								<?php wpmem_admin_page_list( $wpmem_msurl ); ?>
								</select>&nbsp;<span class="description"><?php _e( 'For creating a forgot password link in the login form', 'wp-members' ); ?></span><br />
								<div id="wpmem_mspage_custom">
									<label>&nbsp;</label>
									<input class="regular-text code" type="text" name="wpmem_settings_msurl" value="<?php echo $wpmem_msurl; ?>" size="50" />
								</div>
							  </li>
							<h3><?php _e( 'Stylesheet' ); ?></h3>
							  <li>
								<label><?php _e( 'Stylesheet' ); ?>:</label>
								<select name="wpmem_settings_style" id="wpmem_stylesheet_select">
								<?php wpmem_admin_style_list( $wpmem->style ); ?>
								</select>
							  </li>
							  <?php $wpmem_cssurl = $wpmem->cssurl;
							  if ( ! $wpmem_cssurl ) { $wpmem_cssurl = wpmem_use_ssl(); } ?>
							  <div id="wpmem_stylesheet_custom">
								  <li>
									<label><?php _e( 'Custom Stylesheet:', 'wp-members' ); ?></label>
									<input class="regular-text code" type="text" name="wpmem_settings_cssurl" value="<?php echo $wpmem_cssurl; ?>" size="50" />
								  </li>
							  </div>
								<br /></br />
								<input type="hidden" name="wpmem_admin_a" value="update_settings">
								<input type="submit" name="UpdateSettings"  class="button-primary" value="<?php _e( 'Update Settings', 'wp-members' ); ?> &raquo;" /> 
							</ul>
						</form>
					</div><!-- .inside -->
				</div>
			</div><!-- #post-body-content -->
		</div><!-- #post-body -->
	</div><!-- .metabox-holder -->
	<?php
}


/**
 * Updates the plugin options.
 *
 * @since 2.8.0
 *
 * @return string The options updated message.
 */
function wpmem_update_options() {

	// Check nonce.
	check_admin_referer( 'wpmem-update-settings' );

	$wpmem_settings_msurl  = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? $_POST['wpmem_settings_msurl'] : '';
	$wpmem_settings_mspage = ( $_POST['wpmem_settings_mspage'] == 'use_custom' ) ? '' : $_POST['wpmem_settings_mspage'];
	if ( $wpmem_settings_msurl != wpmem_use_ssl() && $wpmem_settings_msurl != 'use_custom' && ! $wpmem_settings_mspage ) {
		$msurl = trim( $wpmem_settings_msurl );
	} else {
		$msurl = $wpmem_settings_mspage;
	}

	$wpmem_settings_regurl  = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? $_POST['wpmem_settings_regurl'] : '';
	$wpmem_settings_regpage = ( $_POST['wpmem_settings_regpage'] == 'use_custom' ) ? '' : $_POST['wpmem_settings_regpage'];
	if ( $wpmem_settings_regurl != wpmem_use_ssl() && $wpmem_settings_regurl != 'use_custom' && ! $wpmem_settings_regpage ) {
		$regurl = trim( $wpmem_settings_regurl );
	} else {
		$regurl = $wpmem_settings_regpage;
	}

	$wpmem_settings_logurl  = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? $_POST['wpmem_settings_logurl'] : '';
	$wpmem_settings_logpage = ( $_POST['wpmem_settings_logpage'] == 'use_custom' ) ? '' : $_POST['wpmem_settings_logpage'];
	if ( $wpmem_settings_logurl != wpmem_use_ssl() && $wpmem_settings_logurl != 'use_custom' && ! $wpmem_settings_logpage ) {
		$logurl = trim( $wpmem_settings_logurl );
	} else {
		$logurl = $wpmem_settings_logpage;
	}

	$wpmem_settings_cssurl = $_POST['wpmem_settings_cssurl'];
	$cssurl = ( $wpmem_settings_cssurl != wpmem_use_ssl() ) ? trim( $wpmem_settings_cssurl ) : '';

	$wpmem_settings_style = ( isset( $_POST['wpmem_settings_style'] ) ) ? $_POST['wpmem_settings_style'] : false;

	$wpmem_autoex = array (
		'auto_ex'     => isset( $_POST['wpmem_autoex'] ) ? $_POST['wpmem_autoex'] : 0,
		'auto_ex_len' => isset( $_POST['wpmem_autoex_len'] ) ? $_POST['wpmem_autoex_len'] : '',
	);

	$wpmem_newsettings = array(
		'version' => WPMEM_VERSION,
		'notify'    => ( isset( $_POST['wpmem_settings_notify']          ) ) ? $_POST['wpmem_settings_notify']          : 0,
		'mod_reg'   => ( isset( $_POST['wpmem_settings_moderate']        ) ) ? $_POST['wpmem_settings_moderate']        : 0,
		'captcha'   => ( isset( $_POST['wpmem_settings_captcha']         ) ) ? $_POST['wpmem_settings_captcha']         : 0,
		'use_exp'   => ( isset( $_POST['wpmem_settings_time_exp']        ) ) ? $_POST['wpmem_settings_time_exp']        : 0,
		'use_trial' => ( isset( $_POST['wpmem_settings_trial']           ) ) ? $_POST['wpmem_settings_trial']           : 0,
		'warnings'  => ( isset( $_POST['wpmem_settings_ignore_warnings'] ) ) ? $_POST['wpmem_settings_ignore_warnings'] : 0,
		'user_pages' => array(
			'profile'  => ( $msurl  ) ? $msurl  : '',
			'register' => ( $regurl ) ? $regurl : '',
			'login'    => ( $logurl ) ? $logurl : '',
		),
		'cssurl'    => ( $cssurl ) ? $cssurl : '',
		'style'     => $wpmem_settings_style,
		'autoex'    => $wpmem_autoex,
		'attrib'    => ( isset( $_POST['attribution'] ) ) ? $_POST['attribution'] : 0,
	);

	// Build an array of post types
	$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names', 'and' );
	$post_arr = array( 'post', 'page' );
	if ( $post_types ) {
		foreach ( $post_types as $post_type ) { 
			$cpt_obj = get_post_type_object( $post_type );
			$post_arr[] = $cpt_obj->name;
		}
	}
	
	// Get settings for blocking, excerpts, show login, and show registration for posts, pages, and custom post types.
	$option_group_array = array( 'block', 'show_excerpt', 'show_login', 'show_reg' );
	foreach ( $option_group_array as $option_group_item ) {
		$arr = array();
		foreach ( $post_arr as $post_type ) {
			$post_var = 'wpmem_' . $option_group_item . '_' . $post_type;
			$arr[ $post_type ] = ( isset( $_POST[ $post_var ] ) ) ? $_POST[ $post_var ] : 0;
		}
		$wpmem_newsettings[ $option_group_item ] = $arr;
	}

	/*
	 * If we are setting registration to be moderated, 
	 * check to see if the current admin has been 
	 * activated so they don't accidentally lock themselves
	 * out later.
	*/
	if ( isset( $_POST['wpmem_settings_moderate'] ) == 1 ) {
		global $current_user;
		get_currentuserinfo();
		$user_ID = $current_user->ID;
		update_user_meta( $user_ID, 'active', 1 );
	}

	update_option( 'wpmembers_settings', $wpmem_newsettings );

	global $wpmem;
	foreach ( $wpmem_newsettings as $key => $val ) {
		$wpmem->$key = $val;
	}

	return __( 'WP-Members settings were updated', 'wp-members' );
}


/**
 * Create the stylesheet dropdown selection.
 *
 * @since 2.8
 *
 * @param $style string The stored stylesheet setting.
 */
function wpmem_admin_style_list( $style ) {

	$list = array(
		'No Float'                   => WPMEM_DIR . 'css/generic-no-float.css',
		'Rigid'                      => WPMEM_DIR . 'css/generic-rigid.css',
		'Twenty Fifteen'             => WPMEM_DIR . 'css/wp-members-2015.css',
		'Twenty Fifteen - no float'  => WPMEM_DIR . 'css/wp-members-2015-no-float.css',
		'Twenty Fourteen'            => WPMEM_DIR . 'css/wp-members-2014.css',
		'Twenty Fourteen - no float' => WPMEM_DIR . 'css/wp-members-2014-no-float.css',
		'Twenty Thirteen'            => WPMEM_DIR . 'css/wp-members-2013.css',
		'Twenty Twelve'              => WPMEM_DIR . 'css/wp-members-2012.css',
		'Twenty Eleven'              => WPMEM_DIR . 'css/wp-members-2011.css',
		'Twenty Ten'                 => WPMEM_DIR . 'css/wp-members.css',
		'Kubrick'                    => WPMEM_DIR . 'css/wp-members-kubrick.css',
	);

	/**
	 * Filters the list of stylesheets in the plugin options dropdown.
	 *
	 * @since 2.8.0
	 *
	 * @param array $list An array of stylesheets that can be applied to the plugin's forms.
	 */
	$list = apply_filters( 'wpmem_admin_style_list', $list );

	$selected = false;
	foreach ( $list as $name => $location ) {
		$selected = ( $location == $style ) ? true : $selected;
		echo '<option value="' . $location . '" ' . wpmem_selected( $location, $style, 'select' ) . '>' . $name . "</option>\n";
	}
	$selected = ( ! $selected ) ? ' selected' : '';
	echo '<option value="use_custom"' . $selected . '>' . __( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>';

	return;
}


/**
 * Create a dropdown selection of pages.
 *
 * @since 2.8.1
 *
 * @param string $val
 */
function wpmem_admin_page_list( $val, $show_custom_url = true ) {

	$selected = ( $val == 'http://' ) ? 'select a page' : false;
	$pages    = get_pages();

	echo '<option value=""'; echo ( $selected == 'select a page' ) ? ' selected' : ''; echo '>'; echo esc_attr( __( 'Select a page' ) ); echo '</option>';

	foreach ( $pages as $page ) {
		$selected = ( get_page_link( $page->ID ) == $val ) ? true : $selected;
		$option   = '<option value="' . get_page_link( $page->ID ) . '"' . wpmem_selected( get_page_link( $page->ID ), $val, 'select' ) . '>';
		$option  .= $page->post_title;
		$option  .= '</option>';
		echo $option;
	}
	if ( $show_custom_url ) {
		$selected = ( ! $selected ) ? ' selected' : '';
		echo '<option value="use_custom"' . $selected . '>' . __( 'USE CUSTOM URL BELOW', 'wp-members' ) . '</option>'; 
	}
}

/** End of File **/