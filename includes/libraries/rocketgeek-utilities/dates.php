<?php

if ( ! function_exists( 'rktgk_format_date' ) ):
/**
 * Display a localized date based on the WP date format setting.
 *
 * @since 1.0.0
 *
 * @param mixed $args
 * @return date $date
 */
function rktgk_format_date( $args ) {
	if ( ! is_array( $args ) ) {
		$args = array( 'date' => $args );
	}
	
	$defaults = array( 
		'date_format' => get_option( 'date_format' ),
		'time_format' => get_option( 'time_format' ),
		'localize'    => true,
		'timestamp'   => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * Filter the date display and format settings.
	 *
	 * @since 1.0.0
	 *
	 * @param arrag $args
	 */
	$args = apply_filters( 'rktgk_format_date_args', $args );
	$date = ( true === $args['timestamp'] ) ? $args['date'] : strtotime( $args['date'] );
	$date = ( true === $args['localize']  ) ? date_i18n( $args['date_format'], $date ) : date( $args['date_format'], $date );
	return $date;
}
endif;