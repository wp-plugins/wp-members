<?php

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