<?php
/**
 * The WP-Members Class.
 *
 * @since 3.0
 */
class WP_Members {

	function __construct() {

		/**
		 * Filter the options before they are loaded into constants.
		 *
		 * @since 2.9.0
		 *
		 * @param array $this->settings An array of the WP-Members settings.
		 */
		$settings = apply_filters( 'wpmem_settings', get_option( 'wpmembers_settings' ) );

		// Assemble settings.
		foreach ( $settings as $key => $val ) {
			$this->$key = $val;
		}

		// Set the stylesheet.
		$this->cssurl = ( $this->style == 'use_custom' ) ? $this->cssurl : $this->style;
	}
	
	/**
	 * Gets the requested action.
	 *
	 * @since 3.0.0
	 */
	function get_action() {

		// Get the action being done (if any).
		$this->action = ( isset( $_REQUEST['a'] ) ) ? trim( $_REQUEST['a'] ) : '';

		// Get the regchk value (if any).
		$this->regchk = $this->get_regchk( $this->action );

		/**
		 * Filter wpmem_regchk.
		 *
		 * The value of regchk is determined by functions that may be run in the get_regchk function.
		 * This value determines what happens in the wpmem_securify() function.
		 *
		 * @since 2.9.0
		 *
		 * @param  string $this->regchk The value of wpmem_regchk.
		 * @param  string $this->action The $wpmem_a action.
		 */
		$this->regchk = apply_filters( 'wpmem_regchk', $this->regchk, $this->action );
	}
	
	/**
	 * Gets the regchk value.
	 *
	 * @since 3.0.0
	 *
	 * @param  string $action The action being done.
	 * @return string         The regchk value.
	 *
	 * @todo Describe regchk.
	 */
	function get_regchk( $action ) {

		switch ( $action ) {

			case 'login':
				return wpmem_login();
				break;

			case 'logout':
				wpmem_logout();
				break;
			
			case 'pwdchange':
				return wpmem_change_password();
				break;
			
			case 'pwdreset':
				return wpmem_reset_password();
				break;
			
			case 'register':
			case 'update':
				require_once( WPMEM_PATH . 'inc/register.php' );
				return wpmem_registration( $action  );
				break;
		}
		return;
	}
	
	/**
	 * Determines if content should be blocked.
	 *
	 * This function was originally stand alone in the core file and
	 * was moved to the WP_Members class in 3.0.
	 *
	 * @since 3.0
	 *
	 * @return bool $block true|false
	 */
	function is_blocked() {
	
		global $post;

		// Backward compatibility for old block/unblock meta.
		$meta = get_post_meta( $post->ID, '_wpmem_block', true );
		if ( ! $meta ) {
			// Check for old meta.
			$old_block   = get_post_meta( $post->ID, 'block',   true );
			$old_unblock = get_post_meta( $post->ID, 'unblock', true );
			$meta = ( $old_block ) ? 1 : ( ( $old_unblock ) ? 0 : $meta );
		}

		// Setup defaults.
		$defaults = array(
			'post_id'    => $post->ID,
			'post_type'  => $post->post_type,
			'block'      => ( $this->block[ $post->post_type ] == 1 ) ? true : false,
			'block_meta' => $meta, // @todo get_post_meta( $post->ID, '_wpmem_block', true ),
			'block_type' => ( $post->post_type == 'post' ) ? $this->block['post'] : ( ( $post->post_type == 'page' ) ? $this->block['page'] : 0 ),
		);

		/**
		 * Filter the block arguments.
		 *
		 * @since 2.9.8
		 *
		 * @param array $args     Null.
		 * @param array $defaults Although you are not filtering the defaults, knowing what they are can assist developing more powerful functions.
		 */
		$args = apply_filters( 'wpmem_block_args', '', $defaults );

		// Merge $args with defaults.
		$args = ( wp_parse_args( $args, $defaults ) );

		if ( is_single() || is_page() ) {
			switch( $args['block_type'] ) {
				case 1: // If content is blocked by default.
					$args['block'] = ( $args['block_meta'] == '0' ) ? false : $args['block'];
					break;
				case 0 : // If content is unblocked by default.
					$args['block'] = ( $args['block_meta'] == '1' ) ? true : $args['block'];
					break;
			}
		} else {

			$args['block'] = false;

		}

		/**
		 * Filter the block boolean.
		 *
		 * @since 2.7.5
		 *
		 * @param bool  $args['block']
		 * @param array $args
		 */
		return apply_filters( 'wpmem_block', $args['block'], $args );
	}
	
