<?php
/**
 * WP-Members Admin Functions
 *
 * Functions to manage the user profile screen.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
 *
 * Functions included:
 * - wpmem_profile_show_activate
 * - wpmem_profile_show_expiration
 * - wpmem_profile_show_ip
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Adds user activation to the user profile.
 *
 * @since 3.1.1
 *
 * @global object $wpmem
 * @param  int    $user_id
 */
function wpmem_profile_show_activate( $user_id ) {
	global $wpmem;
	// See if reg is moderated, and if the user has been activated.
	if ( $wpmem->mod_reg == 1 ) {
		$user_active_flag = get_user_meta( $user_id, 'active', true );
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
			
		} ?>
        <tr>
            <th><label><?php echo $label; ?></label></th>
            <td><input id="activate_user" type="checkbox" class="input" name="activate_user" value="<?php echo $action; ?>" /></td>
        </tr>
	<?php }
}


/**
 * Adds user expiration to the user profile.
 *
 * @since 3.1.1
 *
 * @global object $wpmem
 * @param  int    $user_id
 */
function wpmem_profile_show_expiration( $user_id ) {
	
global $wpmem;
	/*
	 * If using subscription model, show expiration.
	 * If registration is moderated, this doesn't show 
	 * if user is not active yet.
	 */
	if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
		if ( ( $wpmem->mod_reg == 1 &&  get_user_meta( $user_id, 'active', true ) == 1 ) || ( $wpmem->mod_reg != 1 ) ) {
			if ( function_exists( 'wpmem_a_extenduser' ) ) {
				wpmem_a_extenduser( $user_id );
			}
		}
	} 
} 


/**
 * Adds user registration IP to the user profile.
 *
 * @since 3.1.1
 *
 * @param  int    $user_id
 */
function wpmem_profile_show_ip( $user_id ) { ?>
    <tr>
        <th><label><?php _e( 'IP @ registration', 'wp-members' ); ?></label></th>
        <td><?php echo get_user_meta( $user_id, 'wpmem_reg_ip', true ); ?></td>
    </tr>
    <?php
}

// End of file.