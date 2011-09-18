jQuery(document).ready(function() { WPDT_CustomContent.init(); });

var WPDT_CustomContent = {

	field_count : 0,
	
	init : function () {
		// Set up the advanced view and toggle switch
		WPDT_CustomContent.toggle_advanced();
		jQuery('#show_advanced').click(function () { WPDT_CustomContent.toggle_advanced(); });

		jQuery('#supports_category').click(function () { jQuery('#category_options').fadeToggle('fast'); });
		jQuery('#supports_tag').click(function () { jQuery('#tag_options').fadeToggle('fast'); });
		jQuery('#custom_fields .button_add_field').click(function () { WPDT_CustomContent.clone_row(); return false; });
		
		jQuery('#custom_fields tbody.builder_body > tr').each(function () { WPDT_CustomContent.init_row_scripts(jQuery(this)); });
		
		jQuery('#title').change(function () { WPDT_CustomContent.set_labels(jQuery(this).val()); });
	},
	
	toggle_advanced : function () {
		if (jQuery('#show_advanced:checked').length == 1) {
			jQuery('#post').addClass('advanced'); 
		} else {
			jQuery('#post').removeClass('advanced'); 
		}
	},
	
	set_labels : function (name) {

		singluar = name.singularize().titleize();
		plural = name.pluralize().titleize();
		
		jQuery('#label_name').val(plural);
		jQuery('#label_singular_name').val(singluar);
		jQuery('#label_menu_name').val(plural);
		jQuery('#label_add_new').val('Add New');
		jQuery('#label_all_items').val('All ' + plural);
		jQuery('#label_add_new_item').val('Add New ' + singluar);
		jQuery('#label_edit_item').val('Edit ' + singluar);
		jQuery('#label_new_item').val('New ' + singluar);
		jQuery('#label_view_item').val('View ' + singluar);
		jQuery('#label_search_items').val('Search ' + plural);
		jQuery('#label_not_found').val('No ' + plural.humanize(true) + ' found');
		jQuery('#label_not_found_in_trash').val('No ' + plural.humanize(true) + ' found in trash');
		jQuery('#label_parent_item_colon').val('');
		jQuery('#label_slug').val(singluar.underscore());
	},
	
	clone_row : function () {
		WPDT_CustomContent.field_count++;
		
		jQuery('#builder-field-none').addClass('has_fields');

		// Clone a new row and hide it
		new_row = jQuery('#builder-field-0').clone().hide();
		
		// Set the new ID and field name values
		new_row.attr('id', 'builder-field-' + WPDT_CustomContent.field_count);
		
		row_html = new_row.html().replace(/builder\[fields\]\[0\]/g, "builder[fields][" + WPDT_CustomContent.field_count + "]");
		new_row.html(row_html);
		
		// Add the row scripts
		WPDT_CustomContent.init_row_scripts(new_row);
		
		// Append the new row
		new_row.appendTo('#custom_fields tbody.builder_body').fadeIn();

		jQuery('#custom_fields tbody.builder_body').tableDnD({
			onDragClass : "alternate",
			dragHandle : "drag_handle"
		});
	},
	
	init_row_scripts : function (row) {

		// Set the hover actions
		row.mouseover(function () {
			jQuery(this).toggleClass('hover');
		}).mouseout(function () {
			jQuery(this).toggleClass('hover');
		});

		row.find('.button_update_field a').click(function () {
			WPDT_CustomContent.update_row(jQuery(this).parents(".builder_field"));
			return false;
		});
	
		row.find('.edit_link').click(function () {
			jQuery(this).parents(".builder_field").removeClass('noedit').addClass('edit');
			return false;
		});		

		row.find('.delete_link').click(function () {
			jQuery(this).parents(".builder_field").fadeOut('fast', function () {
				jQuery(this).remove();
				 if (jQuery('#custom_fields .builder_field').length == 1) {
				 	jQuery('#builder-field-none').removeClass('has_fields');
				 }
			});
			return false;
		});		
		
	},
	
	update_row : function (row) {
		row = jQuery(row);
		row.removeClass('edit').addClass('noedit');		
	}
}
