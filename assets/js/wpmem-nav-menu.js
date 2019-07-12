;(function($) {

	$('.wpmem_logged_in_out_field').each(function(i){ 

		var $field = $(this);

		var id = $field.find('input.nav-menu-id').val();

		// if set to display by role (aka is null) then show the roles list, otherwise hide
		if( $field.find('input.wpmem-logged-in-out:checked').val() === 'in' ){
			$field.next('.wpmem_nav_menu_field').show();
		} else {
			$field.next('.wpmem_nav_menu_field').hide();
		}
	});

	// on in/out/role change, hide/show the roles
	$('#menu-to-edit').on('change', 'input.wpmem-logged-in-out', function() {
		if( $(this).val() === 'in' ){
			$(this).parentsUntil('.nav_menu_logged_in_out').next('.wpmem_nav_menu_field').slideDown();
		} else {
			$(this).parentsUntil('.nav_menu_logged_in_out').next('.wpmem_nav_menu_field').slideUp();
		}
	});


})(jQuery);