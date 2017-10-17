<?php
/**
 * WP-Members Shortcode Functions
 *
 * Contains the shortcode functions used by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2017  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Shortcodes
 * @author Chad Butler 
 * @copyright 2006-2017
 *
 * Functions Included:
 * - wpmem_sc_forms
 * - wpmem_sc_logged_in
 * - wpmem_sc_logged_out
 * - wpmem_sc_user_count
 * - wpmem_sc_user_profile
 * - wpmem_sc_loginout
 * - wpmem_sc_fields
 * - wpmem_sc_logout
 * - wpmem_sc_tos
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Function for forms called by shortcode.
 *
 * @since 3.0.0
 * @since 3.1.3 Added forgot_username shortcode.
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_themsg The WP-Members message container.
 *
 * @param  array  $attr
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_sc_forms( $atts, $content = null, $tag = 'wpmem_form' ) {
	
	global $wpmem, $wpmem_themsg;

	// Defaults.
	$redirect_to = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
	$texturize   = ( isset( $atts['texturize']   ) ) ? $atts['texturize']   : false;

	/*
	 * The [wpmem_form] shortcode requires additional tags (login, register, etc) that
	 * will be in the $atts array. If $atts is not an array, no additional tags were
	 * given, so there is nothing to render.
	 */
	if ( is_array( $atts ) ) {

		// If $atts is an array, get the tag from the array so we know what form to render.
		switch ( $atts ) {
			
			case in_array( 'login', $atts ):		
				if ( is_user_logged_in() ) {
					/*
					 * If the user is logged in, return any nested content (if any)
					 * or the default bullet links if no nested content.
					 */
					$content = ( $content ) ? $content : wpmem_inc_memberlinks( 'login' );
				} else {
					/*
					 * If the user is not logged in, return an error message if a login
					 * error state exists, or return the login form.
					 */
					$content = ( $wpmem->regchk == 'loginfailed' ) ? wpmem_inc_loginfailed() : wpmem_inc_login( 'login', $redirect_to );
				}
				break;
	
			case in_array( 'register', $atts ):
				if ( is_user_logged_in() ) {
					/*
					 * If the user is logged in, return any nested content (if any)
					 * or the default bullet links if no nested content.
					 */
					$content = ( $content ) ? $content : wpmem_inc_memberlinks( 'register' );
				} else {
					if ( $wpmem->regchk == 'loginfailed' ) {
						$content = wpmem_inc_loginfailed() . wpmem_inc_login( 'login', $redirect_to );
						break;
					}
					// @todo Can this be moved into another function? Should $wpmem get an error message handler?
					if ( $wpmem->regchk == 'captcha' ) {
						global $wpmem_captcha_err;
						$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
					}
					$content  = ( $wpmem_themsg || $wpmem->regchk == 'success' ) ? wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg ) : '';
					$content .= ( $wpmem->regchk == 'success' ) ? wpmem_inc_login( 'login', $redirect_to ) : wpmem_inc_registration( 'new', '', $redirect_to );
				}
				break;
	
			case in_array( 'password', $atts ):
				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
				break;
	
			case in_array( 'user_edit', $atts ):
				$content = wpmem_page_user_edit( $wpmem->regchk, $content );
				break;
				
			case in_array( 'forgot_username', $atts ):
				$content = wpmem_page_forgot_username( $wpmem->regchk, $content );
				break;
	
		}
		
		/*
		 * This is for texturizing. Need to work it into an argument in the function call as to whether the 
		 * [wpmem_txt] shortcode is even included.  @todo - Is this a temporary solution or is there something
		 * cleaner that can be worked out?
		 */
		if ( 1 == $wpmem->texturize ) {
			if ( array_key_exists( 'texturize', $atts ) && $atts['texturize'] == 'false' ) { 
				$content = str_replace( array( '[wpmem_txt]', '[/wpmem_txt]' ), array( '', '' ), $content );
			}
			if ( strstr( $content, '[wpmem_txt]' ) ) {
				// Fixes the wptexturize.
				remove_filter( 'the_content', 'wpautop' );
				remove_filter( 'the_content', 'wptexturize' );
				add_filter( 'the_content', 'wpmem_texturize', 999 );
			}
		} // End texturize functions
	}
	return do_shortcode( $content );
}

