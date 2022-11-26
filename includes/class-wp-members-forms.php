<?php
/**
 * The WP_Members Forms Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Forms Object Class
 * @since 3.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class WP_Members_Forms {

	/**
	 * Plugin initialization function.
	 *
	 * @since 3.1.0
	 */
	function __construct() {
		
	}

	/**
	 * Sets the registration fields.
	 *
	 * @since 3.0.0
	 * @since 3.1.5 Added $form argument.
	 * @since 3.3.0 Added $tag argument.
	 *
	 * @global stdClass $wpmem
	 * @param  string   $tag
	 * @param  string   $form The form being generated.
	 */
	function load_fields( $tag = 'new', $form = 'default' ) {
		
		global $wpmem;
	
		// Get stored fields settings.
		$fields = get_option( 'wpmembers_fields' );
		
		// Validate fields settings.
		if ( ! isset( $fields ) || empty( $fields ) ) {
			// Update settings.
			$fields = array( array( 10, 'Email', 'user_email', 'email', 'y', 'y', 'y', 'profile'=>true ) );
		}
		
		// Add new field array keys
		foreach ( $fields as $key => $val ) {
			
			// Key fields with meta key.
			$meta_key = $val[2];
			
			// Old format, new key.
			foreach ( $val as $subkey => $subval ) {
				$wpmem->fields[ $meta_key ][ $subkey ] = $subval;
			}
			
			// Setup field properties.
			$wpmem->fields[ $meta_key ]['label']    = $val[1];
			$wpmem->fields[ $meta_key ]['type']     = $val[3];
			$wpmem->fields[ $meta_key ]['register'] = ( 'y' == $val[4] ) ? true : false;
			$wpmem->fields[ $meta_key ]['required'] = ( 'y' == $val[5] ) ? true : false;
			$wpmem->fields[ $meta_key ]['profile']  = ( 'y' == $val[4] ) ? true : false;// ( isset( $val['profile'] ) ) ? $val['profile'] : true ; // // @todo Wait for profile fix
			$wpmem->fields[ $meta_key ]['native']   = ( 'y' == $val[6] ) ? true : false;
			
			// Certain field types have additional properties.
			switch ( $val[3] ) {
				
				case 'checkbox':
					$wpmem->fields[ $meta_key ]['checked_value']   = $val[7];
					$wpmem->fields[ $meta_key ]['checked_default'] = ( 'y' == $val[8] ) ? true : false;
					$wpmem->fields[ $meta_key ]['checkbox_label']  = ( isset( $val['checkbox_label'] ) ) ? $val['checkbox_label'] : 0;
					break;

				case 'select':
				case 'multiselect':
				case 'multicheckbox':
				case 'radio':
				case 'membership':
					if ( 'membership' == $val[3] ) {
						$val[7] = array( __( 'Choose membership', 'wp-members' ) . '|' );
						foreach( $wpmem->membership->products as $membership_key => $membership_value ) {
							$val[7][] = $membership_value['title'] . '|' . $membership_key;
						}
					}
					// Correct a malformed value (if last value is empty due to a trailing comma).
					if ( '' == end( $val[7] ) ) {
						array_pop( $val[7] );
						$wpmem->fields[ $meta_key ][7] = $val[7];
					}
					$wpmem->fields[ $meta_key ]['values']    = $val[7];
					$wpmem->fields[ $meta_key ]['delimiter'] = ( isset( $val[8] ) ) ? $val[8] : '|';
					$wpmem->fields[ $meta_key ]['options']   = array();
					foreach ( $val[7] as $value ) {
						$pieces = explode( '|', trim( $value ) );
						if ( isset( $pieces[1] ) && $pieces[1] != '' ) {
							$wpmem->fields[ $meta_key ]['options'][ $pieces[1] ] = $pieces[0];
						}
					}
					break;

				case 'file':
				case 'image':
					$wpmem->fields[ $meta_key ]['file_types'] = $val[7];
					break;

				case 'hidden':
					$wpmem->fields[ $meta_key ]['value'] = $val[7];
					break;
					
			}
		}
	}

	/**
	 * Filter custom fields for localization.
	 *
	 * @since 3.3.9
	 *
	 * @param array $fields
	 */
	function localize_fields( $fields ) {
		if ( function_exists( 'wpmem_custom_translation_strings' ) ) {
			$string_map = wpmem_custom_translation_strings( get_locale() );
			foreach ( $string_map as $meta_key => $value ) {
				if ( is_array( $value ) ) {
					if ( isset( $fields[ $meta_key ]['placeholder'] ) && isset( $value['placeholder'] ) ) {
						$fields[ $meta_key ]['placeholder'] = $string_map[ $meta_key ]['placeholder'];
					}
					if ( isset( $fields[ $meta_key ]['title'] ) && isset( $value['title'] ) ) {
						$fields[ $meta_key ]['title'] = $string_map[ $meta_key ]['title'];
					}
					if ( isset( $fields[ $meta_key ]['label'] ) && isset( $value['label'] ) ) {
						$fields[ $meta_key ]['label'] = $string_map[ $meta_key ]['label'];
					}
				} else {
					$fields[ $meta_key ]['label'] = $string_map[ $meta_key ];
				}
			}
		}
		return $fields;
	}
	
	/**
	 * Get excluded meta fields.
	 *
	 * @since 3.0.0
	 * @since 3.3.3 Update $tag to match wpmem_fields() tags.
	 *
	 * @param  string $tag A tag so we know where the function is being used.
	 * @return array       The excluded fields.
	 */
	function excluded_fields( $tag ) {

		// Default excluded fields.
		$excluded_fields = array( 'password', 'confirm_password', 'confirm_email', 'password_confirm', 'email_confirm' );
		
		if ( 'update' == $tag || 'admin-profile' == $tag || 'user-profile' == $tag || 'wp-register' == $tag ) {
			$excluded_fields[] = 'username';
		}

		if ( 'admin-profile' == $tag || 'user-profile' == $tag ) {
			array_push( $excluded_fields, 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'description', 'user_url' );
			
			// If WooCommerce is used, remove these meta - WC already adds them in their own section.
			if ( class_exists( 'woocommerce' ) ) {
				array_push( $excluded_fields,
					'billing_first_name',
					'billing_last_name',
					'billing_company',
					'billing_address_1',
					'billing_address_2',
					'billing_city',
					'billing_postcode',
					'billing_country',
					'billing_state',
					'billing_email',
					'billing_phone',
					'shipping_first_name',
					'shipping_last_name',
					'shipping_company',
					'shipping_address_1',
					'shipping_address_2',
					'shipping_city',
					'shipping_postcode',
					'shipping_country',
					'shipping_state'
				);
			}
		}

		/**
		 * Filter excluded meta fields.
		 *
		 * @since 2.9.3
		 * @since 3.0.0 Moved to new method in WP_Members Class.
		 * @since 3.3.3 Update $tag to match wpmem_fields() tags.
		 *
		 * @param array       An array of the field meta names to exclude.
		 * @param string $tag A tag so we know where the function is being used.
		 */
		$excluded_fields = apply_filters( 'wpmem_exclude_fields', $excluded_fields, $tag );

		// Return excluded fields.
		return $excluded_fields;
	}
	
	/**
	 * Creates form fields
	 *
	 * Creates various form fields and returns them as a string.
	 *
	 * @since 3.1.0
	 * @since 3.1.1 Added $delimiter.
	 * @since 3.1.2 Changed $valtochk to $compare.
	 * @since 3.1.6 Added $placeholder.
	 * @since 3.1.7 Added number type & $min, $max, $title and $pattern attributes.
	 * @since 3.2.0 Added $id argument.
	 * @since 3.2.4 Added radio group and multiple checkbox individual item labels.
	 *
	 * @global object $wpmem The WP_Members object class.
	 * @param  array  $args {
	 *     @type string  $id
	 *     @type string  $name
	 *     @type string  $type
	 *     @type string  $value
	 *     @type string  $compare
	 *     @type string  $class
	 *     @type boolean $required
	 *     @type string  $delimiter
	 *     @type string  $placeholder
	 *     @type string  $pattern
	 *     @type string  $title
	 *     @type string  $min
	 *     @type string  $max
	 *     @type string  $rows Number of rows for a textarea (default:5).
	 *     @type string  $cols Number of columns for a textarea (default:20).
	 * }
	 * @return string $str The field returned as a string.
	 */
	function create_form_field( $args ) {
		
		global $wpmem;
		
		// Set defaults for most possible $args.
		$id          = ( isset( $args['id'] ) ) ? esc_attr( $args['id'] ) : esc_attr( $args['name'] );
		$name        = esc_attr( $args['name'] );
		$type        = esc_attr( $args['type'] );
		$value       = ( isset( $args['value']       ) ) ? $args['value']       : '';
		$compare     = ( isset( $args['compare']     ) ) ? $args['compare']     : '';
		$class       = ( isset( $args['class']       ) ) ? $args['class']       : 'textbox';
		$required    = ( isset( $args['required']    ) ) ? $args['required']    : false;
		$delimiter   = ( isset( $args['delimiter']   ) ) ? $args['delimiter']   : '|';
		$placeholder = ( isset( $args['placeholder'] ) ) ? $args['placeholder'] : false;
		$pattern     = ( isset( $args['pattern']     ) ) ? $args['pattern']     : false;
		$title       = ( isset( $args['title']       ) ) ? $args['title']       : false;
		$file_types  = ( isset( $args['file_types']  ) ) ? $args['file_types']  : false;
	
		// Handle field creation by type.
		switch ( $type ) { 

		/*
		 * Field types text|url|email|number|date are all handled essentially the 
		 * same. The primary differences are CSS class (with a default fallback
		 * of 'textbox'), how values are escaped, and the application of min|max
		 * values for number fields.
		 */
		case "text":
		case "url":
		case "email":
		case "number":
		case "date":
			$class = ( 'textbox' == $class ) ? "textbox" : wpmem_sanitize_class( $class );
			switch ( $type ) {
				case 'url':
					$value = esc_url( $value );
					break;
				case 'email':
					$value = esc_attr( wp_unslash( $value ) );
					break;
				default:
					$value = stripslashes( esc_attr( $value ) ); // @todo Could email and default be combined? Both seem to unslash and esc_attr().
					break;
			}
			$required    = ( $required    ) ? ' required' : '';
			$placeholder = ( $placeholder ) ? ' placeholder="' . esc_attr( __( $placeholder, 'wp-members' ) ) . '"' : '';
			$title       = ( $title       ) ? ' title="' . esc_attr( __( $title, 'wp-members' ) ) . '"' : '';
			$pattern     = ( $pattern && 'number' != $type ) ? ' pattern="' . esc_attr( $pattern ) . '"' : '';
			$min         = ( isset( $args['min'] ) && $args['min'] != '' ) ? ' min="' . esc_attr( $args['min'] ) . '"' : '';
			$max         = ( isset( $args['max'] ) && $args['max'] != '' ) ? ' max="' . esc_attr( $args['max'] ). '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$id\" value=\"$value\" class=\"$class\"$placeholder$title$pattern$min$max" . ( ( $required ) ? " required " : "" ) . " />";
			break;
		
		case "password":
			$class = wpmem_sanitize_class( $class );
			$placeholder = ( $placeholder ) ? ' placeholder="' . esc_attr( __( $placeholder, 'wp-members' ) ) . '"' : '';
			$pattern     = ( $pattern     ) ? ' pattern="' . esc_attr( $pattern ) . '"' : '';
			$title       = ( $title       ) ? ' title="' . esc_attr( __( $title, 'wp-members' ) ) . '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$id\" class=\"$class\"$placeholder$title$pattern" . ( ( $required ) ? " required " : "" ) . " />";
			break;
		
		case "image":
		case "file":
			if ( $file_types ) {
				$file_types = explode( '|', $file_types );
				foreach( $file_types as $file_type ) {
					$array[] = "." . $file_type;
				}
				$accept = ' accept="' . implode( ",", $array ) . '"';
			} else {
				$accept = '';
			}
			$class  = ( 'textbox' == $class ) ? "file" : wpmem_sanitize_class( $class );
			$str = "<input name=\"$name\" type=\"file\" id=\"$id\" value=\"" . esc_attr( $value ) . "\" class=\"$class\"$accept" . ( ( $required ) ? " required " : "" ) . ( ( 'image' == $type ) ? ' onchange="loadFile(event, this.id)"' : '' ) . ' />';
			break;
	
		case "checkbox":
			$class = ( 'textbox' == $class ) ? "checkbox" : wpmem_sanitize_class( $class );
			$str = "<input name=\"$name\" type=\"$type\" id=\"$id\" value=\"" . esc_attr( $value ) . "\"" . checked( $value, $compare, false ) . ( ( $required ) ? " required " : "" ) . " />";
			break;
	
		case "textarea":
			$value = esc_textarea( stripslashes( $value ) ); // stripslashes( esc_textarea( $value ) );
			$class = ( 'textbox' == $class ) ? "textarea" : wpmem_sanitize_class( $class );
			$placeholder = ( $placeholder ) ? ' placeholder="' . esc_attr( __( $placeholder, 'wp-members' ) ) . '"' : '';
			$rows  = ( isset( $args['rows'] ) && $args['rows'] ) ? esc_attr( $args['rows'] ) : '5';
			$cols  = ( isset( $args['cols'] ) && $args['cols'] ) ? esc_attr( $args['cols'] ) : '20';
			$str = "<textarea cols=\"$cols\" rows=\"$rows\" name=\"$name\" id=\"$id\" class=\"$class\"$placeholder" . ( ( $required ) ? " required " : "" ) . ">$value</textarea>";
			break;
	
		case "hidden":
			$str = "<input name=\"$name\" type=\"$type\" value=\"" . esc_attr( $value ) . "\" />";
			break;
	
		case "option":
			$str = "<option value=\"" . esc_attr( $value ) . "\" " . selected( $value, $compare, false ) . " >" . __( $name, 'wp-members' ) . "</option>";
			break;
	
		case "select":
		case "multiselect":
		case "membership":
			$class = ( 'textbox' == $class && 'multiselect' != $type ) ? "dropdown"    : $class;
			$class = ( 'textbox' == $class && 'multiselect' == $type ) ? "multiselect" : $class;
			$pname = ( 'multiselect' == $type ) ? $name . "[]" : $name;
			$str = "<select name=\"$pname\" id=\"$id\" class=\"$class\"" . ( ( 'multiselect' == $type ) ? " multiple " : "" ) . ( ( $required ) ? " required " : "" ) . ">\n";
			if ( 'membership' == $type ) {
				$value = array( __( 'Choose membership', 'wp-members' ) . '|' );
				foreach( $wpmem->membership->products as $membership_key => $membership_value ) {
					$value[] = $membership_value['title'] . '|' . $membership_key;
				}
			}
			foreach ( $value as $option ) {
				$pieces = array_map( 'trim', explode( '|', $option ) );
				if ( 'multiselect' == $type ) {
					$chk = '';
					$values = ( empty( $compare ) ) ? array() : ( is_array( $compare ) ? $compare : explode( $delimiter, $compare ) );
				} else {
					$chk = $compare;
					$values = array();
				}
				if ( isset( $pieces[1] ) && '' != $pieces[1] ) {
					$chk = ( ( isset( $pieces[2] ) && '' == $compare ) || in_array( $pieces[1], $values ) ) ? $pieces[1] : $chk;
				} else {
					$chk = 'not selected';
				}
				$pieces[1] = ( isset( $pieces[1] ) ) ? $pieces[1] : ''; // If someone skipped a pipe, treat it as empty.
				$str = $str . "<option value=\"$pieces[1]\"" . selected( $pieces[1], $chk, false ) . ">" . esc_attr( __( $pieces[0], 'wp-members' ) ) . "</option>\n";
			}
			$str = $str . "</select>";
			break;
			
		case "multicheckbox":
			$class = ( 'textbox' == $class ) ? "checkbox" : $class;
			$str = '';
			$num = 1;
			foreach ( $value as $option ) {
				$pieces = explode( '|', $option );
				$values = ( empty( $compare ) ) ? array() : ( is_array( $compare ) ? $compare : explode( $delimiter, $compare ) );
				$chk = ( isset( $pieces[2] ) && '' == $compare ) ? $pieces[1] : '';
				if ( isset( $pieces[1] ) && '' != $pieces[1] ) {
					$id_value = esc_attr( $id . '[' . $pieces[1] . ']' );
					$label = wpmem_form_label( array( 'meta_key'=>$id_value, 'label'=>esc_html( __( $pieces[0], 'wp-members' ) ), 'type'=>'multicheckbox', 'id'=>$id_value ) );
					$str = $str . wpmem_form_field( array(
						'id'      => $id_value,
						'name'    => $name . '[]',
						'type'    => 'checkbox',
						'value'   => $pieces[1],
						'compare' => ( in_array( $pieces[1], $values ) ) ? $pieces[1] : $chk,
					) ) . "&nbsp;" . $label . "<br />\n";
				} else {
					$str = $str . '<span class="div_multicheckbox_separator">' . esc_html( __( $pieces[0], 'wp-members' ) ) . "</span><br />\n";
				}
			}
			break;
			
		case "radio":
			$class = ( 'textbox' == $class ) ? "radio" : wpmem_sanitize_class( $class );
			$str = '';
			$num = 1;
			foreach ( $value as $option ) {
				$pieces = explode( '|', $option );
				$id_num = $id . '_' . $num;
				if ( isset( $pieces[1] ) && '' != $pieces[1] ) {
					$label = wpmem_form_label( array( 'meta_key'=>esc_attr( $id_num ), 'label'=>esc_html( __( $pieces[0], 'wp-members' ) ), 'type'=>'radio', 'id'=>esc_attr( "label_" . $id_num ) ) );
					$str = $str . "<input type=\"radio\" name=\"$name\" id=\"" . esc_attr( $id_num ) . "\" value=\"" . esc_attr( $pieces[1] ) . '"' . checked( $pieces[1], $compare, false ) . ( ( $required ) ? " required " : " " ) . "> $label<br />\n";
					$num++;
				} else {
					$str = $str . '<span class="div_radio_separator">' . esc_html( __( $pieces[0], 'wp-members' ) ) . "</span><br />\n";
				}
			}
			break;		
	
		} 
	
		return $str;
	} // End create_form_field()
	
	/**
	 * Create form label.
	 *
	 * @since 3.1.7
	 * @since 3.2.4 Added $id
	 *
	 * @param array  $args {
	 *     @type string $meta_key
	 *     @type string $label
	 *     @type string $type
	 *     @type string $id       (optional)
	 *     @type string $class    (optional)
	 *     @type string $required (optional)
	 *     @type string $req_mark (optional)
	 * }
	 * @return string $label
	 */
	function create_form_label( $args ) {
		global $wpmem;
		
		$meta_key   = $args['meta_key'];
		$label      = $args['label'];
		$type       = $args['type'];
		$class      = ( isset( $args['class']    ) ) ? $args['class']    : false;
		$id         = ( isset( $args['id']       ) ) ? $args['id']       : false;
		$required   = ( isset( $args['required'] ) ) ? $args['required'] : false;
		$req_mark   = ( isset( $args['req_mark'] ) ) ? $args['req_mark'] : false;
		
		//$req_mark = ( ! $req_mark ) ? wpmem_get_text( 'register_req_mark' ) : '*';
		
		if ( ! $class ) {
			$class = ( $type == 'password' || $type == 'email' || $type == 'url' ) ? 'text' : $type;
		}
		
		$id = ( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

		$label = '<label for="' . esc_attr( $meta_key ) . '"' . $id . ' class="' . wpmem_sanitize_class( $class ) . '">' . __( $label, 'wp-members' );
		$label = ( $required ) ? $label . $req_mark : $label;
		$label = $label . '</label>';
		
		return $label;
	}
	
	/**
	 * Uploads file from the user.
	 *
	 * @since 3.1.0
	 *
	 * @param  array    $file
	 * @param  int      $user_id
	 * @return int|bool
	 */
	function do_file_upload( $file = array(), $user_id = false ) {
		
		// Filter the upload directory.
		add_filter( 'upload_dir', array( &$this, 'file_upload_dir' ) );
		
		// Set up user ID for use in upload process.
		$this->file_user_id = ( $user_id ) ? $user_id : 0;
	
		// Get WordPress file upload processing scripts.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		
		$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );
	
		if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
			return false;
		} else {
	
			$attachment = array(
				'post_mime_type' => $file_return['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_return['file'] ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => $file_return['url'],
				'post_author'    => ( $user_id ) ? $user_id : '',
			);
	
			$attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
	
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_return['file'] );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );
	
			if ( 0 < intval( $attachment_id ) ) {
				// Returns an array with file information.
				return $attachment_id;
			}
		}
	
		return false;
	} // End upload_file()
	
	/**
	 * Sets the file upload directory.
	 *
	 * This is a filter function for upload_dir.
	 *
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/upload_dir
	 *
	 * @since 3.1.0
	 *
	 * @param  array $param {
	 *     The directory information for upload.
	 *
	 *     @type string $path
	 *     @type string $url
	 *     @type string $subdir
	 *     @type string $basedir
	 *     @type string $baseurl
	 *     @type string $error
	 * }
	 * @return array $param
	 */
	function file_upload_dir( $param ) {
		
		global $wpmem;
		
		$user_id  = ( isset( $this->file_user_id ) ) ? $this->file_user_id : null;
		
		$args = array(
			'user_id'   => $user_id,
			'wpmem_dir' => $wpmem->upload_base,
			'user_dir'  => 'user_files/' . $user_id,
		);
		
		/**
		 * Filter the user directory elements.
		 *
		 * @since 3.1.0
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'wpmem_user_upload_dir', $args );

		$param['subdir'] = '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
		$param['path']   = $param['basedir'] . '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
		$param['url']    = $param['baseurl'] . '/' . $args['wpmem_dir'] . '/' . $args['user_dir'];
	
		return $param;
	}

	/**
	 * Login Form Builder.
	 *
	 * Builds the form used for login, change password, and reset password.
	 *
	 * @since 2.5.1
	 * @since 3.1.7 Moved to forms object class as login_form().
	 * @since 3.1.7 Added WP action login_form.
	 * @since 3.2.6 Added nonce to the short form.
	 *
	 * @param  string $page 
	 * @param  array  $arr {
	 *     The elements needed to generate the form (login|reset password|forgotten password).
	 *
	 *     @type string $heading     Form heading text.
	 *     @type string $action      The form action (login|pwdchange|pwdreset|getusername).
	 *     @type string $button_text Form submit button text.
	 *     @type array  $inputs {
	 *         The form input values.
	 *
	 *         @type array {
	 *
	 *             @type string $name  The field label.
	 *             @type string $type  Input type.
	 *             @type string $tag   Input tag name.
	 *             @type string $class Input tag class.
	 *             @type string $div   Div wrapper class.
	 *         }
	 *     }
	 *     @type string $redirect_to Optional. URL to redirect to.
	 * }
	 * @return string $form  The HTML for the form as a string.
	 */
	function login_form( $mixed, $arr = array() ) {
		
		global $wpmem;

		// Handle legacy use.
		if ( is_array( $mixed ) ) {
			$page = ( isset( $mixed['page'] ) ) ? $mixed['page'] : 'login';
			$arr  = $mixed;
		} else {
			$page = $mixed;
		}

		$action = ( ! isset( $arr['action'] ) ) ? 'login' : $arr['action'];
		
		// Set up redirect_to
		$redirect_to = wpmem_get_redirect_to( $arr );

		// Set up default wrappers.
		// NOTE: DO NOT EDIT! There is a filter hook for this -> wpmem_login_form_args. 
		$defaults = array(

			// wrappers
			'heading_before'  => '<legend>',
			'heading_after'   => '</legend>',
			'fieldset_before' => '<fieldset>',
			'fieldset_after'  => '</fieldset>',
			'main_div_before' => '<div id="wpmem_login">',
			'main_div_after'  => '</div>',
			'row_before'      => '',
			'row_after'       => '',
			'buttons_before'  => '<div class="button_div">',
			'buttons_after'   => '</div>',
			'link_before'     => '<div class="link-text">',
			'link_after'      => '</div>',
			'link_span_before' => '<span class="link-text-%s">',
			'link_span_after'  => '</span>',

			// classes & ids
			'form_id'         => 'wpmem_' . $action . '_form',
			'form_class'      => 'form',
			'button_id'       => '',
			'button_class'    => 'buttons',

			// other
			'strip_breaks'    => true,
			'wrap_inputs'     => true,
			'remember_check'  => true,
			'n'               => "\n",
			't'               => "\t",
			'redirect_to'     => $redirect_to,
			'login_form_action' => true,

		);

		/**
		 * Filter the default form arguments.
		 *
		 * This filter accepts an array of various elements to replace the form defaults. This
		 * includes default tags, labels, text, and small items including various booleans.
		 *
		 * @since 2.9.0
		 * @since 3.3.0 Passes $defaults as an argument.
		 *
		 * @param array  $args {
		 *     An array of arguments to merge with defaults.
		 *
		 *     @type string  $heading_before    Default: '<legend>'
		 *     @type string  $heading_after     Default: '</legend>'
		 *     @type string  $fieldset_before   Default: '<fieldset>'
		 *     @type string  $fieldset_after    Default: '</fieldset>'
		 *     @type string  $main_div_before   Default: '<div id="wpmem_login">'
		 *     @type string  $main_div_after    Default: '</div>'
		 *     @type string  $row_before        Default: ''
		 *     @type string  $row_after         Default: ''
		 *     @type string  $buttons_before    Default: '<div class="button_div">'
		 *     @type string  $buttons_after     Default: '</div>'
		 *     @type string  $link_before       Default: '<div class="link-text">'
		 *     @type string  $link_after        Default: '</div>'
		 *     @type string  $link_span_before  Default: '<span class="link-text-%s">'
		 *     @type string  $link_span_after   Default: '</span>'
		 *     @type string  $form_id           Default: 'wpmem_' . $action . '_form' (for example, wpmem_login_form or wpmem_pwdreset_form)
		 *     @type string  $form_class        Default: 'form'
		 *     @type string  $button_id         Default: ''
		 *     @type string  $button_class      Default: buttons'
		 *     @type boolean $strip_breaks      Default: true (if true, strips all line breaks from the generated HTML)
		 *     @type boolean $wrap_inputs       Default: true (if true, includes main_div_before/after wrappers around input tags)
		 *     @type boolean $remember_check    Default: true
		 *     @type string  $n                 Default: "\n" (the new line character if breaks are not stripped (see $strip_breaks))
		 *     @type string  $t                 Default: "\t" (the line indent character if breaks are not stripped (see $strip_breaks))
		 *     @type string  $redirect_to       Default: (the $redirect_to argument passed to the function)
		 *     @type boolean $login_form_action Default: true (if true, adds the WP login_form action)
		 * }
		 * @param string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$args = apply_filters( 'wpmem_login_form_args', $defaults, $action );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		// Build the input rows.
		foreach ( $arr['inputs'] as $input ) {
			$label = '<label for="' . esc_attr( $input['tag'] ) . '">' . $input['name'] . '</label>';
			$field = wpmem_form_field( array(
				'name'     => $input['tag'], 
				'type'     => $input['type'],
				'class'    => $input['class'],
				'required' => true,
			) );
			$field_before = ( $args['wrap_inputs'] ) ? '<div class="' . wpmem_sanitize_class( $input['div'] ) . '">' : '';
			$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
			$rows[] = array( 
				'row_before'   => $args['row_before'],
				'label'        => $label,
				'field_before' => $field_before,
				'field'        => $field,
				'field_after'  => $field_after,
				'row_after'    => $args['row_after'],
			);
		}

		/**
		 * Filter the array of form rows.
		 *
		 * This filter receives an array of the main rows in the form, each array element being
		 * an array of that particular row's pieces. This allows making changes to individual 
		 * parts of a row without needing to parse through a string of HTML.
		 *
		 * @since 2.9.0
		 * @since 3.2.6 Added $arr parameter so all settings are passed.
		 *
		 * @param array  $rows   An array containing the form rows.
		 * @param string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 * @param array  $arr    An array containing all of the form settings.
		 */
		$rows = apply_filters( 'wpmem_login_form_rows', $rows, $action, $arr );

		// Put the rows from the array into $form.
		$form = '';
		foreach ( $rows as $row_item ) {
			$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
			$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
			$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
			$form.= $row;
		}

		// Handle outside elements added to the login form (currently ONLY for login).
		if ( 'login' == $action && $args['login_form_action'] ) {
			ob_start();
			/** This action is documented in wp-login.php */
			do_action( 'login_form' );
			$add_to_form = ob_get_contents();
			ob_end_clean();
			$form.= $add_to_form;
		}

		// Build hidden fields, filter, and add to the form.
		if ( 'set_password_from_key' == wpmem_get( 'a', false, 'request' ) && 'login' != $action ) {
			$hidden['action'] = wpmem_form_field( array( 'name' => 'a', 'type' => 'hidden', 'value' => 'set_password_from_key' ) );
			$hidden['key']    = wpmem_form_field( array( 'name' => 'key',   'type' => 'hidden', 'value' => sanitize_text_field( wpmem_get( 'key',   null, 'request' ) ) ) );
			$hidden['login']  = wpmem_form_field( array( 'name' => 'login', 'type' => 'hidden', 'value' => sanitize_user( wpmem_get( 'login', null, 'request' ) ) ) );
		} else {
			$hidden['action'] = wpmem_form_field( array( 'name' => 'a', 'type' => 'hidden', 'value' => $action ) );
			$hidden['redirect_to'] = wpmem_form_field( array( 'name' => 'redirect_to', 'type' => 'hidden', 'value' => esc_url( $args['redirect_to'] ) ) );
		}
		
		if ( $action != 'login' ) {
			$hidden['formsubmit'] = wpmem_form_field( array( 'name' => 'formsubmit', 'type' => 'hidden', 'value' => '1' ) );
		}
		
		/**
		 * Filter hidden fields array.
		 *
		 * @since 3.4.0
		 *
		 * @param  array  $hidden
		 * @param  string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$hidden = apply_filters( 'wpmem_login_hidden_field_rows', $hidden, $action  );
		
		$hidden_field_string = '';
		foreach ( $hidden as $field ) {
			$hidden_field_string .= $field . $args['n'];
		}

		/**
		 * Filter the hidden field HTML.
		 *
		 * @since 2.9.0
		 *
		 * @param string $hidden The generated HTML of hidden fields.
		 * @param string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = $form . apply_filters( 'wpmem_login_hidden_fields', $hidden_field_string, $action );

		// Build the buttons, filter, and add to the form.
		if ( $action == 'login' ) {
			$buttons[] = ( $args['remember_check'] ) ? $args['t'] . wpmem_form_field( array( 'name' => 'rememberme', 'type' => 'checkbox', 'value' => 'forever' ) ) . '&nbsp;' . '<label for="rememberme">' . wpmem_get_text( 'remember_me' ) . '</label>&nbsp;&nbsp;' . $args['n'] : '';
			$buttons[] = $args['t'] . '<input type="submit" name="Submit" value="' . esc_attr( $arr['button_text'] ) . '" class="' . wpmem_sanitize_class( $args['button_class'] ) . '" />' . $args['n'];
		} else {
			$buttons[] = '<input type="submit" name="Submit" value="' . esc_attr( $arr['button_text'] ) . '" class="' . wpmem_sanitize_class( $args['button_class'] ) . '" />' . $args['n'];
		}

		/**
		 * Filter the button parts.
		 * 
		 * @since 3.4.5
		 * 
		 * @param  array   $rows
		 * @param  string  $action
		 */
		$buttons = apply_filters( 'wpmem_login_form_button_rows', $buttons, $action );

		// HTML assembly for buttons.
		$button_html = '';
		foreach ( $buttons as $button_row ) {
			$button_html .= $button_row;
		}

		/**
		 * Filter the HTML for form buttons.
		 *
		 * The string includes the buttons, as well as the before/after wrapper elements.
		 *
		 * @since 2.9.0
		 *
		 * @param string $buttons The generated HTML of the form buttons.
		 * @param string $action  The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = $form . apply_filters( 'wpmem_login_form_buttons', $args['buttons_before'] . $args['n'] . $button_html . $args['buttons_after'] . $args['n'], $action );

		$links_array = array(
			'forgot' => array(
				'tag'    => 'forgot',
				'link'   => wpmem_pwd_reset_url(),
				'page'   => 'profile',
				'action' => 'login',
			),
			'register' => array(
				'tag'    => 'reg',
				'link'   => wpmem_register_url(),
				'page'   => 'register',
				'action' => 'login',
			),
			'username' => array(
				'tag'    => 'username',
				'link'   => wpmem_forgot_username_url(),
				'page'   => 'profile',
				'action' => 'pwdreset',
			),
		);
		foreach ( $links_array as $key => $value ) {
			$tag = $value['tag'];
			if ( ( $wpmem->user_pages[ $value['page'] ] || 'profile' == $page ) && $value['action'] == $action ) {
				/**
				 * Filters register, forgot password, and forgot username links.
				 *
				 * @since 2.8.0
				 * @since 3.1.7 Combined all to a single process.
				 * @since 3.2.5 Added $tag parameter.
				 *
				 * @param string The raw link.
				 * @param string $tag forgot|reg|pwdreset|username.
				 */
				$link = apply_filters( "wpmem_{$tag}_link", $value['link'], $tag );
				$str  = wpmem_get_text( "{$key}_link_before" ) . '<a href="' . esc_url( $link ) . '">' . wpmem_get_text( "{$key}_link" ) . '</a>';
				$link_str = $args['link_before'];
				$link_str.= ( '' != $args['link_span_before'] ) ? sprintf( $args['link_span_before'], $key ) : '';
				/**
				 * Filters the register, forgot password, and forgot username links HTML.
				 *
				 * @since 2.9.0
				 * @since 3.0.9 Added $link parameter.
				 * @since 3.1.7 Combined all to a single process.
				 * @since 3.2.5 Added $tag parameter.
				 *
				 * @param string $str  The link HTML.
				 * @param string $link The link.
				 * @param string $tag  forgot|reg|pwdreset.
				 */
				$link_str.= apply_filters( "wpmem_{$tag}_link_str", $str, $link, $tag );
				$link_str.= ( '' != $args['link_span_after'] ) ? $args['link_span_after'] : '';
				$link_str.= $args['link_after'] . $args['n'];
				/*
				 * If this is the register link, and the current post type is set to
				 * display the register form, and the current page is not the login
				 * page, then do not add the register link, otherwise add the link.
				 */
				if ( 'register' == $key ) {
					if ( ! isset( $wpmem->user_pages['register'] ) || '' == $wpmem->user_pages['register'] ) {
						$form = $form;
					} else {
						if ( isset( $wpmem->user_pages['login'] ) && '' != $wpmem->user_pages['login'] ) {
							$form = ( 1 == $wpmem->show_reg[ get_post_type( get_the_ID() ) ] && wpmem_current_url( true, false ) != wpmem_login_url() ) ? $form : $form . $link_str;
						} else {
							global $post;
							if ( has_shortcode( $post->post_content, 'wpmem_profile' ) ) {
								$form = $form;
							} else {
								$form = ( 1 == $wpmem->show_reg[ get_post_type( get_the_ID() ) ] && ! has_shortcode( $post->post_content, 'wpmem_form' ) ) ? $form : $form . $link_str;
							}
						}
					}
				} else {
					$form = $form . $link_str;
				}
			}
		}

		// Apply the heading.
		$form = $args['heading_before'] . $arr['heading'] . $args['heading_after'] . $args['n'] . $form;

		// Apply fieldset wrapper.
		$form = $args['fieldset_before'] . $args['n'] . $form . $args['fieldset_after'] . $args['n'];
		
		// Apply nonce.
		$form = wp_nonce_field( 'wpmem_shortform_nonce', '_wpmem_' . $action . '_nonce', true, false ) . $args['n'] . $form;

		// Apply form wrapper.
		$form = '<form action="' . esc_url( get_permalink() ) . '" method="POST" id="' . wpmem_sanitize_class( $args['form_id'] ) . '" class="' . wpmem_sanitize_class( $args['form_class'] ) . '">' . $args['n'] . $form . '</form>';

		// Apply anchor.
		$form = '<a id="' . esc_attr( $action ) . '"></a>' . $args['n'] . $form;

		// Apply main wrapper.
		$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'];

		// Remove line breaks.
		$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

		/**
		 * Filter the generated HTML of the entire form.
		 *
		 * @since 2.7.4
		 * @since 2.9.1 Added $action argument
		 *
		 * @param string $form   The HTML of the final generated form.
		 * @param string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = apply_filters( 'wpmem_login_form', $form, $action );

		/**
		 * Filter before the form.
		 *
		 * This rarely used filter allows you to stick any string onto the front of
		 * the generated form.
		 *
		 * @since 2.7.4
		 *
		 * @param string $str    The HTML to add before the form. Default null.
		 * @param string $action The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = apply_filters( 'wpmem_login_form_before', '', $action ) . $form;

		return $form;
	} // End login_form.

	/**
	 * Registration Form Builder.
	 *
	 * Outputs the form for new user registration and existing user edits.
	 *
	 * @since 2.5.1
	 * @since 3.1.7 Moved to forms object class as register_form().
	 * @since 3.2.5 use_nonce now obsolete (nonce is added automatically).
	 * @since 3.3.0 $heading argument obsolete.
	 * @since 3.3.3 Image field type now shows the preview image when "choose file" is clicked.
	 * @since 3.4.6 Added $user object as a filterable arg.
	 *
	 * @global object $wpmem        The WP_Members object.
	 * @param  mixed  $mixed        (optional) String toggles between new registration ('new') and user profile edit ('edit'), or array containing settings arguments.
	 * @return string $form         The HTML for the entire form as a string.
	 */
	function register_form( $mixed = 'new', $redirect_to = null ) {
		
		/*
		 * Removes the action to load form elements for the WP registration 
		 * form. Otherwise, when the register_form action is fired in this 
		 * form, we'd get a duplication of all custom fields.
		 */
		remove_action( 'register_form', 'wpmem_wp_register_form' );
		
		// Handle legacy use.
		if ( is_array( $mixed ) ) {
			$id          = ( isset( $mixed['id']          ) ) ? $mixed['id']          : '';
			$tag         = ( isset( $mixed['tag']         ) ) ? $mixed['tag']         : 'new';
			$heading     = ( isset( $mixed['heading']     ) ) ? $mixed['heading']     : '';
			$redirect_to = ( isset( $mixed['redirect_to'] ) ) ? $mixed['redirect_to'] : '';
			$fields      = ( isset( $mixed['fields']      ) ) ? $mixed['fields']      : false;
		} else {
			$id  = 'default';
			$tag = $mixed;
		}

		global $wpmem;

		// If editing, the user object of the user being edit.
		$user = ( 'edit' == $tag ) ? get_user_by( 'ID', get_current_user_id() ) : false;

		// Set up default wrappers.
		$defaults = array(

			// Wrappers.
			'heading_before'   => '<legend>',
			'heading_after'    => '</legend>',
			'fieldset_before'  => '<fieldset>',
			'fieldset_after'   => '</fieldset>',
			'main_div_before'  => '<div id="wpmem_reg">',
			'main_div_after'   => '</div>',
			'row_before'       => '',
			'row_after'        => '',
			'buttons_before'   => '<div class="button_div">',
			'buttons_after'    => '</div>',

			// Classes & ids.
			'form_id'          => ( 'new' == $tag ) ? 'wpmem_register_form' : 'wpmem_profile_form',
			'form_class'       => 'form',
			'button_id'        => '',
			'button_class'     => 'buttons',

			// Required field tags and text.
			'req_mark'         => wpmem_get_text( 'register_req_mark' ),
			'req_label'        => wpmem_get_text( 'register_required' ),
			'req_label_before' => '<div class="req-text">',
			'req_label_after'  => '</div>',

			// Buttons.
			'show_clear_form'  => false,
			'clear_form'       => wpmem_get_text( 'register_clear' ),
			'submit_register'  => wpmem_get_text( 'register_submit' ),
			'submit_update'    => wpmem_get_text( 'profile_submit' ),

			// Other.
			'post_to'          => get_permalink(),
			'strip_breaks'     => true,
			'wrap_inputs'      => true,
			'n'                => "\n",
			't'                => "\t",
			
			'register_form_action' => true,

			'user'             => $user,

		);

		/**
		 * Filter the default form arguments.
		 *
		 * This filter accepts an array of various elements to replace the form defaults. This
		 * includes default tags, labels, text, and small items including various booleans.
		 *
		 * @since 2.9.0
		 * @since 3.2.5 Added $id
		 * @since 3.3.0 Passes $defaults as an argument.
		 * @since 3.4.6 Added $user as an argument.
		 *
		 * @param array        An array of arguments to merge with defaults. Default null.
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 * @param string $id   An id for the form (optional).
		 */
		$args = apply_filters( 'wpmem_register_form_args', $defaults, $tag, $id );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );
		
		// Get fields.
		$wpmem_fields = wpmem_fields( $tag );

		// Fields to skip for user profile update.
		if ( 'edit' == $tag ) {
			$pass_arr = array( 'username', 'password', 'confirm_password', 'password_confirm' );
			// Skips tos on user edit page, unless they haven't got a value for tos.
			$user_tos_val = ( is_object( $user ) ) ? get_user_meta( $user->ID, 'tos', true ) : false;
			if ( isset( $wpmem_fields['tos'] ) && ( $wpmem_fields['tos']['checked_value'] == $user_tos_val ) ) { 
				$pass_arr[] = 'tos';
			}
			foreach ( $pass_arr as $pass ) {
				unset( $wpmem_fields[ $pass ] );
			}
		}
		
		/**
		 * Filter the array of form fields.
		 *
		 * The form fields are stored in the WP options table as wpmembers_fields. This
		 * filter can filter that array after the option is retreived before the fields
		 * are parsed. This allows you to change the fields that may be used in the form
		 * on the fly.
		 *
		 * @since 2.9.0
		 * @deprecated 3.1.7 Use wpmem_fields instead.
		 *
		 * @param array        The array of form fields.
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 */
		$wpmem_fields = apply_filters( 'wpmem_register_fields_arr', $wpmem_fields, $tag );

		$hidden_rows = array();
		$form_has_file = false;

		// Loop through the remaining fields.
		foreach ( $wpmem_fields as $meta_key => $field ) {

			// Start with a clean row.
			$val = ''; $label = ''; $input = ''; $field_before = ''; $field_after = '';

			// If the field is set to display and we aren't skipping, construct the row.
			// if ( ( 'new' == $tag && $field['register'] ) || ( 'edit' == $tag && $field['profile'] ) ) { // @todo Wait for profile fix
			if ( $field['register'] ) {
				
				// Handle hidden fields
				if ( 'hidden' == $field['type'] ) {
					$do_row = false;
					$hidden_rows[ $meta_key ] = wpmem_form_field( array( 
						'name'     => $meta_key,
						'type'     => $field['type'],
						'value'    => $field['value'],
						'compare'  => $valtochk,
						'required' => $field['required'],
					) );
				}

				// Label for all but TOS and hidden fields.
				if ( 'tos' != $meta_key && 'hidden' != $field['type'] ) {

					$class = ( $field['type'] == 'password' || $field['type'] == 'email' || $field['type'] == 'url' ) ? 'text' : $field['type'];
					
					$label = wpmem_form_label( array(
						'meta_key' => $meta_key, //( 'username' == $meta_key ) ? 'user_login' : $meta_key, 
						'label'    => __( $field['label'], 'wp-members' ), 
						'type'     => $field['type'], 
						'class'    => $class, 
						'required' => $field['required'], 
						'req_mark' => $args['req_mark'] 
					) );

				} 

				// Gets the field value for edit profile.
				if ( ( 'edit' == $tag ) && ( '' == $wpmem->regchk ) ) {
					switch ( $meta_key ) {
						case( 'description' ):
						case( 'textarea' == $field['type'] ):
							$val = ( is_object( $user ) ) ? get_user_meta( $user->ID, $meta_key, 'true' ) : ''; // esc_textarea() is run when field is created.
							break;

						case 'user_email':
						case 'confirm_email':
							$val = ( is_object( $user ) ) ? sanitize_email( $user->user_email ) : '';
							break;

						case 'user_url':
							$val = ( is_object( $user ) ) ? $user->user_url : ''; // esc_url() is run when the field is created.
							break;

						case 'display_name':
							$val = ( is_object( $user ) ) ? sanitize_text_field( $user->display_name ) : '';
							break; 

						default:
							$val = ( is_object( $user ) ) ? sanitize_text_field( get_user_meta( $user->ID, $meta_key, 'true' ) ) : '';
							break;
					}

				} else {
					if ( 'file' == $field['type'] ) {
						$val = ( isset( $_FILES[ $meta_key ]['name'] ) ) ? sanitize_file_name( $_FILES[ $meta_key ]['name'] ) : '' ;
					} else {
						$val = ( isset( $_POST[ $meta_key ] ) ) ? wpmem_sanitize_field( $_POST[ $meta_key ], $field['type'] ) : '';
					}
				}

				// Does the tos field.
				if ( 'tos' == $meta_key ) {

				//	$val = sanitize_text_field( wpmem_get( $meta_key, '' ) ); 

					// Should be checked by default? and only if form hasn't been submitted.
					$val   = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;
					$input = wpmem_form_field( array(
						'name'     => $meta_key, 
						'type'     => $field['type'], 
						'value'    => $field['checked_value'], 
						'compare'  => $val,
						'required' => $field['required'],
					) );

					$input .= ' ' . $this->get_tos_link( $field, $tag );
					
					$input = ( $field['required'] ) ? $input . $args['req_mark'] : $input;

					// In previous versions, the div class would end up being the same as the row before.
					$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
					$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';

				} elseif ( 'hidden' != $field['type'] ) {

					// For checkboxes.
					if ( 'checkbox' == $field['type'] ) { 
						$valtochk = $val;
						$val = $field['checked_value']; 
						// if it should it be checked by default (& only if form not submitted), then override above...
						if ( $field['checked_default'] && ( ! $_POST && $tag != 'edit' ) ) { 
							$val = $valtochk = $field['checked_value'];
						}
					}

					// For dropdown select.
					if ( 'select' == $field['type'] || 'radio' == $field['type'] || 'multiselect' == $field['type'] || 'multicheckbox' == $field['type'] || 'membership' == $field['type'] ) {
						$valtochk = $val;
						$val = $field['values'];
					}

					if ( ! isset( $valtochk ) ) {
						$valtochk = '';
					}

					if ( ( 'file' == $field['type'] || 'image' == $field['type'] ) ) {
						
						$form_has_file = true;
						
						// Handle files differently for multisite vs. single install.
						// @see: https://core.trac.wordpress.org/ticket/32145
						if ( is_multisite() ) {
							$attachment = get_post( $val );
							$attachment_url = $attachment->guid;
						} else {
							$attachment_url = wp_get_attachment_url( $val );
						}
						
						$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
						if ( 'edit' == $tag ) {
							if ( 'file' == $field['type'] ) {
								$input = ( $attachment_url ) ? '<a href="' . esc_url( $attachment_url ) . '" id="' . $meta_key . '_file">' . get_the_title( $val ) . '</a>' : $empty_file;
							} else {
								$input = ( $attachment_url ) ? '<img src="' . esc_url( $attachment_url ) . '" id="' . $meta_key . '_img" />' : $empty_file;
							}
							$input.= '<br />' . wpmem_get_text( 'profile_upload' ) . '<br />';
						} else {
							if ( 'image' == $field['type'] ) {
								$input = '<img src="" id="' . $meta_key . '_img" />';
							}
						}
						$input.= wpmem_form_field( array(
							'name'       => $meta_key, 
							'type'       => $field['type'], 
							'value'      => $val, 
							'compare'    => $valtochk,
							'file_types' => $field['file_types'],
						) );

					} else {
					
						// For all other input types.
						$formfield_args = array( 
							'name'     => $meta_key, // ( 'username' == $meta_key ) ? 'user_login' : $meta_key,
							'type'     => $field['type'],
							'value'    => $val,
							'compare'  => $valtochk,
							//'class'    => ( $class ) ? $class : 'textbox',
							'required' => $field['required'],
							'placeholder' => ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '',
							'pattern'     => ( isset( $field['pattern']     ) ) ? $field['pattern']     : false,
							'title'       => ( isset( $field['title']       ) ) ? $field['title']       : false,
							'min'         => ( isset( $field['min']         ) ) ? $field['min']         : false,
							'max'         => ( isset( $field['max']         ) ) ? $field['max']         : false,
							'rows'        => ( isset( $field['rows']        ) ) ? $field['rows']        : false,
							'cols'        => ( isset( $field['cols']        ) ) ? $field['cols']        : false,
							'file_types'  => ( isset( $field['file_types']  ) ) ? $field['file_types']  : false,
						);
						if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
							$formfield_args['delimiter'] = $field['delimiter'];
						}
						$input = wpmem_form_field( $formfield_args );

						// If checkbox label option is enabled.
						if ( 'checkbox' == $field['type'] && 1 == $field['checkbox_label'] ) {
							$input = $input . ' <label for="' . $meta_key . '">' . $label . '</label>';
							$fields[ $meta_key ]['label'] = $field['label'] = $label = '';
						}
					}

					// Determine input wrappers.
					$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_' . $class . '">' : '';
					$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
				}

			}

			// If the row is set to display, add the row to the form array.
			if ( ( 'new' == $tag && $field['register'] ) || ( 'edit' == $tag && $field['profile'] ) ) {
			//if ( $field['register'] && 'hidden' != $field['type'] ) {
				if ( 'hidden' != $field['type'] ) {

					$values = '';
					if ( 'multicheckbox' == $field['type'] || 'select' == $field['type'] || 'multiselect' == $field['type'] || 'radio' == $field['type'] ) {
						$values = $val;
						$val = $valtochk;
					}

					$rows[ $meta_key ] = array(
						'meta'         => $meta_key,
						'type'         => $field['type'],
						'value'        => $val,
						'values'       => $values,
						'label_text'   => __( $field['label'], 'wp-members' ),
						'row_before'   => $args['row_before'],
						'label'        => $label,
						'field_before' => $field_before,
						'field'        => $input,
						'field_after'  => $field_after,
						'row_after'    => $args['row_after'],
					);
				}
			}
		}

		// If captcha is Really Simple CAPTCHA.
		if ( 2 == $wpmem->captcha && 'edit' != $tag ) {
			// Build the captcha.
			$row = WP_Members_Captcha::rs_captcha( 'array' );
			$rows['captcha'] = array(
				'meta'         => '', 
				'type'         => 'text', 
				'value'        => '',
				'values'       => '',
				'label_text'   => $row['label_text'],
				'row_before'   => $args['row_before'],
				'label'        => $row['label'],
				'field_before' => ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '',
				'field'        => $row['img'] . $row['hidden'] . $row['field'],
				'field_after'  => ( $args['wrap_inputs'] ) ? '</div>' : '',
				'row_after'    => $args['row_after'],
			);
		}

		/**
		 * Filter the array of form rows.
		 *
		 * This filter receives an array of the main rows in the form, each array element being
		 * an array of that particular row's pieces. This allows making changes to individual 
		 * parts of a row without needing to parse through a string of HTML.
		 *
		 * @since 2.9.0
		 * @since 3.0.9 Added $rows['label_text'].
		 * @since 3.1.0 Added $rows['key'].
		 * @since 3.1.6 Deprecated $rows['order'].
		 *
		 * @param array  $rows    {
		 *     An array containing the form rows. 
		 *
		 *     @type string order        Field display order. (deprecated as of 3.1.6)
		 *     @type string meta         Field meta tag (not used for display).
		 *     @type string type         Input field type (not used for display).
		 *     @type string value        Input field value (not used for display).
		 *     @type string values       Possible field values (dropdown, multiple select/check, radio).
		 *     @type string label_text   Raw text for the label (not used for display).
		 *     @type string row_before   Opening wrapper tag around the row.
		 *     @type string label        Label tag.
		 *     @type string field_before Opening wrapper tag before the input tag.
		 *     @type string field        The field input tag.
		 *     @type string field_after  Closing wrapper tag around the input tag.
		 *     @type string row_after    Closing wrapper tag around the row.
		 * }
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 */
		$rows = apply_filters( 'wpmem_register_form_rows', $rows, $tag );
		
		// Put the rows from the array into $form.
		$form = ''; $enctype = '';
		foreach ( $rows as $meta_key => $row_item ) {
			// Make sure all keys are set just in case someone didn't return a proper array through the filter.
			foreach ( $this->get_reg_row_keys() as $check_key ) {
				if ( ! isset( $rows[ $meta_key ][ $check_key ] ) ) {
					$rows[ $meta_key ][ $check_key ] = '';
				}
			}
			// Check form to see if we need multipart enctype.
			$enctype = ( $row_item['type'] == 'file' ||  $row_item['type'] == 'image' ) ? "multipart/form-data" : $enctype;
			// Assemble row pieces.
			$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
			$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
			$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
			$form.= $row;
		}
		
		// Handle outside elements added to the register form with register_form.
		if ( 'new' == $tag && $args['register_form_action'] ) {
			ob_start();
			/** This action is documented in wp-login.php */
			do_action( 'register_form' );
			$add_to_form = ob_get_contents();
			ob_end_clean();
			$form.= $add_to_form;
		}

		// Do recaptcha if enabled.
		if ( ( 1 == $wpmem->captcha || 3 == $wpmem->captcha || 4 == $wpmem->captcha ) && $tag != 'edit' ) { // don't show on edit page!
			
			$row = WP_Members_Captcha::recaptcha();
			
			if ( 4 != $wpmem->captcha ) {
				$row = '<div class="clear"></div><div class="captcha">' . $row . '</div>';
			}

			// Add the captcha row to the form.
			/**
			 * Filter the HTML for the CAPTCHA row.
			 *
			 * @since 2.9.0
			 *
			 * @param string       The HTML for the entire row (includes HTML tags plus reCAPTCHA).
			 * @param string $tag  Toggle new registration or profile update. new|edit.
			 */
			$form.= apply_filters( 'wpmem_register_captcha_row', $args['row_before'] . $row . $args['row_after'], $tag );
		}
		
		if ( 5 == $wpmem->captcha && 'edit' != $tag ) {
			$row = WP_Members_Captcha::hcaptcha();
			/** This filter is documented in /includes/class-wp-members-forms.php */
			$form.= apply_filters( 'wpmem_register_captcha_row', $args['row_before'] . $row . $args['row_after'], $tag );
		}

		// Create hidden fields.
		$var         = ( $tag == 'edit' ) ? 'update' : 'register';
		$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? $_REQUEST['redirect_to'] : ( ( $redirect_to ) ? $redirect_to : get_permalink() );
		$hidden_rows['_wpmem_a']        = '<input name="a" type="hidden" value="' . esc_attr( $var ) . '" />';
		$hidden_rows['_wpmem_reg_page'] = '<input name="wpmem_reg_page" type="hidden" value="' . esc_url( get_permalink() ) . '" />';
		if ( $redirect_to != get_permalink() ) {
			$hidden_rows['_wpmem_redirect_to'] = '<input name="redirect_to" type="hidden" value="' . esc_url( $redirect_to ) . '" />';
		}
		
		/**
		 * Filter the hidden form rows.
		 *
		 * @since 3.2.0
		 *
		 * @param array  $hidden_rows
		 * @param string $tag
		 */
		$hidden_rows = apply_filters( 'wpmem_register_hidden_rows', $hidden_rows, $tag );
		
		// Assemble hidden fields HTML.
		$hidden = '';
		foreach ( $hidden_rows as $hidden_row ) {
			$hidden .= $hidden_row . $args['n'];
		}

		/**
		 * Filter the hidden field HTML.
		 *
		 * @since 2.9.0
		 *
		 * @param string $hidden The generated HTML of hidden fields.
		 * @param string $tag    Toggle new registration or profile update. new|edit.
		 */
		$hidden = apply_filters( 'wpmem_register_hidden_fields', $hidden, $tag );

		// Add the hidden fields to the form.
		$form.= $hidden;

		// Create buttons and wrapper.
		$button_text = ( $tag == 'edit' ) ? $args['submit_update'] : $args['submit_register'];
		$button_html = array(
			'reset' =>  ( $args['show_clear_form'] ) ? '<input name="reset" type="reset" value="' . esc_attr( $args['clear_form'] ) . '" class="' . wpmem_sanitize_class( $args['button_class'] ) . '" /> ' : '',
			'submit' => '<input name="submit" type="submit" value="' . esc_attr( $button_text ) . '" class="' . wpmem_sanitize_class( $args['button_class'] ) . '" />',
		);
		$buttons = $button_html['reset'] . $args['n'] . $button_html['submit'] . $args['n'];

		/**
		 * Filter the HTML for form buttons.
		 *
		 * The string passed through the filter includes the buttons, as well as the HTML wrapper elements.
		 *
		 * @since 2.9.0
		 * @since 3.2.6 Added $button_html parameter
		 *
		 * @param string $buttons     The generated HTML of the form buttons.
		 * @param string $tag         Toggle new registration or profile update. new|edit.
		 * @param array  $button_html The individual button html.
		 */
		$buttons = apply_filters( 'wpmem_register_form_buttons', $buttons, $tag, $button_html );

		// Add the buttons to the form.
		$form.= $args['buttons_before'] . $args['n'] . $buttons . $args['buttons_after'] . $args['n'];

		// Add the required field notation to the bottom of the form.
		$form.= $args['req_label_before'] . $args['req_label'] . $args['req_label_after'];

		// Apply the heading.
		if ( 'edit' == $tag ) {
			/**
			 * Filter the default heading in User Profile edit mode.
			 *
			 * @since 2.7.5
			 * @since 3.3.0 Moved into main registration function (from profile shortcode).
			 *
			 * @param string The default edit mode heading.
			 */
			$heading = ( isset( $heading ) && '' != $heading ) ? $heading : apply_filters( 'wpmem_user_edit_heading', wpmem_get_text( 'profile_heading' ) );
		} else {
			/**
			 * Filter the registration form heading.
			 *
			 * @since 2.8.2
			 *
			 * @param string $str
			 * @param string $tag Toggle new registration or profile update. new|edit.
			 */
			$heading = ( isset( $heading ) && '' != $heading ) ? $heading : apply_filters( 'wpmem_register_heading', wpmem_get_text( 'register_heading' ), $tag );
		}
		$form = $args['heading_before'] . $heading . $args['heading_after'] . $args['n'] . $form;

		// Apply fieldset wrapper.
		$form = $args['fieldset_before'] . $args['n'] . $form . $args['n'] . $args['fieldset_after'];

		// Apply attribution if enabled.
		$form = $form . $this->attribution();

		// Apply nonce. Nonce uses $tag value of the form processor, NOT the form builder.
		$nonce = ( $tag == 'edit' ) ? 'update' : 'register';
		$form  = wp_nonce_field( 'wpmem_longform_nonce', '_wpmem_' . $nonce . '_nonce', true, false ) . $args['n'] . $form;

		// Apply form wrapper.
		$enctype = ( $enctype == 'multipart/form-data' ) ? ' enctype="multipart/form-data"' : '';
		$form = '<form name="form" method="post"' . $enctype . ' action="' . esc_attr( $args['post_to'] ) . '" id="' . wpmem_sanitize_class( $args['form_id'] ) . '" class="' . wpmem_sanitize_class( $args['form_class'] ) . '">' . $args['n'] . $form . $args['n'] . '</form>';

		// Apply anchor.
		$form = '<a id="register"></a>' . $args['n'] . $form;

		// Apply main div wrapper.
		$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'] . $args['n'];

		// Remove line breaks if enabled for easier filtering later.
		$form = ( $args['strip_breaks'] ) ? $this->strip_breaks( $form, $rows ) : $form; //str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

		// If there is an image input type, include the following script.
		$form = ( $form_has_file ) ? $form . '
