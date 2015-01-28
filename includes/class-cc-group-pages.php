<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC Group Pages
 * @subpackage CC Group Pages/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CC Group Pages
 * @subpackage CC Group Pages/includes
 * @author     Your Name <email@example.com>
 */
class CC_Group_Pages {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * The plugin's slug.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_slug    The string that is the plugin's slug.
	 */
	protected $plugin_slug;

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'cc-group-pages';
		$this->plugin_slug = 'pages';
		$this->version = '1.1.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// Register the custom post type
		$cpt_tax_class = new CC_Group_Pages_CPT_Tax();
		add_filter( 'bp_init', array( $cpt_tax_class, 'register_cpt') );
		// Register the custom taxonomy
		add_filter( 'bp_init', array( $cpt_tax_class, 'register_taxonomy') );

		// Add our templates to BuddyPress' template stack.
		add_filter( 'bp_get_template_stack', array( $this, 'add_template_stack'), 10, 1 );

		// add_action( 'bp_include', array( $this, 'remove_shortcode_filter_on_settings_screen' ), 11 );
		// Catch saves
		add_action( 'bp_init', array( $this, 'save_post' ) );
		// Modify permalinks so that they point to the story as persented in the origin group
		add_filter( 'post_type_link', array( $this, 'permalink_filter'), 10, 2);

		/* Filter "map_meta_caps" to let our users do things they normally can't, like upload media */
		add_action( 'bp_init', array( $this, 'add_mmc_filter') );

		/* Only allow users to see their own items in the media library uploader. */
		add_action( 'pre_get_posts', array( $this, 'show_users_own_attachments') );


 
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the dashboard.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for setting up the custom post type and taxonomy.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-cpt-tax.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cc-group-pages-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cc-group-pages-public.php';

		/**
		 * The templates file.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/cc-group-pages-public-display.php';

		
		// $this->loader = new CC_Group_Pages_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new CC_Group_Pages_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// $plugin_admin = new CC_Group_Pages_Admin( $this->get_plugin_name(), $this->get_version() );

		// add_action( 'wp_enqueue_scripts', array( $plugin_admin, 'enqueue_styles') );
		// add_action( 'wp_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts') );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		if ( $this->is_component() ) {
			$plugin_public = new CC_Group_Pages_Public( $this->get_plugin_name(), $this->get_version() );
			add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_styles') );
			// add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_scripts') );
		}

		if ( $this->is_post_edit() ) {
			$plugin_public = new CC_Group_Pages_Public( $this->get_plugin_name(), $this->get_version() );
			// add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_edit_scripts') );
			add_action( 'wp_enqueue_scripts', array( $plugin_public, 'enqueue_edit_styles') );

		}

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The slug of the plugin is the portion of the uri after the group name.
	 *
	 * @since     1.0.0
	 * @return    string    The slug used.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the customized tab name.
	 *
	 * @since     1.0.0
	 * @return    string    The text of the tab name.
	 */
	public function get_tab_label( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();
		$label = groups_get_groupmeta( $group_id, 'ccgp_tab_label' );
		$label = ! empty( $label ) ? $label : 'Pages' ;
		
		return apply_filters( 'ccgp_get_tab_label', $label);
	}

	/**
	 * Is this plugin activated for this group?
	 *
	 * @since     1.0.0
	 * @return    bool    True if activated.
	 */
	public function get_enabled_status( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();
		$is_enabled = (bool) groups_get_groupmeta( $group_id, "ccgp_is_enabled" );
		
		return apply_filters( "ccgp_is_enabled", $is_enabled, $group_id);
	}

	/**
	 * Should we show the tab?
	 *
	 * @since     1.0.0
	 * @return    string   	access level if activated and content to display. 
	 *			  			noone|member|mod|admin|anyone
	 */
	public function get_tab_visibility( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();

		$setting = 'noone';

		// I think these should always be "for members only" pages, to diff from narratives and bp-docs.
		if ( $this->get_enabled_status( $group_id ) ) {
			$setting = 'member';
		}

		// // Check for the setting saved as groupmeta.
		// $setting = groups_get_groupmeta( $group_id, 'ccgp_tab_visibility' );
		// $setting = ! empty( $setting ) ? $setting : 'member';

		// // Make sure that the setting is a real setting.
		// $allowed_settings = array('anyone', 'loggedin', 'member', 'mod', 'admin', 'noone');
		// if ( ! in_array( $setting, $allowed_settings) ) {
		// 	$setting = 'member';
		// }

		//@TODO: Don't allow "anyone" or "loggedin" to be chosen for hidden groups.

		return apply_filters('ccgp_tab_visibility', $setting);
	}

