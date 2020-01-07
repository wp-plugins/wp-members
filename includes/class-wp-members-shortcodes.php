<?php
/**
 * The WP_Members Shortcodes Class.
 *
 * This class contains functions
 * for the shortcodes used by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP_Members_Shortcodes
 * @author Chad Butler 
 * @copyright 2006-2019
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Shortcodes {
	
	function __construct() {
		/**
		 * Fires before shortcodes load.
		 *
		 * @since 3.0.0
		 * @since 3.1.6 Fires before shortcodes load.
		 */
		do_action( 'wpmem_load_shortcodes' );
		
		add_shortcode( 'wpmem_field',      array( $this, 'fields'       ) );
		add_shortcode( 'wpmem_logged_in',  array( $this, 'logged_in'    ) );
		add_shortcode( 'wpmem_logged_out', array( $this, 'logged_out'   ) );
		add_shortcode( 'wpmem_logout',     array( $this, 'logout'       ) );
		add_shortcode( 'wpmem_form',       array( $this, 'forms'        ) );
		add_shortcode( 'wpmem_show_count', array( $this, 'user_count'   ) );
		add_shortcode( 'wpmem_profile',    array( $this, 'user_profile' ) );
		add_shortcode( 'wpmem_loginout',   array( $this, 'loginout'     ) );
		add_shortcode( 'wpmem_tos',        array( $this, 'tos'          ) );
		add_shortcode( 'wpmem_avatar',     array( $this, 'avatar'       ) );
		add_shortcode( 'wpmem_login_link', array( $this, 'login_link'   ) );
		add_shortcode( 'wpmem_reg_link',   array( $this, 'login_link'   ) );
		add_shortcode( 'wpmem_form_nonce', array( $this, 'form_nonce'   ) );
		
		/**
		 * Fires after shortcodes load.
		 * 
		 * @since 3.0.0
		 * @since 3.1.6 Was wpmem_load_shortcodes, now wpmem_shortcodes_loaded.
		 */
		do_action( 'wpmem_shortcodes_loaded' );
	}
	
	/**
	 * Function for forms called by shortcode.
	 *
	 * @since 3.0.0
	 * @since 3.1.3 Added forgot_username shortcode.
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::forms().
	 * @since 3.2.0 Added id, exclude_fields, include_fields, and product attributes.
	 *
	 * @todo Complete support for id, exlude_fields, include_fields, and product attributes
	 *       May require updates to core functions.
	 *
	 * @global object $wpmem        The WP_Members object.
	 * @global string $wpmem_themsg The WP-Members message container.
	 *
	 * @param  array  $atts {
	 *     Possible shortcode attributes (some vary by form).
	 *
	 *     @type string $id              An ID for the form.
	 *     @type string $login           Idenifies login form.
	 *     @type string $password        Idenifies reset/change password form (login state dependent).
	 *     @type string $user_edit       Idenifies user profile edit form.
	 *     @type string $forgot_username Idenifies forgot username form.
	 *     @type string $register        Idenifies register form.
	 *     @type string $redirect_to     URL to redirect to on form submit.
	 *     @type string $texturize       Add/fix texturization for the from HTML.
	 *     @type string $exclude_fields  Fields to exclude (register/user_edit forms only).
	 *     @type string $include_fields  Fields to include (register/user_edit forms only).
	 *     @type string $product         Register for specific product (if products are enabled).
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function forms( $atts, $content = null, $tag = 'wpmem_form' ) {

		global $wpmem, $wpmem_themsg;

		// Defaults.
		$redirect_to = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
		$texturize   = ( isset( $atts['texturize']   ) ) ? $atts['texturize']   : false;
		
		$customizer = ( is_customize_preview() ) ? get_theme_mod( 'wpmem_show_logged_out_state', false ) : false;
		
		/*
		 * The [wpmem_form] shortcode requires additional tags (login, register, etc) that
		 * will be in the $atts array. If $atts is not an array, no additional tags were
		 * given, so there is nothing to render.
		 */
		if ( is_array( $atts ) ) {

			// If $atts is an array, get the tag from the array so we know what form to render.
			switch ( $atts ) {

				case in_array( 'login', $atts ):		
					if ( is_user_logged_in() && '1' != $customizer ) {
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
						$content = ( $wpmem->regchk == 'loginfailed' || ( is_customize_preview() && get_theme_mod( 'wpmem_show_form_message_dialog', false ) ) ) ? wpmem_inc_loginfailed() : wpmem_inc_login( 'login', $redirect_to );
					}
					break;

				case in_array( 'register', $atts ):
					if ( is_user_logged_in()  && '1' != $customizer ) {
						/*
						 * If the user is logged in, return any nested content (if any)
						 * or the default bullet links if no nested content.
						 */
						$content = ( $content ) ? $content : wpmem_inc_memberlinks( 'register' );
					} elseif ( is_user_logged_in() && is_customize_preview() && get_theme_mod( 'wpmem_show_form_message_dialog', false ) ) {
						$wpmem_themsg = __( "This is a generic message to display the form message dialog in the Customizer.", 'wp-members' );
						$content  = wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
						$content .= wpmem_register_form( 'new', $redirect_to );
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
						$content .= ( $wpmem->regchk == 'success' ) ? wpmem_inc_login( 'login', $redirect_to ) : wpmem_register_form( 'new', $redirect_to );
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
					
				case in_array( 'customizer_login', $atts ):
					$content = wpmem_inc_login( 'login', $redirect_to );
					break;
					
				case in_array( 'customizer_register', $atts ):
					$content = wpmem_register_form( 'new', $redirect_to );
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
					add_filter( 'the_content', array( 'WP_Members', 'texturize' ), 999 );
				}
			} // End texturize functions
		}
		return do_shortcode( $content );
	}

	/**
	 * Handles the logged in status shortcodes [wpmem_logged_in].
	 *
	 * There are several attributes that can be used with the shortcode: 
	 * in|out, sub for subscription only info, id, and role. IDs and roles 
	 * can be comma separated values for multiple users and roles. 
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::logged_in().
	 * @since 3.2.0 Added attributes for meta key/value pairs.
	 * @since 3.2.3 Added product attribute.
	 * @since 3.3.0 Added compare attribute for meta key/value compare (=|!=).
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
	 *     @type string $meta_key
	 *     @type string $meta_value
	 *     @type string $product
	 *     @type string $membership
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function logged_in( $atts, $content = null, $tag = 'wpmem_logged_in' ) {

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
					$do_return = false;
					$value = ( isset( $atts['meta_value'] ) ) ? $atts['meta_value'] : false;
					if ( ! isset( $atts['compare'] ) || "=" == $atts['compare'] ) {
						if ( wpmem_user_has_meta( $atts['meta_key'], $value ) ) {
							$do_return = true;
						}
					}
					if ( isset( $atts['compare'] ) && "!=" == $atts['compare'] ) {
						if ( ! wpmem_user_has_meta( $atts['meta_key'], $value ) ) {
							$do_return = true;
						}						
					}
				}
				
				// If there is a product attribute.
				if ( isset( $atts['product'] ) || isset( $atts['membership'] ) ) {
					// @todo What if attribute is comma separated/multiple?
					$membership = ( isset( $atts['membership'] ) ) ? $atts['membership'] : $atts['product'];
					if ( wpmem_user_has_access( $membership ) ) {
						$do_return = true;
					} elseif ( true === $atts['msg'] || "true" === strtolower( $atts['msg'] ) ) {
						$do_return = true;
						$settings = array(
							'wrapper_before' => '<div class="product_access_failed">',
							'msg'            => sprintf( __( 'Sorry, your account does not currently have access to %s content', 'wp-members' ), $wpmem->membership->products[ $membership ]['title'] ),
							'wrapper_after'  => '</div>',
						);
						/**
						 * Filter the access failed message.
						 *
						 * @since 3.3.0
						 *
						 * @param array $settings.
						 */
						$settings = apply_filters( 'wpmem_sc_product_access_denied', $settings );
						$content  = $settings['wrapper_before'] . $settings['msg'] . $settings['wrapper_after'];
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
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::logged_out().
	 *
	 * @param  array  $atts
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function logged_out( $atts, $content = null, $tag ) {
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
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::user_count().
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
	function user_count( $atts, $content = null ) {
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
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::user_profile().
	 *
	 * @global object $wpmem        The WP_Members object.
	 * @global string $wpmem_themsg The WP-Members message container.
	 * @param  string $atts {
	 *     The shortcode attributes.
	 *
	 *     @type string $redirect_to
	 *     @type string $register    "hide" removes registration form, any other value is false.
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function user_profile( $atts, $content, $tag ) {

		// @todo $redirect_to is not currently used in the user profile.
		$redirect_to   = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
		$hide_register = ( isset( $atts['register'] ) && 'hide' == $atts['register'] ) ? true : false;

		global $wpmem, $wpmem_themsg;

		$content = '';

		if ( $wpmem->regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = $wpmem->get_text( 'reg_captcha_err' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		if ( $wpmem->regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}

		if ( is_user_logged_in() ) {

			switch( $wpmem->action ) {

			case "edit":
				$content = $content . wpmem_register_form( 'edit' );
				break;

			case "update":
				// Determine if there are any errors/empty fields.
				if ( $wpmem->regchk == "updaterr" || $wpmem->regchk == "email" ) {
					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_register_form( 'edit' );
				} else {
					//Case "editsuccess".
					$content = $content . wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_inc_memberlinks();
				}
				break;

			case "pwdchange":
				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
				$content = ( 'pwdchangesuccess' == $wpmem->regchk ) ? $content . wpmem_inc_memberlinks() : $content;
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
					$content = $content . wpmem_register_form();
					break;
				}

			} elseif ( $wpmem->action == 'pwdreset' ) {

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );

			} elseif( $wpmem->action == 'getusername' ) {

				$content = wpmem_page_forgot_username( $wpmem->regchk, $content );

			} else {

				$content = $content . wpmem_inc_login( 'members' );
				$content = ( ! $hide_register ) ? $content . wpmem_register_form() : $content;
			}
		}

		return $content;
	}

	/**
	 * Log in/out shortcode [wpmem_loginout].
	 *
	 * @since 3.1.1
	 * @since 3.1.6 Uses wpmem_loginout().
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::loginout().
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
	function loginout( $atts, $content, $tag ) {
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
	 * - clickable
	 * - label
	 *
	 * Filter the end result with `wpmem_field_shortcode`.
	 *
	 * @since 3.1.2
	 * @since 3.1.4 Changed to display value rather than stored value for dropdown/multicheck/radio.
	 * @since 3.1.5 Added display attribute, meta key as a direct attribute, and image/file display.
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::fields().
	 * @since 3.2.0 Added clickable attribute.
	 * @since 3.2.5 Added label attribute.
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
	 *     @type string $size
	 *     @type string $clickable   default:false
	 *     @type string $label       default:false
	 * }
	 * @param  string $content Any content passed with the shortcode (default:null).
	 * @param  string $tag     The shortcode tag (wpmem_form).
	 * @return string $content Content to return.
	 */
	function fields( $atts, $content = null, $tag ) {

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
		if ( $user_info && isset( $user_info->{$field} ) ) {

			global $wpmem;
			$fields = wpmem_fields();
			$field_type = ( isset( $fields[ $field ]['type'] ) ) ? $fields[ $field ]['type'] : 'native'; // @todo Is this needed? It seems to set the type to "native" if not set.

			$user_info_field = ( isset( $field ) && is_object( $user_info ) ) ? $user_info->{$field} : '';
			$result = false;
			
			// Handle each field type.
			switch ( $field_type ) {
					
				// Select and radio groups have single selections.
				case 'select':
				case 'radio':
					$result = ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) ? $user_info_field : $fields[ $field ]['options'][ $user_info_field ];
					break;
					
				// Multiple select and multiple checkbox have multiple selections.
				case 'multiselect':
				case 'multicheckbox':
					if ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) {
						$result = $user_info_field;
					} else {
						$saved_vals = explode( $fields[ $field ]['delimiter'], $user_info_field );
						$result = ''; $x = 1;
						foreach ( $saved_vals as $value ) {
							$result.= ( $x > 1 ) ? ', ' : ''; $x++;
							$result.= $fields[ $field ]['options'][ $value ];
						}
					}
					break;
					
				case 'file':
				case 'image':
					if ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) {
						$result = $user_info_field;
					} else {
						if ( 'file' == $field_type ) {
							$attachment_url = wp_get_attachment_url( $user_info_field );
							$result = ( $attachment_url ) ? '<a href="' . esc_url( $attachment_url ) . '">' .  get_the_title( $user_info_field ) . '</a>' : '';
						} else {
							$size = 'thumbnail';
							if ( isset( $atts['size'] ) ) {
								$sizes = array( 'thumbnail', 'medium', 'large', 'full' );
								$size  = ( ! in_array( $atts['size'], $sizes ) ) ? explode( ",", $atts['size'] ) : $atts['size'];
							}
							$image = wp_get_attachment_image_src( $user_info_field, $size );
							$result = ( $image ) ? '<img src="' . esc_url( $image[0] ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" />' : '';
						}
					}
					break;
					
				case 'textarea':
					// Handle line breaks for textarea fields
					$result = ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) ? $user_info_field : nl2br( $user_info_field );
					break;
					
				case 'date':
					if ( isset( $atts['format'] ) ) {
						// Formats date: https://secure.php.net/manual/en/function.date.php
						$result = ( '' != $user_info_field ) ? date( $atts['format'], strtotime( $user_info_field ) ) : '';
					} else {
						// Formats date to whatever the WP setting is.
						$result = ( '' != $user_info_field ) ? date_i18n( get_option( 'date_format' ), strtotime( $user_info_field ) ) : '';
					}
					break;
					
				// Handle all other fields.
				default:
					$result = ( ! $result ) ? $user_info_field : $result;					
					break;
			}

			// Remove underscores from value if requested (default: on).
			if ( isset( $atts['underscores'] ) && 'off' == $atts['underscores'] && $user_info ) {
				$result = str_replace( '_', ' ', $result );
			}

			$content = ( $content ) ? $result . $content : $result;
			
			// Make it clickable?
			$content = ( isset( $atts['clickable'] ) && ( true == $atts['clickable'] || 'true' == $atts['clickable'] ) ) ? make_clickable( $content ) : $content;
		
			// Display field label?
			$content = ( isset( $fields[ $field ] ) && isset( $atts['label'] ) && ( true == $atts['label'] ) ) ? $fields[ $field ]['label'] . ": " . $content : $content;
		}
		
		/**
		 * Filters the field shortcode before returning value.
		 *
		 * @since 3.2.5
		 *
		 * @param string $content
		 * @param array  $atts
		 */
		$content = apply_filters( 'wpmem_field_shortcode', $content, $atts );

		return do_shortcode( $content );
	}

	/**
	 * Logout link shortcode [wpmem_logout].
	 *
	 * @since 3.1.2
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::logout().
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
	function logout( $atts, $content, $tag ) {
			// Logout link shortcode.
		if ( is_user_logged_in() && $tag == 'wpmem_logout' ) {
			$link = ( isset( $atts['url'] ) ) ? add_query_arg( array( 'a'=>'logout', 'redirect_to'=>$atts['url'] ) ) : add_query_arg( 'a', 'logout' );
			$text = ( $content ) ? $content : __( 'Click here to log out.', 'wp-members' );
			return do_shortcode( '<a href="' . esc_url( $link ) . '">' . $text . '</a>' );
		}
	}

	/**
	 * TOS shortcode [wpmem_tos].
	 *
	 * @since 3.1.2
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::tos().
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
	function tos( $atts, $content, $tag ) {
		return esc_url( $atts['url'] ); 
	}

	/**
	 * Display user avatar.
	 *
	 * @since 3.1.7
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::avatar().
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
	function avatar( $atts, $content, $tag ) {
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
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::login_link().
	 *
	 * @param  array  $atts {
	 *     The shortcode attributes.
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function login_link( $atts, $content, $tag ) {
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
	
	/**
	 * Generate a nonce for a WP-Members form.
	 *
	 * For situations where a hardcoded form may exist, this shortcode
	 * can output the appropriate nonce.
	 *
	 * @since 3.3.0
	 *
	 * @param  array  $atts {
	 *     The shortcode attributes.
	 *
	 *     $form string The form to generate the nonce for (register|update|login) 
	 * }
	 * $param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function form_nonce( $atts, $content, $tag ) {
		$nonce = ( isset( $atts['form'] ) ) ? $atts['form'] : 'register';
		$content = wpmem_form_nonce( $nonce, false );
		return do_shortcode( $content );
	}
}

// End of file.