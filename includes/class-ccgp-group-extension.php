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
				Create hub information pages for sharing with the public or your hub members.
			</p>
			<?php if ( $ccgp_class->current_user_can_manage() ) : ?>
				<p>
					<label> <input type="checkbox" name="ccgp_is_enabled" id="ccgn_is_enabled" value="1" <?php checked( $is_enabled, true ) ?> /> Enable hub pages.</label>
				</p>

				<!-- <p>
					<label for='ccgp_tab_label'>Set the Hub Pages tab label.</label>
					<input type="text" name="ccgp_tab_label" id="ccgp_tab_label" value="<?php echo esc_html( $tab_label ); ?>" />
				</p> -->
                <?php ccgp_setup_settings_form( $group_id ); ?>
                <?php //cc_group_pages_get_page_order_ui(); ?>

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

				// Save the new groupmeta
				$fields = array(
					'ccgp_is_enabled',
					// 'ccgp_tab_label',
					);
				$success = $ccgp_class->update_groupmeta( $group_id, $fields );

                //Create the structured array for saving post relationships
                if ( ! empty( $_POST[ 'pages-order' ] ) ) {
                    // Create an array with the order of the pages
                    parse_str( urldecode( $_POST[ 'pages-order' ] ), $pages_order );
                }
                $towrite = PHP_EOL . 'parsed: ' . print_r( $pages_order, TRUE );

                $ordered_pages = $_POST[ 'ccgp-tabs' ];
                // Get already used tab slugs
                $bp_options_nav_key = bp_current_item();
                $bp = buddypress();
                $verboten = array();
                if ( isset( $bp->bp_options_nav[$bp_options_nav_key] ) || count( $bp->bp_options_nav[$bp_options_nav_key] ) < 1 ) {
                    foreach ( (array) $bp->bp_options_nav[$bp_options_nav_key] as $subnav_item ) {
                        $verboten[] = $subnav_item['slug'];
                    }
                }

                // Set and sanitize the tab slugs
                $tab_keys = array_keys( $ordered_pages );
                foreach ( $tab_keys as $tab_key) {
                    if ( empty( $ordered_pages[$tab_key]['slug'] ) ) {
                        $ordered_pages[$tab_key]['slug'] = $ordered_pages[$tab_key]['label'];
                    }
                    $ordered_pages[$tab_key]['slug'] = sanitize_title( $ordered_pages[$tab_key]['slug'] );

                    // Check to make sure it isn't a tab that is already registered or forbidden
                    if ( in_array( $ordered_pages[$tab_key]['slug'], $verboten ) ) {
                        for ( $i = 1; $i < 12 ; $i++ ) {
                            $maybe_slug = $ordered_pages[$tab_key]['slug'] . '-' . $i;
                            // Is it OK now?
                            if ( ! in_array( $maybe_slug, $verboten ) ) {
                                $ordered_pages[$tab_key]['slug'] = $maybe_slug;
                                break;
                            }
                        }
                    }

                    // OK? Add this tab's slug to the forbidden slugs list
                    $verboten[] = $ordered_pages[$tab_key]['slug'];
                }
                $towrite .= PHP_EOL . 'ordered pages after ' . print_r( $ordered_pages, TRUE );
                $towrite .= PHP_EOL . 'verboten: ' . print_r( $verboten, TRUE );
                $fp = fopen('ccgp-saving.txt', 'a');
                fwrite($fp, $towrite);
                fclose($fp);
                // Add the post_ids to this array in order
                foreach ( $pages_order as $key => $values ) {
                    // $towrite .= PHP_EOL . 'pages_order key: ' . print_r( $key, TRUE );
                    // $towrite .= PHP_EOL . 'pages_order values: ' . print_r( $values, TRUE );

                    $target_tab = str_replace( 'section-', '', $key );
                    $pages = explode( ',', $values );
                    foreach ( $pages as $post_id ) {
                        $post_id = str_replace('post-', '', $post_id);
                        // $towrite .= PHP_EOL . 'post id: ' . print_r( $post_id, TRUE );
                        if ( empty( $post_id ) ) {
                            // $towrite .= PHP_EOL . 'bailing on empty post id ';
                            continue;
                        }

                        $access = isset( $_POST['post-'. $post_id . '-visibility'] ) ? $_POST['post-'. $post_id . '-visibility'] : 'anyone';
                        $i = 1;
                        $ordered_pages[$target_tab]['pages'][$i] = array(
                            'post_id' => $post_id,
                            'visibility' => $visibility
                            );
                        $i++;
                    }
                }
                // $towrite .= PHP_EOL . 'processed to save: ' . print_r( $ordered_pages, TRUE );
                // $towrite .= PHP_EOL . '$_POST: ' . print_r( $_POST, TRUE );
                // $fp = fopen('ccgp-saving.txt', 'a');
                // fwrite($fp, $towrite);
                // fclose($fp);

                groups_update_groupmeta( $group_id, 'ccgp_page_order', $ordered_pages );

                // Save the post data
                if ( isset( $_POST[ 'ccgp-pages' ] ) && ! empty( $_POST[ 'ccgp-pages' ] ) ) {
                    foreach ( $_POST[ 'ccgp-pages' ] as $post_id => $post_data) {
                        $post_args = array(
                            'ID' => $post_id,
                        );
                        if ( ! empty( $post_data['title'] ) ) {
                            $post_args[ 'post_title' ] = $post_data['title'];
                        }
                        $post_id = wp_insert_post( $post_args );
                    }
                }

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
        function display( $group_id = null ) {
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
function ccgp_setup_settings_form( $group_id ){
    $page_order = ccgp_get_page_order( $group_id, $jsonify = false );
    $access_levels = array(
            0 => array( 'bp_level' => 'anyone', 'label' => 'Anyone' ),
            1 => array( 'bp_level' => 'loggedin', 'label' => 'Logged-in Site Members' ),
            2 => array( 'bp_level' => 'member', 'label' => 'Hub Members' ),
            3 => array( 'bp_level' => 'mod', 'label' => 'Hub Moderators' ),
            4 => array( 'bp_level' => 'admin', 'label' => 'Hub Administrators' ),
        );
    ?>
    <a href="#" id="ccgp-add-tab" class="alignright button">Add a new tab</a>
    <div id="tabs-container" class="clear">
        <?php if ( ! empty( $page_order ) && is_array( $page_order ) ) {
            foreach ( $page_order as $tab_id => $tab_details ) { ?>
               <fieldset id="tabs-<?php echo $tab_id; ?>" class="tab-details half-block">
                    <h4><?php if ( ! empty( $tab_details['label'] ) ) {
                        echo $tab_details['label'] . ' details';
                    } else {
                        echo 'Tab ' . $tab_id . ' details';
                    }?></h4>
                    <a href="#" class="toggle-details-pane alignright">Edit details</a>
                    <div class="details-pane">
                        <label for="ccgp-tab-<?php echo $tab_id; ?>-label" >Tab Label</label>
                        <input type="text" id="ccgp-tab-<?php echo $tab_id; ?>-label" name="ccgp-tabs[<?php echo $tab_id; ?>][label]" value="<?php echo $tab_details['label']; ?>"/>
                        <p class="info">This is the label as shown on the navigation tab</p>
                        <label for="ccgp-tab-<?php echo $tab_id; ?>-slug" >Tab Slug</label>
                        <input type="text" id="ccgp-tab-<?php echo $tab_id; ?>-slug" name="ccgp-tabs[<?php echo $tab_id; ?>][slug]" value="<?php echo $tab_details['slug']; ?>"/>
                        <p class="info">The piece of the URL that follows your group&rsquo;s slug. E.g. http://www.communitycommons.org/groups/my-group/<strong>slug-to-use</strong></p>
                        <p>
                            <label for="ccgp-tab-<?php echo $tab_id; ?>-visibility">Access</label>
                            <select name="ccgp-tabs[<?php echo $tab_id; ?>][visibility]" id="ccgp-tab-<?php echo $tab_id; ?>-visibility">
                                <?php foreach ( $access_levels as $key => $value ) { ?>
                                    <option value="<?php echo $value['bp_level'] ?>" <?php selected( $tab_details['visibility'], $value['bp_level'] ); ?>><?php echo $value['label']; ?></option>
                                <?php } ?>
                            </select>
                        </p>
                    </div>
                    <h5>Pages in this section:</h5>
                    <a href="#" class="ccgp-add-page alignright">Add a new page</a><p class="info">The first page is used as the landing page for this section.</p>
                    <ul id="section-<?php echo $tab_id; ?>" class="sortable no-bullets">
                        <?php if ( ! empty( $tab_details['pages'] && is_array( $tab_details['pages'] ) ) ) {
                                foreach ( $tab_details['pages'] as $order => $post_details ) {
                                 ?>
                                <li class="draggable" id="post-<?php echo $post_details['post_id'] ?>">
                                    <span class="arrow-up"></span><span class="arrow-down"></span>Title: <?php echo get_the_title( $post_details['post_id'] ); ?> <a href="#" class="toggle-details-pane">Edit</a>
                                    <div class="details-pane">
                                        <label for="ccgp-page-<?php echo $post_details['post_id'] ?>-title" >Page Title</label>
                                        <input type="text" id="ccgp-page-<?php echo $post_details['post_id'] ?>-title" name="ccgp-pages[<?php echo $post_details['post_id'] ?>][title]" value="<?php echo get_the_title( $post_details['post_id'] ); ?>"/>
                                        <label for="cccgp-page-<?php echo $post_details['post_id']; ?>-visibility">Access</label>
                                        <select name="ccgp-pages[<?php echo $post_details['post_id']; ?>][visibility]" id="ccgp-page-<?php echo $tab_id; ?>-visibility">
                                            <?php foreach ( $access_levels as $key => $value ) { ?>
                                                <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>" <?php selected( $post_details['visibility'] , $value['bp_level'] ); ?>><?php echo $value['label']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </li>
                        <?php }
                        } ?>
                    </ul>
                </fieldset>
        <?php
            } // End foreach ( $page_order as $tab_id => $tab_details )
        } // End if ( ! empty( $page_order ) ) ?>
    </div>
    Access Levels: <input type="text" id="access_levels" name="access_levels" val="<?php json_encode( $access_levels ); ?>" size=90/><br />
    Init Order: <div type="text" id="init-pages-order" name="init-pages-order" val="<?php echo ccgp_get_page_order( bp_get_current_group_id(), true ); ?>" size=90/><br />
    Order: <input type="text" id="pages-order" name="pages-order" val="" size=90/>
    <style type="text/css">
        .ui-draggable-drop-zone {
            border:2px dashed #EEE;
            height: 24.4px;
        }
        .ui-sortable-handle {
            border:2px solid #EEE;
            padding-left: 1.5em;
        }
        .arrow-up {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;

            border-bottom: 5px solid #BBB;
            position: absolute;
            top: 20%;
            left: 5px;

        }

        .arrow-down {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;

            border-top: 5px solid #BBB;
            position: absolute;
            bottom: 20%;
            left: 5px;
        }
    </style>
    <?php
}

function cc_group_pages_get_page_order_ui(){
    $access_levels = array(
        0 => array( 'bp_level' => 'anyone', 'label' => 'Anyone' ),
        1 => array( 'bp_level' => 'loggedin', 'label' => 'Logged-in Site Members' ),
        2 => array( 'bp_level' => 'member', 'label' => 'Hub Members' ),
        3 => array( 'bp_level' => 'mod', 'label' => 'Hub Moderators' ),
        4 => array( 'bp_level' => 'admin', 'label' => 'Hub Administrators' ),
        );
    ?>
    <a href="#" id="ccgp-add-tab">Add a new tab</a>
    <div id="tabs-container" class="clear">
    </div>
    Access Levels: <input type="text" id="access_levels" name="access_levels" val="<?php json_encode( $access_levels ); ?>" size=90/><br />
    Init Order: <div type="text" id="init-pages-order" name="init-pages-order" val="<?php echo ccgp_get_page_order( bp_get_current_group_id(), true ); ?>" size=90/><br />
    Order: <input type="text" id="pages-order" name="pages-order" val="" size=90/>
    <style type="text/css">
        .ui-draggable-drop-zone {
            border:2px dashed #EEE;
            height: 24.4px;
        }
        .ui-sortable-handle {
            border:2px solid #EEE;
            padding-left: 1.5em;
        }
        .arrow-up {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;

            border-bottom: 5px solid #BBB;
            position: absolute;
            top: 20%;
            left: 5px;

        }

        .arrow-down {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;

            border-top: 5px solid #BBB;
            position: absolute;
            bottom: 20%;
            left: 5px;
        }
    </style>
<?php
}

function ccgp_get_page_order( $group_id = 0, $jsonify = false ) {
    if ( ! $group_id ) {
        return;
    }
    $page_order = groups_get_groupmeta( $group_id, 'ccgp_page_order' );
            $towrite = PHP_EOL . 'in ccgp_get_page_order: ' . print_r($page_order, TRUE);
            $towrite .= PHP_EOL . 'group_id: ' . print_r($group_id, TRUE);
            $fp = fopen('ccgp-save.txt', 'a');
            fwrite($fp, $towrite);
            fclose($fp);
    if ( $jsonify ) {
        $page_order = json_encode( $page_order );
    }
    return $page_order;
}