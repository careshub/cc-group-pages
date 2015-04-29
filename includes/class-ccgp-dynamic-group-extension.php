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

if ( class_exists( 'BP_Group_Extension' ) ) { // Recommended, to prevent problems during upgrade or when Groups are disabled

// Only bother when in a single group
    if ( $group_id = bp_get_current_group_id() ) {
        $tab_structure = array();

        if ( (bool) groups_get_groupmeta( $group_id, "ccgp_is_enabled" ) ) {
            $tab_structure = ccgp_get_page_order( $group_id, $jsonify = false );
        }
        // $towrite = PHP_EOL . 'tab structure in dynamic class generator: ' . print_r( $tab_structure, TRUE );
        // $fp = fopen('ccgp-dynamic-classes.txt', 'a');
        // fwrite($fp, $towrite);
        // fclose($fp);

        if ( ! empty( $tab_structure ) && is_array( $tab_structure )  ) {
            // We'll need an iterator to choose the right extension.
            $j = 1;

            foreach ( $tab_structure as $key => $tab_details ) {

                // Class names can't be declared dynamically, so we'll just have to set a maximum and loop them with hardcoded class names.

                if ( $j == 1 ) {

                    class CCGP_Pages_Tab_One_Extension extends BP_Group_Extension {
                        function __construct() {

                            $tab_details = ccgp_get_group_extension_params( 1 );

                            // The BP group member schema thinks of mods and admins as separate groups, so if we choose "mods and above", we need to specify both groups.
                            if ( 'mod' == $tab_details['visibility'] ) {
                                $visibility = array( 'mod', 'admin' );
                            } else {
                                $visibility = $tab_details['visibility'];
                            }

                            $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $visibility, // BP 2.1
                                    'show_tab'          => $visibility, // BP 2.1
                                    // 'nav_item_position' => 43,
                                    'screens' => array(
                                        'edit' => array(
                                            'enabled' => false,
                                        ),
                                        'create' => array(
                                            'enabled' => false,
                                        ),
                                        'admin' => array(
                                            'enabled' => false,
                                        ),
                                    ),
                                );
                            // $towrite = PHP_EOL . 'tab details: ' . print_r( $tab_details, TRUE );
                            // $towrite .= PHP_EOL . 'init args: ' . print_r( $args, TRUE );
                            // $fp = fopen('ccgp-dynamic-classes.txt', 'a');
                            // fwrite($fp, $towrite);
                            // fclose($fp);
                            parent::init( $args );

                        }
                        /**
                         * settings_screen() is the catch-all method for displaying the content
                         * of the edit, create, and Dashboard admin panels
                         */
                        function settings_screen( $group_id = 0 ) {            }

                        /**
                         * settings_screen_save() contains the catch-all logic for saving
                         * settings from the edit, create, and Dashboard admin panels
                         */
                        function settings_screen_save( $group_id = 0 ) {}

                        /**
                         * Use this function to display the actual content of your group extension when the nav item is selected
                         */
                        function display( $group_id = null ) {
                            // Template location is handled via the template stack. see load_template_filter()
                            bp_get_template_part( 'groups/single/pages/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_One_Extension' );

                } else if ( $j == 2 ) {

                    class CCGP_Pages_Tab_Two_Extension extends BP_Group_Extension {

                        function __construct() {

                            $tab_details = ccgp_get_group_extension_params( 2 );
                            // The BP group member schema thinks of mods and admins as separate groups, so if we choose "mods and above", we need to specify both groups.
                            if ( 'mod' == $tab_details['visibility'] ) {
                                $visibility = array( 'mod', 'admin' );
                            } else {
                                $visibility = $tab_details['visibility'];
                            }

                            $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $visibility, // BP 2.1
                                    'show_tab'          => $visibility, // BP 2.1
                                    // 'nav_item_position' => 43,
                                    'screens' => array(
                                        'edit' => array(
                                            'enabled' => false,
                                        ),
                                        'create' => array(
                                            'enabled' => false,
                                        ),
                                        'admin' => array(
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
                        function settings_screen( $group_id = 0 ) {            }

                        /**
                         * settings_screen_save() contains the catch-all logic for saving
                         * settings from the edit, create, and Dashboard admin panels
                         */
                        function settings_screen_save( $group_id = 0 ) {}
                        /**
                         * Use this function to display the actual content of your group extension when the nav item is selected
                         */
                        function display( $group_id = null ) {
                            // Template location is handled via the template stack. see load_template_filter()
                            bp_get_template_part( 'groups/single/pages/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Two_Extension' );

                } else if ( $j == 3 ) {

                    class CCGP_Pages_Tab_Three_Extension extends BP_Group_Extension {

                        function __construct() {

                            $tab_details = ccgp_get_group_extension_params( 3 );
                            // The BP group member schema thinks of mods and admins as separate groups, so if we choose "mods and above", we need to specify both groups.
                            if ( 'mod' == $tab_details['visibility'] ) {
                                $visibility = array( 'mod', 'admin' );
                            } else {
                                $visibility = $tab_details['visibility'];
                            }

                            $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $visibility, // BP 2.1
                                    'show_tab'          => $visibility, // BP 2.1
                                    // 'nav_item_position' => 43,
                                    'screens' => array(
                                        'edit' => array(
                                            'enabled' => false,
                                        ),
                                        'create' => array(
                                            'enabled' => false,
                                        ),
                                        'admin' => array(
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
                        function settings_screen( $group_id = 0 ) {            }

                        /**
                         * settings_screen_save() contains the catch-all logic for saving
                         * settings from the edit, create, and Dashboard admin panels
                         */
                        function settings_screen_save( $group_id = 0 ) {}
                        /**
                         * Use this function to display the actual content of your group extension when the nav item is selected
                         */
                        function display( $group_id = null ) {
                            // Template location is handled via the template stack. see load_template_filter()
                            bp_get_template_part( 'groups/single/pages/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Three_Extension' );

                } else if ( $j == 4 ) {

                    class CCGP_Pages_Tab_Four_Extension extends BP_Group_Extension {

                        function __construct() {

                            $tab_details = ccgp_get_group_extension_params( 4 );
                            // The BP group member schema thinks of mods and admins as separate groups, so if we choose "mods and above", we need to specify both groups.
                            if ( 'mod' == $tab_details['visibility'] ) {
                                $visibility = array( 'mod', 'admin' );
                            } else {
                                $visibility = $tab_details['visibility'];
                            }

                            $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $visibility, // BP 2.1
                                    'show_tab'          => $visibility, // BP 2.1
                                    // 'nav_item_position' => 43,
                                    'screens' => array(
                                        'edit' => array(
                                            'enabled' => false,
                                        ),
                                        'create' => array(
                                            'enabled' => false,
                                        ),
                                        'admin' => array(
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
                        function settings_screen( $group_id = 0 ) {            }

                        /**
                         * settings_screen_save() contains the catch-all logic for saving
                         * settings from the edit, create, and Dashboard admin panels
                         */
                        function settings_screen_save( $group_id = 0 ) {}
                        /**
                         * Use this function to display the actual content of your group extension when the nav item is selected
                         */
                        function display( $group_id = null ) {
                            // Template location is handled via the template stack. see load_template_filter()
                            bp_get_template_part( 'groups/single/pages/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Four_Extension' );
                }
                $j++;
            } // end foreach

        } // if ( ! empty( $tab_structure ) && is_array( $tab_structure )  ) :
    } // if ( bp_is_group() ) :
} // class_exists( 'BP_Group_Extension' )


/**
* Get the parameters for a single tab.
* Used when creating the tab within the BP_Group_Extension class extension, 
* since variables cannot be passed into the class.
* 
* @since 1.0.0
*/
function ccgp_get_group_extension_params( $position ) {
    // Since we're talking positions, not keys, we'll use array_slice to get one piece only
    $tab_structure = ccgp_get_page_order( bp_get_current_group_id(), $jsonify = false );
    $tab_details = array_slice( $tab_structure, $position - 1, 1 );
    return current( $tab_details );
}