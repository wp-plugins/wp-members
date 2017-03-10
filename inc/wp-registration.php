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
 * Copyright (c) 2006-2017 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2017
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
 * @since 3.1.6 Updated to support new fields array. Added WC classes.
 */
function wpmem_do_wp_register_form() {

	global $wpmem;
	$wpmem_fields = wpmem_fields( 'wp' );
	
	// Check if this is WooCommerce account page.
	$is_woo = false;
	if ( function_exists( 'is_account_page' ) ) {
		$is_woo = ( is_account_page() ) ? true : $is_woo;
	}
	
	if ( isset( $wpmem_fields ) && is_array( $wpmem_fields ) ) {
		foreach ( $wpmem_fields as $meta_key => $field ) {

			$req = ( $field['required'] ) ? ' <span class="req">' . __( '(required)' ) . '</span>' : '';

			// File fields not yet supported for this form.
			if ( $field['register'] && $meta_key != 'user_email' && $field['type'] != 'file' && $field['type'] != 'image' ) {
			
				if ( 'checkbox' == $field['type'] ) {

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

					$label = ( $meta_key == 'tos' ) ? $tos : __( $field['label'], 'wp-members' );

					$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
					$val = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;

					$row_before = '<p class="wpmem-checkbox">';
					$label = '<label for="' . $meta_key . '">' . $label . $req;
					$input = wpmem_create_formfield( $meta_key, $field['type'], $field['checked_value'], $val );
					$row_after = '</label></p>';

				} elseif ( 'hidden' == $field['type'] ) {
					
					// Handle hidden fields
					$row_before = '';
					$label = '';
					$input = wpmem_form_field( array( 
							'name'     => $meta_key,
							'type'     => $field['type'],
							'value'    => $field['value'],
							'compare'  => $valtochk,
							//'class'    => ( $class ) ? $class : 'textbox',
							'required' => $field['required'],
						) );
					$row_after = '';
					
				} else {

					$row_before = ( $is_woo ) ? '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">' : '<p>';
					$label = '<label for="' . $meta_key . '">' . __( $field['label'], 'wp-members' ) . $req . '<br />';

					// determine the field type and generate accordingly...

					switch ( $field['type'] ) {

					case( 'select' ):
						$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
						$input = wpmem_create_formfield( $meta_key, $field['type'], $field['values'], $val );
						break;

					case( 'textarea' ):
						$input = '<textarea name="' . $meta_key . '" id="' . $meta_key . '" class="textarea">';
						$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
						$input.= '</textarea>';		
						break;
						
					case( 'multiselect' ):
					case( 'multicheckbox' ):
					case( 'radio' ):	
						$row_before = '<p class="' . $field['type'] . '">';
						$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
						$formfield_args = array( 
							'name'     => $meta_key,
							'type'     => $field['type'],
							'value'    => $field['values'],
							'compare'  => $valtochk,
							'required' => ( $field['required'] ) ? true : false,
						);
						if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
							$formfield_args['delimiter'] = $field['delimiter'];
						}
						$input = $wpmem->forms->create_form_field( $formfield_args );
						break;
					
					case( 'file' ):
					case( 'image' ):
						// Field type not supported for this yet.
						break;

					default:
						$class = ( $is_woo ) ? 'woocommerce-Input woocommerce-Input--text input-text' : 'input';
						$input = '<input type="' . $field['type'] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="' . $class . '" value="';
						$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; 
						$input.= '" size="25" />';
						break;
					}

					$row_after = '</label></p>';

				}

				// if the row is set to display, add the row to the form array
				$rows[ $meta_key ] = array(
					'type'         => $field['type'],
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
 * @since 3.1.6 Updated to support new fields array.
 */
function wpmem_do_wp_newuser_form() {

	global $wpmem;
	echo '<table class="form-table"><tbody>';

	$wpmem_fields = wpmem_fields( 'add_new' );
	$exclude = wpmem_get_excluded_meta( 'register' );

	foreach ( $wpmem_fields as $meta_key => $field ) {

		if ( ! $field['native'] && ! in_array( $meta_key, $exclude ) ) {

			$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
		
			echo '<tr>
				<th scope="row">
					<label for="' . $meta_key . '">' . __( $field['label'], 'wp-members' ) . $req . '</label>
				</th>
				<td>';

			// determine the field type and generate accordingly.

			switch ( $field['type'] ) {

			case( 'select' ):
				$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				echo wpmem_create_formfield( $meta_key, $field['type'], $field['values'], $val );
				break;

			case( 'textarea' ):
				echo '<textarea name="' . $meta_key . '" id="' . $meta_key . '" class="textarea">';
				echo ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
				echo '</textarea>';
				break;

			case( 'checkbox' ):
				$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				$val = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;
				echo wpmem_create_formfield( $meta_key, $field['type'], $field['checked_value'], $val );
				break;
			
			case( 'multiselect' ):
			case( 'multicheckbox' ):
			case( 'radio' ):
				$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
				$formfield_args = array( 
					'name'     => $meta_key,
					'type'     => $field['type'],
					'value'    => $field['values'],
					'compare'  => $valtochk,
					'required' => $field['required'],
				);
				if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
					$formfield_args['delimiter'] = $field['delimiter'];
				}
				echo $wpmem->forms->create_form_field( $formfield_args );
				break;
				
			case( 'file' ):
			case( 'image' ):
				break;
				
			default:
				echo '<input type="' . $field['type'] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="input" value="'; echo ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; echo '" size="25" />';
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