<?php
function wpmem_do_wp_register_form()
{
	$wpmem_fields = get_option( 'wpmembers_fields' );
	for( $row = 0; $row < count( $wpmem_fields ); $row++ ) {
		
		if( $wpmem_fields[$row][4] == 'y' && $wpmem_fields[$row][2] != 'user_email' ) {
		
			if( $wpmem_fields[$row][3] == 'checkbox' ) {
			
				if( $wpmem_fields[$row][2] == 'tos' ) {
					$tos_content = stripslashes( get_option( 'wpmembers_tos' ) );
					if( stristr( $tos_content, '[wp-members page="tos"' ) ) {
						
						$tos_content = " " . $tos_content;
						$ini = strpos( $tos_content, 'url="' );
						$ini += strlen( 'url="' );
						$len = strpos( $tos_content, '"]', $ini ) - $ini;
						$link = substr( $tos_content, $ini, $len );
						$tos_pop = '<a href="' . $link . '" target="_blank">';

					} else { 
						$tos_pop = "<a href=\"#\" onClick=\"window.open('" . WP_PLUGIN_URL . "/wp-members/wp-members-tos.php','mywindow');\">";
					}
					$tos = apply_filters( 'wpmem_tos_link_txt', sprintf( __( 'Please indicate that you agree to the %s TOS %s', 'wp-members' ), $tos_pop, '</a>' ) );
				
				}
				
				$label = ( $wpmem_fields[$row][2] == 'tos' ) ? $tos : $wpmem_fields[$row][2];
					
				$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
				$val = ( ! $_POST && $wpmem_fields[$row][8] == 'y' ) ? $wpmem_fields[$row][7] : $val;
			
				echo '<p class="wpmem-checkbox">' . wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );
				echo '<label for="' . $wpmem_fields[$row][2] . '">' . $label . '</label></p>';
				
			} else {
		
				echo '<p>
						<label for="' . $wpmem_fields[$row][2] . '">' . $wpmem_fields[$row][1] . '<br />';
				
				// determine the field type and generate accordingly...
				
				switch( $wpmem_fields[$row][3] ) {
				
				case( 'select' ):
					$val = ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : '';
					echo wpmem_create_formfield( $wpmem_fields[$row][2], $wpmem_fields[$row][3], $wpmem_fields[$row][7], $val );
					break;
					
				case( 'textarea' ):
					echo '<textarea name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="textarea">'; 
					echo ( isset( $_POST[ $wpmem_fields[$row][2] ] ) ) ? $_POST[ $wpmem_fields[$row][2] ] : ''; 
					echo '</textarea>';		
					break;

				default:
					echo '<input type="' . $wpmem_fields[$row][3] . '" name="' . $wpmem_fields[$row][2] . '" id="' . $wpmem_fields[$row][2] . '" class="input" value="'; echo ( $_POST ) ? $_POST[ $wpmem_fields[$row][2] ] : ''; echo '" size="25" />';
					break;
				}
					
				echo '</label>
					</p>';	
			
			}
		}
	}
}

/** End of File **/