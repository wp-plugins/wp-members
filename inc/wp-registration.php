<?php
/**
 * WP-Members Functions for WordPress Native Registration
 *
 * Handles functions that add WP-Members custom fields to the 
 * WordPress native (wp-login.php) registration and the 
 * Users > Add New screen.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_do_wp_register_form
 * - wpmem_do_wp_newuser_form
 */


/**
 * Appends WP-Members registration fields to wp-login.php registration form.
 *
 * @since 2.8.7
 * @since 3.1.1 Updated to support new (3.1.0) field types.
 */
function wpmem_do_wp_register_form() {

	global $wpmem;
	$wpmem_fields = $wpmem->fields; //$wpmem_fields = get_option( 'wpmembers_fields' );
	if ( isset( $wpmem_fields ) && is_array( $wpmem_fields ) ) {
		foreach ( $wpmem_fields as $field ) {
			
			$meta_key = $field[2];

			$req = ( $field[5] == 'y' ) ? ' <span class="req">' . __( '(required)' ) . '</span>' : '';

			// File fields not yet supported for this form.
			if ( $field[4] == 'y' && $meta_key != 'user_email' && $field[3] != 'file' && $field[3] != 'image' ) {
			
				if ( $field[3] == 'checkbox' ) {

					if ( $meta_key == 'tos' ) {
						$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
						if ( stristr( $tos_content, '[wp-members page="tos"' ) ) {

							$tos_content = " " . $tos_content;
							$ini = strpos( $tos_content, 'url="' );
							$ini += strlen( 'url="' );
							$len = strpos( $tos_content, '"]', $ini ) - $ini;
							$link = substr( $tos_content, $ini, $len );
							$tos_pop = '<a href="' . $link . '" target="_blank">';

						} else { 
							$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WP_PLUGIN_URL . "/wp-members/wp-members-tos.php','mywindow');\">";
						}
						/** This filter is documented in wp-members/inc/register.php */
						$tos = apply_filters( 'wpmem_tos_link_txt', sprintf( $wpmem->get_text( 'register_tos' ), $tos_pop, '</a>' ) );
					
					}

					$label = ( $meta_key == 'tos' ) ? $tos : __( $field[1], 'wp-members' );

					$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
					$val = ( ! $_POST && $field[8] == 'y' ) ? $field[7] : $val;

					$row_before = '<p class="wpmem-checkbox">';
					$label = '<label for="' . $meta_key . '">' . $label . $req;
					$input = wpmem_create_formfield( $meta_key, $field[3], $field[7], $val );
					$row_after = '</label></p>';

				} else {

					$row_before = '<p>';
					$label = '<label for="' . $meta_key . '">' . __( $field[1], 'wp-members' ) . $req . '<br />';

					// determine the field type and generate accordingly...

					switch ( $field[3] ) {

					case( 'select' ):
						$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
						$input = wpmem_create_formfield( $meta_key, $field[3], $field[7], $val );
						break;

					case( 'textarea' ):
						$input = '<textarea name="' . $meta_key . '" id="' . $meta_key . '" class="textarea">';
						$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
						$input.= '</textarea>';		
						break;
						
					case( 'multiselect' ):
					case( 'multicheckbox' ):
					case( 'radio' ):	
						$row_before = '<p class="' . $field[3] . '">';
						$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
						$formfield_args = array( 
							'name'     => $meta_key,
							'type'     => $field[3],
							'value'    => $field[7],
							'compare'  => $valtochk,
							'required' => ( 'y' == $field[5] ) ? true : false,
						);
						if ( 'multicheckbox' == $field[3] || 'multiselect' == $field[3] ) {
							$formfield_args['delimiter'] = ( isset( $field[8] ) ) ? $field[8] : '|';
						}
						$input = $wpmem->forms->create_form_field( $formfield_args );
						break;
					
					case( 'file' ):
					case( 'image' ):
						// Field type not supported for this yet.
						break;

					default:
						$input = '<input type="' . $field[3] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="input" value="';
						$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; 
						$input.= '" size="25" />';
						break;
					}

					$row_after = '</label></p>';

				}

				// if the row is set to display, add the row to the form array
				$rows[ $meta_key ] = array(
					'type'         => $field[3],
					'row_before'   => $row_before,
					'label'        => $label,
					'field'        => $input,
					'row_after'    => $row_after,
				);
			}
		}
		
		if ( isset( $rows ) && is_array( $rows ) ) {

			/**
			 * Filter the native registration form rows.
			 *
			 * @since 2.9.3.
			 *
			 * @param array $rows The custom rows added to the form.
			 */
			$rows = apply_filters( 'wpmem_native_form_rows', $rows );

			foreach ( $rows as $row_item ) {
				if ( $row_item['type'] == 'checkbox' ) {
					echo $row_item['row_before'] . $row_item['field'] . $row_item['label'] . $row_item['row_after'];
				} else { 
					echo $row_item['row_before'] . $row_item['label'] . $row_item['field'] . $row_item['row_after'];
				}
			}
		}
	}
}


