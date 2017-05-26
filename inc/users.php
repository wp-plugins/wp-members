<?php
/**
 * WP-Members User Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler 
 * @copyright 2006-2017
 */


if ( ! function_exists( 'wpmem_user_profile' ) ):
/**
 * add WP-Members fields to the WP user profile screen.
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_user_profile() {

	global $wpmem, $user_id, $current_screen;
	/**
	 * Filter the heading for the user profile additional fields.
	 *
	 * @since 2.9.1
	 *
	 * @param string The default heading.
	 */?>
	<h3><?php echo apply_filters( 'wpmem_user_profile_heading', __( 'Additional Information', 'wp-members' ) ); ?></h3>
	<table class="form-table">
		<?php
		// Get fields.
		$wpmem_fields = wpmem_fields( 'dashboard_profile' );
		// Get excluded meta.
		$exclude = wpmem_get_excluded_meta( 'user-profile' );

		$rows = array();
		foreach ( $wpmem_fields as $meta => $field ) {

			$valtochk = ''; $values = '';
			
			// Do we exclude the row?
			$chk_pass = ( in_array( $meta, $exclude ) ) ? false : true;

			if ( $field['register'] && ! $field['native'] && $chk_pass ) {
				
				$val = get_user_meta( $user_id, $meta, true );

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
					if ( $meta == 'tos' && $val == 'agree' ) {
						$input = wpmem_create_formfield( $meta, 'hidden', $val );
					} elseif ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
						$input = wpmem_create_formfield( $meta, $field['type'], $values, $valtochk );
					} else {
						$input = wpmem_create_formfield( $meta, $field['type'], $val, $valtochk );
					}
				}

				// If there are any required fields.
				$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
				$label = '<label>' . __( $field['label'], 'wp-members' ) . $req . '</label>';
				
				// Build the form rows for filtering.
				$rows[ $meta ] = array(
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
		 * @since 3.1.6 Deprecated $order and $meta.
		 *
		 * @param array  $rows {
		 *     An array of the profile rows.
		 *
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
		$rows = apply_filters( 'wpmem_register_form_rows_profile', $rows, 'userprofile' );
		
		foreach ( $rows as $row ) {
				
			$show_field = '
				<tr>
					<th>' . $row['label'] . '</th>
					<td>' . $row['field'] . '</td>
				</tr>';

			/**
			 * Filter the field for user profile additional fields.
			 *
			 * @since 2.9.1
			 * @since 3.1.1 Added $user_id and $row.
			 *
			 * @param string $show_field The HTML string of the additional field.
			 * @param int    $user_id
			 * @param array  $rows
			 */
			echo apply_filters( 'wpmem_user_profile_field', $show_field, $user_id, $row );
			
		} ?>
	</table><?php
}
endif;


/**
 * updates WP-Members fields from the WP user profile screen.
 *
 * @since 2.6.5
 *
 * @global int $user_id
 */
function wpmem_profile_update() {

	global $wpmem, $user_id;
	// Get the fields.
	$wpmem_fields = wpmem_fields( 'dashboard_profile_update' );
	// Get any excluded meta fields.
	$exclude = wpmem_get_excluded_meta( 'user-profile' );
	foreach ( $wpmem_fields as $meta => $field ) {
		// If this is not an excluded meta field.
		if ( ! in_array( $meta, $exclude ) ) {
			// If the field is user editable.
			if ( $field['register'] 
			  && $field['type'] != 'password' 
			  && $field['type'] != 'file' 
			  && $field['type'] != 'image' 
			  && ! $field['native'] ) {

				// Check for required fields.
				$chk = '';
				if ( ! $field['required'] ) {
					$chk = 'ok';
				}
				if ( $field['required'] && $_POST[ $meta ] != '' ) {
					$chk = 'ok';
				}

				// Check for field value.
				if ( $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
					$field_val = ( isset( $_POST[ $meta ] ) ) ? implode( '|', $_POST[ $meta ] ) : '';
				} else {
					$field_val = ( isset( $_POST[ $meta ] ) ) ? $_POST[ $meta ] : '';
				}

				if ( $chk == 'ok' ) {
					update_user_meta( $user_id, $meta, $field_val );
				}
			}
		}
	}
	
	if ( ! empty( $_FILES ) ) {
		$wpmem->user->upload_user_files( $user_id, $wpmem_fields );
	}	
}

/**
 * Sets user profile update to multipart form data.
 *
 * If the fields array has a file or image field, this will echo the 
 * necessary "multipart/form-data" enctype for the form tag.
 *
 * @since 3.1.8
 */
function wpmem_user_profile_multipart() {
	global $wpmem;
	$has_file = false;
	foreach ( wpmem_fields() as $field ) {
		if ( $field['type'] == 'file' || $field['type'] == 'image' ) {
			$has_file = true;
			break;
		}
	}
	echo ( $has_file ) ? " enctype=\"multipart/form-data\"" : '';
}

// End of file.