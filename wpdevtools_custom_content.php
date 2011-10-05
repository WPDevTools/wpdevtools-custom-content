<?php
/*
Plugin Name: WPDevTools Custom Content Builder
Plugin URI: http://wpdevtools.com/plugins/custom-content/
Description: A simple custom content builder designed to work with the WPDevTools Custom Content Templates plugin.
Author: Christopher Frazier, David Sutoyo
Version: 0.1
Author URI: http://wpdevtools.com/
*/

require_once dirname( __FILE__ ) . '/lib/wpdevtools_core/wpdevtools_core.php';

/**
 * WPDT_ContentBuilder
 *
 * Core functionality for the Custom Content Builder
 * 
 * @author Christopher Frazier
 */
class WPDT_ContentBuilder extends WPDT_Core
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
		$settings = unserialize($custom['settings'][0]);

	?>
		<div class="misc-pub-section">
			<p><em>Add built-in WordPress features to your content type by checking the options below.</em></p>
		<?php echo self::display_input(array(
			'type' => 'checkbox', 
			'name' => 'builder[supports]',
			'options' => array(
				'title' => 'Title',
				'editor' => 'Visual / HTML editor',
				'excerpt' => 'Optional excerpt editor',
				'thumbnail' => 'Featured image selector'
			)), $settings['supports']); ?>
		</div>
		<div class="misc-pub-section advanced">
		<?php echo self::display_input(array(
			'type' => 'checkbox', 
			'name' => 'builder[supports]',
			'options' => array(
				'custom-fields' => 'User generated custom fields',
				'revisions' => 'Keep track of revisions',
				'page-attributes' => 'Show page attributes',
				'post-formats' => 'Show post formats'
			)), $settings['supports']); ?>
		</div>
		<div class="misc-pub-section misc-pub-section-last">
		<?php echo self::display_input(array(
			'type' => 'checkbox', 
			'name' => 'builder[supports]',
			'options' => array(
				'comments' => 'Comments',
				'trackbacks' => 'Trackbacks',
				'author' => 'Author selection'
			)), $settings['supports']); ?>
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
		$settings = unserialize($custom['settings'][0]);

	?>
		<div class="misc-pub-section misc-pub-section-last">
			<?php echo self::display_input(array('type' => 'checkbox', 'name' => 'builder[category]','options' => array('enable_categories' => 'Enable custom categories')), $settings['category']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[category][name]','label' => 'Category Name'), $settings['category']['name']); ?>
			<?php echo self::display_input(array('class' => 'advanced', 'label_order' => 'before', 'name' => 'builder[category][name_singular]','label' => 'Category Singular Form'), $settings['category']['name_singular']); ?>
			
			<?php echo self::display_input(array('type' => 'checkbox', 'name' => 'builder[tag]','options' => array('enable_tags' => 'Enable custom tags')), $settings['tag']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[tag][name]','label' => 'Tag Name'), $settings['tag']['name']); ?>
			<?php echo self::display_input(array('class' => 'advanced', 'label_order' => 'before', 'name' => 'builder[tag][name_singular]','label' => 'Tag Singular Form'), $settings['tag']['name_singular']); ?>
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
		$custom = get_post_custom($post->ID);
		$settings = unserialize($custom['settings'][0]);

	?>
		<p>The custom content plugin will attempt to automatically set your label names for you, however you can also edit them once they have been set.  If you edit the title of the content type, <strong>these fields will be reset</strong>.</p>
		<div id="labels">
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][name]','label' => 'Name'), $settings['labels']['name']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][singular_name]','label' => 'Singular Name'), $settings['labels']['singular_name']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][add_new]','label' => 'Add New'), $settings['labels']['add_new']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][all_items]','label' => 'All Item'), $settings['labels']['all_items']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][add_new_item]','label' => 'Add New Item'), $settings['labels']['add_new_item']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][edit_item]','label' => 'Edit Item'), $settings['labels']['edit_item']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][new_item]','label' => 'New Item'), $settings['labels']['new_item']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][view_item]','label' => 'View Item'), $settings['labels']['view_item']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][search_items]','label' => 'Search Items'), $settings['labels']['search_items']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][not_found]','label' => 'Not Found'), $settings['labels']['not_found']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][not_found_in_trash]','label' => 'Not Found in Trash'), $settings['labels']['not_found_in_trash']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][parent_item_colon]','label' => 'Parent Item Colon'), $settings['labels']['parent_item_colon']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[labels][menu_name]','label' => 'Menu Name'), $settings['labels']['menu_name']); ?>
			<?php echo self::display_input(array('label_order' => 'before', 'name' => 'builder[slug]','label' => 'Item Slug'), $settings['slug']); ?>
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
		$settings = unserialize($custom['settings'][0]);

	?>
		<div class="misc-pub-section  misc-pub-section-last">
			<?php echo self::display_input(array('type' => 'checkbox', 'name' => 'builder[advanced]','options' => array('advanced' => 'Show Advanced Settings')), $settings['advanced']); ?>
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
				update_post_meta($post->ID, "settings", $settings);
			}
		}

	}

}

// Set up custom content registration hooks
add_action('init', array('WPDT_ContentBuilder','register_builder_type'));
add_action('admin_print_styles', array('WPDT_ContentBuilder','enqueue_scripts'));
add_action('admin_init', array('WPDT_ContentBuilder','init_admin'));
add_action('save_post', array('WPDT_ContentBuilder','save_meta'));