<script>
	var loadFile = function(event, clicked_id) {
		var reader = new FileReader();
		var the_id = clicked_id + "_img";
		reader.onload = function() {
			var output = document.getElementById(the_id);
			output.src = reader.result;
		};
		reader.readAsDataURL(event.target.files[0]);
	};
</script>' : $form;
		
		/**
		 * Filter the generated HTML of the entire form.
		 *
		 * @since 2.7.4
		 *
		 * @param string $form The HTML of the final generated form.
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 * @param array  $rows   {
		 *     An array containing the form rows. 
		 *
		 *     @type string order        Field display order.
		 *     @type string meta         Field meta tag (not used for display).
		 *     @type string type         Input field type (not used for display).
		 *     @type string value        Input field value (not used for display).
		 *     @type string values       The possible values for the field (dropdown, multiple select/checkbox, radio group).
		 *     @type string label_text   Raw text for the label (not used for display).
		 *     @type string row_before   Opening wrapper tag around the row.
		 *     @type string label        Label tag.
		 *     @type string field_before Opening wrapper tag before the input tag.
		 *     @type string field        The field input tag.
		 *     @type string field_after  Closing wrapper tag around the input tag.
		 *     @type string row_after    Closing wrapper tag around the row.
		 * }
		 * @param string $hidden The HTML string of hidden fields
		 */
		$form = apply_filters( 'wpmem_register_form', $form, $tag, $rows, $hidden );

		/**
		 * Filter before the form.
		 *
		 * This rarely used filter allows you to stick any string onto the front of
		 * the generated form.
		 *
		 * @since 2.7.4
		 *
		 * @param string $str The HTML to add before the form. Default null.
		 * @param string $tag Toggle new registration or profile update. new|edit.
		 */
		$form = apply_filters( 'wpmem_register_form_before', '', $tag ) . $form;

		$wpmem->reg_form_showing = true;
		
		// Return the generated form.
		return $form;
	} // End register_form().
	
	/**
	 * Strip line breaks from form.
	 *
	 * Function removes line breaks and tabs. Checks for textarea fields
	 * before stripping line breaks.
	 *
	 * @since 3.1.8
	 *
	 * @param  string $form
	 * @param  array  $rows
	 * @return string $form
	 */
	private function strip_breaks( $form, $rows ) {
		foreach( $rows as $key => $row ) {
			if ( 'textarea' == $row['type'] ) {
				$textareas[ $key ] = $row['field'];
			}
		}
		$form = str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form );
		if ( ! empty ( $textareas ) ) {
			foreach ( $textareas as $textarea ) {
				$stripped = str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $textarea );
				$form = str_replace( $stripped, $textarea, $form );
			}
		}
		return $form;
	}

	/**
	 * Appends WP-Members registration fields to wp-login.php registration form.
	 *
	 * @since 2.8.7
	 * @since 3.1.1 Updated to support new (3.1.0) field types.
	 * @since 3.1.6 Updated to support new fields array. Added WC classes.
	 * @since 3.1.8 Added $process parameter.
	 * @since 3.3.0 Ported from wpmem_do_wp_register_form() in wp-registration.php.
	 *
	 * @global  stdClass  $wpmem
	 * @param   string    $process
	 */
	function wp_register_form( $process = 'wp' ) {

		global $wpmem;
		$wpmem_fields = wpmem_fields( $process );

		// Check if this is WooCommerce account page.
		$is_woo = false;
		if ( 'woo' == $process ) {
			$is_woo = true;
		} else {
			if ( function_exists( 'is_account_page' ) ) {
				$is_woo = ( is_account_page() ) ? true : $is_woo;
			}
		}

		if ( isset( $wpmem_fields ) && is_array( $wpmem_fields ) ) {

			unset( $wpmem_fields['username'] );
			
			if ( $is_woo ) {
				// Woo has its own setting for password fields.
				unset( $wpmem_fields['password'] );
				unset( $wpmem_fields['confirm_password'] );
			}

			foreach ( $wpmem_fields as $meta_key => $field ) {

				$req = ( $field['required'] ) ? ( ( $is_woo ) ? ' <span class="required">*</span>' : ' <span class="req">' . __( '(required)' ) . '</span>' ) : '';

				// File fields not yet supported for this form.
				if ( $field['register'] && $meta_key != 'user_email' && $field['type'] != 'file' && $field['type'] != 'image' ) {

					if ( 'checkbox' == $field['type'] ) {

						if ( 'tos' == $meta_key ) {
							$tos_link_text = $this->get_tos_link( $field, 'woo' );
						}

						$label = ( 'tos' == $meta_key ) ? $tos_link_text : __( $field['label'], 'wp-members' );

						$val = ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : '';
						$val = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;

						$row_before = '<p class="wpmem-checkbox">';
						$label = '<label for="' . $meta_key . '">' . $label . $req . '</label>';
						$input = wpmem_form_field( $meta_key, $field['type'], $field['checked_value'], $val );
						$row_after = '</p>';

					} elseif ( 'hidden' == $field['type'] ) {

						// Handle hidden fields
						$row_before = '';
						$label = '';
						$input = wpmem_form_field( array( 
								'name'     => $meta_key,
								'type'     => $field['type'],
								'value'    => $field['value'],
								'compare'  => $valtochk,
								'required' => $field['required'],
							) );
						$row_after = '';

					} else {

						$row_before = ( $is_woo ) ? '<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">' : '<p>';
						$label  = '<label for="' . $meta_key . '">' . __( $field['label'], 'wp-members' ) . $req . '</label>';
						$label .= ( 'multicheckbox' == $field['type'] ) ? '<br />' : '';

						// determine the field type and generate accordingly...

						switch ( $field['type'] ) {

						case( 'textarea' ):
							$input = '<textarea name="' . $meta_key . '" id="' . $meta_key . '" class="textarea">';
							$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
							$input.= '</textarea>';		
							break;

						case( 'select' ):
						case( 'multiselect' ):
						case( 'multicheckbox' ):
						case( 'radio' ):
						case( 'membership' ):
							$row_before = ( $is_woo && ( 'select' == $field['type'] || 'multiselect' == $field['type'] || 'membership' == $field['type'] ) ) ? $row_before : '<p class="' . $field['type'] . '">';
							$valtochk = ( isset( $_POST[ $meta_key ] ) ) ? sanitize_text_field( $_POST[ $meta_key ] ) : '';
							$formfield_args = array( 
								'name'     => $meta_key,
								'type'     => $field['type'],
								'value'    => $field['values'],
								'compare'  => $valtochk,
								'required' => $field['required'],
								'class'    => ( $is_woo && ( 'select' == $field['type'] || 'multiselect' == $field['type'] || 'membership' == $field['type'] ) ) ? 'woocommerce-Input woocommerce-Input--text input-text' : $field['type'],
							);
							if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
								$formfield_args['delimiter'] = $field['delimiter'];
							}
							$input = wpmem_form_field( $formfield_args );
							break;

						case( 'file' ):
						case( 'image' ):
							// Field type not supported for this yet.
							break;

						default:
							$class = ( $is_woo ) ? 'woocommerce-Input woocommerce-Input--text input-text' : 'input';
							//$input = '<input type="' . $field['type'] . '" name="' . $meta_key . '" id="' . $meta_key . '" class="' . $class . '" value="';
							$formfield_args = array( 
								'name'     => $meta_key,
								'type'     => $field['type'],
								'value'    => wpmem_sanitize_field( wpmem_get( $meta_key, '' ), $field['type'] ),
								'compare'  => ( isset( $field['compare'] ) ) ? $field['compare'] : '',
								'required' => $field['required'],
								'class'    => $class,
								'placeholder' => ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '',
								'pattern'     => ( isset( $field['pattern']     ) ) ? $field['pattern']     : false,
								'title'       => ( isset( $field['title']       ) ) ? $field['title']       : false,
								'min'         => ( isset( $field['min']         ) ) ? $field['min']         : false,
								'max'         => ( isset( $field['max']         ) ) ? $field['max']         : false,
								'rows'        => ( isset( $field['rows']        ) ) ? $field['rows']        : false,
								'cols'        => ( isset( $field['cols']        ) ) ? $field['cols']        : false,
							);
							$input = wpmem_form_field( $formfield_args );
							//$input.= ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : ''; 
							//$input.= '" size="25" />';
							break;
						}

						$row_after = '</p>';

					}

					// if the row is set to display, add the row to the form array
					$rows[ $meta_key ] = array(
						'type'         => $field['type'],
						'row_before'   => $row_before,
						'label'        => $label,
						'field'        => $input,
						'row_after'    => $row_after,
					);
				}
			}
			
			// Do recaptcha if enabled.
			if ( ! $is_woo && isset( $wpmem->captcha ) && $wpmem->captcha != 0 ) {
				
				$row_before = '<p>';
				$row_after  = '</p>';
				$label      = '';
				$captcha    = '';
				
				if ( in_array( $wpmem->captcha, array( 1, 3, 4 ) ) ) {
					$captcha = WP_Members_Captcha::recaptcha();
				} elseif ( 5 == $wpmem->captcha ) {
					$captcha = WP_Members_Captcha::hcaptcha();
				} elseif ( 2 == $wpmem->captcha ) {
					$row = WP_Members_Captcha::rs_captcha( 'array' );
					$label   = $row['label']; //$row['label_text'];
					$captcha = $row['img'] . $row['hidden'] . $row['field'];
				}
				if ( 4 == $wpmem->captcha ) {
					$row_before = '';
					$row_after  = '';
				}
				
				$rows['captcha'] = array(
					'type' => '',
					'row_before' => $row_before,
					'row_after'  => $row_after,
					'label'      => $label,
					'field'      => $captcha,
				);
			}

			if ( isset( $rows ) && is_array( $rows ) ) {

				/**
				 * Filter the native registration form rows.
				 *
				 * @since 2.9.3.
				 *
				 * @param array $rows The custom rows added to the form.
				 */
				$rows = apply_filters( 'wpmem_native_form_rows', $rows );

				foreach ( $rows as $row_item ) {
					if ( $row_item['type'] == 'checkbox' ) {
						echo $row_item['row_before'] . $row_item['field'] . $row_item['label'] . $row_item['row_after'];
					} else { 
						echo $row_item['row_before'] . $row_item['label'] . $row_item['field'] . $row_item['row_after'];
					}
				}
			}
		}
	}


	/**
	 * Appends WP-Members registration fields to Users > Add New User screen.
	 *
	 * @since 2.9.0
	 * @since 3.1.1 Updated to support new (3.1.0) field types and user activation.
	 * @since 3.1.6 Updated to support new fields array.
	 * @since 3.3.0 Ported from wpmem_do_wp_newuser_form() in wp-registration.php.
	 *
	 * @global stdClass $wpmem
	 */
	function wp_newuser_form() {

		global $wpmem;
		echo '<table class="form-table"><tbody>';

		$wpmem_fields = wpmem_fields( 'add_new' );
		$exclude = wpmem_get_excluded_meta( 'wp-register' );

		foreach ( $wpmem_fields as $meta_key => $field ) {

			if ( ! $field['native'] && ! in_array( $meta_key, $exclude ) ) {

				$req = ( $field['required'] ) ? ' <span class="description">' . __( '(required)' ) . '</span>' : '';

				$class = ( 'radio'    == $field['type'] 
					    || 'checkbox' == $field['type']
						|| 'date'     == $field['type'] ) ? '' : ' class="form-field" ';
				echo '<tr' . $class . '>
					<th scope="row">
						<label for="' . $meta_key . '">' . __( $field['label'], 'wp-members' ) . $req . '</label>
					</th>
					<td>';

				// determine the field type and generate accordingly.
				
				// All fields use the following:
				$args['name']     = $meta_key;
				$args['type']     = $field['type'];
				$args['required'] = $field['required'];
				
				$args['placeholder'] = ( isset( $field['placeholder'] ) ) ? $field['placeholder'] : '';
				$args['pattern']     = ( isset( $field['pattern']     ) ) ? $field['pattern']     : '';
				$args['title']       = ( isset( $field['title']       ) ) ? $field['title']       : '';

				switch ( $field['type'] ) {

				case( 'select' ):
					$val = ( isset( $_POST[ $meta_key ] ) ) ? sanitize_text_field( $_POST[ $meta_key ] ) : '';
					$args['value']   = $field['values'];
					$args['compare'] = $val;
					echo wpmem_form_field( $args );
					break;

				case( 'textarea' ):
					$args['value'] = ( isset( $_POST[ $meta_key ] ) ) ? esc_textarea( $_POST[ $meta_key ] ) : '';
					echo wpmem_form_field( $args );
					break;

				case( 'checkbox' ):
					$val = ( isset( $_POST[ $meta_key ] ) ) ? sanitize_text_field( $_POST[ $meta_key ] ) : '';
					$val = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;
					$args['value']   = $field['checked_value'];
					$args['compare'] = $val;
					echo wpmem_form_field( $args );
					break;

				case( 'multiselect' ):
				case( 'multicheckbox' ):
				case( 'radio' ):
				case( 'membership' );
					$args['value']   = $field['values'];
					$args['compare'] = ( isset( $_POST[ $meta_key ] ) ) ? sanitize_text_field( $_POST[ $meta_key ] ) : '';
					if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
						$args['delimiter'] = $field['delimiter'];
					}
					echo wpmem_form_field( $args );
					break;

				case( 'file' ):
				case( 'image' ):
					echo 'Not currently supported for this form';
					break;

				default:
					$args['value'] = ( isset( $_POST[ $meta_key ] ) ) ? esc_attr( $_POST[ $meta_key ] ) : '';
					echo wpmem_form_field( $args );
					break;
				}

				echo '</td>
					</tr>';

			}
		}

		// If moderated registration is enabled, add checkbox to set user as active.
		if ( 1 == $wpmem->mod_reg ) {
			echo '<tr>
					<th scope="row">
						<label for="activate_user">' . __( 'Activate this user?', 'wp-members' ) . '</label>
					</th>
					<td>' . wpmem_form_field( array( 'name' => 'activate_user', 'type' => 'checkbox', 'value' => 1, 'compare' => '' ) ) . '</td>
				  </tr>';
		}

		echo '</tbody></table>';

	}

	/**
	 * Create an attribution link in the form.
	 *
	 * @since 2.6.0
	 * @since 3.1.1 Updated to use new object setting.
	 * @since 3.3.0 Ported from wpmem_inc_attribution() in forms.php.
	 *
	 * @global object $wpmem
	 * @return string $str
	 */
	function attribution() {

		global $wpmem;
		$str = '
		<div align="center">
			<small>Powered by <a href="https://rocketgeek.com" target="_blank">WP-Members</a></small>
		</div>';

		return ( 1 == $wpmem->attrib ) ? $str : '';
	}

	/**
	 * Settings for building Short Form (login).
	 *
	 * Replaces individual legacy functions and filters for
	 * the short forms, combined into a single method.
	 *
	 * @since 3.3.0
	 * @since 3.4.0 Change inputs.
	 *
	 * @global  stdClass  $post
	 * @global  stdClass  $wpmem
	 *
	 * @param   string    $form  login|changepassword|resetpassword|forgotusername
	 * @param   array     $args
	 * @return  string    $form
	 */
	function do_shortform( $form, $args = array() ) {
		
		global $post, $wpmem;

		$input_arrays = array(
			'login' => array(
				array(
					'name'   => wpmem_get_text( 'login_username' ), 
					'type'   => 'text', 
					'tag'    => 'log',
					'class'  => 'username',
					'div'    => 'div_text',
				),
				array( 
					'name'   => wpmem_get_text( 'login_password' ), 
					'type'   => 'password', 
					'tag'    => 'pwd', 
					'class'  => 'password',
					'div'    => 'div_text',
				),
			),
			'changepassword' => array(
				array(
					'name'   => wpmem_get_text( 'pwdchg_password1' ), 
					'type'   => 'password',
					'tag'    => 'pass1',
					'class'  => 'password',
					'div'    => 'div_text',
				),
				array( 
					'name'   => wpmem_get_text( 'pwdchg_password2' ), 
					'type'   => 'password', 
					'tag'    => 'pass2',
					'class'  => 'password',
					'div'    => 'div_text',
				),
			),
			'resetpassword' => array(
				array(
					'name'   => wpmem_get_text( 'login_username' ), 
					'type'   => 'text',
					'tag'    => 'user', 
					'class'  => 'username',
					'div'    => 'div_text',
				),
			),
			'forgotusername' => array(
				array(
					'name'   => wpmem_get_text( 'username_email' ), 
					'type'   => 'text',
					'tag'    => 'user_email',
					'class'  => 'username',
					'div'    => 'div_text',
				),
			),
		);

		// @todo Temp until 3.5.0 removes old password reset.
		if ( 1 != $wpmem->pwd_link ) {
			$input_arrays['resetpassword'] = array(
				array(
					'name'   => wpmem_get_text( 'pwdreset_username' ), 
					'type'   => 'text',
					'tag'    => 'user', 
					'class'  => 'username',
					'div'    => 'div_text',
				),
				array( 
					'name'   => wpmem_get_text( 'pwdreset_email' ), 
					'type'   => 'text', 
					'tag'    => 'email', 
					'class'  => 'textbox',
					'div'    => 'div_text',
				),
			);
		}
		
		/**
		 * Filter the array of change password form fields.
		 *
		 * @since 2.9.0
		 * @deprecated 3.3.0 Use wpmem_{$form}_form_defaults instead.
		 *
		 * @param array $default_inputs An array matching the elements used by default.
		 */	
		$default_inputs = apply_filters( 'wpmem_inc_' . $form . '_inputs', $input_arrays[ $form ] );

		$form_arrays = array(
			'login' => array( 
				'heading'      => wpmem_get_text( 'login_heading' ), 
				'action'       => 'login', 
				'button_text'  => wpmem_get_text( 'login_button' ),
				'inputs'       => $default_inputs,
				'redirect_to'  => ( isset( $args['redirect_to'] ) ) ? $args['redirect_to'] : get_permalink(),
			),
			'changepassword' => array(
				'heading'      => wpmem_get_text( 'pwdchg_heading' ), 
				'action'       => 'pwdchange', 
				'button_text'  => wpmem_get_text( 'pwdchg_button' ), 
				'inputs'       => $default_inputs,
			),
			'resetpassword' => array(
				'heading'      => wpmem_get_text( 'pwdreset_heading' ),
				'action'       => 'pwdreset', 
				'button_text'  => wpmem_get_text( 'pwdreset_button' ), 
				'inputs'       => $default_inputs,
			),
			'forgotusername' => array(
				'heading'      => wpmem_get_text( 'username_heading' ), 
				'action'       => 'getusername', 
				'button_text'  => wpmem_get_text( 'username_button' ),
				'inputs'       => $default_inputs,
			),
		);
		
		/**
		 * Filter the arguments to override form defaults.
		 *
		 * @since 2.9.0
		 * @deprecated 3.3.0 Use wpmem_{$form}_form_defaults instead.
		 *
		 * @param array $args An array of arguments to use. Default null. (login|changepassword|resetpassword|forgotusername)
		 */
		$args = apply_filters( 'wpmem_inc_' . $form . '_args', '' );
		$arr  = wp_parse_args( $args, $form_arrays[ $form ] );
		
		/**
		 * Filter the arguments to override change password form defaults.
		 *
		 * @since 3.3.0
		 * @since 3.4.6 Added $form of the filter being used.
		 *
		 * @param array   $args  An array of arguments to use. 
		 * @param string  $form  Tag of the form being used (login|changepassword|resetpassword|forgotusername)
		 */
		$arr = apply_filters( 'wpmem_' . $form . '_form_defaults', $arr, $form );
		
		return $this->login_form( '', $arr );
	}

	/**
	 * Applies the post restricted message above the short form.
	 *
	 * @since 3.3.0
	 *
	 * @global stdClass $wpmem
	 *
	 * @return string $str The generated message.
	 */
	public function add_restricted_msg() {

		global $wpmem;
		
		$str = '';

		if ( $wpmem->regchk != "success" ) {

			$dialogs = get_option( 'wpmembers_dialogs' );

			// This shown above blocked content.
			$msg = wpmem_get_text( 'restricted_msg' );
			$msg = ( $dialogs['restricted_msg'] == $msg ) ? $msg : __( stripslashes( $dialogs['restricted_msg'] ), 'wp-members' );
			$str = '<div id="wpmem_restricted_msg"><p>' . $msg . '</p></div>';

			/**
			 * Filter the post restricted message.
			 *
			 * @since 2.7.3
			 * @since 3.2.0 Added raw message string and HTML as separate params.
			 *
			 * @param string $str The post restricted message with HTML.
			 * @param string $msg The raw message string.
			 * @param string      The 'before' HTML wrapper.
			 * @param string      The 'after' HTML wrapper.
			 */
			$str = apply_filters( 'wpmem_restricted_msg', $str, $msg, '<div id="wpmem_restricted_msg"><p>', '</p></div>' );
		}
		
		return $str;
	}
	
	/**
	 * Alias for handing the default WP login form.
	 *
	 * @since 3.3.2
	 */
	function wp_login_form( $args ) {
		return wp_login_form( $args );
	}
	
	/**
	 * Generate TOS field with link.
	 *
	 * @since 3.3.5
	 *
	 * @param  array  $field
	 * @param  string $tag
	 * @return string
	 */
	function get_tos_link( $field, $tag = 'new' ) {
		global $wpmem;
		// Determine if TOS is a WP page or not.
		$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
		if ( has_shortcode( $tos_content, 'wpmem_tos' ) || has_shortcode( $tos_content, 'wp-members' ) ) {	
			$tos_link_url = do_shortcode( $tos_content );
			$tos_link_tag = '<a href="' . esc_url( $tos_link_url ) . '" target="_blank">';
		} else {
			$tos_link_url = add_query_arg( 'tos', 'display' );
			$tos_link_tag = "<a href=\"#\" onClick=\"window.open('" . $tos_link_url . "','tos');\">";
		}

		/**
		 * Filter the TOS link.
		 *
		 * @since 3.2.6
		 *
		 * @param string $tos_link_tag
		 * @param string $tos_link_url
		 */
		$tos_link_tag = apply_filters( 'wpmem_tos_link_tag', $tos_link_tag, $tos_link_url );

		/**
		 * Filter the TOS link text.
		 *
		 * @since 2.7.5
		 *
		 * @param string       The link text.
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 */
		$tos_link_text = apply_filters( 'wpmem_tos_link_txt', wpmem_get_text( 'register_tos' ), $tag );

		// If filtered value is not the default label, use that, otherwise use label.
		// @note: if default changes, this check must change.
		if ( __( 'Please indicate that you agree to the %s Terms of Service %s', 'wp-members' ) == $tos_link_text ) {
			if ( __( 'TOS', 'wp-members' ) != $field['label'] && __( 'Terms of Service', 'wp-members' ) != $field['label'] ) {
				$tos_link_text = $field['label'];
			}
		}

		// If tos string does not contain link identifiers (%s), wrap the whole string.
		if ( ! strpos( $tos_link_text, '%s' ) ) {
			$tos_link_text = '%s' . $tos_link_text . '%s';
		}
		
		return sprintf( $tos_link_text, $tos_link_tag, '</a>' );
	}
	
	function get_reg_row_keys() {
		return array( 'meta', 'type', 'value', 'values', 'label_text', 'row_before', 'label', 'field_before', 'field', 'field_after', 'row_after' );
	}
} // End of WP_Members_Forms class.