/**
 * Appends WP-Members registration fields to Users > Add New User screen.
 *
 * @since 2.9.0
 * @since 3.1.1 Updated to support new (3.1.0) field types and user activation.
 */
function wpmem_do_wp_newuser_form() {

	global $wpmem;
	echo '<table class="form-table"><tbody>';

	$wpmem_fields = $wpmem->fields; //get_option( 'wpmembers_fields' );
	$exclude = wpmem_get_excluded_meta( 'register' );

	foreach ( $wpmem_fields as $field ) {
		
		$meta_key = $field[2];

		if ( $field[6] == 'n' && ! in_array( $meta_key, $exclude ) ) {

			$req = ( $field[5] == 'y' ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
		
			echo '<tr>
				<th scope="row">
					<label for="' . $meta_key . '">' . __( $field[1], 'wp-members' ) . $req . '</label>
				</th>
				<td>';

			// determine the field type and generate accordingly.

			switch ( $field[3] ) {

			case( 'select' ):
				$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				echo wpmem_create_formfield( $meta_key, $field[3], $field[7], $val );
				break;

			case( 'textarea' ):
				echo '<textarea name="' . $meta_key . '" id="' . $meta_key . '" class="textarea">';
				echo ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
				echo '</textarea>';
				break;

			case( 'checkbox' ):
				$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				$val = ( ! $_POST && $field[8] == 'y' ) ? $field[7] : $val;
				echo wpmem_create_formfield( $meta_key, $field[3], $field[7], $val );
				break;
			
			case( 'multiselect' ):
			case( 'multicheckbox' ):
			case( 'radio' ):
				$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				$formfield_args = array( 
					'name'     => $meta_key,
					'type'     => $field[3],
					'value'    => $field[7],
					'compare'  => $valtochk,
					'required' => ( 'y' == $field[5] ) ? true : false,
				);
				if ( 'multicheckbox' == $field[3] || 'multiselect' == $field[3] ) {
					$formfield_args['delimiter'] = ( isset( $field[8] ) ) ? $field[8] : '|';
				}
				echo $wpmem->forms->create_form_field( $formfield_args );
				break;
				
			case( 'file' ):
			case( 'image' ):
				break;
				
			default:
				echo '<input type="' . $field[3] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="input" value="'; echo ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; echo '" size="25" />';
				break;
			}

			echo '</td>
				</tr>';

		}
	}
	
	// If moderated registration is enabled, add checkbox to set user as active.
	if ( 1 == $wpmem->mod_reg ) {
		echo '<tr>
				<th scope="row">
					<label for="activate_user">' . __( 'Activate this user?', 'wp-members' ) . '</label>
				</th>
				<td>' . $wpmem->forms->create_form_field( array( 'name' => 'activate_user', 'type' => 'checkbox', 'value' => 1, 'compare' => '' ) ) . '</td>
			  </tr>';
	}
	
	echo '</tbody></table>';

}
// End of file.