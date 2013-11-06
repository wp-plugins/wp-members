<?php
/**
 * WP-Members User Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
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


if ( ! function_exists( 'wpmem_user_profile' ) ):
/**
 * add WP-Members fields to the WP user profile screen
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_user_profile()
{
	global $user_id; ?>

	<h3><?php _e( 'Additional Info', 'wp-members' ); ?></h3>   
 	<table class="form-table">
		<?php
		$wpmem_fields = get_option( 'wpmembers_fields' );
		for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
			$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
		
			$chk_tos = true;
			if( $wpmem_fields[$row][2] == 'tos' && $val == 'agree' ) { 
				$chk_tos = false; 
				echo wpmem_create_formfield( $wpmem_fields[$row][2], 'hidden', $val );
			}
			
			$chk_pass = true;
			if( $wpmem_fields[$row][2] == 'password' ) { $chk_pass = false; }
		
			if( $wpmem_fields[$row][4] == "y" && $wpmem_fields[$row][6] == "n" && $chk_tos && $chk_pass ) { 
			
				// if there are any required fields, set a toggle to show indicator in last line
				if( $wpmem_fields[$row][5] == 'y' ) { $has_req = true; } ?>  
				
				<tr>
					<th><label><?php echo $wpmem_fields[$row][1]; ?></label></th>
					<td><?php
					
						$val = get_user_meta( $user_id, $wpmem_fields[$row][2], 'true' );
						if( $wpmem_fields[$row][3] == 'checkbox' || $wpmem_fields[$row][3] == 'select' ) {
							$valtochk = $val; 
							$val = $wpmem_fields[$row][7];
						}
						echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $val, $valtochk );
						if( $wpmem_fields[$row][5] == 'y' ) { echo '<font color="red">*</font>'; }
						$valtochk = ''; // empty for the next field in the loop
					?></td>
				</tr>
			<?php } 
		}
		
		if( $has_req ) { ?>
				<tr>
					<th>&nbsp;</th>
					<td><font color="red">*</font> <?php _e( 'Indicates a required field', 'wp-members' ); ?></td>
				</tr><?php
		} ?>
	</table><?php
}
endif;


/**
 * updates WP-Members fields from the WP user profile screen
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_profile_update()
{
	global $user_id;
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {

		// if the field is user editable, 
		if( $wpmem_fields[$row][4] == "y" && $wpmem_fields[$row][6] == "n" && $wpmem_fields[$row][2] != 'password' ) {
		
			// check for required fields
			$chk = '';
			if( $wpmem_fields[$row][5] == "n" || ( ! $wpmem_fields[$row][5] ) ) { $chk = 'ok'; }
			if( $wpmem_fields[$row][5] == "y" && $_POST[$wpmem_fields[$row][2]] != '' ) { $chk = 'ok'; }

			if( $chk == 'ok' ) { 
				update_user_meta( $user_id, $wpmem_fields[$row][2], sanitize_text_field( $_POST[$wpmem_fields[$row][2]] ) ); 
			} 
		}
	} 
}

/** End of File **/