/**
 * Handles the logged in status shortcodes.
 *
 * There are two shortcodes to display content based on a user being logged
 * in - [wp-members status=in] and [wpmem_logged_in] (status=in is a legacy
 * shortcode, but will still function). There are several attributes that
 * can be used with the shortcode: in|out, sub for subscription only info,
 * id, and role. IDs and roles can be comma separated values for multiple
 * users and roles. Additionally, status=out can be used to display content
 * only to logged out users or visitors.
 *
 * @since 3.0.0
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string $status
 *     @type int    $id
 *     @type string $role
 *     @type string $sub
 * }
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_sc_logged_in( $atts, $content = null, $tag = 'wpmem_logged_in' ) {

	global $wpmem;

	// Handles the 'status' attribute.
	if ( ( isset( $atts['status'] ) ) || $tag == 'wpmem_logged_in' ) {

		$do_return = false;

		// If there is a status attribute of "out" and the user is not logged in.
		$do_return = ( isset( $atts['status'] ) && $atts['status'] == 'out' && ! is_user_logged_in() ) ? true : $do_return;

		if ( is_user_logged_in() ) {

			// In case $current_user is not already global
			$current_user = wp_get_current_user();

			// If there is a status attribute of "in" and the user is logged in.
			$do_return = ( isset( $atts['status'] ) && $atts['status'] == 'in' ) ? true : $do_return;
			
			// If using the wpmem_logged_in tag with no attributes & the user is logged in.
			$do_return = ( $tag == 'wpmem_logged_in' && ( ! $atts ) ) ? true : $do_return;
			
			// If there is an "id" attribute and the user ID is in it.
			if ( isset( $atts['id'] ) ) {
				$ids = explode( ',', $atts['id'] );
				foreach ( $ids as $id ) {
					if ( trim( $id ) == $current_user->ID ) {
						$do_return = true;
					}
				}
			}
			
			// If there is a "role" attribute and the user has a matching role.
			if ( isset( $atts['role'] ) ) {
				$roles = explode( ',', $atts['role'] );
				if ( wpmem_user_has_role( $roles ) ) {
					$do_return = true;
				}
			}
			
			// If there is a status attribute of "sub" and the user is logged in.
			if ( ( isset( $atts['status'] ) ) && $atts['status'] == 'sub' ) {
				if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {	
					if ( ! wpmem_chk_exp() ) {
						$do_return = true;
					} elseif ( $atts['msg'] == true ) {
						$do_return = true;
						$content = wpmem_sc_expmessage();
					}
				}
			}
			
			// If there is a meta key attribute.
			if ( isset( $atts['meta_key'] ) ) {
				$value = ( isset( $atts['meta_value'] ) ) ? $atta['meta_value'] : false;
				if ( wpmem_user_has_meta( $atts['meta_key'], $value ) ) {
					$do_return = true;
				}
			}
			
			// Prevents display if the current page is the user profile and an action is being handled.
			if ( ( wpmem_current_url( true, false ) == wpmem_profile_url() ) && isset( $_GET['a'] ) ) {
				$do_return = false;
			}
		
		}

		// Return content (or empty content) depending on the result of the above logic.
		return ( $do_return ) ? do_shortcode( $content ) : '';
	}
}

/**
 * Handles the [wpmem_logged_out] shortcode.
 *
 * @since 3.0.0
 *
 * @param  array  $atts
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_sc_logged_out( $atts, $content = null, $tag ) {
	return ( ! is_user_logged_in() ) ? do_shortcode( $content ) : '';
}

/**
 * User count shortcode [wpmem_show_count].
 *
 * User count displays a total user count or a count of users by specific
 * role (role="some_role").  It also accepts attributes for counting users
 * by a meta field (key="meta_key" value="meta_value").  A label can be 
 * displayed using the attribute label (label="Some label:").
 *
 * @since 3.0.0
 * @since 3.1.5 Added total user count features.
 *
 * @global object $wpdb    The WordPress database object.
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string $key
 *     @type string $value
 *     @type string $role
 *     @type string $label
 * }
 * @param  string $content The shortcode content.
 * @return string $content
 */
