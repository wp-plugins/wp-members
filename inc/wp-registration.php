<?php
/**
 * WP-Members Functions for WordPress Native Registration
 *
 * Handles functions that add WP-Members custom fields to the 
 * WordPress native (wp-login.php) registration and the 
 * Users > Add New screen.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2018 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler
 * @copyright 2006-2018
 *
 * Functions Included:
 * - wpmem_do_wp_register_form
 * - wpmem_do_wp_newuser_form
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Appends WP-Members registration fields to wp-login.php registration form.
 *
 * @since 2.8.7
 * @since 3.1.1 Updated to support new (3.1.0) field types.
 * @since 3.1.6 Updated to support new fields array. Added WC classes.
 * @since 3.1.8 Added $process parameter.
 */
function wpmem_do_wp_register_form( $process = 'wp' ) {

	global $wpmem;
	$wpmem_fields = wpmem_fields( $process );
	
	// Check if this is WooCommerce account page.
	$is_woo = false;
	if ( 'woo' == $process ) {
		$is_woo = true;
	} else {
		if ( function_exists( 'is_account_page' ) ) {
			$is_woo = ( is_account_page() ) ? true : $is_woo;
		}
	}
	
	if ( isset( $wpmem_fields ) && is_array( $wpmem_fields ) ) {
		
		unset( $wpmem_fields['username'] );
		
		foreach ( $wpmem_fields as $meta_key => $field ) {

			$req = ( $field['required'] ) ? ( ( $is_woo ) ? ' <span class="required">*</span>' : ' <span class="req">' . __( '(required)' ) . '</span>' ) : '';

			// File fields not yet supported for this form.
			if ( $field['register'] && $meta_key != 'user_email' && $field['type'] != 'file' && $field['type'] != 'image' ) {
			
				if ( 'checkbox' == $field['type'] ) {

					if ( 'tos' == $meta_key ) {
						$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
						if ( has_shortcode( $tos_content, 'wpmem_tos' ) || has_shortcode( $tos_content, 'wp-members' ) ) {	
							$link = do_shortcode( $tos_content );
							$tos_pop = '<a href="' . esc_url( $link ) . '" target="_blank">';
						} else { 
							$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WPMEM_DIR . "/wp-members-tos.php','mywindow');\">";
						}
						/** This filter is documented in wp-members/inc/register.php */
						$tos_link_text = apply_filters( 'wpmem_tos_link_txt', $wpmem->get_text( 'register_tos' ), 'new' );
												
						// If filtered value is not the default label, use that, otherwise use label.
						// @note: if default changes, this check must change.
						if ( __( 'Please indicate that you agree to the %s Terms of Service %s', 'wp-members' ) == $tos_link_text ) {
							if ( __( 'TOS', 'wp-members' ) != $field['label'] && __( 'Terms of Service', 'wp-members' ) != $field['label'] ) {
								$tos_link_text = $field['label'];
							}
						}

						// If tos string does not contain link identifiers (%s), wrap the whole string.
						if ( ! strpos( $tos_link_text, '%s' ) ) {
							$tos_link_text = '%s' . $tos_link_text . '%s';
						}

						$tos_link_text = ' ' . sprintf( $tos_link_text, $tos_pop, '</a>' );
					
					}

					$label = ( 'tos' == $meta_key ) ? $tos_link_text : __( $field['label'], 'wp-members' );

					$val = ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : '';
					$val = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;

					$row_before = '<p class="wpmem-checkbox">';
					$label = '<label for="' . $meta_key . '">' . $label . $req;
					$input = wpmem_form_field( $meta_key, $field['type'], $field['checked_value'], $val );
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
						$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : ''; // @todo Should this be escaped?
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
						//$input = '<input type="' . $field['type'] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="' . $class . '" value="';
						$input = wpmem_form_field( array( 
								'name' => $meta_key, 
								'type' => $field['type'], 
								'value' => ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : '',
								'compare' => ( isset( $field['compare'] ) ) ? $field['compare'] : '',
								'placeholder' => ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '',
							) );
						//$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; 
						//$input.= '" size="25" />';
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
	$exclude = wpmem_get_excluded_meta( 'wp-register' );

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

/**
 * Add registration fields to the native WP registration.
 *
 * @since 2.8.3
 * @since 3.1.8 Added $process argument.
 */
function wpmem_wp_register_form( $process = 'wp' ) {
	/**
	 * Load native WP registration functions.
	 */
	require_once( WPMEM_PATH . 'inc/wp-registration.php' );
	wpmem_do_wp_register_form( $process );
}

/**
 * Validates registration fields in the native WP registration.
 *
 * @since 2.8.3
 *
 * @global object $wpmem The WP-Members object class.
 *
 * @param  array  $errors               A WP_Error object containing any errors encountered during registration.
 * @param  string $sanitized_user_login User's username after it has been sanitized.
 * @param  string $user_email           User's email.
 * @return array  $errors               A WP_Error object containing any errors encountered during registration.
 */
function wpmem_wp_reg_validate( $errors, $sanitized_user_login, $user_email ) {

	global $wpmem;

	// Get any meta fields that should be excluded.
	$exclude = wpmem_get_excluded_meta( 'wp-register' );

	foreach ( wpmem_fields( 'wp_validate' ) as $meta_key => $field ) {
		$is_error = false;
		if ( $field['required'] && $meta_key != 'user_email' && ! in_array( $meta_key, $exclude ) ) {
			if ( ( $field['type'] == 'checkbox' || $field['type'] == 'multicheckbox' || $field['type'] == 'multiselect' || $field['type'] == 'radio' ) && ( ! isset( $_POST[ $meta_key ] ) ) ) {
				$is_error = true;
			} 
			if ( ( $field['type'] != 'checkbox' && $field['type'] != 'multicheckbox' && $field['type'] != 'multiselect' && $field['type'] != 'radio' ) && ( ! $_POST[ $meta_key ] ) ) {
				$is_error = true;
			}
			if ( $is_error ) { $errors->add( 'wpmem_error', sprintf( $wpmem->get_text( 'reg_empty_field' ), __( $field['label'], 'wp-members' ) ) ); }
		}
	}

	return $errors;
}

/**
 * Inserts registration data from the native WP registration.
 *
 * @since 2.8.3
 * @since 3.1.1 Added new 3.1 field types and activate user support.
 *
 * @todo Compartmentalize file upload along with main register function.
 *
 * @global object $wpmem The WP-Members object class.
 * @param int $user_id The WP user ID.
 */
function wpmem_wp_reg_finalize( $user_id ) {

	global $wpmem;
	// Is this WP's native registration? Checks the native submit button.
	$is_native  = ( __( 'Register' ) == wpmem_get( 'wp-submit' ) ) ? true : false;
	// Is this a Users > Add New process? Checks the post action.
	$is_add_new = ( 'createuser' == wpmem_get( 'action' ) ) ? true : false;
	// Is this a WooCommerce checkout registration? Checks for WC fields.
	$is_woo     = ( wpmem_get( 'woocommerce_checkout_place_order' ) || wpmem_get( 'woocommerce-register-nonce' ) ) ? true : false;
	if ( $is_native || $is_add_new || $is_woo ) {
		// Get any excluded meta fields.
		$exclude = wpmem_get_excluded_meta( 'wp-register' );
		foreach ( wpmem_fields( 'wp_finalize' ) as $meta_key => $field ) {
			$value = wpmem_get( $meta_key, false );
			if ( false !== $value && ! in_array( $meta_key, $exclude ) && 'file' != $field['type'] && 'image' != $field['type'] ) {
				if ( 'multiselect' == $field['type'] || 'multicheckbox' == $field['type'] ) {
					$value = implode( $field['delimiter'], $value );
				}
				$sanitized_value = sanitize_text_field( $value );
				update_user_meta( $user_id, $meta_key, $sanitized_value );
			}
		}
	}
	
	// If this is Users > Add New.
	if ( is_admin() && $is_add_new ) {
		// If moderated registration and activate is checked, set active flags.
		if ( 1 == $wpmem->mod_reg && isset( $_POST['activate_user'] ) ) {
			update_user_meta( $user_id, 'active', 1 );
			wpmem_set_user_status( $user_id, 0 );
		}
	}
	
	return;
}

/**
 * Loads the stylesheet for backend registration.
 *
 * @since 2.8.7
 */
function wpmem_wplogin_stylesheet() {
	// @todo Should this enqueue styles?
	echo '<link rel="stylesheet" id="custom_wp_admin_css"  href="' . WPMEM_DIR . 'css/wp-login.css" type="text/css" media="all" />';
}

// End of file.