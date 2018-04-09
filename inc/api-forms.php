<?php

/**
 * Invokes a login form.
 *
 * @since 3.2.0
 *
 * @global object $wpmem
 * @param  array  $args {
 *     Possible arguments for creating the form.
 *
 *     @type string id
 *     @type string tag
 *     @type string form
 *     @type string redirect_to
 * }
 * @return string $html
 */
/*function wpmem_login_form( $args ) {
  global $wpmem;
  return $wpmem->forms->login_form( $args );
}*/

/**
 * Invokes a registration or user profile update form.
 *
 * @since 3.2.0
 *
 * @global object $wpmem
 * @param  array  $args {
 *     Possible arguments for creating the form.
 *
 *     @type string id
 *     @type string tag
 *     @type string form
 *     @type string product
 *     @type string include_fields
 *     @type string exclude_fields
 *     @type string redirect_to
 *     @type string heading
 * }
 * @return string $html
 */
function wpmem_register_form( $args ) {
  global $wpmem;
  return $wpmem->forms->register_form( $args );
}