function wpmem_sc_user_count( $atts, $content = null ) {
	if ( isset( $atts['key'] ) && isset( $atts['value'] ) ) {
		// If by meta key.
		global $wpdb;
		$user_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s",
			$atts['key'],
			$atts['value']
		) );
	} else {
		// If no meta, it's a total count.
		$users = count_users();
		$user_count = ( isset( $atts['role'] ) ) ? $users['avail_roles'][ $atts['role'] ] : $users['total_users'];
	}
	
	// Assemble the output and return.
	$content = ( isset( $atts['label'] ) ) ? $atts['label'] . ' ' . $user_count : $content . ' ' . $user_count;
	return do_shortcode( $content );
}

/**
 * Creates the user profile dashboard area [wpmem_profile].
 *
 * @since 3.1.0
 * @since 3.1.2 Added function arguments.
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_themsg The WP-Members message container.
 *
 * @param  string $atts {
 *     The shortcode attributes.
 *
 *     @type string $redirect_to
 * }
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_sc_user_profile( $atts, $content, $tag ) {

	// @todo $redirect_to is not currently used in the user profile.
	$redirect_to   = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
	$hide_register = ( isset( $atts['register'] ) && 'hide' == $atts['register'] ) ? true : false;

	global $wpmem, $wpmem_themsg;

	$content = '';

	if ( $wpmem->regchk == "captcha" ) {
		global $wpmem_captcha_err;
		$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
	}

	if ( $wpmem->regchk == "loginfailed" ) {
		return wpmem_inc_loginfailed();
	}

	if ( is_user_logged_in() ) {

		/**
		 * Filter the default heading in User Profile edit mode.
		 *
		 * @since 2.7.5
		 *
		 * @param string The default edit mode heading.
		 */
		$heading = apply_filters( 'wpmem_user_edit_heading', $wpmem->get_text( 'profile_heading' ) );

		switch( $wpmem->action ) {

		case "edit":
			$content = $content . wpmem_inc_registration( 'edit', $heading );
			break;

		case "update":
			// Determine if there are any errors/empty fields.
			if ( $wpmem->regchk == "updaterr" || $wpmem->regchk == "email" ) {
				$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
				$content = $content . wpmem_inc_registration( 'edit', $heading );
			} else {
				//Case "editsuccess".
				$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
				$content = $content . wpmem_inc_memberlinks();
			}
			break;

		case "pwdchange":

			$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
			break;

		case "renew":
			$content = wpmem_renew();
			break;

		default:
			$content = wpmem_inc_memberlinks();
			break;
		}

	} else {
		
		if ( $wpmem->action == 'register' && ! $hide_register ) {

			switch( $wpmem->regchk ) {

			case "success":
				$content = wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
				$content = $content . wpmem_inc_login();
				break;

			default:
				$content = wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
				$content = $content . wpmem_inc_registration();
				break;
			}

		} elseif ( $wpmem->action == 'pwdreset' ) {

			$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );

		} elseif( $wpmem->action == 'getusername' ) {

			$content = wpmem_page_forgot_username( $wpmem->regchk, $content );

		} else {

			$content = $content . wpmem_inc_login( 'members' );
			$content = ( ! $hide_register ) ? $content . wpmem_inc_registration() : $content;
		}
	}

	return $content;
}

/**
 * Log in/out shortcode [wpmem_loginout].
 *
 * @since 3.1.1
 * @since 3.1.6 Uses wpmem_loginout().
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string  $login_redirect_to  The url to redirect to after login (optional).
 *     @type string  $logout_redirect_to The url to redirect to after logout (optional).
 *     @type string  $login_text         Text for the login link (optional).
 *     @type string  $logout_text        Text for the logout link (optional).
 * }
 * @param  string $content
 * @param  string $tag
 * @return string $content
 */
