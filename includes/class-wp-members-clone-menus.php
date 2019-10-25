<?php
/**
 * The WP_Members Clone Menus Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Clone Menus Object Class
 * @since 3.2.0
 * @since 3.3.0 Renamed WP_Members_Clone_Menus
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Clone_Menus {

	function __construct() {
		add_action( 'init',                      array( $this, 'register_menus' ), 99 );
		add_filter( 'wp_nav_menu_args',          array( $this, 'serve_menu' ) );
		add_filter( 'jetpack_mobile_theme_menu', array( $this, 'jetpack_mobile_theme_menu' ) );
	}
	
	/**
	 * Function to clone existing menu locations and create _loggedin ones.
	 *
	 * @since 3.2.0
	 */
	function register_menus() {

		/**
		 * Filter cloned menus settings.
		 *
		 * @since 3.2.0
		 * @since 3.3.0 Changed to stem "wpmem_clone_"
		 *
		 * @param array $settings
		 */
		$settings = apply_filters( 'wpmem_clone_menu_settings', array( 'slug' => '_wpmem_loggedin', 'menu' => '(Logged In)' ) );

		$menus = get_registered_nav_menus();
		$wpmem_menus = array();
		foreach ( $menus as $slug => $name ) {
			$wpmem_menus[ $slug . $settings['slug'] ] = $name . ' ' . $settings['menu'];
		}

		/**
		 * Filter the cloned menus.
		 *
		 * @since 3.2.0
		 * @since 3.3.0 Changed to stem "wpmem_clone_"
		 *
		 * @param array $wpmem_menus
		 */
		$wpmem_menus = apply_filters( 'wpmem_clone_menus', $wpmem_menus );

		register_nav_menus( $wpmem_menus );
	}

	/**
	 * If there is a logged in theme location, show that if the user is logged in.
	 *
	 * @since 3.2.0
	 *
	 * @param  array $args
	 * @return array $args
	 */
	function serve_menu( $args ) {

		$theme_loc = $args['theme_location'] . '_wpmem_loggedin';
		$menu_locs = get_nav_menu_locations();

		/**
		 * Filter the served menu.
		 *
		 * @since 3.2.6
		 * @todo Determine what extra parameters to pass and how (then document them).
		 *
		 * @param boolean
		 * @param string  $theme_loc
		 * @param         $menu_locs
		 * @param array   $args
		 */
		$serve_menu = apply_filters( 'wpmem_serve_menu', true, $theme_loc, $menu_locs, $args );

		if ( is_user_logged_in( $menu_locs ) 
		  && ! empty( $args['theme_location'] )
		  && array_key_exists( $theme_loc, $menu_locs ) 
		  && $serve_menu ) {

			if ( $menu_locs[ $theme_loc ] != 0 ) {
				$args['theme_location'] = $args['theme_location'] . '_wpmem_loggedin';
			}
		}

		return $args;
	}

	/**
	 * Handles Jetpack mobile theme, if enabled.
	 *
	 * Jetpack mobile theme uses a custom mobile theme called Minileven. This theme uses
	 * the default primary menu for the main menu. If this is enabled, the Logged-in
	 * Menus extension uses the jetpack_mobile_theme_menu filter hook to apply the default
	 * primary menu if the user is not logged in or the logged in primary menu if they are.
	 *
	 * @since 3.2.0
	 */
	function jetpack_mobile_theme_menu() {
		// Get menus
		$menu_locs = get_nav_menu_locations();
		if ( is_user_logged_in() ) {
			return $menu_locs['primary_wpmem_loggedin'];
		} else {
			return $menu_locs['primary'];
		}
	}

}