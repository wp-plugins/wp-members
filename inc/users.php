<?php
/**
 * WP-Members User Functions
 *
 * Handles primary functions that are carried out in most
 * situations. Includes commonly used utility functions.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler 
 * @copyright 2006-2016
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

	global $wpmem, $user_id;
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
		$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
		// Get excluded meta.
		$exclude = wpmem_get_excluded_meta( 'user-profile' );

		$rows = array();
		foreach ( $wpmem_fields as $meta ) {

			$valtochk = ''; $values = '';
			
			// Do we exclude the row?
			$chk_pass = ( in_array( $meta[2], $exclude ) ) ? false : true;

			if ( $meta[4] == "y" && $meta[6] == "n" && $chk_pass ) {
				
				$val = get_user_meta( $user_id, $meta[2], true );

				if ( $meta[3] == 'checkbox' ) {
					$valtochk = $val; 
					$val = $meta[7];
				}
				
				if ( 'multicheckbox' == $meta[3] || 'select' == $meta[3] || 'multiselect' == $meta[3] || 'radio' == $meta[3] ) {
					$values = $meta[7];
					$valtochk = $val;
				}

				// Is this an image or a file?
				if ( 'file' == $meta[3] || 'image' == $meta[3] ) {
					$attachment_url = wp_get_attachment_url( $val );
					$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
					if ( 'file' == $meta[3] ) {
						$input = ( 0 < $attachment_url ) ? '<a href="' . $attachment_url . '">' . $attachment_url . '</a>' : $empty_file;
					} else {
						$input = ( 0 < $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
					}
					// @todo - come up with a way to handle file updates - user profile form does not support multitype
					//$show_field.= '<br /><span class="description">' . __( 'Update this file:' ) . '</span><br />';
					//$show_field.= wpmem_create_formfield( $meta[2] . '_update_file', $meta[3], $val, $valtochk );
				} else {
					if ( $meta[2] == 'tos' && $val == 'agree' ) {
						$input = wpmem_create_formfield( $meta[2], 'hidden', $val );
					} elseif ( 'multicheckbox' == $meta[3] || 'select' == $meta[3] || 'multiselect' == $meta[3] || 'radio' == $meta[3] ) {
						$input = wpmem_create_formfield( $meta[2], $meta[3], $values, $valtochk );
					} else {
						$input = wpmem_create_formfield( $meta[2], $meta[3], $val, $valtochk );
					}
				}

				// If there are any required fields.
				$req = ( $meta[5] == 'y' ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
				$label = '<label>' . __( $meta[1], 'wp-members' ) . $req . '</label>';
				
				// Build the form rows for filtering.
				$rows[ $meta[2] ] = array(
					'order'        => $meta[0],
					'meta'         => $meta[2],
					'type'         => $meta[3],
					'value'        => $val,
					'values'       => $values,
					'label_text'   => __( $meta[1], 'wp-members' ),
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
		 *
		 * @param array  $rows
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
	$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
	// Get any excluded meta fields.
	$exclude = wpmem_get_excluded_meta( 'user-profile' );
	foreach ( $wpmem_fields as $meta ) {
		// If this is not an excluded meta field.
		if ( ! in_array( $meta[2], $exclude ) ) {
			// If the field is user editable.
			if ( $meta[4] == "y" 
			  && $meta[6] == "n" 
			  && $meta[3] != 'password' 
			  && $meta[3] != 'file' 
			  && $meta[3] != 'image' ) {

				// Check for required fields.
				$chk = '';
				if ( $meta[5] == "n" || ( ! $meta[5] ) ) {
					$chk = 'ok';
				}
				if ( $meta[5] == "y" && $_POST[$meta[2]] != '' ) {
					$chk = 'ok';
				}

				// Check for field value.
				if ( $meta[3] == 'multiselect' || $meta[3] == 'multicheckbox' ) {
					$field_val = ( isset( $_POST[ $meta[2] ] ) ) ? implode( '|', $_POST[ $meta[2] ] ) : '';
				} else {
					$field_val = ( isset( $_POST[$meta[2]] ) ) ? $_POST[$meta[2]] : '';
				}

				if ( $chk == 'ok' ) {
					update_user_meta( $user_id, $meta[2], $field_val );
				}
			}
		}
	}
}

// End of file.