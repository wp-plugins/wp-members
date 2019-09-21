/**
 * WP-Members Admin JavaScript Functions
 *
 * Contains the JavaScript functions for WP-Members admin.
 * 
 * This file is part of the WP-Members plugin by Chad Butler
 * You can find out more about this plugin at https://rocketgeek.com
 * Copyright (c) 2006-2019  Chad Butler
 * WP-Members(tm) is a trademark of butlerblog.com
 *
 * @package WP-Members
 * @author Chad Butler 
 * @copyright 2006-2019
 */
 
 
 /**
  * JS for forms field table drag-and-drop.
  *
  * @since 3.1.2
  */
(function($) {
	$(document).ready(function($) {
		$("#the-list").sortable({
			items: '.list_item',
			opacity: 0.6,
			cursor: 'move',
			axis: 'y',
			update: function() {
				var order = $(this).sortable('serialize') + '&action=wpmem_do_field_reorder';
				$.post(ajaxurl, order, function(response) {
					alert(response);
				});
				$('.list_item').each(function(i) { 
					$(this).data('id', i + 1); // updates the data object
					$(this).attr('list_item', i + 1); // updates the attribute
				});
			}
		});
	});
})(jQuery);


/**
 * JS for displaying custom url for:
 * - Profile page
 * - Register page
 * - Custom stylesheet
 *
 * @since 2.9.6
 */
(function($) {
	$(document).ready(function() {
		if ($("#wpmem_logpage_select").val() == 'use_custom')
			$("#wpmem_logpage_custom").show();
		else
			$("#wpmem_logpage_custom").hide();
		if ($("#wpmem_regpage_select").val() == 'use_custom')
			$("#wpmem_regpage_custom").show();
		else
			$("#wpmem_regpage_custom").hide();
		if ($("#wpmem_mspage_select").val() == 'use_custom')
			$("#wpmem_mspage_custom").show();
		else
			$("#wpmem_mspage_custom").hide();
		if ($("#wpmem_stylesheet_select").val() == 'use_custom')
			$("#wpmem_stylesheet_custom").show();
		else
			$("#wpmem_stylesheet_custom").hide();
		$("#wpmem_logpage_select").change(function() {
			if ($("#wpmem_logpage_select").val() == 'use_custom')
				$("#wpmem_logpage_custom").show();
			else
				$("#wpmem_logpage_custom").hide();
		});
		$("#wpmem_regpage_select").change(function() {
			if ($("#wpmem_regpage_select").val() == 'use_custom')
				$("#wpmem_regpage_custom").show();
			else
				$("#wpmem_regpage_custom").hide();
		});
		$("#wpmem_mspage_select").change(function() {
			if ($("#wpmem_mspage_select").val() == 'use_custom')
				$("#wpmem_mspage_custom").show();
			else
				$("#wpmem_mspage_custom").hide();
		});
		$("#wpmem_stylesheet_select").change(function() {
			if ($("#wpmem_stylesheet_select").val() == 'use_custom')
				$("#wpmem_stylesheet_custom").show();
			else
				$("#wpmem_stylesheet_custom").hide();
		});
	});
})(jQuery);


/**
 * JS for displaying additional info for checkbox/dropdowns
 *
 * @since 2.9.6
 */
