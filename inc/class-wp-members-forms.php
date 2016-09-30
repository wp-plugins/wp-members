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
	 * }
	 * @return string $str The field returned as a string.
	 */
	function create_form_field( $args ) {
		
		$name        = $args['name'];
		$type        = $args['type'];
		$value       = maybe_unserialize( $args['value'] );
		$compare     = ( isset( $args['compare'] ) ) ? $args['compare'] : '';
		$class       = ( isset( $args['class'] ) ) ? $args['class'] : 'textbox';
		$required    = ( isset( $args['required'] ) ) ? $args['required'] : false;
		$delimiter   = ( isset( $args['delimiter'] ) ) ? $args['delimiter'] : '|';
		$placeholder = ( isset( $args['placeholder'] ) ) ? $args['placeholder'] : false;
	
		switch ( $type ) { 
			
		case "url":
		case "email":
			$class = ( $class == 'textbox' ) ? "textbox" : $class;
			$value = ( 'url' == $type ) ? esc_url( $value ) : esc_attr( wp_unslash( $value ) );
			$placeholder = ( $placeholder ) ? ' placeholder="' . $placeholder . '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . "$placeholder />";
			break;
		
		case "image":
		case "file":
			$class = ( $class == 'textbox' ) ? "file" : $class;
			$str = "<input name=\"$name\" type=\"file\" id=\"$name\" value=\"$value\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . " />";
			break;
	
		case "checkbox":
			$class = ( $class == 'textbox' ) ? "checkbox" : $class;
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"" . esc_attr( $value ) . "\"" . checked( $value, $compare, false ) . ( ( $required ) ? " required " : "" ) . " />";
			break;
	
		case "text":
			$value = stripslashes( esc_attr( $value ) );
			$placeholder = ( $placeholder ) ? ' placeholder="' . $placeholder . '"' : '';
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" value=\"$value\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . "$placeholder />";
			break;
	
		case "textarea":
			$value = stripslashes( esc_textarea( $value ) );
			$class = ( $class == 'textbox' ) ? "textarea" : $class;
			$str = "<textarea cols=\"20\" rows=\"5\" name=\"$name\" id=\"$name\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . ">$value</textarea>";
			break;
	
		case "password":
			$str = "<input name=\"$name\" type=\"$type\" id=\"$name\" class=\"$class\"" . ( ( $required ) ? " required " : "" ) . " />";
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
			$class = ( $class == 'textbox' ) ? "checkbox" : $class;
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
			$class = ( $class == 'textbox' ) ? "radio" : $class;
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
	
} // End of WP_Members_Forms class.