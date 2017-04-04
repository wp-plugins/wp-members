<?php
/**
 * The WP_Members Forms Class.
 *
 * @package WP-Members
 * @subpackage WP_Members Forms Object Class
 * @since 3.1.0
 */

class WP_Members_Forms {

	/**
	 * Plugin initialization function.
	 *
	 * @since 3.1.0
	 */
	function __construct() {
		
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
	 *
	 * @param array  $args {
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
		
		$name        = $args['name'];
		$type        = $args['type'];
		$value       = maybe_unserialize( $args['value'] );
		$compare     = ( isset( $args['compare']     ) ) ? $args['compare']     : '';
		$class       = ( isset( $args['class']       ) ) ? $args['class']       : 'textbox';
		$required    = ( isset( $args['required']    ) ) ? $args['required']    : false;
		$delimiter   = ( isset( $args['delimiter']   ) ) ? $args['delimiter']   : '|';
		$placeholder = ( isset( $args['placeholder'] ) ) ? $args['placeholder'] : false;
		$pattern     = ( isset( $args['pattern']     ) ) ? $args['pattern']     : false;
		$title       = ( isset( $args['title']       ) ) ? $args['title']       : false;
	
		switch ( $type ) { 

		case "text":
		case "url":
		case "email":
		case "number":
			$class = ( 'textbox' == $class ) ? "textbox" : $class;
			switch ( $type ) {
				case 'url':
					$value = esc_url( $value );
					break;
				case 'email':
					$value = esc_attr( wp_unslash( $value ) );
					break;
				case 'number':
					$value = $value;
				default:
					$value = stripslashes( esc_attr( $value ) );
					break;
			}
			$required    = ( $required    ) ? ' required' : '';
			$placeholder = ( $placeholder ) ? ' placeholder="' . $placeholder . '"' : '';
			$pattern     = ( $pattern     ) ? ' pattern="' . $pattern . '"' : '';
			$title       = ( $title       ) ? ' title="' . $title . '"' : '';
			$min         = ( isset( $args['min'] ) ) ? ' min="' . $args['min'] . '"' : '';
			$max         = ( isset( $args['max'] ) ) ? ' max="' . $args['max'] . '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\"$placeholder$title$pattern$min$max" . ( ( $required ) ? " required " : "" ) . " />";
			break;
		
		case "password":
			$placeholder = ( $placeholder ) ? ' placeholder="' . $placeholder . '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\"$placeholder" . ( ( $required ) ? " required " : "" ) . " />";
			break;
		
		case "image":
		case "file":
			$class = ( 'textbox' == $class ) ? "file" : $class;
			$str = "<input name=\"$name\" type=\"file\" id=\"$name\" value=\"$value\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . " />";
			break;
	
		case "checkbox":
			$class = ( 'textbox' == $class ) ? "checkbox" : $class;
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"" . esc_attr( $value ) . "\"" . checked( $value, $compare, false ) . ( ( $required ) ? " required " : "" ) . " />";
			break;
	
		case "textarea":
			$value = stripslashes( esc_textarea( $value ) );
			$class = ( 'textbox' == $class ) ? "textarea" : $class;
			$rows  = ( isset( $args['rows'] ) ) ? $args['rows'] : '5';
			$cols  = ( isset( $args['cols'] ) ) ? $args['cols'] : '20';
			$str = "<textarea cols=\"$cols\" rows=\"$rows\" name=\"$name\" id=\"$name\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . ">$value</textarea>";
			break;
	
		case "hidden":
			$str = "<input name=\"$name\" type=\"$type\" value=\"" . esc_attr( $value ) . "\" />";
			break;
	
		case "option":
			$str = "<option value=\"" . esc_attr( $value ) . "\" " . selected( $value, $compare, false ) . " >$name</option>";
			break;
	
		case "select":
		case "multiselect":
			$class = ( 'textbox' == $class ) ? "dropdown" : $class;
			$class = ( 'multiselect' == $type ) ? "multiselect" : $class;
			$pname = ( 'multiselect' == $type ) ? $name . "[]" : $name;
			$str = "<select name=\"$pname\" id=\"$name\" class=\"$class\"" . ( ( 'multiselect' == $type ) ? " multiple " : "" ) . ( ( $required ) ? " required " : "" ) . ">\n";
			foreach ( $value as $option ) {
				$pieces = explode( '|', $option );
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
				$str = $str . "<option value=\"$pieces[1]\"" . selected( $pieces[1], $chk, false ) . ">" . __( $pieces[0], 'wp-members' ) . "</option>\n";
			}
			$str = $str . "</select>";
			break;
			
		case "multicheckbox":
			$class = ( 'textbox' == $class ) ? "checkbox" : $class;
			$str = '';
			foreach ( $value as $option ) {
				$pieces = explode( '|', $option );
				$values = ( empty( $compare ) ) ? array() : ( is_array( $compare ) ? $compare : explode( $delimiter, $compare ) );
				$chk = ( isset( $pieces[2] ) && '' == $compare ) ? $pieces[1] : '';
				$str = $str . $this->create_form_field( array(
					'name'    => $name . '[]',
					'type'    => 'checkbox',
					'value'   => $pieces[1],
					'compare' => ( in_array( $pieces[1], $values ) ) ? $pieces[1] : $chk,
				) ) . "&nbsp;" . $pieces[0] . "<br />\n";
			}
			break;
			
		case "radio":
			$class = ( 'textbox' == $class ) ? "radio" : $class;
			$str = '';
			$num = 1;
			foreach ( $value as $option ) {
				$pieces = explode( '|', $option );
				$id = $name . '_' . $num;
				$str = $str . "<input type=\"radio\" name=\"$name\" id=\"$id\" value=\"$pieces[1]\"" . checked( $pieces[1], $compare, false ) . ( ( $required ) ? " required " : " " ) . "> " . __( $pieces[0], 'wp-members' ) . "<br />\n";
				$num++;
			}
			break;		
	
		} 
	
		return $str;
	} // End create_form_field()
	
	/**
	 * Create form label.
	 *
	 * @since 3.1.7
	 *
	 * @param array  $args {
	 *     @type string $meta_key
	 *     @type string $label_text
	 *     @type string $type
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
		$required   = ( isset( $args['required'] ) ) ? $args['required'] : false;
		$req_mark   = ( isset( $args['req_mark'] ) ) ? $args['req_mark'] : false;
		
		//$req_mark = ( ! $req_mark ) ? $wpmem->get_text( 'register_req_mark' ) : '*';
		
		if ( ! $class ) {
			$class = ( $type == 'password' || $type == 'email' || $type == 'url' ) ? 'text' : $type;
		}

		$label = '<label for="' . $meta_key . '" class="' . $class . '">' . __( $label, 'wp-members' );
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
		add_filter( 'upload_dir', array( &$this,'file_upload_dir' ) );
		
		// Set up user ID for use in upload process.
		$this->file_user_id = ( $user_id ) ? $user_id : 0;
	
		// Get WordPress file upload processing scripts.
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		
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
		$user_id  = ( isset( $this->file_user_id ) ) ? $this->file_user_id : null;
		
		$args = array(
			'user_id'   => $user_id,
			'wpmem_dir' => 'wpmembers/',
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

		$param['subdir'] = '/' . $args['wpmem_dir'] . $args['user_dir'];
		$param['path']   = $param['basedir'] . '/' . $args['wpmem_dir'] . $args['user_dir'];
		$param['url']    = $param['baseurl'] . '/' . $args['wpmem_dir'] . $args['user_dir'];
	
		return $param;
	}

	/**
	 * Login Form Dialog.
	 *
	 * Builds the form used for login, change password, and reset password.
	 *
	 * @since 2.5.1
	 * @since 3.1.7 Added WP action login_form.
	 *
	 * @param  string $page 
	 * @param  array  $arr {
	 *     The elements needed to generate the form (login|reset password|forgotten password).
	 *
	 *     @type string $heading     Form heading text.
	 *     @type string $action      The form action (login|pwdchange|pwdreset).
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
	function login_form( $page, $arr ) {

		global $wpmem;

		// set up default wrappers
		$defaults = array(

			// wrappers
			'heading_before'  => '<legend>',
			'heading_after'   => '</legend>',
			'fieldset_before' => '<fieldset>',
			'fieldset_after'  => '</fieldset>',
			'main_div_before' => '<div id="wpmem_login">',
			'main_div_after'  => '</div>',
			'txt_before'      => '',
			'txt_after'       => '',
			'row_before'      => '',
			'row_after'       => '',
			'buttons_before'  => '<div class="button_div">',
			'buttons_after'   => '</div>',
			'link_before'     => '<div align="right" class="link-text">',
			'link_after'      => '</div>',

			// classes & ids
			'form_id'         => '',
			'form_class'      => 'form',
			'button_id'       => '',
			'button_class'    => 'buttons',

			// other
			'strip_breaks'    => true,
			'wrap_inputs'     => true,
			'remember_check'  => true,
			'n'               => "\n",
			't'               => "\t",
			'redirect_to'     => ( isset( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : ( ( isset( $arr['redirect_to'] ) ) ? $arr['redirect_to'] : get_permalink() ),
			'login_form_action' => true,

		);

		/**
		 * Filter the default form arguments.
		 *
		 * This filter accepts an array of various elements to replace the form defaults. This
		 * includes default tags, labels, text, and small items including various booleans.
		 *
		 * @since 2.9.0
		 *
		 * @param array                 An array of arguments to merge with defaults. Default null.
		 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$args = apply_filters( 'wpmem_login_form_args', '', $arr['action'] );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		// Build the input rows.
		foreach ( $arr['inputs'] as $input ) {
			$label = '<label for="' . $input['tag'] . '">' . $input['name'] . '</label>';
			$field = wpmem_create_formfield( $input['tag'], $input['type'], '', '', $input['class'] );
			$field_before = ( $args['wrap_inputs'] ) ? '<div class="' . $input['div'] . '">' : '';
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
		 *
		 * @param array  $rows          An array containing the form rows.
		 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$rows = apply_filters( 'wpmem_login_form_rows', $rows, $arr['action'] );

		// Put the rows from the array into $form.
		$form = '';
		foreach ( $rows as $row_item ) {
			$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
			$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
			$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
			$form.= $row;
		}

		// Handle outside elements added to the login form (currently ONLY for login).
		if ( 'login' == $arr['action'] && $args['login_form_action'] ) {
			ob_start();
			/** This action is documented in wp-login.php */
			do_action( 'login_form' );
			$add_to_form = ob_get_contents();
			ob_end_clean();
			$form.= $add_to_form;
		}

