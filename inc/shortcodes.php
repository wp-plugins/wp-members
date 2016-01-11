<?php
/**
 * WP-Members Shortcode Functions
 *
 * Contains the shortcode functions used by the plugin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at http://rocketgeek.com
 * Copyright (c) 2006-2016  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @subpackage WP-Members Shortcodes
 * @author Chad Butler 
 * @copyright 2006-2016
 *
 * Functions Included:
 * - wpmem_sc_forms
 * - wpmem_sc_logged_in
 * - wpmem_sc_logged_out
 * - wpmem_shortcode
 * - wpmem_do_sc_pages
 * - wpmem_sc_user_count
 */


/**
 @todo:
	
	New shortcodes will include [wpmem_form] to display various forms.
	
	Forms to be called with single attribute, such as [wpmem_form login] or [wpmem_form register]
	
	Forms should nest for logged in state as well, so we can do:
		[wpmem_form login]This would be logged in content.[/wpmem_form]
	
	Need to have a single stage password reset?
	
	Need to have a form for forgotten username.  This would be a totally new process.
		[wpmem_form forgot_username]?
 
 */


/**
 * Function for forms called by shortcode.
 *
 * @since 3.0.0
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
	
	/**
	 * Load core functions if they are not already loaded.
	 */
	include_once( WPMEM_PATH . 'inc/core.php' );
	
	/**
	 * Load dialog functions if they are not already loaded.
	 */
	include_once( WPMEM_PATH . 'inc/dialogs.php' );

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
					// @todo Can this be moved into another function? Should $wpmem get an error message handler?
					if ( $wpmem->regchk == 'captcha' ) {
						global $wpmem_captcha_err;
						$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
					}
					$content  = ( $wpmem_themsg || $wpmem->regchk == 'success' ) ? wpmem_inc_regmessage( $wpmem->regchk, $wpmem_themsg ) : '';
					$content .= ( $wpmem->regchk == 'success' ) ? wpmem_inc_login() : wpmem_inc_registration( 'new', '', $redirect_to );
				}
				break;
	
			case in_array( 'password', $atts ):
				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
				break;
	
			case in_array( 'user_edit', $atts ):
				$content = wpmem_page_user_edit( $wpmem->regchk, $content );
				break;
	
		}
		
		/*
		 * This is for texturizing. Need to work it into an argument in the function call as to whether the 
		 * [wpmem_txt] shortcode is even included.  @todo - Is this a temporary solution or is there something
		 * cleaner that can be worked out?
		 */
		if ( array_key_exists( 'texturize', $atts ) && $atts['texturize'] == 'false' ) { 
			$content = str_replace( array( '[wpmem_txt]', '[/wpmem_txt]' ), array( '', '' ), $content );
		}
		if ( strstr( $content, '[wpmem_txt]' ) ) {
			// Fixes the wptexturize.
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			add_filter( 'the_content', 'wpmem_texturize', 999 );
		}
		// End texturize functions */
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
 * @param  array  $atts
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
				foreach ( $roles as $role ) {
					if ( in_array( trim( $role ), $current_user->roles ) ) {
						$do_return = true;
					}
				}
			}
			
			// If there is a status attribute of "sub" and the user is logged in.
			if ( ( isset( $atts['status'] ) ) && $atts['status'] == 'sub' && is_user_logged_in() ) {
				if ( defined( 'WPMEM_EXP_MODULE' ) && $wpmem->use_exp == 1 ) {	
					if ( ! wpmem_chk_exp() ) {
						$do_return = true;
					} elseif ( $atts['msg'] == true ) {
						$do_return = true;
						$content = wpmem_sc_expmessage();
					}
				}
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
 * Displays login form when called by shortcode.
 *
 * @since 3.0.0
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param $atts
 * @param $content
 * @param $tag
 * @return $content

function wpmem_sc_login_form( $atts, $content, $tag ) {
	
	// Dependencies.
	global $wpmem;
	include_once( WPMEM_PATH . 'inc/core.php' );
	include_once( WPMEM_PATH . 'inc/dialogs.php' );
	// Defaults.
	$redirect_to = ( isset( $atts['redirect_to'] ) ) ? $atts['redirect_to'] : null;
	$texturize   = ( isset( $atts['texturize']   ) ) ? $atts['texturize']   : false;
	
	if ( is_user_logged_in() ) {
		return ( $content ) ? $content : wpmem_inc_memberlinks( 'login' );
	} else {
		return ( $wpmem->regchk == 'loginfailed' ) ? wpmem_inc_loginfailed() : wpmem_inc_login( 'login', $redirect_to, $texturize );
	}
} */


if ( ! function_exists( 'wpmem_shortcode' ) ):
/**
 * Executes various shortcodes.
 *
 * This function executes shortcodes for pages (settings, register, login, user-list,
 * and tos pages), as well as login status and field attributes when the wp-members tag
 * is used.  Also executes shortcodes for login status with the wpmem_logged_in tags
 * and fields when the wpmem_field tags are used.
 *
 * @since 2.4.0
 *
 * @global object $wpmem The WP_Members object.
 *
 * @param  array  $attr page|url|status|msg|field|id
 * @param  string $content
 * @param  string $tag
 * @return string Returns the result of wpmem_do_sc_pages|wpmem_list_users|wpmem_sc_expmessage|$content.
 */
function wpmem_shortcode( $attr, $content = null, $tag = 'wp-members' ) {

	global $wpmem;

	// Set all default attributes to false.
	$defaults = array(
		'page'        => false,
		'redirect_to' => null,
		'url'         => false,
		'status'      => false,
		'msg'         => false,
		'field'       => false,
		'id'          => false,
		'underscores' => 'off',
	);

	// Merge defaults with $attr.
	$atts = shortcode_atts( $defaults, $attr, $tag );

	// Handles the 'page' attribute.
	if ( $atts['page'] ) {
		if ( $atts['page'] == 'user-list' ) {
			if ( function_exists( 'wpmem_list_users' ) ) {
				$content = do_shortcode( wpmem_list_users( $attr, $content ) );
			}
		} elseif ( $atts['page'] == 'tos' ) {
			return $atts['url'];
		} else {
			$content = do_shortcode( wpmem_do_sc_pages( $atts['page'], $atts['redirect_to'] ) );
		}

		// Resolve any texturize issues.
		if ( strstr( $content, '[wpmem_txt]' ) ) {
			// Fixes the wptexturize.
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			add_filter( 'the_content', 'wpmem_texturize', 999 );
		}
		return $content;
	}

	// Handles the 'status' attribute.
	if ( ( $atts['status'] ) || $tag == 'wpmem_logged_in' ) {
		return do_shortcode( wpmem_sc_logged_in( $atts, $content, $tag ) );
	}

	// @deprecated 3.0.0
	// Handles the wpmem_logged_out tag with no attributes & the user is not logged in.
	/*
	if ( $tag == 'wpmem_logged_out' && ( ! $attr ) && ! is_user_logged_in() ) {
		return do_shortcode( $content );
	}
	*/

	// Handles the 'field' attribute.
	if ( $atts['field'] || $tag == 'wpmem_field' ) {
		if ( $atts['id'] ) {
			// We are getting some other user.
			if ( $atts['id'] == 'get' ) {
				$the_user_ID = ( isset( $_GET['uid'] ) ) ? $_GET['uid'] : '';
			} else {
				$the_user_ID = $atts['id'];
			}
		} else {
			// Get the current user.
			$the_user_ID = get_current_user_id();
		}
		$user_info = get_userdata( $the_user_ID );

		if ( $atts['underscores'] == 'off' && $user_info ) {
			$user_info->$atts['field'] = str_replace( '_', ' ', $user_info->$atts['field'] );
		}

		return ( $user_info ) ? htmlspecialchars( $user_info->$atts['field'] ) . do_shortcode( $content ) : do_shortcode( $content );
	}

	// Logout link shortcode.
	if ( is_user_logged_in() && $tag == 'wpmem_logout' ) {
		$link = ( $atts['url'] ) ? wpmem_chk_qstr( $atts['url'] ) . 'a=logout' : wpmem_chk_qstr( get_permalink() ) . 'a=logout';
		$text = ( $content ) ? $content : __( 'Click here to log out.', 'wp-members' );
		return do_shortcode( "<a href=\"$link\">$text</a>" );
	}

}
endif;


if ( ! function_exists( 'wpmem_do_sc_pages' ) ):
/**
 * Builds the shortcode pages (login, register, user-profile, user-edit, password).
 *
 * Some of the logic here is similar to the wpmem_securify() function. 
 * But where that function handles general content, this function 
 * handles building specific pages generated by shortcodes.
 *
 * @since 2.6.0
 *
 * @global object $wpmem        The WP_Members object.
 * @global string $wpmem_themsg The WP-Members message container.
 * @global object $post         The WordPress post object.
 *
 * @param  string $page
 * @param  string $redirect_to
 * @return string $content
 */
function wpmem_do_sc_pages( $page, $redirect_to = null ) {

	global $wpmem, $wpmem_themsg, $post;
	include_once( WPMEM_PATH . 'inc/dialogs.php' );

	$content = '';

	// Deprecating members-area parameter to be replaced by user-profile.
	$page = ( $page == 'user-profile' ) ? 'members-area' : $page;

	if ( $page == 'members-area' || $page == 'register' ) {

		if ( $wpmem->regchk == "captcha" ) {
			global $wpmem_captcha_err;
			$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
		}

		if ( $wpmem->regchk == "loginfailed" ) {
			return wpmem_inc_loginfailed();
		}

		if ( ! is_user_logged_in() ) {
			if ( $wpmem->action == 'register' ) {

				switch( $wpmem->regchk ) {

				case "success":
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_login();
					break;

				default:
					$content = wpmem_inc_regmessage( $wpmem->regchk,$wpmem_themsg );
					$content = $content . wpmem_inc_registration();
					break;
				}

			} elseif ( $wpmem->action == 'pwdreset' ) {

				$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );

			} elseif( $wpmem->action == 'getusername' ) {
				
				$content = wpmem_page_forgot_username( $wpmem->regchk, $content );
				
			} else {

				$content = ( $page == 'members-area' ) ? $content . wpmem_inc_login( 'members' ) : $content;
				$content = ( $page == 'register' || $wpmem->show_reg[ $post->post_type ] != 0 ) ? $content . wpmem_inc_registration() : $content;
			}

		} elseif ( is_user_logged_in() && $page == 'members-area' ) {

			/**
			 * Filter the default heading in User Profile edit mode.
			 *
			 * @since 2.7.5
			 *
			 * @param string The default edit mode heading.
			 */
			$heading = apply_filters( 'wpmem_user_edit_heading', __( 'Edit Your Information', 'wp-members' ) );

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

		} elseif ( is_user_logged_in() && $page == 'register' ) {

			$content = $content . wpmem_inc_memberlinks( 'register' );

		}

	}

	if ( $page == 'login' ) {
		$content = ( $wpmem->regchk == "loginfailed" ) ? wpmem_inc_loginfailed() : $content;
		$content = ( ! is_user_logged_in() ) ? $content . wpmem_inc_login( 'login', $redirect_to ) : wpmem_inc_memberlinks( 'login' );
	}

	if ( $page == 'password' ) {
		$content = wpmem_page_pwd_reset( $wpmem->regchk, $content );
	}

	if ( $page == 'user-edit' ) {
		$content = wpmem_page_user_edit( $wpmem->regchk, $content );
	}

	return $content;
} // End wpmem_do_sc_pages.
endif;


/**
 * User count shortcode.
 *
 * @since 3.0.0
 *
 * @global object $wpdb The WordPress database object.
 *
 * @param  array  $atts Shortcode attributes.
 * @param  string $content The shortcode content.
 * @return string $content
 */
function wpmem_sc_user_count( $atts, $content = null ) {
	global $wpdb;
	$do_query = ( $atts['key'] && $atts['value'] ) ? true : false;
	if ( $do_query ) {
		$user_meta_query = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
			 FROM $wpdb->usermeta
			 WHERE meta_key = %s
			 AND meta_value = %s",
			$atts['key'],
			$atts['value']
		) );
	}
	return ( $do_query ) ? $atts['label'] . $user_meta_query : '';
}

// End of file.