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
 * Copyright (c) 2006-2014 Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WordPress
 * @subpackage WP-Members
 * @author Chad Butler
 * @copyright 2006-2014
 *
 * Functions Included:
 * * wpmem_do_wp_register_form
 * * wpmem_do_wp_newuser_form
 */


/**
 * Appends WP-Members registration fields to wp-login.php registration form.
 *
 * @since 2.8.7
 */
function wpmem_do_wp_register_form()
{
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
	
		$req = ( $wpmem_fields[$row][5] == 'y' ) ? ' <span class="req">' . __( '(required)' ) . '</span>' : '';
		
		if( $wpmem_fields[$row][4] == 'y' && $wpmem_fields[$row][2] != 'user_email' ) {
		
			if( $wpmem_fields[$row][3] == 'checkbox' ) {
			
				if( $wpmem_fields[$row][2] == 'tos' ) {
					$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
					if( stristr( $tos_content, '[wp-members page="tos"' ) ) {
						
						$tos_content = " " . $tos_content;
						$ini = strpos( $tos_content, 'url="' );
						$ini += strlen( 'url="' );
						$len = strpos( $tos_content, '"]', $ini ) - $ini;
						$link = substr( $tos_content, $ini, $len );
						$tos_pop = '<a href="' . $link . '" target="_blank">';

					} else { 
						$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WP_PLUGIN_URL . "/wp-members/wp-members-tos.php','mywindow');\">";
					}
					/**
					 * Filter the TOS link text.
					 *
					 * When this filter is used for the WP native registration, the $toggle parameter is not passed.
					 *
					 * @since 2.7.5
					 *
					 * @param string The text and link for the TOS.
					 */
					$tos = apply_filters( 'wpmem_tos_link_txt', sprintf( __( 'Please indicate that you agree to the %s TOS %s', 'wp-members' ), $tos_pop, '</a>' ) );
				
				}
				
				$label = ( $wpmem_fields[$row][2] == 'tos' ) ? $tos : __( $wpmem_fields[$row][2], 'wp-members' );

				$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
				$val = ( ! $_POST && $wpmem_fields[$row][8] == 'y' ) ? $wpmem_fields[$row][7] : $val;
			
				echo '<p class="wpmem-checkbox">' . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );
				echo '<label for="' . $wpmem_fields[$row][2] . '">' . $label . $req . '</label></p>';
				
			} else {
			
				echo '<p>
						<label for="' . $wpmem_fields[$row][2] . '">' . __( $wpmem_fields[$row][1], 'wp-members' ) . $req . '<br />';
				
				// determine the field type and generate accordingly...
				
				switch( $wpmem_fields[$row][3] ) {
				
				case( 'select' ):
					$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
					echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );
					break;
					
				case( 'textarea' ):
					echo '<textarea name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="textarea">'; 
					echo ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? esc_textarea( $_POST[ $wpmem_fields[$row][2] ] ) : ''; 
					echo '</textarea>';		
					break;

				default:
					echo '<input type="' . $wpmem_fields[$row][3] . '" name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="input" value="'; 
					echo ( $_POST ) ? esc_attr( $_POST[ $wpmem_fields[$row][2] ] ) : ''; 
					echo '" size="25" />';
					break;
				}
					
				echo '</label>
					</p>';	
			
			}
		}
	}
}


/**
 * Appends WP-Members registration fields to wp-login.php registration form.
 *
 * @since 2.9.0
 */
function wpmem_do_wp_newuser_form()
{

	echo '<table class="form-table"><tbody>';
	
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {	
		if( $wpmem_fields[$row][4] == 'y' && $wpmem_fields[$row][6] == 'n' ) {

			$req = ( $wpmem_fields[$row][5] == 'y' ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';
		
			echo '<tr>
				<th scope="row">
					<label for="' . $wpmem_fields[$row][2] . '">' . __( $wpmem_fields[$row][1], 'wp-members' ) . $req . '</label>
				</th>
				<td>';
		
			// determine the field type and generate accordingly...
			
			switch( $wpmem_fields[$row][3] ) {
			
			case( 'select' ):
				$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
				echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );
				break;
				
			case( 'textarea' ):
				echo '<textarea name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="textarea">'; 
				echo ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? esc_textarea( $_POST[ $wpmem_fields[$row][2] ] ) : ''; 
				echo '</textarea>';		
				break;
				
			case( 'checkbox' ):
				echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], '' );
				break;

			default:
				echo '<input type="' . $wpmem_fields[$row][3] . '" name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="input" value="'; echo ( $_POST ) ? esc_attr( $_POST[ $wpmem_fields[$row][2] ] ) : ''; echo '" size="25" />';
				break;
			}
				
			echo '</td>
				</tr>';

		}
	}
	echo '</tbody></table>';

}
/** End of File **/