function wpmem_sc_loginout( $atts, $content, $tag ) {
	$link = wpmem_loginout( $atts );
	return do_shortcode( $link );
}

/**
 * Function to handle field shortcodes [wpmem_field].
 *
 * Shortcode to display the data for a given user field. Requires
 * that a field meta key be passed as an attribute.  Can either of
 * the following:
 * - [wpmem_field field="meta_key"]
 * - [wpmem_field meta_key] 
 *
 * Other attributes:
 *
 * - id (numeric user ID or "get" to retrieve uid from query string.
 * - underscores="true" strips underscores from the displayed value.
 * - display="raw" displays the stored value for dropdowns, radios, files.
 * - size(thumbnail|medium|large|full|w,h): image field only.
 *
 * @since 3.1.2
 * @since 3.1.4 Changed to display value rather than stored value for dropdown/multicheck/radio.
 * @since 3.1.5 Added display attribute, meta key as a direct attribute, and image/file display.
 *
 * @global object $wpmem   The WP_Members object.
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string {meta_key}
 *     @type string $field
 *     @type int    $id
 *     @type string $underscores
 *     @type string $display
 *     @type string size
 * }
 * @param  string $content Any content passed with the shortcode (default:null).
 * @param  string $tag     The shortcode tag (wpmem_form).
 * @return string $content Content to return.
 */
function wpmem_sc_fields( $atts, $content = null, $tag ) {
	
	// What field?
	$field = ( isset( $atts[0] ) ) ? $atts[0] : $atts['field'];
	
	// What user?
	if ( isset( $atts['id'] ) ) {
		$the_ID = ( $atts['id'] == 'get' ) ? filter_var( wpmem_get( 'uid', '', 'get' ), FILTER_SANITIZE_NUMBER_INT ) : $atts['id']; // Ultimately, the_ID will be checked to determine if it is numeric by WP_User::get_data_by().
	} else {
		$the_ID = get_current_user_id();
	}
	$user_info = get_userdata( $the_ID );
	
	// If there is userdata.
	if ( $user_info ) {

		global $wpmem;
		$fields = wpmem_fields();
		$field_type = ( isset( $fields[ $field ]['type'] ) ) ? $fields[ $field ]['type'] : 'native'; // @todo Is this needed? It seems to set the type to "native" if not set.

		$result = $user_info->{$field};
		
		// Handle select and radio groups (have single selections).
		if ( 'select' == $field_type || 'radio' == $field_type ) {
			$result = ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) ? $user_info->{$field} : $fields[ $field ]['options'][ $user_info->{$field} ];
		}

		// Handle multiple select and multiple checkbox (have multiple selections).
		if ( 'multiselect' == $field_type || 'multicheckbox' == $field_type ) {
			if ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) {
				$result = $user_info->{$field};
			} else {
				$saved_vals = explode( $fields[ $field ]['delimiter'], $user_info->{$field} );
				$result = ''; $x = 1;
				foreach ( $saved_vals as $value ) {
					$result.= ( $x > 1 ) ? ', ' : ''; $x++;
					$result.= $fields[ $field ]['options'][ $value ];
				}
			}
		}

		// Handle file/image fields.
		if ( isset( $field_type ) && ( 'file' == $field_type || 'image' == $field_type ) ) {
			if ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) {
				$result = $user_info->{$field};
			} else {
				if ( 'file' == $field_type ) {
					$attachment_url = wp_get_attachment_url( $user_info->{$field} );
					$result = ( $attachment_url ) ? '<a href="' . esc_url( $attachment_url ) . '">' .  get_the_title( $user_info->{$field} ) . '</a>' : '';
				} else {
					$size = 'thumbnail';
					if ( isset( $atts['size'] ) ) {
						$sizes = array( 'thumbnail', 'medium', 'large', 'full' );
						$size  = ( ! in_array( $atts['size'], $sizes ) ) ? explode( ",", $atts['size'] ) : $atts['size'];
					}
					$image = wp_get_attachment_image_src( $user_info->{$field}, $size );
					$result = ( $image ) ? '<img src="' . esc_url( $image[0] ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" />' : '';
				}
			}
			return do_shortcode( $result );
		}
		
		// Handle line breaks for textarea fields
		if ( isset( $field_type ) && 'textarea' == $field_type ) {
			$result = ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) ? $user_info->{$field} : nl2br( $user_info->{$field} );
		}

		// Handle date fields.
		if ( isset( $field_type ) && 'date' == $field_type ) {
			if ( isset( $atts['format'] ) ) {
				// Formats date: http://php.net/manual/en/function.date.php
				$result =  date( $atts['format'], strtotime( $user_info->{$field} ) );
			} else {
				// Formats date to whatever the WP setting is.
				$result = date_i18n( get_option( 'date_format' ), strtotime( $user_info->{$field} ) );
			}
		}
		
		// Remove underscores from value if requested (default: on).
		if ( isset( $atts['underscores'] ) && 'off' == $atts['underscores'] && $user_info ) {
			$result = str_replace( '_', ' ', $result );
		}

		$content = ( $content ) ? $result . $content : $result;

		return do_shortcode( $content );
	}
	return;
}

