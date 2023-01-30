<?php
class WP_Members_WooCommerce_Integration {

    public function __construct( $wpmem ) {

        foreach ( $wpmem->woo as $key => $value ) {
            $this->$key = $value;
        }

        // Handle "My Account" page registration.
        if ( wpmem_is_enabled( 'woo/add_my_account_fields' ) ) {
            add_action( 'woocommerce_register_form', 'wpmem_woo_register_form' );
            add_action( 'woocommerce_register_post', 'wpmem_woo_reg_validate', 10, 3 );
        }
        // Handle Registration checkout
        if ( wpmem_is_enabled( 'woo/add_checkout_fields' ) ) {
            add_filter( 'woocommerce_checkout_fields', 'wpmem_woo_checkout_form' );
            add_action( 'woocommerce_checkout_update_order_meta', 'wpmem_woo_checkout_update_meta' );
            //add_action( 'woocommerce_save_account_details_errors', 'wpmem_woo_reg_validate' );
            add_action( 'woocommerce_form_field_multicheckbox', 'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_action( 'woocommerce_form_field_multiselect',   'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_action( 'woocommerce_form_field_radio',         'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_action( 'woocommerce_form_field_select',        'wpmem_form_field_wc_custom_field_types', 10, 4 );
            add_action( 'woocommerce_form_field_checkbox',      'wpmem_form_field_wc_custom_field_types', 10, 4 );
        }

        if (  wpmem_is_enabled( 'woo/add_update_fields' ) ) {
            add_action( 'woocommerce_edit_account_form', 'wpmem_woo_edit_account_form' );
        }

        if ( wpmem_is_enabled( 'woo/product_restrict' ) ) {
            add_filter( 'woocommerce_is_purchasable', 'wpmem_woo_is_purchasable', PHP_INT_MAX, 2 );
        }
    }
}