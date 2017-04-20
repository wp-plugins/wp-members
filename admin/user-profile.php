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
 * - wpmem_admin_fields
 * - wpmem_admin_update
 * - wpmem_profile_show_activate
 * - wpmem_profile_show_expiration
 * - wpmem_profile_show_ip
 */

/**
 * Add WP-Members fields to the WP user profile screen.
 *
 * @since 2.1
 *
 * @global array $current_screen The WordPress screen object
 * @global int   $user_ID The user ID
 */
function wpmem_admin_fields() {

	global $current_screen, $user_ID, $wpmem;
	$user_id = ( $current_screen->id == 'profile' ) ? $user_ID : $_REQUEST['user_id']; ?>

	<h3><?php
	/**
	 * Filter the heading for additional profile fields.
	 *
	 * @since 2.8.2
	 *
	 * @param string The default additional fields heading.
	 */
	echo apply_filters( 'wpmem_admin_profile_heading', __( 'WP-Members Additional Fields', 'wp-members' ) ); ?></h3>   
 	<table class="form-table">
		<?php
		// Get fields.
		$wpmem_fields = wpmem_fields( 'admin_profile' );
		// Get excluded meta.
		$exclude = wpmem_get_excluded_meta( 'admin-profile' );

		/**
		 * Fires at the beginning of generating the WP-Members fields in the user profile.
		 *
		 * @since 2.9.3
		 *
		 * @param int   $user_id      The user's ID.
		 * @param array $wpmem_fields The WP-Members fields.
		 */
		do_action( 'wpmem_admin_before_profile', $user_id, $wpmem_fields );

		// Assemble form rows array.
		$rows = array();
		foreach ( $wpmem_fields as $meta => $field ) {

			$valtochk = ''; $values = '';

			// Determine which fields to show in the additional fields area.
			$show = ( ! $field['native'] && ! in_array( $meta, $exclude ) ) ? true : false;
			$show = ( $field['label'] == 'TOS' && $field['register'] ) ? null : $show;

			if ( $show ) {

				$val = get_user_meta( $user_id, $meta, true );
				$val = ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) ? $val : htmlspecialchars( $val );
				if ( $field['type'] == 'checkbox' ) {
					$valtochk = $val;
					$val = $field['checked_value'];
				}
				
				if ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
					$values = $field['values'];
					$valtochk = $val;
				}
				
				// Is this an image or a file?
				if ( 'file' == $field['type'] || 'image' == $field['type'] ) {
					$attachment_url = wp_get_attachment_url( $val );
					$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
					if ( 'file' == $field['type'] ) {
						$input = ( $attachment_url ) ? '<a href="' . $attachment_url . '">' . $attachment_url . '</a>' : $empty_file;
					} else {
						$input = ( $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
					}
					$input.= '<br />' . $wpmem->get_text( 'profile_upload' ) . '<br />';
					$input.= wpmem_form_field( array(
						'name'    => $meta, 
						'type'    => $field['type'], 
						'value'   => $val, 
						'compare' => $valtochk,
					) );
				} else {
					if ( 'select' == $field['type'] || 'radio' == $field['type'] ) {
						$input = wpmem_create_formfield( $meta, $field['type'], $values, $valtochk );
					} elseif( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
						$input = $wpmem->forms->create_form_field( array( 'name'=>$meta, 'type'=>$field['type'], 'value'=>$values, 'compare'=>$valtochk, 'delimiter'=>$field['delimiter'] ) );
					} else {
						$field['type'] = ( 'hidden' == $field['type'] ) ? 'text' : $field['type'];
						$input = wpmem_create_formfield( $meta, $field['type'], $val, $valtochk );
					}
				}
				
				// Is the field required?
				$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
				$label = '<label>' . __( $field['label'], 'wp-members' ) . $req . '</label>';
				
				// Build the form rows for filtering.
				$rows[ $meta ] = array(
					'meta'         => $meta,
					'type'         => $field['type'],
					'value'        => $val,
					'values'       => $values,
					'label_text'   => __( $field['label'], 'wp-members' ),
					'row_before'   => '',
					'label'        => $label,
					'field_before' => '',
					'field'        => $input,
					'field_after'  => '',
					'row_after'    => '',
				);
			}
		}
		
		/**
		 * Filter for rows
		 *
		 * @since 3.1.0
		 * @since 3.1.6 Deprecated $order.
		 *
		 * @param array  $rows {
		 *     An array of the profile rows.
		 *
		 *     @type string $meta         The meta key.
		 *     @type string $type         The field type.
		 *     @type string $value        Value if set.
		 *     @type string $values       Possible values (select, multiselect, multicheckbox, radio).
		 *     @type string $label_text   Raw label text (no HTML).
		 *     @type string $row_before   HTML before the row.
		 *     @type string $label        HTML label.
		 *     @type string $field_before HTML before the field input tag.
		 *     @type string $field        HTML for field input.
		 *     @type string $field_after  HTML after the field.
		 *     @type string $row_after    HTML after the row.
		 * }
		 * @param string $toggle
		 */
		$rows = apply_filters( 'wpmem_register_form_rows_admin', $rows, 'adminprofile' );
		
		// Handle form rows display from array.
		foreach ( $rows as $row ) {
			$show_field = '
				<tr>
					<th>' . $row['label'] . '</th>
					<td>' . $row['field'] . '</td>
				</tr>';

			/**
			 * Filter the profile field.
			 * 
			 * @since 2.8.2
			 * @since 3.1.1 Added $user_id and $row
			 *
			 * @param string $show_field The HTML string for the additional profile field.
			 * @param string $user_id
			 * @param array  $row
			 */
			echo apply_filters( 'wpmem_admin_profile_field', $show_field, $user_id, $row );
		}

		/**
		 * Fires after generating the WP-Members fields in the user profile.
		 *
		 * @since 2.9.3
		 *
		 * @param int   $user_id      The user's ID.
		 * @param array $wpmem_fields The WP-Members fields.
		 */
		do_action( 'wpmem_admin_after_profile', $user_id, $wpmem_fields ); ?>

	</table><?php
}