		// Build hidden fields, filter, and add to the form.
		$hidden = wpmem_create_formfield( 'redirect_to', 'hidden', $args['redirect_to'] ) . $args['n'];
		$hidden = $hidden . wpmem_create_formfield( 'a', 'hidden', $arr['action'] ) . $args['n'];
		$hidden = ( $arr['action'] != 'login' ) ? $hidden . wpmem_create_formfield( 'formsubmit', 'hidden', '1' ) : $hidden;

		/**
		 * Filter the hidden field HTML.
		 *
		 * @since 2.9.0
		 *
		 * @param string $hidden        The generated HTML of hidden fields.
		 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = $form . apply_filters( 'wpmem_login_hidden_fields', $hidden, $arr['action'] );

		// Build the buttons, filter, and add to the form.
		if ( $arr['action'] == 'login' ) {
			$args['remember_check'] = ( $args['remember_check'] ) ? $args['t'] . wpmem_create_formfield( 'rememberme', 'checkbox', 'forever' ) . '&nbsp;' . '<label for="rememberme">' . $wpmem->get_text( 'remember_me' ) . '</label>&nbsp;&nbsp;' . $args['n'] : '';
			$buttons =  $args['remember_check'] . $args['t'] . '<input type="submit" name="Submit" value="' . $arr['button_text'] . '" class="' . $args['button_class'] . '" />' . $args['n'];
		} else {
			$buttons = '<input type="submit" name="Submit" value="' . $arr['button_text'] . '" class="' . $args['button_class'] . '" />' . $args['n'];
		}

		/**
		 * Filter the HTML for form buttons.
		 *
		 * The string includes the buttons, as well as the before/after wrapper elements.
		 *
		 * @since 2.9.0
		 *
		 * @param string $buttons        The generated HTML of the form buttons.
		 * @param string $arr['action']  The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = $form . apply_filters( 'wpmem_login_form_buttons', $args['buttons_before'] . $args['n'] . $buttons . $args['buttons_after'] . $args['n'], $arr['action'] );

		$links_array = array(
			'forgot' => array(
				'link' => add_query_arg( 'a', 'pwdreset', $wpmem->user_pages['profile'] ),
				'page' => 'profile',
				'action' => 'login',
			),
			'register' => array( 
				'link' => $wpmem->user_pages['register'],
				'page' => 'register',
				'action' => 'login',
			),
			'username' => array(
				'link' => add_query_arg( 'a', 'getusername', $wpmem->user_pages['profile'] ),
				'page' => 'profile',
				'action' => 'pwdreset',
			),
		);
		foreach ( $links_array as $key => $value ) {
			if ( ( $wpmem->user_pages[ $value['page'] ] || 'members' == $page ) && $value['action'] == $arr['action'] ) {
				/**
				 * Filters register, forgot password, and forgot username links.
				 *
				 * @since 2.8.0
				 * @since 3.1.7 Combined all to a single process.
				 *
				 * @param string The raw link.
				 */
				$link = apply_filters( "wpmem_{$key}_link", $value['link'] );
				$str  = $wpmem->get_text( "{$key}_link_before" ) . '<a href="' . $link . '">' . $wpmem->get_text( "{$key}_link" ) . '</a>';
				/**
				 * Filters the register, forgot password, and forgot username links HTML.
				 *
				 * @since 2.9.0
				 * @since 3.0.9 Added $link parameter.
				 * @since 3.1.7 Combined all to a single process.
				 *
				 * @param string $str  The link HTML.
				 * @param string $link The link.
				 */
				$form = $form . $args['link_before'] . apply_filters( "wpmem_{$key}_link_str", $str, $link ) . $args['link_after'] . $args['n'];
			}
		}

		// Apply the heading.
		$form = $args['heading_before'] . $arr['heading'] . $args['heading_after'] . $args['n'] . $form;

		// Apply fieldset wrapper.
		$form = $args['fieldset_before'] . $args['n'] . $form . $args['fieldset_after'] . $args['n'];

		// Apply form wrapper.
		$form = '<form action="' . get_permalink() . '" method="POST" id="' . $args['form_id'] . '" class="' . $args['form_class'] . '">' . $args['n'] . $form . '</form>';

		// Apply anchor.
		$form = '<a name="' . $arr['action'] . '"></a>' . $args['n'] . $form;

		// Apply main wrapper.
		$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'];

		// Apply wpmem_txt wrapper.
		$form = $args['txt_before'] . $form . $args['txt_after'];

		// Remove line breaks.
		$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

		/**
		 * Filter the generated HTML of the entire form.
		 *
		 * @since 2.7.4
		 *
		 * @param string $form          The HTML of the final generated form.
		 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = apply_filters( 'wpmem_login_form', $form, $arr['action'] );

		/**
		 * Filter before the form.
		 *
		 * This rarely used filter allows you to stick any string onto the front of
		 * the generated form.
		 *
		 * @since 2.7.4
		 *
		 * @param string $str           The HTML to add before the form. Default null.
		 * @param string $arr['action'] The action being performed by the form. login|pwdreset|pwdchange|getusername.
		 */
		$form = apply_filters( 'wpmem_login_form_before', '', $arr['action'] ) . $form;

		return $form;
	} // End wpmem_login_form.

	/**
	 * Registration Form Dialog.
	 *
	 * Outputs the form for new user registration and existing user edits.
	 *
	 * @since 2.5.1
	 *
	 * @global object $wpmem        The WP_Members object.
	 * @global string $wpmem_regchk Used to determine if the form is in an error state.
	 * @global array  $userdata     Used to get the user's registration data if they are logged in (user profile edit).
	 * @param  string $tag          (optional) Toggles between new registration ('new') and user profile edit ('edit').
	 * @param  string $heading      (optional) The heading text for the form, null (default) for new registration.
	 * @return string $form         The HTML for the entire form as a string.
	 */
	function register_form( $tag = 'new', $heading = '', $redirect_to = null ) {

		global $wpmem, $wpmem_regchk, $userdata; 

		// Set up default wrappers.
		$defaults = array(

			// Wrappers.
			'heading_before'   => '<legend>',
			'heading_after'    => '</legend>',
			'fieldset_before'  => '<fieldset>',
			'fieldset_after'   => '</fieldset>',
			'main_div_before'  => '<div id="wpmem_reg">',
			'main_div_after'   => '</div>',
			'txt_before'       => '',
			'txt_after'        => '',
			'row_before'       => '',
			'row_after'        => '',
			'buttons_before'   => '<div class="button_div">',
			'buttons_after'    => '</div>',

			// Classes & ids.
			'form_id'          => '',
			'form_class'       => 'form',
			'button_id'        => '',
			'button_class'     => 'buttons',

			// Required field tags and text.
			'req_mark'         => $wpmem->get_text( 'register_req_mark' ),
			'req_label'        => $wpmem->get_text( 'register_required' ),
			'req_label_before' => '<div class="req-text">',
			'req_label_after'  => '</div>',

			// Buttons.
			'show_clear_form'  => false,
			'clear_form'       => $wpmem->get_text( 'register_clear' ),
			'submit_register'  => $wpmem->get_text( 'register_submit' ),
			'submit_update'    => $wpmem->get_text( 'profile_submit' ),

			// Other.
			'strip_breaks'     => true,
			'use_nonce'        => false,
			'wrap_inputs'      => true,
			'n'                => "\n",
			't'                => "\t",

		);

		/**
		 * Filter the default form arguments.
		 *
		 * This filter accepts an array of various elements to replace the form defaults. This
		 * includes default tags, labels, text, and small items including various booleans.
		 *
		 * @since 2.9.0
		 *
		 * @param array        An array of arguments to merge with defaults. Default null.
		 * @param string $tag  Toggle new registration or profile update. new|edit.
		 */
		$args = apply_filters( 'wpmem_register_form_args', '', $tag );

		// Merge $args with defaults.
		$args = wp_parse_args( $args, $defaults );

		// Username is editable if new reg, otherwise user profile is not.
		if ( $tag == 'edit' ) {
			// This is the User Profile edit - username is not editable.
			$val   = $userdata->user_login;
			$label = '<label for="user_login" class="text">' . $wpmem->get_text( 'profile_username' ) . '</label>';
			$input = '<p class="noinput">' . $val . '</p>';
			$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
			$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
		} else { 
			// This is a new registration.
			$val   = ( isset( $_POST['user_login'] ) ) ? stripslashes( $_POST['user_login'] ) : '';
			$label = '<label for="user_login" class="text">' . $wpmem->get_text( 'register_username' ) . $args['req_mark'] . '</label>';
			$input = wpmem_form_field( array( 
				'name'     => 'user_login',
				'type'     => 'text',
				'value'    => $val,
				'compare'  => '',
				'required' => true,
			) );

		}

		// Add the username row to the array.
		$rows['username'] = array( 
			'meta'         => 'username',
			'type'         => 'text',
			'value'        => $val,
			'label_text'   => $wpmem->get_text( 'register_username' ),
			'row_before'   => $args['row_before'],
			'label'        => $label,
			'field_before' => ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '',
			'field'        => $input,
			'field_after'  => ( $args['wrap_inputs'] ) ? '</div>': '',
			'row_after'    => $args['row_after'],
		);

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
		$wpmem_fields = apply_filters( 'wpmem_register_fields_arr', wpmem_fields( $tag ), $tag );

		$hidden = '';

		// Loop through the remaining fields.
		foreach ( $wpmem_fields as $meta_key => $field ) {

			// Start with a clean row.
			$val = ''; $label = ''; $input = ''; $field_before = ''; $field_after = '';

			// Skips user selected passwords for profile update.
			$pass_arr = array( 'password', 'confirm_password', 'password_confirm' );
			$do_row = ( $tag == 'edit' && in_array( $meta_key, $pass_arr ) ) ? false : true;

			// Skips tos, makes tos field hidden on user edit page, unless they haven't got a value for tos.
			if ( $meta_key == 'tos' && $tag == 'edit' && ( get_user_meta( $userdata->ID, 'tos', true ) ) ) { 
				$do_row = false; 
				$hidden_tos = wpmem_form_field( array(
					'name'  => $meta_key, 
					'type'  => 'hidden', 
					'value' => get_user_meta( $userdata->ID, 'tos', true )
				) );
			}

			// Handle hidden fields
			if ( 'hidden' == $field['type'] ) {
				$do_row = false;
				$hidden.= wpmem_form_field( array( 
					'name'     => $meta_key,
					'type'     => $field['type'],
					'value'    => $field['value'],
					'compare'  => $valtochk,
					//'class'    => ( $class ) ? $class : 'textbox',
					'required' => $field['required'],
				) );
			}

			// If the field is set to display and we aren't skipping, construct the row.
			if ( $do_row && $field['register'] ) {

				// Label for all but TOS.
				if ( $meta_key != 'tos' ) {

					$class = ( $field['type'] == 'password' || $field['type'] == 'email' || $field['type'] == 'url' ) ? 'text' : $field['type'];

				//	$label = '<label for="' . $meta_key . '" class="' . $class . '">' . __( $field['label'], 'wp-members' );
				//	$label = ( $field['required'] ) ? $label . $args['req_mark'] : $label;
				//	$label = $label . '</label>';
					
					$label = wpmem_form_label( array(
						'meta_key' => $meta_key, 
						'label'    => __( $field['label'], 'wp-members' ), 
						'type'     => $field['type'], 
						'class'    => $class, 
						'required' => $field['required'], 
						'req_mark' => $args['req_mark'] 
					) );

				} 

				// Gets the field value for both edit profile and submitted reg w/ error.
				if ( ( $tag == 'edit' ) && ( $wpmem_regchk != 'updaterr' ) ) { // @todo Should this use $wpmem->regchk? This is the last remaining use of $wpmem_regchk in this function.

					switch ( $meta_key ) {
						case( 'description' ):
							$val = htmlspecialchars( get_user_meta( $userdata->ID, 'description', 'true' ) );
							break;

						case 'user_email':
						case 'confirm_email':
							$val = sanitize_email( $userdata->user_email );
							break;

						case 'user_url':
							$val = esc_url( $userdata->user_url );
							break;

						case 'display_name':
							$val = sanitize_text_field( $userdata->display_name );
							break; 

						default:
							$val = sanitize_text_field( get_user_meta( $userdata->ID, $meta_key, 'true' ) );
							break;
					}

				} else {
					if ( 'file' == $field['type'] ) {
						$val = ( isset( $_FILES[ $meta_key ]['name'] ) ) ? $_FILES[ $meta_key ]['name'] : '' ;
					} else {
						$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : '';
					}
				}

				// Does the tos field.
				if ( $meta_key == 'tos' ) {

					$val = ( isset( $_POST[ $meta_key ] ) ) ? $_POST[ $meta_key ] : ''; 

					// Should be checked by default? and only if form hasn't been submitted.
					$val   = ( ! $_POST && $field['checked_default'] ) ? $field['checked_value'] : $val;
					$input = wpmem_form_field( array(
						'name'     => $meta_key, 
						'type'     => $field['type'], 
						'value'    => $field['checked_value'], 
						'compare'  => $val 
					) );
					$input = ( $field['required'] ) ? $input . $args['req_mark'] : $input;

					// Determine if TOS is a WP page or not.
					$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
					if ( has_shortcode( $tos_content, 'wp-members' ) || has_shortcode( $tos_content, 'wpmem_tos' ) ) {	
						$link = do_shortcode( $tos_content );
						$tos_pop = '<a href="' . $link . '" target="_blank">';
					} else { 
						$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WPMEM_DIR . "/wp-members-tos.php','mywindow');\">";
					}

					/**
					 * Filter the TOS link text.
					 *
					 * @since 2.7.5
					 *
					 * @param string       The link text.
					 * @param string $tag  Toggle new registration or profile update. new|edit.
					 */
					$input.= apply_filters( 'wpmem_tos_link_txt', sprintf( $wpmem->get_text( 'register_tos' ), $tos_pop, '</a>' ), $tag );

					// In previous versions, the div class would end up being the same as the row before.
					$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '';
					$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';

				} else {

					// For checkboxes.
					if ( $field['type'] == 'checkbox' ) { 
						$valtochk = $val;
						$val = $field['checked_value']; 
						// if it should it be checked by default (& only if form not submitted), then override above...
						if ( $field['checked_default'] && ( ! $_POST && $tag != 'edit' ) ) { $val = $valtochk = $field['checked_value']; }
					}

					// For dropdown select.
					if ( $field['type'] == 'select' || $field['type'] == 'radio' || $field['type'] == 'multiselect' || $field['type'] == 'multicheckbox' ) {
						$valtochk = $val;
						$val = $field['values'];
					}

					if ( ! isset( $valtochk ) ) { $valtochk = ''; }

					if ( 'edit' == $tag && ( 'file' == $field['type'] || 'image' == $field['type'] ) ) {

						$attachment_url = wp_get_attachment_url( $val );
						$empty_file = '<span class="description">' . __( 'None' ) . '</span>';
						if ( 'file' == $field['type'] ) {
							$input = ( $attachment_url ) ? '<a href="' . $attachment_url . '">' . get_the_title( $val ) . '</a>' : $empty_file;
						} else {
							$input = ( $attachment_url ) ? '<img src="' . $attachment_url . '">' : $empty_file;
						}
						// @todo - come up with a way to handle file updates - user profile form does not support multitype
						$input.= '<br />' . $wpmem->get_text( 'profile_upload' ) . '<br />';
						$input.= wpmem_form_field( array(
							'name'    => $meta_key, 
							'type'    => $field['type'], 
							'value'   => $val, 
							'compare' => $valtochk,
						) );

					} else {

						// For all other input types.
						//$input = wpmem_create_formfield( $meta_key, $field['type'], $val, $valtochk );
						$formfield_args = array( 
							'name'     => $meta_key,
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
						);
						if ( 'multicheckbox' == $field['type'] || 'multiselect' == $field['type'] ) {
							$formfield_args['delimiter'] = $field['delimiter'];
						}
						$input = wpmem_form_field( $formfield_args );

					}

					// Determine input wrappers.
					$field_before = ( $args['wrap_inputs'] ) ? '<div class="div_' . $class . '">' : '';
					$field_after  = ( $args['wrap_inputs'] ) ? '</div>' : '';
				}

			}

			// If the row is set to display, add the row to the form array.
			if ( $field['register'] ) {

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

		// If captcha is Really Simple CAPTCHA.
		if ( $wpmem->captcha == 2 && $tag != 'edit' ) {
			$row = wpmem_build_rs_captcha();
			$rows['captcha'] = array(
				'meta'         => '', 
				'type'         => 'text', 
				'value'        => '',
				'values'       => '',
				'label_text'   => $row['label_text'],
				'row_before'   => $args['row_before'],
				'label'        => $row['label'],
				'field_before' => ( $args['wrap_inputs'] ) ? '<div class="div_text">' : '',
				'field'        => $row['field'],
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
		foreach ( $rows as $row_item ) {
			$enctype = ( $row_item['type'] == 'file' ||  $row_item['type'] == 'image' ) ? "multipart/form-data" : $enctype;
			$row  = ( $row_item['row_before']   != '' ) ? $row_item['row_before'] . $args['n'] . $row_item['label'] . $args['n'] : $row_item['label'] . $args['n'];
			$row .= ( $row_item['field_before'] != '' ) ? $row_item['field_before'] . $args['n'] . $args['t'] . $row_item['field'] . $args['n'] . $row_item['field_after'] . $args['n'] : $row_item['field'] . $args['n'];
			$row .= ( $row_item['row_after']    != '' ) ? $row_item['row_after'] . $args['n'] : '';
			$form.= $row;
		}

		// Do recaptcha if enabled.
		if ( ( $wpmem->captcha == 1 || $wpmem->captcha == 3 ) && $tag != 'edit' ) { // don't show on edit page!

			// Get the captcha options.
			$wpmem_captcha = get_option( 'wpmembers_captcha' ); 

			// Start with a clean row.
			$row = '';
			$row = '<div class="clear"></div>';
			$row.= '<div align="right" class="captcha">' . wpmem_inc_recaptcha( $wpmem_captcha['recaptcha'] ) . '</div>';

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

		// Create hidden fields.
		$var         = ( $tag == 'edit' ) ? 'update' : 'register';
		$redirect_to = ( isset( $_REQUEST['redirect_to'] ) ) ? esc_url( $_REQUEST['redirect_to'] ) : ( ( $redirect_to ) ? $redirect_to : get_permalink() );
		$hidden     .= '<input name="a" type="hidden" value="' . $var . '" />' . $args['n'];
		$hidden     .= '<input name="wpmem_reg_page" type="hidden" value="' . get_permalink() . '" />' . $args['n'];
		if ( $redirect_to != get_permalink() ) {
			$hidden.= '<input name="redirect_to" type="hidden" value="' . $redirect_to . '" />' . $args['n'];
		}
		$hidden      = ( isset( $hidden_tos ) ) ? $hidden . $hidden_tos . $args['n'] : $hidden;

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
		$buttons = ( $args['show_clear_form'] ) ? '<input name="reset" type="reset" value="' . $args['clear_form'] . '" class="' . $args['button_class'] . '" /> ' . $args['n'] : '';
		$buttons.= '<input name="submit" type="submit" value="' . $button_text . '" class="' . $args['button_class'] . '" />' . $args['n'];

		/**
		 * Filter the HTML for form buttons.
		 *
		 * The string passed through the filter includes the buttons, as well as the HTML wrapper elements.
		 *
		 * @since 2.9.0
		 *
		 * @param string $buttons The generated HTML of the form buttons.
		 * @param string $tag     Toggle new registration or profile update. new|edit.
		 */
		$buttons = apply_filters( 'wpmem_register_form_buttons', $buttons, $tag );

		// Add the buttons to the form.
		$form.= $args['buttons_before'] . $args['n'] . $buttons . $args['buttons_after'] . $args['n'];

		// Add the required field notation to the bottom of the form.
		$form.= $args['req_label_before'] . $args['req_label'] . $args['req_label_after'];

		// Apply the heading.
		/**
		 * Filter the registration form heading.
		 *
		 * @since 2.8.2
		 *
		 * @param string $str
		 * @param string $tag Toggle new registration or profile update. new|edit.
		 */
		$heading = ( !$heading ) ? apply_filters( 'wpmem_register_heading', $wpmem->get_text( 'register_heading' ), $tag ) : $heading;
		$form = $args['heading_before'] . $heading . $args['heading_after'] . $args['n'] . $form;

		// Apply fieldset wrapper.
		$form = $args['fieldset_before'] . $args['n'] . $form . $args['n'] . $args['fieldset_after'];

		// Apply attribution if enabled.
		$form = $form . wpmem_inc_attribution();

		// Apply nonce.
		$form = ( defined( 'WPMEM_USE_NONCE' ) || $args['use_nonce'] ) ? wp_nonce_field( 'wpmem-validate-submit', 'wpmem-form-submit' ) . $args['n'] . $form : $form;

		// Apply form wrapper.
		$enctype = ( $enctype == 'multipart/form-data' ) ? ' enctype="multipart/form-data"' : '';
		$post_to = get_permalink();
		$form = '<form name="form" method="post"' . $enctype . ' action="' . $post_to . '" id="' . $args['form_id'] . '" class="' . $args['form_class'] . '">' . $args['n'] . $form . $args['n'] . '</form>';

		// Apply anchor.
		$form = '<a name="register"></a>' . $args['n'] . $form;

		// Apply main div wrapper.
		$form = $args['main_div_before'] . $args['n'] . $form . $args['n'] . $args['main_div_after'] . $args['n'];

		// Apply wpmem_txt wrapper.
		$form = $args['txt_before'] . $form . $args['txt_after'];

		// Remove line breaks if enabled for easier filtering later.
		$form = ( $args['strip_breaks'] ) ? str_replace( array( "\n", "\r", "\t" ), array( '','','' ), $form ) : $form;

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

		// Return the generated form.
		return $form;
	} // End wpmem_inc_registration.

} // End of WP_Members_Forms class.