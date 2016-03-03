<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC Group Pages
 * @subpackage CC Group Pages/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    CC Group Pages
 * @subpackage CC Group Pages/public
 * @author     David Cavins
 */
class CC_Group_Pages_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cc-group-pages-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Public_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Public_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_edit_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-edit.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the stylesheets for the group manage pane.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_manage_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cc-group-pages-manage.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the scripts for the group's manage pane.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_group_manage_scripts() {

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-edit.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script(
			"ccgp-manage",
			plugin_dir_url( __FILE__ ) . 'js/cc-group-pages-manage.js',
			array( "wp-util" ),
			$this->version,
			true //makes sure this is enqueued in the footer
		);
		// Print out a nonce so we can verify this request.
		wp_localize_script( "ccgp-manage", "ccgpAJAXNonce", wp_create_nonce( "ccgp-manage-" . bp_get_current_group_id() ) );
	}

	/**
	 * Include the needed js templates in the page footer.
	 *
	 * @since    1.0.0
	 */
	public function include_group_manage_js_templates() {
		require_once plugin_dir_path( __FILE__ ) . 'templates/groups/single/pages/manage-js-templates.php';
	}

	/**
	 * Catch AJAX request for page details.
	 *
	 * @since    1.0.0
	 */
	public function ccgp_ajax_retrieve_page_details() {
		$nonce = isset( $_POST["nonce"] ) ? $_POST["nonce"] : "";
		$group_id = isset( $_POST["group_id"] ) ? (int) $_POST["group_id"] : 0;
		$post_id = isset( $_POST["post_id"] ) ? (int) $_POST["post_id"] : 0;

		if ( ! wp_verify_nonce( $nonce, "ccgp-manage-" . $group_id ) ) {
			// If the nonce was invalid, send an error.
			wp_send_json_error( "This came from the wrong place" );
		}

		// If the post doesn't exist, create a new one.
		if ( $post_id == 0 ) {
			$ccgp_class = new CC_Group_Pages();
			$group_term = $ccgp_class->get_group_term_id( $group_id );

			if ( ! $group_term ) {
				wp_send_json_error( $group_term );
			} else {
				// Create a new post
				$post_data = array(
					'post_title'	=> 'Untitled',
					'post_status'    => 'auto-draft',
					'post_type'      => 'cc_group_page',
					'tax_input'      => array( 'ccgp_related_groups' => $group_term ),
				);
				$post_id = wp_insert_post( $post_data );
			}
		}
		$post_details = get_post( $post_id );

		$retval = array(
			'post_id' => $post_id,
			'post_title' => $post_details->post_title,
			'target_list' => $_POST['target_list']
			);

		// Return the info.
		wp_send_json_success( $retval );
	}

	/**
	 * Catch AJAX request for page order information.
	 *
	 * @since    1.0.0
	 */
	public function ccgp_ajax_retrieve_page_order() {
		$nonce = isset( $_POST["nonce"] ) ? $_POST["nonce"] : "";
		$group_id = isset( $_POST["group_id"] ) ? (int) $_POST["group_id"] : 0;


		if ( ! wp_verify_nonce( $nonce, "ccgp-manage-" . $group_id ) ) {
			// If the nonce was invalid, send an error.
			wp_send_json_error( "This came from the wrong place" );
		}

		$retval = ccgp_get_page_order( $group_id, $jsonify = false );

			// $towrite = PHP_EOL . 'passed nonce: ';
			// $towrite .= PHP_EOL . 'get page order, group id: ' . print_r($group_id, TRUE);
			// $towrite .= PHP_EOL . 'value to send: ' . print_r($retval, TRUE);
			// $fp = fopen('ccgp-save.txt', 'a');
			// fwrite($fp, $towrite);
			// fclose($fp);

		// Send the comment data back to Javascript.
		wp_send_json_success( $retval );
	}

	/**
	 * Let the CC Open Graph plugin know which post we're using.
	 *
	 * @since    1.0.0
	 */
	public function open_graph_post_id( $post_id ) {
		$ccgp_class = new CC_Group_Pages();
		$query = array();
		// If a page is specifically requested, this is the single result.
		if ( $ccgp_class->is_single_post() ) {
			$query = $ccgp_class->get_query();
		} else {
			// This is the default page for one of the tabs.
		    $query = $ccgp_class->get_pages_query_for_tab();
		}

		if ( $query ) {
			// All we need is the post's ID
			$query['fields'] = 'ids';
			// Don't include drafts.
			$query['post_status'] = array( 'publish' );
			$pages = new WP_Query( $query );
		    if ( ! empty( $pages->posts ) ) {
		    	$post_id = current( $pages->posts );
			}
		}

		return $post_id;

	}
}