/**
 * Updates WP-Members fields from the WP user profile screen.
 *
 * @since 2.1
 *
 * @global object $wpmem
 */
function wpmem_admin_update() {

	$user_id = wpmem_get( 'user_id', false, 'request' ); //$_REQUEST['user_id'];
	
	if ( ! $user_id ) {
		// With no user id, no user can be updated.
		return;
	}
	
	global $wpmem;
	$wpmem_fields = wpmem_fields( 'admin_profile_update' );

	/**
	 * Fires before the user profile is updated.
	 *
	 * @since 2.9.2
	 *
	 * @param int   $user_id      The user ID.
	 * @param array $wpmem_fields Array of the custom fields.
	 */
	do_action( 'wpmem_admin_pre_user_update', $user_id, $wpmem_fields );

	$fields = array();
	$chk_pass = false;
	foreach ( $wpmem_fields as $meta => $field ) {
		if ( ! $field['native']
		  && $field['type'] != 'password' 
		  && $field['type'] != 'checkbox' 
		  && $field['type'] != 'multiselect' 
		  && $field['type'] != 'multicheckbox' 
		  && $field['type'] != 'file' 
		  && $field['type'] != 'image' ) {
			( isset( $_POST[ $meta ] ) ) ? $fields[ $meta ] = $_POST[ $meta ] : false;
		} elseif ( $meta == 'password' && $field['register'] ) {
			$chk_pass = true;
		} elseif ( $field['type'] == 'checkbox' ) {
			$fields[ $meta ] = ( isset( $_POST[ $meta ] ) ) ? $_POST[ $meta ] : '';
		} elseif ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
			$fields[ $meta ] = ( isset( $_POST[ $meta ] ) ) ? implode( $field['delimiter'], $_POST[ $meta ] ) : '';
		}
	}
	
	/**
	 * Filter the submitted field values for backend profile update.
	 *
	 * @since 2.8.2
	 *
	 * @param array $fields An array of the posted form values.
	 * @param int   $user_id The ID of the user being updated.
	 */
	$fields = apply_filters( 'wpmem_admin_profile_update', $fields, $user_id );

	// Get any excluded meta fields.
	$exclude = wpmem_get_excluded_meta( 'admin-profile' );
	foreach ( $fields as $key => $val ) {
		if ( ! in_array( $key, $exclude ) ) {
			update_user_meta( $user_id, $key, $val );
		}
	}
	
	if ( ! empty( $_FILES ) ) {
		$wpmem->user->upload_user_files( $user_id, $wpmem->fields );
	}	

	if ( $wpmem->mod_reg == 1 ) {

		$wpmem_activate_user = ( isset( $_POST['activate_user'] ) == '' ) ? -1 : $_POST['activate_user'];
		
		if ( $wpmem_activate_user == 1 ) {
			wpmem_a_activate_user( $user_id, $chk_pass );
		} elseif ( $wpmem_activate_user == 0 ) {
			wpmem_a_deactivate_user( $user_id );
		}
	}

	if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {
		if ( function_exists( 'wpmem_a_extenduser' ) ) {
			wpmem_a_extend_user( $user_id );
		}
	}

	/**
	 * Fires after the user profile is updated.
	 *
	 * @since 2.9.2
	 *
	 * @param int $user_id The user ID.
	 */
	do_action( 'wpmem_admin_after_user_update', $user_id );

	return;
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