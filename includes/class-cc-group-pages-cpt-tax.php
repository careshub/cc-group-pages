<?php

/**
 * The file that defines the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC Group Pages
 * @subpackage CC Group Pages/includes
 */

/**
 * Define the custom post type and taxonomy we'll need for this plugin.
 *
 *
 * @since      1.0.0
 * @package    CC Group Pages
 * @subpackage CC Group Pages/includes
 * @author     Your Name <email@example.com>
 */
class CC_Group_Pages_CPT_Tax {

	/**
	 * Creates the group story custom post type.
	 *
	 * @since    1.0.0
	 */
	public function register_cpt() {

	    $labels = array( 
	        'name' => _x( 'Hub Pages', 'cc_group_page' ),
	        'singular_name' => _x( 'Hub Page', 'cc_group_page' ),
	        'add_new' => _x( 'Add New', 'cc_group_page' ),
	        'add_new_item' => _x( 'Add New Hub Page', 'cc_group_page' ),
	        'edit_item' => _x( 'Edit Hub Page', 'cc_group_page' ),
	        'new_item' => _x( 'New Hub Page', 'cc_group_page' ),
	        'view_item' => _x( 'View Hub Page', 'cc_group_page' ),
	        'search_items' => _x( 'Search Hub Pages', 'cc_group_page' ),
	        'not_found' => _x( 'No hub pages found', 'cc_group_page' ),
	        'not_found_in_trash' => _x( 'No hub pages found in Trash', 'cc_group_page' ),
	        'parent_item_colon' => _x( 'Parent Hub Page:', 'cc_group_page' ),
	        'menu_name' => _x( 'Hub Pages', 'cc_group_page' ),
	    );

	    $args = array( 
	        'labels' => $labels,
	        'hierarchical' => false,
	        'description' => 'Information pages displayed in BuddyPress groups.',
	        'supports' => array( 'title', 'editor', 'comments', 'revisions' ),
	        'taxonomies' => array( 'ccgp_related_groups' ),
	        'public' => false,
	        'show_ui' => true,
	        'show_in_menu' => true,	        
	        'show_in_nav_menus' => false,
	        'publicly_queryable' => true,
	        'exclude_from_search' => true,
	        'has_archive' => false,
	        'query_var' => true,
	        'can_export' => true,
	        'rewrite' => true,
	        'capability_type' => 'post'
	    );

	    register_post_type( 'cc_group_page', $args );
	}

	/**
	 * Creates the group story custom taxonomy.
	 *
	 * @since    1.0.0
	 */
	public function register_taxonomy() {

	    $labels = array( 
	        'name' => _x( 'CCGP Related Groups', 'ccgp_related_groups' ),
	        'singular_name' => _x( 'CCGP Related Group', 'ccgp_related_groups' ),
	        'search_items' => _x( 'Search Related Groups', 'ccgp_related_groups' ),
	        'popular_items' => _x( 'Popular Related Groups', 'ccgp_related_groups' ),
	        'all_items' => _x( 'All Related Groups', 'ccgp_related_groups' ),
	        'parent_item' => _x( 'Parent CCGP Related Group', 'ccgp_related_groups' ),
	        'parent_item_colon' => _x( 'Parent CCGP Related Group:', 'ccgp_related_groups' ),
	        'edit_item' => _x( 'Edit Related Group', 'ccgp_related_groups' ),
	        'update_item' => _x( 'Update Related Group', 'ccgp_related_groups' ),
	        'add_new_item' => _x( 'Add New Related Group', 'ccgp_related_groups' ),
	        'new_item_name' => _x( 'New Related Group', 'ccgp_related_groups' ),
	        'separate_items_with_commas' => _x( 'Separate ccgp related groups with commas', 'ccgp_related_groups' ),
	        'add_or_remove_items' => _x( 'Add or remove ccgp related groups', 'ccgp_related_groups' ),
	        'choose_from_most_used' => _x( 'Choose from the most used ccgp related groups', 'ccgp_related_groups' ),
	        'menu_name' => _x( 'CCGP Related Groups', 'ccgp_related_groups' ),
	    );

	    $args = array( 
	        'labels' => $labels,
	        'public' => true,
	        'show_in_nav_menus' => false,
	        'show_ui' => true,
	        'show_tagcloud' => false,
	        'show_admin_column' => false,
	        'hierarchical' => true,

	        'rewrite' => true,
	        'query_var' => true
	    );

	    register_taxonomy( 'ccgp_related_groups', array('cc_group_page'), $args );
	}

}