	/**
	 * The Securify Content Filter.
	 *
	 * This is the primary function that picks up where get_action() leaves off.
	 * Determines whether content is shown or hidden for both post and pages.
	 *
	 * @since 3.0
	 *
	 * @global string $wpmem_themsg      Contains messages to be output.
	 * @global string $wpmem_captcha_err Contains error message for reCAPTCHA.
	 * @global object $post              The post object.
	 * @param  string $content
	 * @return string $content
	 */
	function do_securify( $content = null ) {

		global $wpmem_themsg, $post;

		$content = ( is_single() || is_page() ) ? $content : wpmem_do_excerpt( $content );

		if ( ( ! wpmem_test_shortcode( $content, 'wp-members' ) ) ) {

			if ( $this->regchk == "captcha" ) {
				global $wpmem_captcha_err;
				$wpmem_themsg = __( 'There was an error with the CAPTCHA form.' ) . '<br /><br />' . $wpmem_captcha_err;
			}

			// Block/unblock Posts.
			if ( ! is_user_logged_in() && $this->is_blocked() == true ) {

				include_once( WPMEM_PATH . 'inc/dialogs.php' );
				
				//Show the login and registration forms.
				if ( $this->regchk ) {
					
					// Empty content in any of these scenarios.
					$content = '';

					switch ( $this->regchk ) {

					case "loginfailed":
						$content = wpmem_inc_loginfailed();
						break;

					case "success":
						$content = wpmem_inc_regmessage( $this->regchk, $wpmem_themsg );
						$content = $content . wpmem_inc_login();
						break;

					default:
						$content = wpmem_inc_regmessage( $this->regchk, $wpmem_themsg );
						$content = $content . wpmem_inc_registration();
						break;
					}

				} else {

					// Toggle shows excerpt above login/reg on posts/pages.
					global $wp_query;
					if ( $wp_query->query_vars['page'] > 1 ) {

							// Shuts down excerpts on multipage posts if not on first page.
							$content = '';

					} elseif ( $this->show_excerpt[ $post->post_type ] == 1 ) {

						if ( ! stristr( $content, '<span id="more' ) ) {
							$content = wpmem_do_excerpt( $content );
						} else {
							$len = strpos( $content, '<span id="more' );
							$content = substr( $content, 0, $len );
						}

					} else {

						// Empty all content.
						$content = '';

					}

					$content = $content . wpmem_inc_login();

					$content = ( $this->show_reg[ $post->post_type ] == 1 ) ? $content . wpmem_inc_registration() : $content;
				}

			// Protects comments if expiration module is used and user is expired.
			} elseif ( is_user_logged_in() && $this->is_blocked() == true ){

				$content = ( $this->use_exp == 1 && function_exists( 'wpmem_do_expmessage' ) ) ? wpmem_do_expmessage( $content ) : $content;

			}
		}

		/**
		 * Filter the value of $content after wpmem_securify has run.
		 *
		 * @since 2.7.7
		 *
		 * @param string $content The content after securify has run.
		 */
		$content = apply_filters( 'wpmem_securify', $content );

		if ( strstr( $content, '[wpmem_txt]' ) ) {
			// Fix the wptexturize.
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'wptexturize' );
			add_filter( 'the_content', 'wpmem_texturize', 99 );
		}

		return $content;
		
	}

}