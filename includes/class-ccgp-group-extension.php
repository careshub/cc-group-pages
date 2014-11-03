<?php
/**
 * CC BuddyPress Group Home Pages
 *
 * @package   CC BuddyPress Group Home Pages
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

// We're mostly using the group extension to create a way for group admins to edit the group's home page via the group's Admin tab

if ( class_exists( 'BP_Group_Extension' ) ) : // Recommended, to prevent problems during upgrade or when Groups are disabled
 
    class CC_Group_Pages_Extension extends BP_Group_Extension {

        function __construct() {

        	// Instantiate the main class so we can get the slug
        	$ccgp_class = new CC_Group_Pages();
			$access = $ccgp_class->get_tab_visibility( bp_get_current_group_id() );

			$args = array(
	            	'slug'              => 'pages',
	           		'name'              => $ccgp_class->get_tab_label(),
	           		'access'			=> $access, // BP 2.1
	           		'show_tab'			=> $access, // BP 2.1
	           		'nav_item_position' => 1,
	           		'screens' => array(
		                'edit' => array(
		                    'name' => 'Pages',
		                    'enabled' => true,
		                ),
		                'create' => array(
		                    'enabled' => false,
		                ),
		            ),
	        	);
	        
        	parent::init( $args );
			
		}

		/**
	     * settings_screen() is the catch-all method for displaying the content 
	     * of the edit, create, and Dashboard admin panels
	     */
	    function settings_screen( $group_id = 0 ) {
	    	$action = bp_action_variable( 1 );
	    	$ccgp_class = new CC_Group_Pages();


	        if ( ! $action) {
	        	// If action_variable[1] isn't set, this is the main settings screen.
	        	$is_enabled = $ccgp_class->get_enabled_status( $group_id );
		    	$tab_label = $ccgp_class->get_tab_label();
		    	// $visibility = $ccgp_class->get_tab_visibility();
				?>

				<p>
					Create hub information pages for access by hub members. For sharing stories out of your hub to a wider audience, use Hub Narratives instead. To create cooperatively edited documents, use the Hub Library.
				</p>
				<p>
					<label for="ccgp_is_enabled"> <input type="checkbox" name="ccgp_is_enabled" id="ccgn_is_enabled" value="1" <?php checked( $is_enabled, true ) ?> /> Enable hub pages.</label>
				</p>

				<p>
					<label for='ccgp_tab_label'>Change the BuddyPress hub tab label from 'Narratives' to whatever you'd like.</label>
					<input type="text" name="ccgp_tab_label" id="ccgp_tab_label" value="<?php echo esc_html( $tab_label ); ?>" />
				</p>


			<?php 

	        } else {
  		        // If it is "new", this is a new post.
  		        
	        	if ( $action == 'edit' ) {
	  		        // If action_variable[1] is "edit", this is a post edit screen, and we need to pre-populate some variables.
	  		        $post_id = bp_action_variable( 2 );

	  		        // Check to make sure this post is associated with this group.
	  		        // @TODO

		        	// Get the post data
		        	$post = get_post( $post_id );
		        } else if ( $action == 'new' ) {
		        	// We have to set up a dummy
		        	$post = new WP_Post();
		        	$post->post_status = 'draft';
		        }

                $args = array(
                        // 'textarea_rows' => 100,
                        // 'teeny' => true,
                        // 'quicktags' => false
                		'tinymce' => true,
                		'media_buttons' => true,
	                	'editor_height' => 360,
	                	'tabfocus_elements' => 'insert-media-button,save-post',
                    );
                    wp_editor( $post->post_content, 'cc_group_page_content', $args); 
                ?>
	            <p>
		            <label for="cc_group_page_published">Published Status</label>
			        <select name="cc_group_page_published" id="cc_group_page_published">
			            <option <?php selected( $post->post_status, "publish" ); ?> value="publish">Published</option>
			            <option <?php selected( $post->post_status, "draft" ); 
			                if ( empty( $post->post_status ) ) { echo 'selected="selected"' ; } 
			                ?> value="draft">Draft</option>
			        </select>
			    </p>
                <input type="hidden" name="cc_group_page_post_id" value="<?php echo $post->ID; ?>">
                <?php

			}
	    }
	 
	    /**
	     * settings_screen_save() contains the catch-all logic for saving 
	     * settings from the edit, create, and Dashboard admin panels
	     */
	    function settings_screen_save( $group_id = 0 ) {
	    	// Main settings panel
	    	if ( isset( $_POST['ccgp_is_enabled'] ) ) {
	    		$ccgp_class = new CC_Group_Pages();
	    		$success = false;

	    		// Create or update the taxonomy term
				$group_term = $ccgp_class->update_group_term();

				//Save the new groupmeta
				$fields = array(
					'ccgp_is_enabled',
					'ccgp_tab_label',
					);
				$success = $ccgp_class->update_groupmeta( $group_id, $fields );

				if ( $group_term && $success ) {
					bp_core_add_message( __( 'Hub Pages settings were successfully updated.', 'cc-group-pages' ) );
				} else {
					bp_core_add_message( __( 'There was an error updating the Hub Pages settings, please try again.', 'cc-group-pages' ), 'error' );
				}
			} // End "is_enabled" check

	    	// If the page is new, $_POST['cc_group_page_post_id'] will be null
	    	// If the page already exists, $_POST['group_home_page_content'] will be set
	    	if ( isset( $_POST['cc_group_page_content'] ) ) {

	    		// Get group name to use for title
	    		$current_group = groups_get_group( array( 'group_id' => $group_id ) );
	    		//Get the selected "published" status
    		    $published_status = in_array( $_POST['cc_group_page_published'], array( 'publish', 'draft' ) ) ? $_POST['cc_group_page_published'] : 'draft';

		    	// Some defaults
				$post_data = array(
	                'post_type' => 'cc_group_page',
	                'post_title' => $_POST['cc_group_page_title'],
   	                'post_content' => $_POST['cc_group_page_content'],
                    'post_status' => $published_status,
	                'comment_status' => 'closed'
	            );

		        // Does a post already exist? TODO: Trust this or check it via a meta query?
	   	        if ( isset( $_POST['cc_group_page_post_id'] ) && is_numeric( $_POST['cc_group_page_post_id'] ) ) {
	   	        	$post_data['ID'] = $_POST['cc_group_page_post_id'];
	   	        } else {
	   	        	//If this is a new post, we'll add an author id.
	   	        	$post_data['post_author'] = get_current_user_id();
	   	        }

	   			// $towrite = PHP_EOL . print_r($post_data, TRUE);
				// $fp = fopen('creating_group_home_page.txt', 'a');
				// fwrite($fp, $towrite);
				// fclose($fp);

	        	// Save the post
	            $post_id = wp_insert_post($post_data);

	        	// If the post save was successful, save the postmeta
	            if ( $post_id ) {
		            // Associate the post with the group
					update_post_meta( $post_id, 'group_home_page_association', $group_id, false );
					// Add a success message
					bp_core_add_message( 'Group home page was successfully updated.', 'success' );

				} else {
					// Something went wrong
					bp_core_add_message( 'We couldn\'t update the group home page at this time.', 'error' );
				}
	        }		    
		}

        /**
         * Use this function to display the actual content of your group extension when the nav item is selected
         */
        function display() {
        	if ( ! isset( $group_id ) ) {
	    		$group_id = bp_get_current_group_id();
	    	} 

		    $custom_front_query = cc_get_group_home_page_post( $group_id );

			while ( $custom_front_query->have_posts() ) :
				$custom_front_query->the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'clear' ); ?>>
					<!-- <h1 class="entry-title"><?php the_title(); ?></h1> -->
					<div class="entry-content">
						<?php the_content(); ?>
					</div><!-- .entry-content -->
				</article><!-- #post -->

			<?php
			endwhile;

			do_action( 'cc_group_home_page_after_content', $group_id );

        }
 
        /**
         * If your group extension requires a meta box in the Dashboard group admin,
         * use this method to display the content of the metabox
         *
         * As in the case of create_screen() and edit_screen(), it may be helpful
         * to abstract shared markup into a separate method.
         *
         * This is an optional method. If you don't need/want a metabox on the group
         * admin panel, don't define this method in your class.
         *
         * <a href="http://buddypress.org/community/members/param/" rel="nofollow">@param</a> int $group_id The numeric ID of the group being edited. Use
         *   this id to pull up any relevant metadata
         *
         */
        // We're using the fallback method setting_screen, but may use this later.
        // function admin_screen( $group_id ) {
        //     if ( ccghp_enabled_for_group( $group_id ) ){
        //      	echo '<p>This group has a custom home page.</p>';
        //     } else {
        //      	echo '<p>This group <strong>does not</strong> have a custom home page.</p>';
        //     };
        // }
 
        /**
         * The routine run after the group is saved on the Dashboard group admin screen
         *
         * <a href="http://buddypress.org/community/members/param/" rel="nofollow">@param</a> int $group_id The numeric ID of the group being edited. Use
         *   this id to pull up any relevant metadata
         */
        // We're using the fallback method setting_screen_save, but may use this later.
        // function admin_screen_save( $group_id ) {
            // Grab your data out of the $_POST global and save as necessary
        // }
 
        // function widget_display() {
        // }

    }
 
    bp_register_group_extension( 'CC_Group_Pages_Extension' );
 
endif; // class_exists( 'BP_Group_Extension' )