<?php
if ( ! function_exists( 'rktgk_compare_array_values' ) ):
/**
 * Compares two arrays, disregarding order.
 *
 * @since 1.0.0
 *
 * @param   array   $a
 * @param   array   $b
 * @return  boolean
 */
function rktgk_compare_array_values( array $a, array $b ) {
    // check size of both arrays
    if ( count( $a ) !== count( $b ) ) {
        return false;
    }

    foreach ( $b as $key => $b_value ) {

        // check that expected value exists in the array
        if ( ! in_array( $b_value, $a, true ) ) {
            return false;
        }

        // check that expected value occurs the same amount of times in both arrays
        if ( count( array_keys( $a, $b_value, true ) ) !== count( array_keys( $b, $b_value, true ) ) ) {
            return false;
        }

    }

    return true;
}
endif;

if ( ! function_exists( 'rktgk_array_insert' ) ):
/**
 * Inserts array items at a specific point in an array.
 *
 * @since 1.0.0
 *
 * @param  array  $array Original array.
 * @param  array  $new   Array of new items to insert into $array.
 * @param  string $key   Array key to insert new items before or after.
 * @param  string $loc   Location to insert relative to $key (before|after) default:after.
 * @return array         Original array with new items inserted.
 */
function rktgk_array_insert( array $array, array $new, $key, $loc = 'after' ) {
	$keys = array_keys( $array );
	if ( 'before' == $loc ) {
		$pos = (int) array_search( $key, $keys );
	} else {
		$index = array_search( $key, $keys );
		$pos = ( false === $index ) ? count( $array ) : $index + 1;
	}
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}
endif;