/**
 * Logout link shortcode [wpmem_logout].
 *
 * @since 3.1.2
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string $url
 * }
 * @param  string $content
 * @param  string $tag
 * @retrun string $content
 */
function wpmem_sc_logout( $atts, $content, $tag ) {
		// Logout link shortcode.
	if ( is_user_logged_in() && $tag == 'wpmem_logout' ) {
		$link = ( isset( $atts['url'] ) ) ? add_query_arg( 'a', 'logout', $atts['url'] ) : add_query_arg( 'a', 'logout' );
		$text = ( $content ) ? $content : __( 'Click here to log out.', 'wp-members' );
		return do_shortcode( "<a href=\"$link\">$text</a>" );
	}
}

/**
 * TOS shortcode [wpmem_tos].
 *
 * @since 3.1.2
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string $url
 * }
 * @param  string $content
 * @param  string $tag
 * @retrun string $content
 */
function wpmem_sc_tos( $atts, $content, $tag ) {
	return do_shortcode( $atts['url'] ); 
}

/**
 * Display user avatar.
 *
 * @since 3.1.7
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 *
 *     @type string $id   The user email or id.
 *     @type int    $size Avatar size (square) in pixels.
 * }
 * @param  string $content
 * @param  string $tag
 * @retrun string $content
 */
function wpmem_sc_avatar( $atts, $content, $tag ) {
	$content = '';
	$size = ( isset( $atts['size'] ) ) ? $atts['size'] : '';
	if ( isset( $atts['id'] ) ) {
		$content = get_avatar( $atts['id'], $size );
	} elseif ( is_user_logged_in() ) {
		// If the user is logged in and this isn't specifying a user ID, return the current user avatar.
		global $current_user;
		wp_get_current_user();
		$content = get_avatar( $current_user->ID, $size );
	}
	return do_shortcode( $content );
}

/**
 * Generates a login link with a return url.
 *
 * @since 3.1.7
 *
 * @param  array  $atts {
 *     The shortcode attributes.
 * }
 * @param  string $content
 * @param  string $tag
 * @retrun string $content
 */
function wpmem_sc_link( $atts, $content, $tag ) {
	if ( 'wpmem_reg_link' == $tag ) {
		$text = ( $content ) ? $content : __( 'Register' );
		$link = add_query_arg( 'redirect_to', wpmem_current_url(), wpmem_register_url() );
	} else {
		$text = ( $content ) ? $content : __( 'Log In' );
		$link = wpmem_login_url( wpmem_current_url() );
	}
	$content = '<a href="' . $link . '">' . $text . '</a>';
	return do_shortcode( $content );
}

// End of file.