<?php
/**
 * The WP_Members Export Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Export Object Class
 * @since 3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_User_Export {

	/**
	 * Used instead of getting global.
	 * 
	 * @since 3.4.1
	 * @todo May change how this is used. (Currently just replaces minor use of $wpmem global object for this one thing.)
	 */
	public static $membership_product_stem = "_wpmem_products_";

	/**
	 * New export function to export all or selected users
	 *
	 * @since 2.9.7
	 * @since 3.2.0 Updated to use fputcsv.
	 * @since 3.2.1 Added user data filters.
	 * @since 3.3.0 Moved to new object class as static method.
	 * @since 3.4.0 Added $tag to identify what export process is being run.
	 *
	 * @param array $args array {
	 *     Array of defaults for export.
	 *
	 *     @type  string  $export          The type of export (all|selected)
	 *     @type  string  $filename
	 *     @type  array   $fields {
	 *         The array of export fields is keyed as 'meta_key' => 'heading value'.
	 *         The array can include fields in the Fields tab, plus the following:
	 *
	 *         @type int    $ID               ID from wp_users
	 *         @type string $username         user_login from wp_users
	 *         @type string $user_nicename    user_nicename
	 *         @type string $user_url         user_url
	 *         @type string $display_name     display_name
	 *         @type int    $active           Whether the user is active/deactivated.
	 *         @type string $exp_type         If the PayPal extension is installed pending|subscrption (optional)
	 *         @type string $expires          If the PayPal extension is installed MM/DD/YYYY (optional)
	 *         @type string $user_registered  user_registered
	 *         @type string $user_ip          The IP of the user when they registered.
	 *         @type string $role             The user's role (or roles, if multiple).
	 *     }
	 *     @type  array   $exclude_fields  @deprecated 3.4.0
	 *     @type  boolean $entity_decode   Whether HTML entities should be decoded (default: false)
	 *     @type  string  $date_format     A PHP readable date format (default: Y-m-d which results in YYYY-MM-DD)
	 *     @type  string  $required_caps   The user capability required to export.
	 * }
	 * @param array  $users Array of user IDs to export.
	 * @param string $tag
	 */
	public static function export_users( $args = array(), $users = array(), $tag = 'default' ) {
		
		$export_fields = ( ! isset( $args['fields'] ) ) ? self::get_export_fields() : $args['fields'];

		/**
		 * Filter the export fields.
		 *
		 * @since 3.2.5
		 * @since 3.4.0 Added $tag.
		 *
		 * @param array $export_fields {
		 *     The array of export fields is keyed as 'meta_key' => 'heading value'.
		 *     The array will include all fields in the Fields tab, plus the following:
		 *
		 *     @type int    $ID               ID from wp_users
		 *     @type string $username         user_login from wp_users
		 *     @type string $user_nicename    user_nicename
		 *     @type string $user_url         user_url
		 *     @type string $display_name     display_name
		 *     @type int    $active           Whether the user is active/deactivated.
		 *     @type string $exp_type         If the PayPal extension is installed pending|subscrption (optional)
		 *     @type string $expires          If the PayPal extension is installed MM/DD/YYYY (optional)
		 *     @type string $user_registered  user_registered
		 *     @type string $user_ip          The IP of the user when they registered.
		 *     @type string $role             The user's role (or roles, if multiple).
		 * }
		 * @param string $tag
		 */
		$export_fields = apply_filters( 'wpmem_export_fields', $export_fields, $tag );

		$today = date( "Y-m-d" );

		// Setup defaults.
		$defaults = array(
			'export'         => 'all',
			'filename'       => 'wp-members-user-export-' . $today . '.csv',
			'fields'         => $export_fields,
			'entity_decode'  => false,
			'date_format'    => 'Y-m-d',
			'required_caps'  => 'list_users',
		);
		// Merge args with default (in case any were missing).
		$args = wp_parse_args( $args, $defaults );

		/**
		 * Filter the default export arguments.
		 *
		 * @since 2.9.7
		 * @since 3.4.0 Filter all defaults (like other _args changes), then wp_parse_args() in case any are missing.
		 * @since 3.4.0 Added $tag.
		 * @since 3.4.0 Deprecated 'exclude_fields' (unset using wpmem_export_fields instead).
		 * @since 3.4.0 Deprecated 'export_fields'  use "fields" instead.
		 *
		 * @param array $args {
		 *     Array of defaults for export.
		 *
		 *     @type  string  $export
		 *     @type  string  $filename
		 *     @type  array   $fields
		 *     @type  array   $exclude_fields  @deprecated 3.4.0
		 *     @type  boolean $entity_decode
		 *     @type  string  $date_format
		 *     @type  string  $required_caps
		 * }
		 * @param string $tag
		 */
		$args = apply_filters( 'wpmem_export_args', $args, $tag );
		
		// Merge args with default (in case any were missing).
		$args = wp_parse_args( $args, $defaults );

		if ( current_user_can( $args['required_caps'] ) ) {
			// Output needs to be buffered, start the buffer.
			ob_start();

			// If exporting all, get all of the users.
			$export_users = ( 'all' == $args['export'] ) ? get_users( array( 'fields' => 'ID' ) ) : $users;

			// Generate headers and a filename based on date of export.
			header( "Content-Description: File Transfer" );
			header( "Content-type: application/octet-stream" );
			header( "Content-Disposition: attachment; filename=" . $args['filename'] );
			header( "Content-Type: text/csv; charset=" . get_option( 'blog_charset' ), true );

			$handle = fopen( 'php://output', 'w' );
			fputs( $handle, "\xEF\xBB\xBF" ); // UTF-8 BOM

			// Remove excluded fields from $export_fields while setting up $header array.
			$header = array();
			foreach ( $args['fields'] as $meta => $field ) {
				$header[ $meta ] = $field;
			}

			/**
			 * Filters user export header row before assembly.
			 *
			 * As of 3.4.0, this really isn't a necessary filter. You can specify the header
			 * value in wpmem_export_fields instead and just use one filter.
			 *
			 * @since 3.2.1
			 * @since 3.4.0 Added $tag.
			 *
			 * @param array  $header The header column values
			 * @param string $tag
			 */
			$header = apply_filters( 'wpmem_user_export_header', $header, $tag );

			fputcsv( $handle, $header );

			// Loop through the array of users, assemble csv.
			// $fields only includes fields to be exported at this point.
			foreach ( $export_users as $user ) {

				$user_info = get_userdata( $user );

				$wp_user_fields = array( 'ID', 'user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name' );
				foreach ( $args['fields'] as $meta => $field ) {

					switch ( $meta ) {
						case 'ID':
						case 'user_login':
						case 'user_pass':
						case 'user_nicename':
						case 'user_email':
						case 'user_url':
						case 'user_registered':
						case 'user_activation_key':
						case 'user_status':
						case 'display_name':
							$row[ $meta ] = $user_info->{$meta};
							break;
						case 'username':
							$row['username'] = $user_info->user_login;
							break;
						case 'password':
							$row['password'] = $user_info->user_pass;
							break;
						case 'active':
							$row['active'] = wpmem_get_user_meta( $user, 'active' ) ? __( 'Yes' ) : __( 'No' );
							break;
						case 'exp_type':
							$exp_type = wpmem_get_user_meta( $user, 'exp_type' );
							$row['exp_type'] = ( false !== $exp_type ) ? $exp_type : '';
							break;
						case 'expires':
							$expires = wpmem_get_user_meta( $user, 'expires' );
							$row['expires'] = ( false !== $expires ) ? $expires : '';
							break;
						case 'wpmem_reg_ip':
							$reg_ip = wpmem_get_user_meta( $user, 'wpmem_reg_ip' );
							$row['wpmem_reg_ip'] = ( false !== $reg_ip ) ? $reg_ip : '';
							break;
						case 'role':
							$role = wpmem_get_user_role( $user, true ); // As of 3.4, wpmem_get_user_role() can get all roles.
							$row['role'] = ( is_array( $role ) ) ? implode( ",", $role ) : $role;
							break;
						case ( self::$membership_product_stem === substr( $meta, 0, strlen( self::$membership_product_stem ) ) ):
							$product = str_replace( self::$membership_product_stem, '', $meta );
							$row[ $meta ] = wpmem_get_user_meta( $user, $meta );
							// If value is a date and false is not the format_date option...
							if ( false !== $args['date_format'] && '' != $row[ $meta ] && $row[ $meta ] > 2 ) {
								$date_format = ( 'wp' == $args['date_format'] ) ? get_option('date_format') : $args['date_format'];
								$row[ $meta ] = date( $date_format, $row[ $meta ] );
							}
							break;
						default:
							if ( in_array( $meta, $wp_user_fields ) ) {
								$row[ $meta ] = ( 'username' == $meta ) ? $user_info->user_login : $user_info->{$meta};
							} else {
								$raw_data = wpmem_get_user_meta( $user, $meta );
								$raw_data = ( $raw_data ) ? $raw_data : '';
								$row[ $meta ] = ( $args['entity_decode'] ) ? html_entity_decode( $raw_data ) : $raw_data;
							}
							break;
					}
				}

				/**
				 * Filter the user data before assembly.
				 *
				 * @since 3.2.1
				 * @since 3.4.0 Added user ID (it may not be included in the $row array if the field were filtered out).
				 * @since 3.4.0 Added $tag.
				 *
				 * @param array  $row     The user data row.
				 * @param int    $user_id The user ID.
				 * @param string $tag
				 */
				$row = apply_filters( 'wpmem_user_export_row', $row, $user_info->ID, $tag );

				fputcsv( $handle, $row );

				// Update the user record as being exported.
				if ( 'all' != $args['export'] ) {
					update_user_meta( $user, 'exported', 1 );
				}
			}

			fclose( $handle );
			print( ob_get_clean() );

			exit();
		} else {
			wp_die( __( 'You do not have the required user capabilities to export users.', 'wp-members' ) );
		}
	}

	private static function get_export_fields() {
		
		$wpmem_fields = wpmem_fields();
		
		// Fields to exclude.
		$exclude_fields = array( 'user_pass', 'password', 'confirm_password', 'confirm_email' );
		
		// Prepare fields, add additional "special" fields.
		$export_fields = array(
			'ID' => __( 'User ID', 'wp-members' ),
		);
		foreach( $wpmem_fields as $meta_key => $value ) {
			if ( ! in_array( $meta_key, $exclude_fields ) ) {
				$export_fields[ $meta_key ] = $value['label'];
			}
		}
		$export_fields['username'] = __( 'Username', 'wp-members' );
		if ( wpmem_is_enabled( 'mod_reg' ) ) {
			$export_fields['active'] = __( 'Activated?', 'wp-members' );
		}
		if ( wpmem_is_enabled( 'act_link' ) ) {
			$export_fields['_wpmem_user_confirmed'] = __( 'Confirmed?', 'wp-members' );
		}
		if ( defined( 'WPMEM_EXP_MODULE' ) && wpmem_is_enabled( 'use_exp' ) ) {
			$export_fields['exp_type'] = __( 'Subscription', 'wp-members' );
			$export_fields['expires']  = __( 'Expires', 'wp-members' );
		}
		$export_fields['user_registered'] = __( 'Registered', 'wp-members' );
		$export_fields['wpmem_reg_ip']    = __( 'IP', 'wp-members' );
		$export_fields['role']            = __( 'Role', 'wp-members' );
		if ( wpmem_is_enabled( 'enable_products' ) ) {
			$membership_products = wpmem_get_memberships();
			foreach( $membership_products as $product_key => $product ) {
				$export_fields[ self::$membership_product_stem . $product_key ] = $membership_products[ $product_key ]['title'];
			}
		}
	
		return $export_fields;
	}
}