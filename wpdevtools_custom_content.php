<?php
/*
Plugin Name: WPDevTools Custom Content Builder
Plugin URI: http://wpdevtools.com/plugins/custom-content/
Description: A simple custom content builder designed to work with the WPDevTools Custom Content Templates plugin.
Author: Christopher Frazier, David Sutoyo
Version: 0.1
Author URI: http://wpdevtools.com/
*/


/**
 * WPDT_CustomContent
 *
 * Core functionality for the Custom Content Builder
 * 
 * @author Christopher Frazier
 */
class WPDT_ContentBuilder
{

	/**
	 * Set of default settings for new content types
	 *
	 * @var string
	 **/
	var $default_settings = array(
		
	);

	/**
	 * Registers the Content Builder custom content type
	 *
	 * @author Christopher Frazier
	 */
	public function register_builder_type()
	{
		$labels = array(
			'name' => _x('Custom Content Types', 'post type general name'),
			'singular_name' => _x('Custom Content Type', 'post type singular name'),
			'menu_name' => 'Content Types',
			'add_new' => _x('Add New', 'portfolio item'),
			'add_new_item' => __('Add New Content Type'),
			'edit_item' => __('Edit Content Type'),
			'new_item' => __('New Content Type'),
			'view_item' => __('View Content Type'),
			'search_items' => __('Search Content Types'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
		);

		$args = array(
			'description' => 'A specialized, customizable content type',
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'query_var' => false,
			'has_archive' => false,
			'show_in_nav_menus' => false,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => 65,
			'menu_icon' => plugins_url( 'images/application-form.png' , __FILE__ ),
			'supports' => array('title')
		  ); 

		register_post_type( 'content_builder' , $args );
	}


	/**
	 * Adds a meta box for the Additional Template Settings section of the template admin
	 *
	 * @author Christopher Frazier
	 */
	public function init_admin()
	{

		add_meta_box(
		  	"builder_support", 
		  	"WordPress Features", 
		  	array('WPDT_ContentBuilder','show_support_meta'), 
		  	"content_builder", 
		  	"side", 
		  	"core"
		);

		add_meta_box(
		  	"builder_taxonomy", 
		  	"Categories and Tags", 
		  	array('WPDT_ContentBuilder','show_taxonomy_meta'), 
		  	"content_builder", 
		  	"side", 
		  	"core"
		);

		add_meta_box(
		  	"builder_advanced", 
		  	"Advanced Settings", 
		  	array('WPDT_ContentBuilder','show_advanced_meta'), 
		  	"content_builder", 
		  	"side", 
		  	"low"
		);

		add_meta_box(
		  	"builder_template", 
		  	"Custom Content Templates", 
		  	array('WPDT_ContentBuilder','show_template_meta'), 
		  	"content_builder", 
		  	"normal", 
		  	"core"
		);

		add_meta_box(
		  	"builder_labels", 
		  	"Labels", 
		  	array('WPDT_ContentBuilder','show_label_meta'), 
		  	"content_builder", 
		  	"normal", 
		  	"core"
		);

		add_meta_box(
		  	"builder_content", 
		  	"Custom Field Builder", 
		  	array('WPDT_ContentBuilder','show_custom_fields_meta'), 
		  	"content_builder", 
		  	"normal", 
		  	"core"
		);
		
		add_contextual_help( 'content_builder', '<p>For complete support for this and all WPDevTools plugins, please visit our support website.</p>
		<h4>Available Support Options</h4>
		<ul>
			<li><a href="#">Plugin Information</a></li>
			<li><a href="#">Documentation</a></li>
			<li><a href="#">Tips and Tricks</a></li>
			<li><a href="#">Support Forums</a></li>
			<li><a href="http://wpdevtools.com">WPDevTools Website</a></li>
		</ul>');

	}


	/**
	 * Calls CSS and JS scripts for admin tools
	 *
	 * @author Christopher Frazier
	 */
	public function enqueue_scripts () 
	{
		global $post;
		global $pagenow;
		
		if (is_admin() && (($pagenow == 'post.php' && get_post_type($post->ID) == 'content_builder') || ($pagenow == 'post-new.php' && $_REQUEST['post_type'] == 'content_builder'))) {
			// Set up the code coloring for the template admin
			wp_enqueue_script("codemirror", plugins_url('/lib/codemirror/lib/codemirror.js', __FILE__));
			wp_enqueue_style("codemirror", plugins_url('/lib/codemirror/lib/codemirror.css', __FILE__));

			wp_enqueue_script("codemirror-mode-javascript", plugins_url('/lib/codemirror/mode/javascript.js', __FILE__));
			wp_enqueue_script("codemirror-mode-css", plugins_url('/lib/codemirror/mode/css.js', __FILE__));
			wp_enqueue_style("codemirror-theme-default", plugins_url('/lib/codemirror/theme/default.css', __FILE__));

			wp_enqueue_script("jquery");
			wp_enqueue_script("jquery-tablednd", plugins_url('/lib/tablednd/jquery.tablednd.js', __FILE__));
			
			// Set up the inflection javascript code
			wp_enqueue_script("inflection-js", plugins_url('/lib/inflection-js/inflection.js', __FILE__));

			// Set up the main admin JS and CSS
			wp_enqueue_script("custom_content_admin", plugins_url('/admin.js', __FILE__), false, array('jquery'));
			wp_enqueue_style("custom_content_admin", plugins_url('/admin.css', __FILE__));
		} else {

			// Just in case the template doesn't actually use jQuery
			wp_enqueue_script("jquery");

		}
	}


	/**
	 * Plugin Supports Meta Box
	 *
	 * @author Christopher Frazier
	 */
	public function show_support_meta()
	{
		global $post;
		$custom = get_post_custom($post->ID);

	?>
		<div class="misc-pub-section">
			<p><em>Add built-in WordPress features to your content type by checking the options below.</em></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_title" value="title"/> <label for="supports_title">Title</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_editor" value="editor"/> <label for="supports_editor">Visual / HTML editor</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_excerpt" value="excerpt"/> <label for="supports_excerpt">Optional excerpt editor</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_thumbnail" value="thumbnail"/> <label for="supports_thumbnail">Featured image selector</label></p>
		</div>
		<div class="misc-pub-section advanced">
			<p><input type="checkbox" name="builder[supports][]" id="supports_custom_fields" value="custom-fields"/> <label for="supports_custom_fields">Custom fields</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_revisions" value="revisions"/> <label for="supports_revisions">Keep track of revisions</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_page_attributes" value="page-attributes"/> <label for="supports_page_attributes">Show page attributes</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_post_formats" value="post-formats"/> <label for="supports_post_formats">Show post formats</label></p>
		</div>
		<div class="misc-pub-section misc-pub-section-last">
			<p><input type="checkbox" name="builder[supports][]" id="supports_comments" value="comments"/> <label for="supports_comments">Comments</label></p>
			<p class="advanced"><input type="checkbox" name="builder[supports][]" id="supports_trackbacks" value="trackbacks"/> <label for="supports_trackbacks">Trackbacks</label></p>
			<p><input type="checkbox" name="builder[supports][]" id="supports_author" value="author"/> <label for="supports_author">Author selection</label></p>
		</div>
	<?php
	
	}

	/**
	 * Plugin Supports Meta Box
	 *
	 * @author Christopher Frazier
	 */
	public function show_taxonomy_meta()
	{
		global $post;
		$custom = get_post_custom($post->ID);

	?>
		<div class="misc-pub-section misc-pub-section-last">
			<p><input type="checkbox" name="builder[category][enable]" id="supports_category" value="true"/> <label for="supports_category">Enable custom categories</label></p>
			<div id="category_options" class="group_disabled">
				<p><label for="category_name">Category Name</label><br><input type="text" id="category_name" name="builder[category][name]" value="" /></p>
				<p class="advanced"><label for="category_singular_name">Category Singular Form</label><br><input type="text" id="category_singular_name" name="builder[category][name_singular]" value="" /></p>
			</div>
			<p><input type="checkbox" name="builder[tag][enable]" id="supports_tag" value="true"/> <label for="supports_tag">Enable custom tags</label></p>
			<div id="tag_options" class="group_disabled">
				<p><label for="tag_name">Tag Name</label><br><input type="text" id="tag_name" name="builder[tag][name]" value="" /></p>
				<p class="advanced"><label for="tag_singular_name">Tag Singular Form</label><br><input type="text" id="tag_singular_name" name="builder[tag][name_singular]" value="" /></p>
			</div>
		</div>
	<?php
	
	}
	

	/**
	 * Template Selector Meta Box
	 *
	 * @author Christopher Frazier
	 */
	public function show_template_meta()
	{
	?>

		<p>Did you know that custom content types need additional template support to display properly?  If your theme doesn't support your custom content type, <strong>your custom fields won't show up</strong>.  With WPDevTools Custom Content Template Plugin you can create templates directly through WordPress without having to write any code or edit template files.  Visit our site to find out more!</p>
		<p style="text-align: right; margin: 1em 0em;"><a href="#" class="button-secondary">Download the Custom Templates Plugin</a></p>

	<?php
	}
	

	public function show_label_meta()
	{
		global $post;

	?>
		<p>The custom content plugin will attempt to automatically set your label names for you, however you can also edit them once they have been set.  If you edit the title of the content type, <strong>these fields will be reset</strong>.</p>
		<div id="labels">
			<p><label for="label_name">Name</label><br><input type="text" id="label_name" name="builder[labels][name]" value="" /></p>
			<p><label for="label_singular_name">Singular Name</label><br><input type="text" id="label_singular_name" name="builder[labels][singular_name]" value="" /></p>
			<p><label for="label_add_new">Add New</label><br><input type="text" id="label_add_new" name="builder[labels][add_new]" value="" /></p>
			<p><label for="label_all_items">All Item</label><br><input type="text" id="label_all_items" name="builder[labels][all_items]" value="" /></p>
			<p><label for="label_add_new_item">Add New Item</label><br><input type="text" id="label_add_new_item" name="builder[labels][add_new_item]" value="" /></p>
			<p><label for="label_edit_item">Edit Item</label><br><input type="text" id="label_edit_item" name="builder[labels][edit_item]" value="" /></p>
			<p><label for="label_new_item">New Item</label><br><input type="text" id="label_new_item" name="builder[labels][new_item]" value="" /></p>
			<p><label for="label_view_item">View Item</label><br><input type="text" id="label_view_item" name="builder[labels][view_item]" value="" /></p>
			<p><label for="label_search_items">Search Items</label><br><input type="text" id="label_search_items" name="builder[labels][search_items]" value="" /></p>
			<p><label for="label_not_found">Not Found</label><br><input type="text" id="label_not_found" name="builder[labels][not_found]" value="" /></p>
			<p><label for="label_not_found_in_trash">Not Found in Trash</label><br><input type="text" id="label_not_found_in_trash" name="builder[labels][not_found_in_trash]" value="" /></p>
			<p><label for="label_parent_item_colon">Parent Item Colon</label><br><input type="text" id="label_parent_item_colon" name="builder[labels][parent_item_colon]" value="" /></p>
			<p><label for="label_menu_name">Menu Name</label><br><input type="text" id="label_menu_name" name="builder[labels][menu_name]" value="" /></p>
			<p><label for="label_slug">Item Slug</label><br><input type="text" id="label_slug" name="builder[slug]" value="" /></p>
		</div>
	
	<?php
	}


	/**
	 * Advanced Setting Meta Box
	 *
	 * @author Christopher Frazier
	 */
	public function show_advanced_meta()
	{

		global $post;
		$custom = get_post_custom($post->ID);

	?>
		<div class="misc-pub-section  misc-pub-section-last">
			<p><input type="checkbox" name="builder[advanced]" id="show_advanced" value="Show Advanced"/> <label for="show_advanced">Show Advanced Settings</label></p>
		</div>
	<?php
	
	}
	

	/**
	 * Custom Field Builder Meta Box
	 *
	 * @author Christopher Frazier
	 */
	public function show_custom_fields_meta()
	{

		global $post;
		$custom = get_post_custom($post->ID);
	?>

		<div id="custom_fields">
			<table class="widefat">
				<thead>
					<tr>
						<th width="5%"><img src="<?php echo plugins_url('/images/arrow-move.png', __FILE__) ?>" /></th>
						<th width="45%" class="field_name">Field Name</th>
						<th width="40%" class="field_type">Type</th>
						<th width="10%" class="field_required">Required</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>&nbsp;</th>
						<th>Field Name</th>
						<th>Type</th>
						<th align="center">Required</th>
					</tr>
				</tfoot>
				
				<tbody class="builder_body">
					<tr id="builder-field-0" class="builder_field edit">
						<td class="drag_handle">&nbsp;</td>
						<td colspan="4">
							<table class="field_main">
								<tr class="field_main_noedit">
									<td width="45%" class="field_name">
										<p class="field_name_title">E-mail Address</p>
										<p class="controls"><span><a href="#" class="edit_link">Edit</a> | <a href="#" class="delete_link">Delete</a></span>&nbsp;</p>
									</td>
									<td width="40%" class="field_type"><p>Checkboxes</p></td>
									<td width="10%" class="field_required"><p>Yes</p></td>
								</tr>
								<tr class="field_main_edit">
									<td width="45%" class="field_name"><p><input type="text" name="builder[fields][0][name]" value="" /></p></td>
									<td width="40%" class="field_type"><p>
										<select name="builder[fields][0][type]">
											<optgroup label="Text Editors">
												<option value="text">Single line of text</option>
												<option value="textarea">Multiple lines of text</option>
												<option value="code">Code editor</option>
											</optgroup>
											<optgroup label="Multiple Choice">
												<option value="checkbox">Checkboxes</option>
												<option value="radio">Radio Buttons</option>
												<option value="select">Dropdown</option>
												<option value="multiselect">Multiple Select</option>
											</optgroup>
											<optgroup label="Advanced">
												<option value="image">Image selector</option>
												<option value="color">Color selector</option>
												<option value="date">Date entry</option>
											</optgroup>
										</select>
									</p></td>
									<td width="10%" class="field_required"><p><input type="checkbox" name="builder[fields][0][required]" value="true"/></p></td>
								</tr>
							</table>
							<div class="field_options">
								<p class="button_update_field"><a href="#" class="button-secondary" title="Update Custom Field">Update Custom Field</a></p>
							</div>
						</td>
					</tr>
					<tr id="builder-field-none">
						<td colspan="4">
							<p>Add specialized information to your custom content type by clicking below.</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="button_add_field"><a href="#" class="button-secondary" title="Add a Custom Field">Add a Custom Field</a></p>
		</div>
	<?php
	}


	/**
	 * Saves the fields for the Custom Content Builder
	 *
	 * @author Christopher Frazier
	 */
	public function save_meta()
	{

		global $post;
		
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {

			// Do nothing?

		} else {

			if (get_post_type($post->ID) == 'content_builder') {
				$settings = $_POST['builder'];
				echo '<pre>'; print_r ($_POST['builder']); echo '</pre>';
				die();
			}
		}

	}

}

// Set up custom content registration hooks
add_action('init', array('WPDT_ContentBuilder','register_builder_type'));
add_action('admin_print_styles', array('WPDT_ContentBuilder','enqueue_scripts'));
add_action('admin_init', array('WPDT_ContentBuilder','init_admin'));
add_action('save_post', array('WPDT_ContentBuilder','save_meta'));
