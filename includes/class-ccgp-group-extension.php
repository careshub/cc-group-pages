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
	            	'slug'              => $ccgp_class->get_plugin_slug(),
	           		'name'              => $ccgp_class->get_tab_label(),
	           		'access'			=> $access, // BP 2.1
	           		'show_tab'			=> $access, // BP 2.1
	           		'nav_item_position' => 43,
	           		'screens' => array(
		                'edit' => array(
		                    'name' => 'Pages',
		                    'enabled' => $ccgp_class->current_user_can_manage(),
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
	    	$ccgp_class = new CC_Group_Pages();
        	$is_enabled = $ccgp_class->get_enabled_status( $group_id );
	    	$tab_label = $ccgp_class->get_tab_label();
			?>

			<p>
				Create hub information pages for access by hub members. For sharing stories out of your hub to a wider audience, use Hub Narratives instead. To create cooperatively edited documents, use the Hub Library.
			</p>
			<?php if ( $ccgp_class->current_user_can_manage() ) : ?>
				<p>
					<label> <input type="checkbox" name="ccgp_is_enabled" id="ccgn_is_enabled" value="1" <?php checked( $is_enabled, true ) ?> /> Enable hub pages.</label>
				</p>

				<p>
					<label for='ccgp_tab_label'>Set the Hub Pages tab label.</label>
					<input type="text" name="ccgp_tab_label" id="ccgp_tab_label" value="<?php echo esc_html( $tab_label ); ?>" />
				</p>
			<?php else : ?>
				<p>Contact a site administrator to enable Hub Pages for your group.</p>
			<?php endif; ?>

		<?php
	    }
	 
	    /**
	     * settings_screen_save() contains the catch-all logic for saving 
	     * settings from the edit, create, and Dashboard admin panels
	     */
	    function settings_screen_save( $group_id = 0 ) {
	    	// Main settings panel
	    	// @TODO: Fix term creation from wp admin
	    	if ( isset( $_POST['ccgp_is_enabled'] ) ) {
	    		$ccgp_class = new CC_Group_Pages();
	    		$success = false;

	    		if ( ! $ccgp_class->current_user_can_manage() ) {
					bp_core_add_message( __( 'You are not allowed to update the Hub Pages settings.', 'cc-group-pages' ), 'error' );
					return false;
	    		}

	    		// Create or update the taxonomy term
				$group_term = $ccgp_class->update_group_term( $group_id );

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
		}

        /**
         * Use this function to display the actual content of your group extension when the nav item is selected
         */
        function display() {
			// Template location is handled via the template stack. see load_template_filter()
			bp_get_template_part( 'groups/single/pages' );
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