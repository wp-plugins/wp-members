<?php
/**
 * The WP_Members Utilities Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Utilities Object Class
 * @since 3.1.0
 */

class WP_Members_Utilities {

	/**
	 * Plugin initialization function.
	 *
	 * @since 3.1.0
	 */
	function __construct() {

	}
	
	/**
	 * Get field keys by meta.
	 *
	 * @since 3.1.0
	 */
	function get_field_keys_by_meta() {
		global $wpmem;
		$field_keys = array();
		foreach ( $wpmem->fields as $key => $field ) {
			$field_keys[ $field[2] ] = $key;
		}
		return $field_keys;
	}
	
	/**
	 * Get select field display values.
	 *
	 * @since 3.1.0
	 *
	 * @param  string $meta_key       The field's meta key.
	 * @return array  $display_values {
	 *     The field's display values in an array.
	 *     Elements are stored_value => display value
	 *
	 *     @type string The display value.
	 * }
	 */
	function get_select_display_values( $meta_key ) {
		global $wpmem;
		$keys = $this->get_field_keys_by_meta();
		$raw  = $wpmem->fields[ $keys[ $meta_key ] ][7];
		$display_values = array();
		foreach ( $raw as $value ) {
			$pieces = explode( '|', trim( $value ) );
			if ( $pieces[1] != '' ) {
				$display_values[ $pieces[1] ] = $pieces[0];
			}
		}
		return $display_values;
	}
	
} // End of WP_Members_Utilties class.