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
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cc-group-pages-loader.php';

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

		
		$this->loader = new CC_Group_Pages_Loader();

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

		$plugin_i18n = new Plugin_Name_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new CC_Group_Pages_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new CC_Group_Pages_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
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
	 *			  			noone|members|mods|admins|anyone
	 */
	public function get_tab_visibility( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();

		// Stop now if it isn't enabled for this group.
		if ( ! get_enabled_status( $group_id ) ) {
			$setting = 'noone';
		}

		// I think these should always be "for members only" pages, to diff from narratives and bp-docs.
		$setting = 'member';

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
	function update_group_term( $group_id = false ) {
		$group_id = ( $group_id ) ? $group_id : bp_get_current_group_id();

		// Create a group object, using BP Group Hierarchy or not.
		$group_object = ( class_exists( 'BP_Groups_Hierarchy' ) ) ? new BP_Groups_Hierarchy( $group_id ) : groups_get_group( array( 'group_id' => $group_id ) );

		$group_name = $group_object->name;
		$term_args['description'] = 'Group pages for ' . $group_name;

		// Check for a term for this group's parent group, set a value for the term's 'parent' arg
		// Depends on BP_Group_Hierarchy being active
		if  ( ( $parent_group_id = $group_object->vars['parent_id'] )  &&  
				( $parent_group_term = get_term_by( 'slug', $ccgp_class->create_taxonomy_slug( $parent_group_id ), 'ccgp_related_groups' ) ) 
			) {
			$term_args['parent'] = (int) $parent_group_term->term_id;
		}

		if ( $existing_term_id = $ccgp_class->get_group_term_id( $group_id ) ) {
			$term_args['name'] = $group_name;
			$result = wp_update_term( $existing_term_id, 'ccgp_related_groups', $term_args );
		} else {
			$term_args['slug'] = $ccgp_class->create_taxonomy_slug( $group_id );
			$result = wp_insert_term( $group_name, 'ccgp_related_groups', $term_args );
		}
	}

	/**
	 * Get the taxonomy term specific to group.
	 * 
	 * @since     1.0.0
	 * @return integer
	 */
	function get_group_term_id( $group_id = false ) {
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
	function create_taxonomy_slug( $group_id = false ) {
		$group_id = ( $group_id ) ?  $group_id : bp_get_current_group_id();
		return 'ccgp_related_group_' . $group_id;
	}

	/**
	 * Update group meta settings.
	 * 
	 * @since  1.0.0
	 * @return boolean
	 */
	function update_groupmeta( $group_id = false, $fields = array() ) {
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


}