	/**
	 * Create or update the taxonomy term specific to group.
	 * 
 	 * @since     1.0.0
	 * @return integer
	 */
	public function update_group_term( $group_id = false ) {
		$group_id = $group_id ? $group_id : bp_get_current_group_id();

		// Create a group object, using BP Group Hierarchy or not.
		$group_object = class_exists( 'BP_Groups_Hierarchy' ) ? new BP_Groups_Hierarchy( $group_id ) : groups_get_group( array( 'group_id' => $group_id ) );

		$group_name = $group_object->name;
		$term_args['description'] = 'Group pages for ' . $group_name;

		// Check for a term for this group's parent group, set a value for the term's 'parent' arg
		// Depends on BP_Group_Hierarchy being active
		if  ( ( $parent_group_id = $group_object->vars['parent_id'] )  &&  
				( $parent_group_term = get_term_by( 'slug', $ccgp_class->create_taxonomy_slug( $parent_group_id ), 'ccgp_related_groups' ) ) 
			) {
			$term_args['parent'] = (int) $parent_group_term->term_id;
		}

		if ( $existing_term_id = $this->get_group_term_id( $group_id ) ) {
			$term_args['name'] = $group_name;
			$result = wp_update_term( $existing_term_id, 'ccgp_related_groups', $term_args );
		} else {
			$term_args['slug'] = $this->create_taxonomy_slug( $group_id );
			$result = wp_insert_term( $group_name, 'ccgp_related_groups', $term_args );
		}
		return $result;
	}

	/**
	 * Get the taxonomy term specific to group.
	 * 
	 * @since     1.0.0
	 * @return integer
	 */
	public function get_group_term_id( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();
		
		if ( $term = get_term_by( 'slug', $this->create_taxonomy_slug( $group_id ), 'ccgp_related_groups' ) ) {
			return $term->term_id;
		} else {
			return false;
		}

	}
	/**
	 * Build the taxonomy slug.
	 * 
	 * @since     1.0.0
	 * @return string
	 */
	public function create_taxonomy_slug( $group_id = false ) {
		$group_id = ( $group_id ) ?  $group_id : bp_get_current_group_id();
		return 'ccgp_related_group_' . $group_id;
	}
	/**
	 * Reverse lookup the group id from the term.
	 * 
	 * @since     1.0.0
	 * @return string
	 */
	public function get_group_id_from_post_id( $post_id = false ) {
			$towrite = PHP_EOL . 'incoming post_id: ' . print_r($post_id, TRUE);  
			$fp = fopen('ccgp-save.txt', 'a');
			fwrite($fp, $towrite);
			fclose($fp);  

		if ( ! $post_id )
			return 0;

		$term_list = wp_get_post_terms( $post_id, 'ccgp_related_groups', array("fields" => "all"));
			$towrite = PHP_EOL . 'term list: ' . print_r($term_list, TRUE);
			$fp = fopen('ccgp-save.txt', 'a');
			fwrite($fp, $towrite);
			fclose($fp);
		if ( is_wp_error( $term_list ) ) {
			return 0;
		}

		$group_id = (int) str_replace( 'ccgp_related_group_', '', $term_list[0]->slug ); 

		return $group_id;
	}