(function($) {
	$(document).ready(function() {
		$("#wpmem_allowhtml").hide();
		$("#wpmem_rows_cols").hide();
		$("#wpmem_min_max").hide();
		$("#wpmem_checkbox_info").hide();
		$("#wpmem_dropdown_info").hide();
		$("#wpmem_file_info").hide();
		$("#wpmem_delimiter_info").hide();
		$("#wpmem_hidden_info").hide();
	});
	$(document).ready(function() {
		$("#wpmem_field_type_select").change(function() {
			if ($("#wpmem_field_type_select").val() == 'text'
				|| $("#wpmem_field_type_select").val() == 'password' 
				|| $("#wpmem_field_type_select").val() == 'email' 
				|| $("#wpmem_field_type_select").val() == 'url'
				|| $("#wpmem_field_type_select").val() == 'number' 
				|| $("#wpmem_field_type_select").val() == 'date'
				|| $("#wpmem_field_type_select").val() == 'textarea' )
				$("#wpmem_placeholder").show();
			else
				$("#wpmem_placeholder").hide();
			if ($("#wpmem_field_type_select").val() == 'text'
				|| $("#wpmem_field_type_select").val() == 'password' 
				|| $("#wpmem_field_type_select").val() == 'email' 
				|| $("#wpmem_field_type_select").val() == 'url'
				|| $("#wpmem_field_type_select").val() == 'number' 
				|| $("#wpmem_field_type_select").val() == 'date' ) {
				$("#wpmem_pattern").show();
				$("#wpmem_title").show();
			} else {
				$("#wpmem_pattern").hide();
				$("#wpmem_title").hide();
			}
			if ($("#wpmem_field_type_select").val() == 'textarea' ) {
				$("#wpmem_allowhtml").show();
				$("#wpmem_rows_cols").show();
			} else {
				$("#wpmem_allowhtml").hide();
				$("#wpmem_rows_cols").hide();
			}
			if ($("#wpmem_field_type_select").val() == 'number' || $("#wpmem_field_type_select").val() == 'date' )
				$("#wpmem_min_max").show();
			else
				$("#wpmem_min_max").hide();
			if ($("#wpmem_field_type_select").val() == 'checkbox') {
				$("#wpmem_checkbox_info").show();
				$("#add_checked_value").prop('required',true);
			} else {
				$("#wpmem_checkbox_info").hide();
				$("#add_checked_value").prop('required',false);
			}
			if ( $("#wpmem_field_type_select").val() == 'select' 
				|| $("#wpmem_field_type_select").val() == 'multiselect'
				|| $("#wpmem_field_type_select").val() == 'radio'
				|| $("#wpmem_field_type_select").val() == 'multicheckbox'
			) {
				$("#wpmem_dropdown_info").show();
				$("#add_dropdown_value").prop('required',true);
			} else {
				$("#wpmem_dropdown_info").hide();
				$("#add_dropdown_value").prop('required',false);
			}
			if ( $("#wpmem_field_type_select").val() == 'multiselect' || $("#wpmem_field_type_select").val() == 'multicheckbox'
			)
				$("#wpmem_delimiter_info").show();
			else
				$("#wpmem_delimiter_info").hide();
			if ($("#wpmem_field_type_select").val() == 'file' || $("#wpmem_field_type_select").val() == 'image' )
				$("#wpmem_file_info").show();
			else
				$("#wpmem_file_info").hide();
			if ($("#wpmem_field_type_select").val() == 'hidden') {
				$("#wpmem_hidden_info").show();
				$("#add_hidden_value").prop('required',true);
			} else {
				$("#wpmem_hidden_info").hide();
				$("#add_hidden_value").prop('required',false);
			}
		});
	});
})(jQuery);

/**
 * JS for post block status meta box
 *
 * @since 3.2.1
 */
(function($) {
	$(document).ready(function() {
		$("#wpmem_block").hide();
	});
	$(document).ready(function() {
		$("#wpmem_edit_block_status").click(function() {
			$("#wpmem_block").show('normal');
			$("#wpmem_edit_block_status").hide();
		});
		$("#wpmem_cancel_block_status").click(function() {
			$("#wpmem_block").hide('normal');
			$("#wpmem_edit_block_status").show();
			$("#wpmem_post_block_status").text($("input[name=wpmem_block_original_label]").val());
			$('#wpmem_post_block_status_' + $('input[name=wpmem_block_original_value]')).val().prop("checked", true);
		});
		$("#wpmem_ok_block_status").click(function() {
			$("#wpmem_block").hide('normal');
			$("#wpmem_edit_block_status").show();
			$("#wpmem_post_block_status").text($("input[name=wpmem_block]:checked + label").text());
			if($('#wpmem_block_status_0').is(':checked')) { 
				$("#wpmem_post_icon_0").show();
				$("#wpmem_post_icon_1").hide();
				$("#wpmem_post_icon_2").hide();
			}
			if($('#wpmem_block_status_1').is(':checked')) { 
				$("#wpmem_post_icon_0").hide();
				$("#wpmem_post_icon_1").show();
				$("#wpmem_post_icon_2").hide();
			}
			if($('#wpmem_block_status_2').is(':checked')) { 
				$("#wpmem_post_icon_0").hide();
				$("#wpmem_post_icon_1").hide();
				$("#wpmem_post_icon_2").show();
			}
		});
	});
})(jQuery);