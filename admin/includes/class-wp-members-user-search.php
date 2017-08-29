<?php
/**
 * The WP_Members Admin User Search Class.
 *
 * An object class to improve the backend user search. Allows
 * searching by selected meta keys as defined in the plugin
 * settings.  Hooks into pre_user_query.
 *
 * Modified from Better User Search:
 * https://wordpress.org/plugins/better-user-search/
 *
 * @package WP-Members
 * @subpackage WP_Members User Search Object Class
 * @since 3.1.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Admin_User_Search {
	
	/**
	 * Container for tabs.
	 *
	 * @since 3.1.9
	 * @access public
	 * @var array
	 */
	public $tabs = array();
	
	/**
	 * Container for user search meta keys.
	 *
	 * @since 3.1.9
	 * @access public
	 * @var array
	 */
	public $keys = array();

	/**
	 * Constructor function.
	 *
	 * @since 3.1.9
	 */
	public function __construct() {
		// This plugin is for the backend only
		if ( ! is_admin() ) {
		  return;
		}
		
		$saved_keys = get_option( 'wpmembers_usfields' );
		if ( $saved_keys ) {
			$this->keys = $saved_keys;
			// Add the overwrite actions for the search
			add_action( 'pre_user_query', array( $this, 'pre_user_query' ), 100 );
		}
	}
  
	/**
	 * pre_user_query function.
	 *
	 * @since 3.1.9
	 *
	 * @param string $user_query
	 */
	public function pre_user_query( $user_query ) {

		// Exit if no search is being done.
		$terms = wpmem_get( 's', false, 'get' );
		if ( ! $terms ) {
			return;
		}

		global $wpdb;

		// Get the data we need from helper methods.
		$terms     = $this->get_search_terms();
		$meta_keys = $this->get_meta_keys();
		
		// If there are meta keys to search.
		if ( ! empty( $meta_keys ) ) {

			// Are we performing an AND (default) or an OR?
			$search_with_or = in_array( 'or', $terms );

			if ( $search_with_or ) {
				// Remove the OR keyword(s) from the terms.
				$terms = array_diff( $terms, array( 'or', 'and' ) );

				// Reset the array keys.
				$terms = array_values( $terms );
			}

			// Use a permanent table because you cannot reference MySQL temporary tables more than once per query.
			$mktable = "{$wpdb->prefix}wpmembers_user_search_keys";

			// Create the table to store the meta keys.
			$wpdb->query( $sql = "CREATE TABLE IF NOT EXISTS {$mktable} (meta_key VARCHAR(255) NOT NULL);" );

			// Empty the table to ensure that we have an accurate set of meta keys.
			$wpdb->query( $sql = "TRUNCATE TABLE {$mktable};" );

			// Insert the meta keys into the table.
			$prepare_values_array = array_fill( 0, count( $meta_keys ), '(%s)' );
			$prepare_values = implode( ", ", $prepare_values_array );

			$insert_sql = $wpdb->prepare( "
				INSERT INTO {$mktable}
				(meta_key)
				VALUES
				{$prepare_values};", $meta_keys );

			$wpdb->query( $insert_sql );

			// Build data for $wpdb->prepare.
			$values = array();

			// Make sure we replicate each term XX number of times (refer to query below for correct number).
			foreach ( $terms as $term ) {
				for ( $i = 0; $i < 6; $i++ ) {
					$values[] = "%{$term}%";
				}
			}

			// Last value is for HAVING COUNT(*), so add that.
			// Note the min count is 1 if we found OR in the terms.
			$values[] = ( $search_with_or !== false ? 1 : count( $terms ) );

			// Query for matching users.
			$user_ids = $wpdb->get_col( $sql = $wpdb->prepare( "
				SELECT user_id
				FROM (" . implode( 'UNION ALL', array_fill( 0, count( $terms ), "
					SELECT DISTINCT u.ID AS user_id
					FROM {$wpdb->users} u
					INNER JOIN {$wpdb->usermeta} um
					ON um.user_id = u.ID
					INNER JOIN {$mktable} mk
					ON mk.meta_key = um.meta_key
					WHERE LOWER(um.meta_value) LIKE %s
					OR LOWER(u.user_login) LIKE %s
					OR LOWER(u.user_nicename) LIKE %s
					OR LOWER(u.user_email) LIKE %s
					OR LOWER(u.user_url) LIKE %s
					OR LOWER(u.display_name) LIKE %s
				  " ) ) . ") AS user_search_union
				GROUP BY user_id
				HAVING COUNT(*) >= %d;", $values ) );

			// Change query to include the new user IDs.
			if ( is_array( $user_ids ) && count( $user_ids ) ) {
				// Combine the IDs into a comma separated list.
				$id_string = implode( ',', $user_ids );

				// Build the SQL we are adding to the query.
				$extra_sql = " OR ID IN ({$id_string})";
				$add_after    = 'WHERE ';
				$add_position = strpos( $user_query->query_where, $add_after ) + strlen( $add_after );

				// Add the query to the end.
				$user_query->query_where = substr( $user_query->query_where, 0, $add_position ) . '(' . substr( $user_query->query_where, $add_position ) . ')' . $extra_sql;
			}
		}
	}

	/**
	* Get array of user search terms.
	*
	* @since 3.1.9
	*
	* @return array $terms
	*/
	public function get_search_terms() {
		// Get the WordPress search term(s).
		$terms = ( wpmem_get( 's', false, 'get' ) ) ? trim( strtolower( stripslashes( $_GET['s'] ) ) ) : false;

		// Quite if no search term(s) exist.
		if ( empty( $terms ) ) {
			return array();
		}

		// Split terms by space into an array.
		$terms = explode( ' ', $terms );

		// Remove empty terms.
		foreach ( $terms as $key => $term ) {
			if ( empty( $term ) ) {
				unset( $terms[ $key ] );
			}
		}

		// Reset the array keys.
		$terms = array_values( $terms );

		return $terms;
	}
  
	/**
	* Get meta keys for query.
	*
	* @since 3.1.9
	*
	* @return array $meta_keys
	*/
	public function get_meta_keys() {
		// Setting is an array of meta_key => meta_value, need the key.
		$meta_keys = array();
		if ( $this->keys ) {
			foreach ( $this->keys as $key => $val ) {
				$meta_keys[] = $key;
			}
		}
		// Return the meta keys.
		return $meta_keys;
	}
}