	/**
	 * Update group meta settings.
	 * 
	 * @since  1.0.0
	 * @return boolean
	 */
	public function update_groupmeta( $group_id = false, $fields = array() ) {
		$group_id = ( $group_id ) ?  $group_id : bp_get_current_group_id();
		$successes = 0;

		foreach( $fields as $field ) {
			//groups_update_groupmeta returns false if the old value matches the new value, so we'll need to check for that case
			$old_setting = groups_get_groupmeta( $group_id, $field );
			$new_setting = ( isset( $_POST[$field] ) ) ? $_POST[$field] : '' ;
			$success = false;

			switch ( $new_setting ) {
				case ( $new_setting == $old_setting ) :
					// No need to resave settings if they're the same
					$success = true;
					break;
				case ( empty( $new_setting ) ) :
					// Remove existing entries
					$success = groups_delete_groupmeta( $group_id, $field );
					break;	
				default:
					$success = groups_update_groupmeta( $group_id, $field, $new_setting );
					break;
			}

			if ( $success ) {
				$successes++;
			}
		}

		if ( $successes == count( $fields ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Helper functions to determine what is going on/what action is being requested.
	 * @return bool 
	 */
	public function is_component(){
		if ( bp_is_groups_component() && bp_is_current_action( $this->get_plugin_slug() ) )
			return true;
		
		return false;
	}

	// This is the base screen for the group's list of narratives
	public function is_home(){
		if ( $this->is_component() && ! ( bp_action_variables() ) )
			return true;

		return false;
	}

	// Looking at a single post
	public function is_single_post(){
		$action_variables = bp_action_variables();
		// var_dump($action_variables);
		if ( $this->is_component() && ! empty( $action_variables ) && $action_variables[0] != 'edit' )
			return true;

		return false;
	}
	// Editing a post happens at pages/edit/post_id.
	public function is_post_edit(){
		if ( $this->is_component() && bp_action_variable( 0 ) == 'edit' ) {
			return true;
		}
		return false;
	}

	public function current_user_can_post(){
		if ( current_user_can( 'delete_pages' ) ) {
			return true;
		}
		return false;
	}

	public function current_user_can_manage(){
		if ( current_user_can( 'delete_pages' ) ) {
			return true;
		}
		return false;
	}

	public function get_post_form( $group_id = false ){
		$group_id = $group_id ? $group_id : bp_get_current_group_id();
		
		// Should the user be able to visit this page?
		if ( ! $this->current_user_can_post() ) {
			echo '<div id="message" class="error"><p>You do not have the capability to edit or create posts in this group.</p></div>';
			return;
		}
		// Edit page functionality
		$actions = bp_action_variables();

		// Should this post be editable from this group?
		if ( $actions[1] ) {
			if ( $group_id != $this->get_group_id_from_post_id( $actions[1] ) ) {
				echo '<div id="message" class="error"><p>That post is not associated with this group.</p></div>';
				return;
			}
		}

		if ( 'edit' == $actions[0] ) {
			if ( ! ( $actions[1] ) ) {
				// This is a new post and we need to auto-draft it.
				$post_id = wp_insert_post( array( 'post_title' => __( 'Auto Draft' ), 'post_type' => 'cc_group_page', 'post_status' => 'auto-draft' ) );
				// Associated the post with this group
				wp_set_object_terms( $post_id, $this->get_group_term_id( $group_id ), 'ccgp_related_groups' );
			} else {
				//This is an existing post and we need to pre-fill the form
				$post_id = (int) $actions[1];
				$post = get_post( $post_id, OBJECT, 'edit' );
				$post_content = $post->post_content;
				$post_title = $post->post_title;
				$post_published = $post->post_status;
				$comment_status = $post->comment_status;
			}
		}

		//Warn WP that we're going to want the media js
		//TODO I'm skeptical of this
		$args = array( 'post' => $post_id );
		wp_enqueue_media( $args );
		// $GLOBALS['post_ID'] = $post_id;

		?>

		<form enctype="multipart/form-data" action="<?php echo $this->get_base_permalink() . "edit/" . $post_id; ?>" method="post" class="standard-form">

			<label for="ccgp_title">Title&emsp;<input type="text" value="<?php echo apply_filters( "the_title", $post_title ); ?>" name="ccgp_title" size="80"></label>
		
			<?php
			$args = array(
					// 'textarea_rows' => 100,
					// 'teeny' => true,
					// 'quicktags' => false
					'tinymce' => true,
					'media_buttons' => true,
					'editor_height' => 360,
					'tabfocus_elements' => 'insert-media-button,save-post',
				);
				wp_editor( $post_content, 'ccgp_content', $args); 
			?>

			<div class="narrative-meta">
				<p>
					<label for="ccgp_published">Published Status</label>
					<select name="ccgp_published" id="ccgp_published">
						<option value="publish" <?php selected( $post_published, "publish" ); ?>>Published</option>
						<option  value="draft" <?php
							if ( empty( $post_published ) || $post_published == 'draft' ) { 
								echo 'selected="selected"' ; 
							} 
							?>>Draft</option>
						<option value="trash" <?php selected( $post_published, "trash" ); ?>>Trash</option>
					</select>
				</p>

				<p>
					<label for="ccgp_comment_status"> <input type="checkbox" value="open" id="ccgp_comment_status" name="ccgp_comment_status" <?php 
					if ( empty( $comment_status ) || $comment_status == 'open' ) { 
						echo 'checked="checked"'; 
					} ?>> Allow comments on this post.</label>
				</p>
			</div>

			<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
			<!-- This is created for the media modal to reference 
			TODO: This doesn't work.-->
			<input id="post_ID" type="hidden" value="<?php echo $post_id; ?>" name="post_ID">

			<input type="hidden" name="post_form_url" value="<?php echo $this->get_base_permalink() . "edit/" . $post_id; ?>">
			<?php wp_nonce_field( 'edit_group_page_' . $post_id ); ?>
			<br />
			<input type="submit" value="Save Changes" name="group_page_post_submitted" id="submit">
		</form>
	<?php
	}

	// A catch-action-type of save
	public function save_post( $group_id = false ) {
		// Make sure this action should run
		// Only on post edit page, and only when an id is specified
		if ( ! ( $this->is_post_edit() && $post_id = bp_action_variable( 1 ) ) ) {
			return;
		}

		// Is there something to do?
		if ( ! isset( $_POST['group_page_post_submitted'] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'edit_group_page_' . $post_id ) ) {
			return;
		}

		// Should this post be editable from this group?
		$group_id = $group_id ? $group_id : bp_get_current_group_id();

		if ( $group_id != $this->get_group_id_from_post_id( $post_id ) ) {
			return;
		}

		// Should the user be able to edit this post?
		if ( ! $this->current_user_can_post() ) {
			return;
		}

		//WP's update_post function does a bunch of data cleaning, so we can leave validation to that.

		$published_status = in_array( $_POST['ccgp_published'], array( 'publish', 'draft', 'trash' ) ) ? $_POST['ccgp_published'] : 'draft';
		$title = isset( $_POST['ccgp_title'] ) ? $_POST['ccgp_title'] : 'Draft Hub Page';
		$comment_status = isset( $_POST['ccgp_comment_status'] ) ? 'open' : 'closed';

		$args = array(

			'post_title' => $title,
			'post_content' => $_POST['ccgp_content'],
			'post_name' => sanitize_title( $title ),
			'post_type' => 'cc_group_page',
			'post_status' => $published_status,
			'comment_status' => $comment_status,

		);

		if ( $post_id ) {
			$args['ID'] = $post_id;
		}

		$post_id = wp_update_post( $args );

		// If this is a move to the trash, send user back to TOC
		if ( $published_status == 'trash' ) {
			// @TODO: This doesn't work
			wp_redirect( $this->get_base_permalink( $group_id ) );
			// exit;
		}
	}

	/**
	 * Get the appropriate query for various screens
	 * 
	 * @return array of args for WP_Query
	 */
	public function get_query( $group_id = null, $status = null ) {

	  	// For single post, get the post by the slug
		if( $this->is_single_post() ){
			$query = array(
				'name' => bp_action_variable( 0 ),
				'post_type' => 'cc_group_page',
				// 'post_status' => array( 'publish', 'draft'),
			);		
		} else {
			$group_id = $group_id ? $group_id : bp_get_current_group_id();
			// Not a single post, this is the list of narratives for a group.
			// TODO: Finish pagination
			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
			// $query= "related_groups=".$cats_list;
			$query = array(
				'post_type' => 'cc_group_page',
				'tax_query' => array(
					array(
						'taxonomy' => 'ccgp_related_groups',
						'field' => 'id',
						'terms' => $this->get_group_term_id( $group_id ),
						'include_children' => false,
						// 'operator' => 'IN'
					)
				),
				'orderby' => array( 'post_title' => 'ASC' ),
			);

			// If the status is specified, respect it, otherwise use the user's abilities to determine.
			if ( $status == 'draft' ) {
				$query['post_status'] = array( 'publish', 'draft');
			} else if ( $status == 'publish' ) {
				$query['post_status'] = array( 'publish' );
			} else {
				// Get draft posts for those who can edit, otherwise only show published stories
				$query['post_status'] = $this->current_user_can_post() ? array( 'publish', 'draft') : array( 'publish' );
			}
		}

		return apply_filters( "ccgp_get_query", $query );
	}

	//Permalinks
	/**
	 * Get the permalink of the Narratives tab if set
	 * 
	 * @return string: URI
	 */
	public function get_base_permalink( $group_id = false ){
		$group_id = $group_id ? $group_id : bp_get_current_group_id();
		$group_slug = bp_get_group_permalink( groups_get_group( array( 'group_id' => $group_id ) ) );
	    $permalink = $group_slug . trailingslashit( $this->get_plugin_slug() );

	    return apply_filters( 'ccgp_base_permalink', $permalink, $group_id );
	}
	public function get_create_permalink( $group_id = false ){
		$group_id = $group_id ? $group_id : bp_get_current_group_id();
		$group_slug = bp_get_group_permalink( groups_get_group( array( 'group_id' => $group_id ) ) );
	    $permalink = $group_slug . trailingslashit( $this->get_plugin_slug() ) . trailingslashit('edit' );
	    return apply_filters( 'ccgp_create_permalink', $permalink, $group_id );
	}
	public function get_edit_permalink( $post_id = 0 ){
		if ( ! $post_id ) {
			return $this->get_create_permalink();
		}

		$permalink = $this->get_create_permalink() . trailingslashit( $post_id );
	    return apply_filters( 'ccgp_edit_permalink', $permalink, $post_id );
	}

	/** TEMPLATE LOADER ************************************************/

	/**
	* Get the location of the template directory.
	*
	* @since 1.1.0
	*
	* @uses apply_filters()
	* @return string
	*/
	public function get_template_directory() {
	return apply_filters( 'ccgp_get_template_directory', plugin_dir_path( __FILE__ ) . '../public/templates' );
	}

	/**
	 * Add our templates to BuddyPress' template stack.
	 *
	 * @since    1.1.0
	 */
	public function add_template_stack( $templates ) {
	    // if we're on a page of our plugin and the theme is not BP Default, then we
	    // add our path to the template path array
	    if ( $this->is_component() ) {
	        $templates[] = trailingslashit( $this->get_template_directory() );
	    }

	    return $templates;
	}

	/**
	 * Creates the rewrites necessary so that the group is really where this stuff lives.
	 *
	 * @since    1.0.0
	 */
	public function permalink_filter( $permalink, $post ) {
	 
	    if ( 'cc_group_page' == get_post_type( $post )  ) {
	    	$group_id = $this->get_group_id_from_post_id( $post->ID );
	        $permalink = $this->get_base_permalink( $group_id ) . $post->post_name;
	    }

	    return $permalink;
	}

	/**
	 * We need to stop the evaluation of shortcodes on this plugin's group settings screen. 
	 * If they're interpreted for display, then the code is consumed and lost upon the next save.
	 *
	 * @since    1.0.0
	 */
	public function remove_shortcode_filter_on_settings_screen() {
	      if ( $this->is_post_edit() ) {
	        	remove_filter( 'the_content', 'do_shortcode', 11);
	      }
	}

	/**
	 * Filter "map_meta_caps" to let our users do things they normally can't.
	 *
	 * @since    1.0.0
	 */
	public function add_mmc_filter() {
		if ( $this->is_post_edit() || ( isset( $_POST['action'] ) && $_POST['action'] == 'upload-attachment' ) ) {
		    add_filter( 'map_meta_cap', array( $this, 'setup_map_meta_cap' ), 14, 4 );
		}
	}

	/**
	 * Filter "map_meta_caps" to let our users do things they normally can't.
	 * This enables the media button on the post edit form (allows an ordinary user to add media).
	 *
	 * @since    1.0.0
	 */
	public function setup_map_meta_cap( $primitive_caps, $meta_cap, $user_id, $args ) {	
		// In order to upload media, a user needs to have caps.
		// Check if this is a request we want to filter. 
		if ( ! in_array( $meta_cap, array( 'upload_files', 'edit_post', 'delete_post' ) ) ) {  
	        return $primitive_caps;  
	    }

		// It would be useful for a user to be able to delete her own uploaded media.
	    // If this is someone else's post, we don't want to allow deletion of that, though.
	    if ( $meta_cap == 'delete_post' && in_array( 'delete_others_posts', $primitive_caps ) ) {
	        return $primitive_caps;  
	    }

	  	// We pass a blank array back, meaning there's no capability required.
	    $primitive_caps = array();

		return $primitive_caps;
	}


	/**
	 * Only allow users to see their own items in the media library uploader.
	 *
	 * @since    1.0.0
	 */
	public function show_users_own_attachments( $wp_query_obj ) {
	 
		// The image library is populated via an AJAX request, so we'll check for that
		if( isset( $_POST['action'] ) && $_POST['action'] == 'query-attachments' ) {

			// If the user isn't a site admin, limit the image library to only show his images.
			if( ! current_user_can( 'delete_pages' ) )
			    $wp_query_obj->set( 'author', get_current_user_id() );

		}
	}
}