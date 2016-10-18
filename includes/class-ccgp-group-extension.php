<?php
/**
 * CC BuddyPress Group Pages
 *
 * @package   CC BuddyPress Group Pages
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

// This group extension creates the page management tab.
// We do this because group mods may need to edit pages, and they can't access the group's admin screens.

if ( class_exists( 'BP_Group_Extension' ) ) : // Recommended, to prevent problems during upgrade or when Groups are disabled

    class CC_Group_Pages_Extension extends BP_Group_Extension {

        function __construct() {

        	// Instantiate the main class so we can get the slug
        	$ccgp_class = new CC_Group_Pages();
			$access = $ccgp_class->get_tab_visibility( bp_get_current_group_id() );
			$args = array(
	            	'slug'              => $ccgp_class->get_manage_pages_slug(),
	           		'name'              => $ccgp_class->get_tab_label(),
	           		'access'			=> $access,
	           		// 'show_tab'			=> 'mod',
	           		'nav_item_position' => 98,
	           		'screens' => array(
		                'edit' => array(
                            'slug' => $ccgp_class->get_plugin_slug(),
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
	    function edit_screen( $group_id = 0 ) {
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
                <?php
                // Only show the form if the plugin is enabled.
                // Enabling the plugin creates the necessary group term.
                if ( $is_enabled ) {
                    ccgp_setup_settings_form( $group_id );
                }
                ?>

			<?php else : ?>
				<p>Contact a site administrator to enable Hub Pages for your group.</p>
			<?php endif; ?>


		<?php
	    }

	    /**
	     * settings_screen_save() contains the catch-all logic for saving
	     * settings from the edit, create, and Dashboard admin panels
	     */
	    function edit_screen_save( $group_id = 0 ) {
	    	// Main settings panel
	    	// @TODO: Fix term creation from wp admin

    		$ccgp_class = new CC_Group_Pages();
    		$success = true;
            $access_levels = ccgp_get_access_settings_options();
            $group_object = groups_get_group( array( 'group_id' => $group_id ) );
            $group_status = bp_get_group_status( $group_object );

    		if ( ! $ccgp_class->current_user_can_manage() ) {
				bp_core_add_message( __( 'You are not allowed to update the Hub Pages settings.', 'cc-group-pages' ), 'error' );
				return false;
    		}

    		// Create or update the taxonomy term
			$group_term = $ccgp_class->update_group_term( $group_id );

            // Record whether the plugin is enabled or not for this group.
            if ( isset( $_POST['ccgp_is_enabled'] ) ) {
                groups_update_groupmeta( $group_id, 'ccgp_is_enabled', 1 );
            } else {
                groups_delete_groupmeta( $group_id, 'ccgp_is_enabled' );
            }


            // How the settings array is created
            // 1. The save data ( $_POST[ 'ccgp-tabs' ] ) is basically
            //    structured by the naming of the fields on the settings form.
            // 2. Figure out how the pages are associated with the tabs
            // 3. Connect the page info in $_POST['ccgp-pages'] with the tabs array in

            // Create an array of "already-used" tab slugs to avoid
            // action-catching conflicts.
            $bp_options_nav_key = bp_current_item();
            $bp = buddypress();
            $verboten = array();
            if ( isset( $bp->bp_options_nav[$bp_options_nav_key] ) ) {
                // $towrite .= PHP_EOL . '$bp->bp_options_nav[$bp_options_nav_key]: ' . print_r( $bp->bp_options_nav[$bp_options_nav_key], TRUE );
                foreach ( (array) $bp->bp_options_nav[$bp_options_nav_key] as $subnav_item ) {
                    $verboten[] = $subnav_item['slug'];
                }
            }

            // $_POST[ 'ccgp-tabs' ] is array of arrays of the tab details
            // (label, slug, visibility)
            $ordered_tabs = $_POST[ 'ccgp-tabs' ];

            // Do some cleanup of the tab information.
            $tab_keys = array_keys( $ordered_tabs );
            foreach ( $tab_keys as $tab_key) {
                // Make sure that the tab slugs are url-friendly and are not
                // forbidden or duplicates. Sanitize.
                if ( empty( $ordered_tabs[$tab_key]['slug'] ) ) {
                    $ordered_tabs[$tab_key]['slug'] = $ordered_tabs[$tab_key]['label'];
                }
                $ordered_tabs[$tab_key]['slug'] = sanitize_title( $ordered_tabs[$tab_key]['slug'] );

                // Check to make sure it isn't a tab that is already registered or forbidden
                if ( in_array( $ordered_tabs[$tab_key]['slug'], $verboten ) ) {
                    // If it is a duplicate, add a number to the end to unique-ify.
                    for ( $i = 1; $i < 12 ; $i++ ) {
                        $maybe_slug = $ordered_tabs[$tab_key]['slug'] . '-' . $i;
                        // Is it OK now?
                        if ( ! in_array( $maybe_slug, $verboten ) ) {
                            $ordered_tabs[$tab_key]['slug'] = $maybe_slug;
                            break;
                        }
                    }
                }

                // OK? Add this tab's slug to the forbidden slugs list.
                $verboten[] = $ordered_tabs[$tab_key]['slug'];

                // We need to make sure that a tab's visibility isn't too permissive
                // if the group is hidden.
                // It's OK for private group pages to be public, though.
                if ( $group_status == 'hidden' ) {
                    if ( in_array( $ordered_tabs[$tab_key]['visibility'], array( 'anyone', 'loggedin' ) ) ) {
                        $ordered_tabs[$tab_key]['visibility'] = 'member';
                    }
                }

            }
            // $towrite .= PHP_EOL . 'ordered tabs after slug checks ' . print_r( $ordered_tabs, TRUE );
            // $towrite .= PHP_EOL . 'verboten: ' . print_r( $verboten, TRUE );

            // $_POST[ 'pages-order' ] is a URL-encoded string which stores
            // which pages show in which tab, and in which order.
            if ( ! empty( $_POST[ 'pages-order' ] ) ) {
                // Create an array with the order of the pages
                parse_str( urldecode( $_POST[ 'pages-order' ] ), $pages_order );
                if ( isset( $pages_order['unused-page-list'] ) ) {
                    unset( $pages_order['unused-page-list'] );
                }
                // Clean up the array values
                // Simplify array values
                $pages_order = str_replace( 'post-', '', $pages_order);
                // Simplify keys
                foreach ( $pages_order as $old_key => $value ) {
                    $new_key = str_replace( 'section-', '', $old_key);
                    $pages_order[ $new_key ] = $value;
                    unset( $pages_order[$old_key] );
                }
            }
            $towrite .= PHP_EOL . 'parsed: ' . print_r( $pages_order, TRUE );
            $towrite .= PHP_EOL . 'pages info: ' . print_r( $_POST['ccgp-pages'], TRUE );

            $page_details = isset( $_POST['ccgp-pages'] ) ? $_POST['ccgp-pages'] : array();

            // Add the post_ids to the correct tab array in order
            foreach ( $pages_order as $tab_key => $pages_in_tab ) {
                // $towrite .= PHP_EOL . 'pages_order key: ' . print_r( $key, TRUE );
                // $towrite .= PHP_EOL . 'pages_order values: ' . print_r( $values, TRUE );

                $pages_in_tab = explode( ',', $pages_in_tab );
                $j = 1;
                foreach ( $pages_in_tab as $post_id ) {
                    // $towrite .= PHP_EOL . 'post id: ' . print_r( $post_id, TRUE );
                    if ( empty( $post_id ) ) {
                        // $towrite .= PHP_EOL . 'bailing on empty post id ';
                        continue;
                    }

                    // The first page in a tab must have the same visibility setting as the tab.
                    if ( $j == 1 ) {
                        $visibility = $ordered_tabs[$tab_key]['visibility'];
                    } else {
                        // Other pages can use the same access setting or one more restrictive than the tab.
                        $visibility = isset( $page_details[$post_id]['visibility'] ) ? $page_details[$post_id]['visibility'] : 'anyone';
                        if ( ! ccgp_is_access_setting_more_restrictive( $ordered_tabs[$tab_key]['visibility'], $visibility ) ) {
                            $visibility = $ordered_tabs[$tab_key]['visibility'];
                            // Correct the ccgp_pages array setting as well, since we'll be using that later.
                            $page_details[$post_id]['visibility'] = $visibility;
                        }
                    }
                    if ( isset( $page_details[$post_id]['slug'] ) ) {
                        $page_slug = sanitize_title( $page_details[$post_id]['slug'] );
                    } else {
                        $page_slug = '';
                    }

                    $ordered_tabs[$tab_key]['pages'][$j] = array(
                        'post_id' => $post_id,
                        'visibility' => $visibility
                        );
                    $j++;
                }
            }
            $towrite .= PHP_EOL . 'processed to save: ' . print_r( $ordered_tabs, TRUE );
            $towrite .= PHP_EOL . '$_POST: ' . print_r( $_POST, TRUE );
            $fp = fopen('ccgp-saving.txt', 'a');
            fwrite($fp, $towrite);
            fclose($fp);

            groups_update_groupmeta( $group_id, 'ccgp_page_order', $ordered_tabs );

            // Save the post data.
            // $page_details is $_POST['ccgp-pages']
            if ( isset( $page_details ) && ! empty( $page_details ) ) {
                foreach ( $page_details as $post_id => $post_data) {
                    // Get the current (pre-save) post status.
                    $post_status = get_post_status( $post_id );

                    $post_args = array(
                        'ID' => $post_id,
                        'post_type' => 'cc_group_page'
                    );

                    // Set the post title.
                    if ( ! empty( $post_data['title'] ) ) {
                        $post_args[ 'post_title' ] = $post_data['title'];
                    }

                    // Set the post name (slug).
                    // If one has been set, we use it.
                    if ( ! empty( $post_data['slug'] ) ) {
                        $post_args[ 'post_name' ] = sanitize_title( $post_data['slug'] );
                    }

                    if ( 'auto-draft' == $post_status ) {
                        // If the post is still an auto-draft, we may need to invent a slug.
                        if ( empty( $post_args[ 'post_name' ] ) ) {
                            $post_args[ 'post_name' ] = sanitize_title( $post_data['title'] );
                        }
                        // Switch the post status to "draft"
                        $post_args[ 'post_status' ] = 'draft';
                    }

                    $post_id = wp_update_post( $post_args, true );
                    if ( is_wp_error( $post_id ) ) {
                        $errors = $post_id->get_error_messages();
                        foreach ( $errors as $error ) {
                            $towrite = PHP_EOL . 'wp_error: ' . print_r($error, TRUE);
                        }
                        $fp = fopen('ccgp-save-errors.txt', 'a');
                        fwrite($fp, $towrite);
                        fclose($fp);
                    } else {
                        // Save was successful, let's store the access level as post meta.
                        $update_postmeta = update_post_meta( $post_id, 'ccgp_visibility', $post_data['visibility'] );
                    }
                }
            }

			if ( $group_term && $success ) {
				bp_core_add_message( __( 'Hub Pages settings were successfully updated.', 'cc-group-pages' ) );
			} else {
				bp_core_add_message( __( 'There was an error updating the Hub Pages settings, please try again.', 'cc-group-pages' ), 'error' );
			}
		}

        /**
         * Use this function to display the actual content of your group extension when the nav item is selected
         */
        function display( $group_id = null ) {
			// Template location is handled via the template stack. see load_template_filter()
			bp_get_template_part( 'groups/single/pages/pages' );
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

/**
 * Output the tab settings form.
 *
 * @since  1.0.0
 * @return html
 */
function ccgp_setup_settings_form( $group_id ){
    $page_order = ccgp_get_page_order( $group_id, $jsonify = false );
    $used_pages = array();
    $access_levels = ccgp_get_access_settings_options();
    ?>
    <a href="#" id="ccgp-add-tab" class="button">Add a new tab</a>
    <div id="tabs-container" class="clear clear-both">
        <?php if ( ! empty( $page_order ) && is_array( $page_order ) ) {
            foreach ( $page_order as $tab_id => $tab_details ) { ?>
               <fieldset id="tabs-<?php echo $tab_id; ?>" class="tab-details half-block">
                    <h4 class="tab-title"><?php if ( ! empty( $tab_details['label'] ) ) {
                        echo $tab_details['label'];
                    } else {
                        echo 'Tab ' . $tab_id;
                    }?></h4>
                    <a href="#" class="toggle-details-pane">Edit details</a>
                    <div class="details-pane">
                        <label for="ccgp-tab-<?php echo $tab_id; ?>-label" >Tab Label</label>
                        <input type="text" id="ccgp-tab-<?php echo $tab_id; ?>-label" name="ccgp-tabs[<?php echo $tab_id; ?>][label]" value="<?php echo $tab_details['label']; ?>"/>
                        <p class="info">This is the label as shown on the navigation tab</p>

                        <label for="ccgp-tab-<?php echo $tab_id; ?>-slug" >Tab Slug (optional)</label>
                        <input type="text" id="ccgp-tab-<?php echo $tab_id; ?>-slug" name="ccgp-tabs[<?php echo $tab_id; ?>][slug]" value="<?php echo $tab_details['slug']; ?>"/>
                        <p class="info">The piece of the URL that follows your group&rsquo;s slug. E.g. http://www.communitycommons.org/groups/my-group/<strong>slug-to-use</strong></p>

                        <label for="ccgp-tab-<?php echo $tab_id; ?>-visibility">Visibility</label>
                        <select name="ccgp-tabs[<?php echo $tab_id; ?>][visibility]" id="ccgp-tab-<?php echo $tab_id; ?>-visibility" class="tab-visibility">
                            <?php foreach ( $access_levels as $key => $value ) { ?>
                                <option value="<?php echo $value['bp_level'] ?>" <?php selected( $tab_details['visibility'], $value['bp_level'] ); ?> data-level="<?php echo $key; ?>"><?php echo $value['label']; ?></option>
                            <?php } ?>
                        </select>

                        <label><input type="checkbox" name="ccgp-tabs[<?php echo $tab_id; ?>][show-tab]" id="ccgp-tab-<?php echo $tab_id; ?>-show-tab" class="show-tab-setting" value="1" <?php checked( $tab_details['show-tab'], '1' ) ?> /> Include this tab in the hub navigation.</label>
                        <p class="info">(Hiding the tab is non-standard behavior and should be avoided, unless you&rsquo;ve got another navigation method in place.)</p>

                        <div id="navigation-order-<?php echo $tab_id; ?>" class="navigation-order-container <?php if ( ! isset( $tab_details['show-tab'] ) ) { echo 'toggled-off'; } ?>">
                            <label for="ccgp-tab-<?php echo $tab_id; ?>-nav-order" >Placement in Hub Navigation (optional)</label>
                            <input type="text" id="ccgp-tab-<?php echo $tab_id; ?>-nav-order" name="ccgp-tabs[<?php echo $tab_id; ?>][nav_order]" value="<?php echo $tab_details['nav_order']; ?>"/>
                            <p class="info">Input a number (1-100) to change this tab&rsquo;s placement in the hub&rsquo;s navigation. Low numbers end up to the left by &ldquo;Home,&rdquo; high numbers end up near &ldquo;Manage.&rdquo;</p>
                        </div>

                        <a href="#" class="remove-tab">Remove this tab</a>
                    </div>

                    <div class="page-list clear">
                        <h5 class="page-list-header">Pages in this section</h5>
                        <a href="#" class="ccgp-add-page button alignright">Add a new page</a>
                        <p class="info">The first page is used as this section's landing page.</p>
                        <ul id="section-<?php echo $tab_id; ?>" class="sortable no-bullets">
                            <?php if ( ! empty( $tab_details['pages'] ) && is_array( $tab_details['pages'] ) ) {
                                    foreach ( $tab_details['pages'] as $order => $post_details ) {
                                        $used_pages[] = $post_details['post_id'];
                                        $post_object = get_post( $post_details['post_id'] );
                                     ?>
                                    <li class="draggable" id="post-<?php echo $post_details['post_id'] ?>">
                                        <span class="arrow-up"></span><span class="arrow-down"></span>Title: <?php echo get_the_title( $post_details['post_id'] ); ?> <a href="#" class="toggle-details-pane">Edit details</a>
                                        <div class="details-pane">
                                            <label for="ccgp-page-<?php echo $post_details['post_id'] ?>-title" >Page Title</label>
                                            <input type="text" id="ccgp-page-<?php echo $post_details['post_id'] ?>-title" name="ccgp-pages[<?php echo $post_details['post_id'] ?>][title]" value="<?php echo get_the_title( $post_details['post_id'] ); ?>"/>
                                            <label for="ccgp-page-<?php echo $post_details['post_id']; ?>-slug" >Page Slug</label>
                                            <input type="text" id="ccgp-page-<?php echo $post_details['post_id'] ?>-slug" name="ccgp-pages[<?php echo $post_details['post_id'] ?>][slug]" value="<?php echo $post_object->post_name; ?>"/>
                                            <div class="page-visibility-control">
                                                <label for="cccgp-page-<?php echo $post_details['post_id']; ?>-visibility">Access</label>
                                                <select name="ccgp-pages[<?php echo $post_details['post_id']; ?>][visibility]" id="ccgp-page-<?php echo $post_details['post_id']; ?>-visibility" class="page-visibility">
                                            </div>
                                                <?php foreach ( $access_levels as $key => $value ) { ?>
                                                    <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>" <?php selected( $post_details['visibility'] , $value['bp_level'] ); ?>><?php echo $value['label']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </li>
                            <?php }
                            } ?>
                        </ul>
                    </div>
                </fieldset>
        <?php
            } // End foreach ( $page_order as $tab_id => $tab_details )
        } // End if ( ! empty( $page_order ) ) ?>
        <fieldset id="unused-pages" class="tab-details half-block">
            <?php
            $all_pages = ccgp_get_all_group_pages( $group_id );
            // var_dump($all_pages);
            ?>
            <h4 class="tab-title">Free Parking</h4>
            <p class="info">Pages in this list aren't displayed anywhere.</p>
            <div class="page-list">
                <ul id="unused-page-list" class="sortable no-bullets">
                    <?php
                    if ( ! empty( $all_pages ) ) {
                        foreach ( $all_pages as $page ) {
                            if ( ! in_array( $page->ID, $used_pages ) ) {
                                $post_object = get_post( $page->ID );
                                ?>
                                    <li class="draggable" id="post-<?php echo $page->ID; ?>">
                                        <span class="arrow-up"></span><span class="arrow-down"></span>Title: <?php echo $page->post_title; ?> <a href="#" class="toggle-details-pane">Edit details</a>
                                        <div class="details-pane">
                                            <label for="ccgp-page-<?php echo $page->ID; ?>-title" >Page Title</label>
                                            <input type="text" id="ccgp-page-<?php echo $page->ID; ?>-title" name="ccgp-pages[<?php echo $page->ID; ?>][title]" value="<?php echo $page->post_title; ?>"/>
                                            <label for="ccgp-page-<?php echo $page->ID; ?>-slug" >Page Slug</label>
                                            <input type="text" id="ccgp-page-<?php echo $page->ID; ?>-slug" name="ccgp-pages[<?php echo $page->ID; ?>][slug]" value="<?php echo $post_object->post_name; ?>"/>
                                            <label for="cccgp-page-<?php echo $page->ID; ?>-visibility">Access</label>
                                            <select name="ccgp-pages[<?php echo $page->ID;?>][visibility]" id="ccgp-page-<?php echo $tab_id; ?>-visibility">
                                                <?php foreach ( $access_levels as $key => $value ) { ?>
                                                    <option value="<?php echo $value['bp_level'] ?>" data-level="<?php echo $key; ?>" ><?php echo $value['label']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </li>
                                <?php
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </fieldset>
    </div>
    <!-- This input keeps track of which pages go in which tab, and in which order. -->
    <input type="hidden" id="pages-order" name="pages-order" val="" size=90/>
    <?php
}

/**
 * Fetch a group's tab organization and page order.
 *
 * @since     1.0.0
 * @return    array
 */
function ccgp_get_page_order( $group_id = 0, $jsonify = false ) {
    if ( ! $group_id ) {
        return;
    }
    // @TODO: Add caching?
    $page_order = groups_get_groupmeta( $group_id, 'ccgp_page_order' );
        // $towrite = PHP_EOL . 'in ccgp_get_page_order: ' . print_r($page_order, TRUE);
        // $towrite .= PHP_EOL . 'group_id: ' . print_r($group_id, TRUE);
        // $fp = fopen('ccgp-save.txt', 'a');
        // fwrite($fp, $towrite);
        // fclose($fp);
    if ( $jsonify ) {
        $page_order = json_encode( $page_order );
    }
    return $page_order;
}

/**
 * Fetch the details for a single tab by matching the tab slug.
 *
 * @since     1.0.0
 * @return    array
 */
function ccgp_get_single_tab_details( $group_id, $current_action ) {
    if ( ! $group_id ) {
        $group_id = bp_get_current_group_id();
    }
    $tab_details = array();
    if ( empty( $current_action ) ) {
        $current_action = bp_current_action();
    }
    $page_order = ccgp_get_page_order( $group_id );
    foreach ( (array) $page_order as $key => $tab_details ) {
        if ( $current_action == $tab_details['slug'] ) {
            $tab_details = $page_order[$key];
            break;
        }
    }
    return $tab_details;
}

/**
 * Fetch all the pages tab slugs for the group.
 *
 * @since     1.0.0
 * @return    array
 */
function ccgp_get_tab_slugs( $group_id = 0 ) {
    $tab_slugs = array();

    if ( ! $group_id ) {
        return $tab_slugs;
    }
    $page_order = ccgp_get_page_order( $group_id );

    if ( is_array( $page_order ) ) {
        foreach ( $page_order as $key => $tab_details) {
            $tab_slugs[] = $tab_details['slug'];
        }
    }

    // $towrite = PHP_EOL . 'in ccgp_get_page_order: ' . print_r($tab_slugs, TRUE);
    // // $towrite .= PHP_EOL . 'group_id: ' . print_r($group_id, TRUE);
    // $fp = fopen('ccgp-get-tab-slugs.txt', 'a');
    // fwrite($fp, $towrite);
    // fclose($fp);

    return $tab_slugs;
}

/**
 * Fetch all the pages associated with the group.
 *
 * @since     1.0.0
 * @return    array of WP_Post objects
 */
function ccgp_get_all_group_pages( $group_id = 0 ) {
    if ( empty( $group_id ) ) {
        $group_id = bp_get_current_group_id();
    }
    $instance = new CC_Group_Pages();
    $args = array(
                'post_type' => 'cc_group_page',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'ccgp_related_groups',
                        'field' => 'id',
                        'terms' => $instance->get_group_term_id( $group_id ),
                        'include_children' => false,
                        // 'operator' => 'IN'
                    )
                ),
                'post_status' => array( 'publish', 'draft'),
                'posts_per_page' => -1,
                // 'orderby' => array( 'post_title' => 'ASC' ),
            );
    return get_posts( $args );
}

/**
 * Output a standard array of the possible access settings.
 *
 * @since     1.0.0
 * @return    array
 */
function ccgp_get_access_settings_options() {
    $access_levels = array(
        0 => array( 'bp_level' => 'anyone', 'label' => 'Anyone' ),
        1 => array( 'bp_level' => 'loggedin', 'label' => 'Logged-in Site Members' ),
        2 => array( 'bp_level' => 'member', 'label' => 'Hub Members' ),
        3 => array( 'bp_level' => 'mod', 'label' => 'Hub Moderators' ),
        4 => array( 'bp_level' => 'admin', 'label' => 'Hub Administrators' ),
    );
    return $access_levels;
}

/**
 * Output a human readable string of a specific access settings.
 *
 * @since     1.0.0
 * @return    array
 */
function ccgp_get_access_setting_human_readable( $level = 'anyone' ) {
    $retval = "Anyone";

    $access_levels = ccgp_get_access_settings_options();

    foreach ( $access_levels as $key => $access ) {
        if ( $access['bp_level'] == $level ) {
            $retval = $access['label'];
        }
    }

    return $retval;
}

/**
 * Make sure that the page access settings are at least as restrictive as their
 * parent tab.
 *
 * @since     1.0.0
 * @return    bool
 */
function ccgp_is_access_setting_more_restrictive( $par = 'anyone', $setting = 'anyone' ) {
    $access_levels = ccgp_get_access_settings_options();
    $par_level = 0;
    $setting_level = 0;
    $is_restrictive = false;

    foreach ( $access_levels as $key => $level ) {
        if ( $par == $level['bp_level'] ) {
            $par_level = $key;
        }
        if ( $setting == $level['bp_level'] ) {
            $setting_level = $key;
        }
    }

    if ( $setting_level >= $par_level ) {
        $is_restrictive = true;
    }

    return $is_restrictive;
}

/**
 * Reverse lookup the group id from the term.
 *
 * @since     1.0.0
 * @return    string
 */
function ccgp_get_group_id_from_post_id( $post_id = false ) {
        // $towrite = PHP_EOL . 'incoming post_id: ' . print_r($post_id, TRUE);
        // $fp = fopen('ccgp-save.txt', 'a');
        // fwrite($fp, $towrite);
        // fclose($fp);

    if ( ! $post_id )
        return 0;

    $term_list = wp_get_post_terms( $post_id, 'ccgp_related_groups', array("fields" => "all"));
        // $towrite = PHP_EOL . 'term list: ' . print_r($term_list, TRUE);
        // $fp = fopen('ccgp-save.txt', 'a');
        // fwrite($fp, $towrite);
        // fclose($fp);
    if ( is_wp_error( $term_list ) ) {
        return 0;
    }

    $group_id = (int) str_replace( 'ccgp_related_group_', '', $term_list[0]->slug );

    return $group_id;
}