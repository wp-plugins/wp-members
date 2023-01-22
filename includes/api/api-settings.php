<?php

/**
 * Checks if requested setting is enabled.
 * 
 * @since 3.4.1
 * @since 3.4.6 Moved from api.php.
 * 
 * @param  string  $option
 * @return boolean
 */
function wpmem_is_enabled( $option ) {
	global $wpmem;
    if ( strpos( $option, "/" ) ) {
        $parts = explode( "/", $option );
        if ( is_array( $wpmem->{$parts[0]} ) ) {
            return ( isset( $wpmem->{$parts[0]}[ $parts[1] ] ) && 1 == $wpmem->{$parts[0]}[ $parts[1] ] ) ? true : false;
        } elseif ( is_object( $wpmem->{$parts[0]} ) ) {
            return ( isset( $wpmem->{$parts[0]}->{$parts[1]} ) && 1 == $wpmem->{$parts[0]}->{$parts[1]} ) ? true : false;
        }
    } else {
	    return ( isset( $wpmem->{$option} ) && 1 == $wpmem->{$option} ) ? true : false;
    }
}

/**
 * Checks if WooCommerce is active.
 *
 * @since 3.3.7
 * @since 3.4.0 Now an alias for rktgk_is_woo_active().
 * @since 3.4.6 Moved from api-utilities.php.
 *
 * @return boolean
 */
function wpmem_is_woo_active() {
	return rktgk_is_woo_active();
}

/**
 * Conditional test if moderated registration is enabled.
 * 
 * @since 3.4.6
 * 
 * @return boolean
 */
function wpmem_is_mod_reg() {
    return wpmem_is_enabled( 'mod_reg' );
}

/**
 * Verbose alias of wpmem_is_mod_reg()
 * 
 * @since 3.4.6
 * 
 * @return boolean
 */
function wpmem_is_registration_moderated() {
    return wpmem_is_mod_reg();
}

function wpmem_is_act_link() {
    return wpmem_is_enabled( 'act_link' );
}

function wpmem_is_confirmation_link_enabled() {
    return wpmem_is_act_link();
}