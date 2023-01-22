<?php
/**
 * The WP_Members Shortcodes Class.
 *
 * This class contains functions
 * for the shortcodes used by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2022  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP_Members_Shortcodes
 * @author Chad Butler 
 * @copyright 2006-2022
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
		
		add_shortcode( 'wpmem_field',        array( $this, 'fields'       ) );
		add_shortcode( 'wpmem_logged_in',    array( $this, 'logged_in'    ) );
		add_shortcode( 'wpmem_logged_out',   array( $this, 'logged_out'   ) );
		add_shortcode( 'wpmem_logout',       array( $this, 'logout'       ) );
		add_shortcode( 'wpmem_form',         array( $this, 'forms'        ) );
		add_shortcode( 'wpmem_show_count',   array( $this, 'user_count'   ) );
		add_shortcode( 'wpmem_profile',      array( $this, 'user_profile' ) );
		add_shortcode( 'wpmem_loginout',     array( $this, 'loginout'     ) );
		add_shortcode( 'wpmem_tos',          array( $this, 'tos'          ) );
		add_shortcode( 'wpmem_avatar',       array( $this, 'avatar'       ) );
		add_shortcode( 'wpmem_login_link',   array( $this, 'login_link'   ) );
		add_shortcode( 'wpmem_login_button', array( $this, 'login_button' ) );
		add_shortcode( 'wpmem_reg_link',     array( $this, 'login_link'   ) );
		add_shortcode( 'wpmem_form_nonce',   array( $this, 'form_nonce'   ) );
		
		/**
		 * Fires after shortcodes load.
		 * 
		 * @since 3.0.0
		 * @since 3.1.6 Was wpmem_load_shortcodes, now wpmem_shortcodes_loaded.
		 */
		do_action( 'wpmem_shortcodes_loaded' );
	}
	
	/**
	 * Renders forms called by [wpmem_form] shortcode.
	 *
	 * @since 3.0.0
	 * @since 3.1.3 Added forgot_username shortcode.
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::forms().
	 * @since 3.2.0 Added id, exclude_fields, include_fields, and product attributes.
	 * @since 3.3.2 Added WP default login form.
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
	 *     @type string $login           Renders a login form.
	 *     @type string $password        Renders a reset/change password form (login state dependent).
	 *     @type string $user_edit       Renders a user profile edit form.
	 *     @type string $forgot_username Renders a forgot username form.
	 *     @type string $register        Renders a register form.
	 *     @type string $wp_login        Renders the WP login form.
	 *     @type string $redirect_to     URL to redirect to on form submit (used for login and register forms).
	 *     @type string $texturize       Add/fix texturization for the from HTML (usually not necessary).
	 *     @type string $exclude_fields  Fields to exclude as comma separated meta keys (register/user_edit forms only).
	 *     @type string $include_fields  Fields to include as comma separated meta keys (register/user_edit forms only).
	 *     @type string $product         Register for specific product (if products are enabled).
	 * }
	 * @param  string $content Nested content displayed if the shortcode is in the logged in state (optional).
	 * @param  string $tag     The shortcode's tag (wpmem_form).
	 * @return string          The form HTML (or nested content, if used and the user is logged in).
	 */
	function forms( $atts, $content, $tag ) {

		if ( is_admin() ) {
			return;
		}

		global $wpmem, $wpmem_themsg;

		// Defaults.
		$defaults = array(
			'form'        => '',
			'redirect_to' => null,
			'texturize'   => false,
			'form_id'     => false,
		);
		$atts = wp_parse_args( $atts, $defaults );
		
		$atts['form'] = ( isset( $atts[0] ) ) ? $atts[0] : 'login';
		unset( $atts[0] );
		
		$customizer = ( is_customize_preview() ) ? get_theme_mod( 'wpmem_show_logged_out_state', false ) : false;

		// If $atts is an array, get the tag from the array so we know what form to render.
		switch ( $atts['form'] ) {

			case 'wp_login':
				if ( is_user_logged_in() && '1' != $customizer ) {
					// If the user is logged in, return any nested content (if any) or the default bullet links if no nested content.
					$content = ( $content ) ? $content : $this->render_links( 'login' );
				} else {
					$atts['echo'] = false;
					$atts['redirect'] = ( isset( $atts['redirect'] ) ) ? $atts['redirect'] : ( ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : wpmem_current_url() );
					$content = wpmem_wp_login_form( $atts );
				}
				break;

			case 'register':

				// Set up register form args.
				$reg_form_args = array( 'tag' => 'new' );
				if ( isset( $redirect_to ) ) {
					$reg_form_args['redirect_to'] = $redirect_to;
				}

				if ( is_user_logged_in()  && '1' != $customizer ) {
					/*
					 * If the user is logged in, return any nested content (if any)
					 * or the default bullet links if no nested content.
					 */
					$content = ( $content ) ? $content : $this->render_links( 'register' );
				} elseif ( is_user_logged_in() && is_customize_preview() && get_theme_mod( 'wpmem_show_form_message_dialog', false ) ) {
					$wpmem_themsg = __( "This is a generic message to display the form message dialog in the Customizer.", 'wp-members' );
					$content  = wpmem_get_display_message( $wpmem->regchk, $wpmem_themsg );
					$content .= wpmem_register_form( $reg_form_args );
				} else {
					if ( $wpmem->regchk == 'loginfailed' ) {
						$content = $wpmem->dialogs->login_failed() . wpmem_login_form();
						break;
					}
					// @todo Can this be moved into another function? Should $wpmem get an error message handler?
					if ( $wpmem->regchk == 'captcha' ) {
						global $wpmem_captcha_err;
						$wpmem_themsg = wpmem_get_text( 'reg_captcha_err' ) . '<br /><br />' . $wpmem_captcha_err;
					}
					$content  = ( $wpmem_themsg || $wpmem->regchk == 'success' ) ? wpmem_get_display_message( $wpmem->regchk, $wpmem_themsg ) : '';
					$content .= ( $wpmem->regchk == 'success' ) ? wpmem_login_form() : wpmem_register_form( $reg_form_args );
				}
				break;

			case 'password':
				$content = $this->render_pwd_reset( $wpmem->regchk, $content );
				break;

			case 'user_edit':
				$content = $this->render_user_edit( $wpmem->regchk, $content, $atts );
				break;

			case 'forgot_username':
				$content = $this->render_forgot_username( $wpmem->regchk, $content );
				break;

			// @todo Review - is this actually ever triggered?
			case 'customizer_login':
				$content = wpmem_login_form();
				break;

			// @todo Review - is this actually ever triggered?
			case 'customizer_register':
				$content = wpmem_register_form( 'new' );
				break;
				
			case 'login':
			default:
				if ( is_user_logged_in() && '1' != $customizer ) {
					// If the user is logged in, return any nested content (if any) or the default bullet links if no nested content.
					$content = ( $content ) ? $content : $this->render_links( 'login' );
				} else {
					$content = '';
					if ( $wpmem->regchk == 'loginfailed' || ( is_customize_preview() && get_theme_mod( 'wpmem_show_form_message_dialog', false ) ) ) {
						$content = wpmem_get_display_message( 'loginfailed' );
					}
					$form_id = ( $atts['form_id'] ) ? $atts['form_id'] : 'wpmem_login_form';
					$content .= wpmem_login_form( array( 'redirect_to'=>$atts['redirect_to'], 'form_id'=>$form_id ) );
				}
				break;

		}

		return do_shortcode( $content );
	}

	/**
	 * Restricts content to logged in users using the shortcode [wpmem_logged_in].
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
	 *     Attributes of the wpmem_logged_in shortcode.
	 *
	 *     @type string $status      User status to check (in|out) (optional).
	 *     @type int    $id          The user's ID. Restricts to a specified user by ID (optional).
	 *     @type string $role        The user's role. Restrictes to a specific role (optional).
	 *     @type string $sub         If the user is a current subscriber (for use with the PayPal extension) (optional).
	 *     @type string $meta_key    If the user has a specified meta key (use with meta_value) (optional).
	 *     @type string $meta_value  If the user has a specific meta key value (use with meta_key) (optional).
	 *     @tryp string $compare     Can specify a comparison operator when using meta_key/meta_value (optional).
	 *     @type string $product     If the user has a specific product/membership (optional).
	 *     @type string $membership  If the user has a specific product/membership (optional).
	 *     @type string $wrap_id     Adds a div wrapper with specified id.
	 *     @type string $wrap_class  Adds a div wrapper with specified class.
	 *     @type string $wrap_tag    Specifies wrapper tag (default: div)
	 * }
	 * @param  string $content Shortcode content.
	 * @param  string $tag     The shortcode's tag (wpmem_logged_in).
	 * @return string|void     The restricted content to display if the user meets the criteria.
	 */
	function logged_in( $atts, $content, $tag ) {

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
						if ( ! wpmem_is_user_current() ) {
							$do_return = true;
						} elseif ( $atts['msg'] == "true" ) {
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
					$message    = ( isset( $atts['msg'] ) && ( true === $atts['msg'] || "true" === strtolower( $atts['msg'] ) ) ) ? true : false;
					$not_in     = ( isset( $atts['not_in'] ) && "false" != $atts['not_in'] ) ? true : false;
					if ( true == $not_in ) {
						$do_return = ( wpmem_user_has_access( $membership ) || ! is_user_logged_in() ) ? false : true;
					} else {
						if ( wpmem_user_has_access( $membership ) ) {
							$do_return = true;
						} elseif ( true === $message ) {
							$do_return = true;
							$settings = array(
								'wrapper_before' => '<div class="product_restricted_msg">',
								'msg'            => sprintf( wpmem_get_text( 'product_restricted' ), wpmem_get_membership_name( $membership ) ),
								'wrapper_after'  => '</div>',
							);
							/**
							 * Filter the access failed message.
							 *
							 * @since 3.3.0
							 * @since 3.3.3 Changed from 'wpmem_sc_product_access_denied'
							 *
							 * @param array $settings.
							 */
							$settings = apply_filters( 'wpmem_sc_product_restricted', $settings );
							$content  = $settings['wrapper_before'] . $settings['msg'] . $settings['wrapper_after'];
						}
					}
				}

				// Prevents display if the current page is the user profile and an action is being handled.
				if ( ( wpmem_current_url( true, false ) == wpmem_profile_url() ) && isset( $_GET['a'] ) ) {
					$do_return = false;
				}

				// Adds optional wrapper.
				if ( isset( $atts['wrap_id'] ) || isset( $atts['wrap_class'] ) ) {
					$tag = ( isset( $atts['wrap_tag'] ) ) ? $atts['wrap_tag'] : 'div';
					$wrapper  = '<' . $tag;
					$wrapper .= ( isset( $atts['wrap_id']    ) ) ? ' id="'    . $atts['wrap_id']    . '"' : '';
					$wrapper .= ( isset( $atts['wrap_class'] ) ) ? ' class="' . $atts['wrap_class'] . '"' : '';
					$wrapper .= '>';
					$content = $wrapper . $content . '</' . $tag . '>';
				}

			}

			// Return content (or empty content) depending on the result of the above logic.
			return ( $do_return ) ? do_shortcode( $content ) : '';
		}
	}

	/**
	 * Renders the [wpmem_logged_out] shortcode.
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Moved to WP_Members_Shortcodes::logged_out().
	 *
	 * @param  array  $atts    There are no attributes for this shortcode.
	 * @param  string $content Conent to display if user is logged out.
	 * @param  string $tag     The shortcode tab (wpmem_logged_out).
	 * @return string $content The content, if the user is logged out, otherwise an empty string.
	 */
	function logged_out( $atts, $content, $tag ) {
		return ( ! is_user_logged_in() ) ? do_shortcode( $content ) : '';
	}

	/**
	 * Displays the user count shortcode [wpmem_show_count].
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
	 *     @type string $key    The user meta key, if displaying count by meta key.
	 *     @type string $value  The user meta value, if displaying count by meta key.
	 *     @type string $role   The user role, if displaying count by role.
	 *     @type string $label  Label to display with the count (optional).
	 * }
 	 * @param  string $content Does not accept nested content.
	 * @param  string $tag     The shortcode's tag (wpmem_show_count).
	 * @return string $content The user count.
	 */
	function user_count( $atts, $content, $tag ) {
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
	 *     The shortcode attributes (filter with 'shortcode_atts_wpmem_profile').
	 *
	 *     @type string $redirect_to
	 *     @type string $register    "hide" removes registration form, any other value is false.
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function user_profile( $atts, $content, $tag ) {

		global $wpmem, $wpmem_themsg;

		$pairs = array(
			'register'    => 'show',
			'redirect_to' => '',
		);

		$args = shortcode_atts( $pairs, $atts, $tag );

		// @todo $redirect_to is not currently used in the user profile.
		$redirect_to   = $args['redirect_to'];
		$hide_register = ( isset( $args['register'] ) && 'hide' == $args['register'] ) ? true : false;

		$content = '';

		if ( $wpmem->regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = wpmem_get_text( 'reg_captcha_err' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		if ( is_user_logged_in() ) {

			switch( $wpmem->action ) {

			case "edit":
				$content = $content . wpmem_register_form( 'edit' );
				break;

			case "update":
				// Determine if there are any errors/empty fields.
				if ( $wpmem->regchk == "updaterr" || $wpmem->regchk == "email" ) {
					$content = $content . wpmem_get_display_message( $wpmem->regchk, $wpmem_themsg );
					$content = $content . wpmem_register_form( 'edit' );
				} else {
					//Case "editsuccess".
					$content = $content . wpmem_get_display_message( $wpmem->regchk, $wpmem_themsg );
					$content = $content . $this->render_links();
				}
				break;

			case "pwdchange":
				$content = $this->render_pwd_reset( $wpmem->regchk, $content );
				$content = ( 'pwdchangesuccess' == $wpmem->regchk ) ? $content . $this->render_links() : $content;
				break;

			case "renew":
				if ( function_exists( 'wpmem_renew' ) ) {
					$content = wpmem_renew();
				} else {
					$content = '';
				}
				break;

			default:
				$content = $this->render_links();
				break;
			}

		} else {

			if (  ( 'login' == $wpmem->action ) || ( 'register' == $wpmem->action && ! $hide_register ) ) {
				
 				$content = wpmem_get_display_message( $wpmem->regchk, $wpmem_themsg );
				$content.= ( 'loginfailed' == $wpmem->regchk || 'success' == $wpmem->regchk ) ? wpmem_login_form() : wpmem_register_form();
				
			} elseif ( 'pwdreset' == $wpmem->action ) {

				$content = $this->render_pwd_reset( $wpmem->regchk, $content );

			} elseif ( 'set_password_from_key' == $wpmem->action ) {
				
				$content = ( false != $wpmem->pwd_reset->content ) ? $wpmem->pwd_reset->content : $content;

			} elseif ( 'getusername' == $wpmem->action ) {

				$content = $this->render_forgot_username( $wpmem->regchk, $content );

			} else {

				$content = $content . wpmem_login_form( 'profile' );
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
	function fields( $atts, $content, $tag ) {

		// What field?
		if ( isset( $atts[0] ) ) {
			$field = $atts[0];
		} elseif ( isset( $atts['field'] ) ) {
			$field = $atts['field'];
		} else {
			return; // If the field is not directly set in the attributes array, or keyed as "field", then it's not used correctly so return void.
		}

		// What user?
		if ( isset( $atts['id'] ) ) {
			if ( 'author' == $atts['id'] ) {
				global $post;
				$field_user_id = get_post_field( 'post_author', $post->ID );

				// Alternate method:
				// $field_user_id = get_the_author_meta( 'ID' );
			} else {
				$field_user_id = ( $atts['id'] == 'get' ) ? wpmem_get( 'uid', '', 'get' ) : $atts['id'];
			}
		} else {
			$field_user_id = get_current_user_id();
		}

		// Sanitize the result.
		$sanitized_user_id = intval( $field_user_id );

		// Get the user data.
		$user_info = get_userdata( $sanitized_user_id );

		// If there is userdata.
		if ( $user_info && isset( $user_info->{$field} ) ) {

			global $wpmem;
			$fields = wpmem_fields();
			
			$field_type = ( 'user_login' == $field || ! isset( $fields[ $field ] ) ) ? 'text' : $fields[ $field ]['type'];
			$user_info_field = ( isset( $field ) && is_object( $user_info ) ) ? $user_info->{$field} : '';
			$result = false;

			// Handle each field type.
			switch ( $field_type ) {
					
				// Select and radio groups have single selections.
				case 'select':
				case 'radio':
					$result = ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) ? $user_info_field : wpmem_select_field_display( $field, $user_info_field );
					break;
					
				// Multiple select and multiple checkbox have multiple selections.
				case 'multiselect':
				case 'multicheckbox':
				case 'membership':
					if ( isset( $atts['display'] ) && 'raw' == $atts['display'] ) {
						$result = $user_info_field;
					} else {
						$saved_vals = explode( $fields[ $field ]['delimiter'], $user_info_field );
						$result = ''; $x = 1;
						if ( 'list' == $atts['display'] ) {
							/**
							 * Filter list multi field list display HTML parts.
							 * 
							 * @since 3.4.5
							 * 
							 * @param  array  {
							 *     The HTML parts (defaults as a bulleted list)
							 * 
							 *     @type string $wrapper_before
							 *     @type string $item_before
							 *     @type string $item_after
							 *     @type string $wrapper_after
							 * }
							 * @param  string  $field
							 * /
							$multi_args = apply_filters( 'wpmem_field_shortcode_multi_args', array(
								'wrapper_before' => '<ul id="wpmem_sc_field_' . $field . '">',
								'item_id'        => 'wpmem-sc-multi-' . $field,
								'item_class'     => 'wpmem-sc-multi',
								'item_before'    => '<li id="%s" class="%s">',
								'item_after'     => '</li>',
								'wrapper_after'  => '</ul>',
							), $field );

							foreach ( $saved_vals as $value ) {
								$rows[ $value ] = array(
									'item_before' => $multi_args['item_before'],
									'id'     => $multi_args['item_id'] . '-' . $value,
									'class'  => $multi_args['item_class'],
									'value'  => $value,
									'title'  => $value,
									'item_after'  => $multi_args['item_after'],
								);
							}
							/**
							 * Filter the row parts
							 * 
							 * @since 3.4.5
							 * 
							 * @param  array  $rows 
							 * @param  string $field
							 * /
							$rows = apply_filters( 'wpmem_field_shortcode_multi_rows', $rows, $field );
							$row_items = '';
							foreach ( $rows as $value => $row ) {
								$row_items .= sprintf( $row['item_before'], esc_attr( $row['id'] . '-' . $row['value'] ), esc_attr( $row['class'] ) ) . esc_attr( $row['title'] ) . $row['item_after'];
							}

							$result = $multi_args['wrapper_before'] . $row_items . $multi_args['wrapper_after'];
						*/

							$args = array(
								'wrapper' => array(
									'tag'  => ( isset( $atts['wrapper_tag'] ) ) ? $atts['wrapper_tag'] : 'ul',
									'atts' => array(
										'id'    => ( isset( $atts['wrapper_id']    ) ) ? $atts['wrapper_id']    : 'wpmem_field_' . $field,
										'class' => ( isset( $atts['wrapper_class'] ) ) ? $atts['wrapper_class'] : 'wpmem-field-multi-list',
									),
								),
							);
							foreach ( $saved_vals as $value ) {
								$args['item'][ $value ] = array(
									'tag'  => ( isset( $atts['item_tag'] ) ) ? $atts['item_tag'] : 'li',
									'atts' => array(
										'id' => 'wpmem_field_' . $field . '_' . $value,
										'class' => 'wpmem-field-' . $field . '-item-' . $value,
									),
									'content' => $value
								);
							}
							/**
							 * Filter list multi field list display HTML parts.
							 * 
							 * @since 3.4.5
							 * 
							 * @param  array  {
							 *     The HTML parts (defaults as a bulleted list)
							 * 
							 *     @type array  $wrapper {
							 *          The wrapper parts.
							 * 
							 *          @type string $tag     The HTML tag (default: ul)
							 *          @type array  $atts    The HTML tag attributes
							 *          @type string $content The content wrapped by the tag (default: list items)
							 *     }
							 *     @type array  $item {
							 *          An item for each list item.
							 * 
							 *          @type string $tag     The HTML tag (default: li)
							 *          @type array  $atts    The HTML tag attributes
							 *          @type string $content The list item value
							 *     }
							 * }
							 * @param  string  $field
							 */
							$multi_args = apply_filters( 'wpmem_field_sc_multi_html', $args, $field );

							$list = '';
							foreach ( $multi_args['item'] as $item ) {
								$list .= rktgk_build_html_tag( $item );
							}
							$multi_args['wrapper']['content'] = $list;
							$result = rktgk_build_html_tag( $multi_args['wrapper'] );

						} else {
							foreach ( $saved_vals as $value ) {
								$result.= ( $x > 1 ) ? ', ' : ''; $x++;
								$result.= wpmem_select_field_display( $field, $value );;
							}
						}
					} 
					break;
					
				case 'file':
				case 'image':
					if ( isset( $atts['display'] ) ) {
						switch ( $atts['display'] ) {
							case "url":
								$result = wp_get_attachment_url( $user_info_field );
								break;
							case "raw":
							default:
								$result = $user_info_field;
								break;
						}
						
					} else {
						if ( 'file' == $field_type ) {
							$attachment_url = wp_get_attachment_url( $user_info_field );
							/**
							 * Filter the file html tag parts.
							 * 
							 * @since 3.4.5
							 * 
							 * @param  array  $args
							 * @param  string $field
							 */
							$html_args = apply_filters( 'wpmem_field_sc_file_html', array(
								'tag'  => 'a',
								'atts' => array(
									'href'  => esc_url( $attachment_url ),
									'id'    => ( isset( $atts['id']    ) ) ? esc_attr( $atts['id']    ) : esc_attr( 'wpmem_field_file_' . $field ),
									'class' => ( isset( $atts['class'] ) ) ? esc_attr( $atts['class'] ) : esc_attr( 'wpmem-field-file-' . $field ),
								),
								'content' => get_the_title( $user_info_field ),
							), $field );
							$result = ( $attachment_url ) ? rktgk_build_html_tag( $html_args ) : '';
						} else {
							$size = 'thumbnail';
							if ( isset( $atts['size'] ) ) {
								$sizes = array( 'thumbnail', 'medium', 'large', 'full' );
								$size  = ( ! in_array( $atts['size'], $sizes ) ) ? explode( ",", $atts['size'] ) : $atts['size'];
							}
							$image = wp_get_attachment_image_src( $user_info_field, $size );
							/**
							 * Filter the image html tag parts.
							 * 
							 * @since 3.4.5
							 * 
							 * @param  array  $args
							 * @param  string $field
							 */
							$html_args = apply_filters( 'wpmem_field_sc_image_html', array(
								'tag' => 'img',
								'atts' => array(
									'src'    => esc_url( $image[0] ),
									'width'  => esc_attr( $image[1] ),
									'height' => esc_attr( $image[2] ),
									'id'     => ( isset( $atts['id']    ) ) ? esc_attr( $atts['id']    ) : esc_attr( 'wpmem_field_img_' . $field ),
									'class'  => ( isset( $atts['class'] ) ) ? esc_attr( $atts['class'] ) : esc_attr( 'wpmem-field-img-' . $field )
								),
							), $field );
							$result = ( $image ) ? rktgk_build_html_tag( $html_args ) : '';
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
		 * @since 3.4.5 Added $field
		 *
		 * @param string $content
		 * @param array  $atts
		 * @param string $field
		 */
		$content = apply_filters( 'wpmem_field_shortcode', $content, $atts, $field );

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
	 * @since 3.2.5 Now can use page slug (without full url).
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
		$url = ( strpos( $atts['url'], 'http://' ) || strpos( $atts['url'], 'https://' ) ) ? $atts['url'] : home_url( $atts['url'] );
		return esc_url( $url ); 
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
	 * @since 3.4.6 Using wpmem_get_login_link() and wpmem_get_reg_link() adds id and class attributes to HTML tag.
	 *
	 * @param  array  $atts {
	 *     The shortcode attributes.
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function login_link( $atts, $content, $tag ) {
		if ( isset( $atts ) ) {
			$args['attributes'] = $atts;
		}
		if ( 'wpmem_reg_link' == $tag ) {
			$args['content'] = ( isset( $content ) && '' != $content ) ? $content : __( 'Register' );
			return do_shortcode( wpmem_get_reg_link( $args ) );
		} else {
			$args['content'] = ( isset( $content ) && '' != $content ) ? $content : __( 'Log In' );
			return do_shortcode( wpmem_get_login_link( $args ) );
		}
	}
	
	/**
	 * Generages a login link formatted as a button.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $atts {
	 *     The shortcode attributes.
	 * }
	 * @param  string $content
	 * @param  string $tag
	 * @return string $content
	 */
	function login_button( $atts, $content, $tag ) {
		$content = wpmem_loginout( array( 'format'=>'button' ) );
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
	
	/**
	 * Password reset forms.
	 *
	 * This function creates both password reset and forgotten
	 * password forms for page=password shortcode.
	 *
	 * @since 2.7.6
	 * @since 3.2.6 Added nonce validation.
	 * @since 3.4.0 Moved to shortcodes as private function, renamed from wpmem_page_pwd_reset().
	 *
	 * @global object $wpmem
	 * @param  string $wpmem_regchk
	 * @param  string $content
	 * @return string $content
	 */
	function render_pwd_reset( $wpmem_regchk, $content ) {

		global $wpmem;

		if ( is_user_logged_in() ) {

			switch ( $wpmem_regchk ) {

				case "pwdchangesuccess":
					$content = $content . wpmem_get_display_message( $wpmem_regchk );
					break;

				default:
					if ( isset( $wpmem_regchk ) && '' != $wpmem_regchk ) {
						$content .= wpmem_get_display_message( $wpmem_regchk, wpmem_get_text( $wpmem_regchk ) );
					}
					$content = $content . wpmem_change_password_form();
					break;
			}

		} else {

			// If the password shortcode page is set as User Profile page.
			if ( 'getusername' == $wpmem->action ) {

				return $this->render_forgot_username( $wpmem_regchk, $content );

			} elseif ( 'set_password_from_key' == $wpmem->action ) {
				
				return ( false != $wpmem->pwd_reset->content ) ? $wpmem->pwd_reset->content : $content;
			
			} else {

				switch( $wpmem_regchk ) {

					case "pwdresetsuccess":
						$content = $content . wpmem_get_display_message( $wpmem_regchk );
						$wpmem_regchk = ''; // Clear regchk.
						break;

					default:
						if ( $wpmem->has_errors() ) {
							$errors = $wpmem->error->get_error_messages();
							if ( sizeof( $errors ) > 1 ) {
								$error_string = "";
								foreach ( $errors as $error ) {
									$error_string .= $error . '<br />';
								}
							} else {
								$error_string = $errors[0];
							}
							$content = wpmem_get_display_message( $error_string );
						} elseif ( isset( $wpmem_regchk ) && '' != $wpmem_regchk ) {
							$content = wpmem_get_display_message( $wpmem_regchk, wpmem_get_text( $wpmem_regchk ) );
						}
						$content = $content . wpmem_reset_password_form();
						break;
				}

			}

		}

		return $content;

	}
	
	/**
	 * Creates a user edit page.
	 *
	 * @since 2.7.6
	 * @since 3.3.9 Added $atts
	 * @since 3.4.0 Moved to shortcodes as private function, renamed from wpmem_page_user_edit
	 *
	 * @global object $wpmem
	 * @global string $wpmem_a
	 * @global string $wpmem_themsg
	 * @param  string $wpmem_regchk
	 * @param  string $content
	 * @return string $content
	 */
	function render_user_edit( $wpmem_regchk, $content, $atts = false ) {

		global $wpmem, $wpmem_a, $wpmem_themsg;
		/**
		 * Filter the default User Edit heading for shortcode.
		 *
		 * @since 2.7.6
		 *
		 * @param string The default edit mode heading.
		 */	
		$heading = apply_filters( 'wpmem_user_edit_heading', wpmem_get_text( 'profile_heading' ) );

		if ( $wpmem_a == "update") {
			$content.= wpmem_get_display_message( $wpmem_regchk, $wpmem_themsg );
		}

		$args['tag'] = 'edit';
		$args['heading'] = $heading;
		if ( false !== $atts && isset( $atts['fields'] ) ) {
			$args['fields'] = $atts['fields'];
		}

		$content = $content . wpmem_register_form( $args );

		return $content;
	}

	/**
	 * Forgot username form.
	 *
	 * This function creates a form for retrieving a forgotten username.
	 *
	 * @since 3.0.8
	 * @since 3.4.0 Moved to shortcodes as private function, renamed from wpmem_page_forgot_username().
	 *
	 * @param  string $wpmem_regchk
	 * @param  string $content
	 * @return string $content
	 */
	function render_forgot_username( $wpmem_regchk, $content ) {

		if ( ! is_user_logged_in() ) {

			global $wpmem;
			switch( $wpmem->regchk ) {

			case "usernamefailed":
				$msg = wpmem_get_text( 'usernamefailed' );
				$content = $content
					. wpmem_get_display_message( 'usernamefailed', $msg ) 
					. wpmem_forgot_username_form();
				$wpmem->regchk = ''; // Clear regchk.
				break;

			case "usernamesuccess":
				$email = ( isset( $_POST['user_email'] ) ) ? sanitize_email( $_POST['user_email'] ) : '';
				$msg = sprintf( wpmem_get_text( 'usernamesuccess' ), $email );
				$content = $content . wpmem_get_display_message( 'usernamesuccess', $msg );
				$wpmem->regchk = ''; // Clear regchk.
				break;

			default:
				$content = $content . wpmem_forgot_username_form();
				break;
			}

		}

		return $content;

	}

	/**
	 * Member Links Dialog.
	 *
	 * Outputs the links used on the members area.
	 *
	 * @since 2.0
	 * @since 3.4.0 Replaces wpmem_inc_memberlinks().
	 *
	 * @gloabl        $user_login
	 * @global object $wpmem
	 * @param  string $page
	 * @return string $str
	 */
	function render_links( $page = 'member' ) {

		global $user_login, $wpmem;

		$logout = wpmem_logout_link();

		switch ( $page ) {

			case 'register':

				$url = ( isset( $wpmem->user_pages['profile'] ) && '' != $wpmem->user_pages['profile'] ) ? $wpmem->user_pages['profile'] : get_option( 'home' );

				// NOTE: DO NOT EDIT THESE. Use the filter below.
				$arr = array(
					'before_wrapper' => '<p class="register_status">' . sprintf( wpmem_get_text( 'register_status' ), $user_login ) . '</p>',
					'wrapper_before' => '<ul class="register_links">',
					'wrapper_after'  => '</ul>',
					'rows'           => array(
						'<li><a href="' . esc_url( $logout ) . '">' . wpmem_get_text( 'register_logout' ) . '</a></li>',
						'<li><a href="' . esc_url( $url ) . '">' . wpmem_get_text( 'register_continue' ) . '</a></li>',
					),
					'after_wrapper'  => '',
				);

				/**
				 * Filter the register links array.
				 *
				 * @since 3.0.9
				 * @since 3.1.0 Added after_wrapper
				 *
				 * @param array $arr {
				 *      The components of the links.
				 *
				 *      @type string $before_wrapper HTML before the wrapper (default: login status).
				 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
				 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
				 *      @type array  $rows           Row items HTML.
				 *      @type string $after_wrapper  Anything that comes after the wrapper.
				 * }
				 */
				$arr = apply_filters( "wpmem_{$page}_links_args", $arr );

				$str = $arr['before_wrapper'];
				$str.= $arr['wrapper_before'];
				foreach ( $arr['rows'] as $row ) {
					$str.= $row;
				}
				$str.= $arr['wrapper_after'];
				$str.= $arr['after_wrapper'];

				/**
				 * Filter the links displayed on the Register page (logged in state).
				 *
				 * @since 2.8.3
				 *
				 * @param string $str The default links.
				 */
				$str = apply_filters( "wpmem_{$page}_links", $str );
				break;

			case 'login':

				$logout = urldecode( $logout );
				// NOTE: DO NOT EDIT THESE. Use the filter hook below.
				$args = array(
					'wrapper_before' => '<p class="login_status">',
					'wrapper_after'  => '</p>',
					'user_login'     => $user_login,
					'welcome'        => wpmem_get_text( 'login_welcome' ),
					'logout_text'    => wpmem_get_text( 'login_logout' ),
					'logout_link'    => '<a href="' . esc_url( $logout ) . '">%s</a>',
					'separator'      => '<br />',
				);
				/**
				 * Filter the status message parts.
				 *
				 * @since 2.9.9
				 *
				 * @param array $args {
				 *      The components of the links.
				 *
				 *      @type string $wrapper_before The wrapper opening tag (default: <p>).
				 *      @type string $wrapper_after  The wrapper closing tag (default: </p>).
				 *      @type string $user_login
				 *      @type string $welcome
				 *      @type string $logout_text
				 *      @type string $logout_link
				 *      @type string $separator
				 * }
				 */
				$args = apply_filters( "wpmem_{$page}_links_args", $args );

				// Assemble the message string.
				$str = $args['wrapper_before']
					. sprintf( $args['welcome'], $args['user_login'] )
					. $args['separator']
					. sprintf( $args['logout_link'], $args['logout_text'] )
					. $args['wrapper_after'];

				/**
				 * Filter the links displayed on the Log In page (logged in state).
				 *
				 * @since 2.8.3
				 *
				 * @param string $str The default links.
				 */
				$str = apply_filters( "wpmem_{$page}_links", $str );
				break;
			
			case 'member':
			default:
				
				// NOTE: DO NOT EDIT. Use the filter below.
				$arr = array(
					'before_wrapper' => '',
					'wrapper_before' => '<ul>',
					'wrapper_after'  => '</ul>',
					'rows'           => array(
						'<li><a href="' . esc_url( add_query_arg( 'a', 'edit',      remove_query_arg( 'key' ) ) ) . '">' . wpmem_get_text( 'profile_edit'     ) . '</a></li>',
						'<li><a href="' . esc_url( add_query_arg( 'a', 'pwdchange', remove_query_arg( 'key' ) ) ) . '">' . wpmem_get_text( 'profile_password' ) . '</a></li>',
					),
					'after_wrapper'  => '',
				);

				if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 && function_exists( 'wpmem_user_page_detail' ) ) {
					$arr['rows'][] = wpmem_user_page_detail();
				}

				/**
				 * Filter the member links array.
				 *
				 * @since 3.0.9
				 * @since 3.1.0 Added after_wrapper
				 *
				 * @param array $arr {
				 *      The components of the links.
				 *
				 *      @type string $before_wrapper Anything that comes before the wrapper.
				 *      @type string $wrapper_before The wrapper opening tag (default: <ul>).
				 *      @type string $wrapper_after  The wrapper closing tag (default: </ul>).
				 *      @type array  $rows           Row items HTML.
				 *      @type string $after_wrapper  Anything that comes after the wrapper.
				 * }
				 */
				$arr = apply_filters( "wpmem_{$page}_links_args", $arr );

				$str = $arr['before_wrapper'];
				$str.= $arr['wrapper_before'];
				foreach ( $arr['rows'] as $row ) {
					$str.= $row;
				}
				$str.= $arr['wrapper_after'];
				$str.= $arr['after_wrapper'];

				/**
				 * Filter the links displayed on the User Profile page (logged in state).
				 *
				 * @since 2.8.3
				 *
				 * @param string $str The default links.
				 */
				$str = apply_filters( "wpmem_{$page}_links", $str );
				break;
		}

		return $str;
	}
	
	function render_links_filter_args( $page, $args ) {
		
	}
}

// End of file.