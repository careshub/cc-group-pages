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

        $tab_structure = ccgp_get_page_order( $group_id, $jsonify = false );
        $towrite = PHP_EOL . 'tab structure in dynamic class generator: ' . print_r( $tab_structure, TRUE );
        $fp = fopen('ccgp-dynamic-classes.txt', 'a');
        fwrite($fp, $towrite);
        fclose($fp);

        if ( ! empty( $tab_structure ) && is_array( $tab_structure )  ) {

            foreach ( $tab_structure as $key => $tab_details ) {


                // Class names can't be declared dynamically, so maybe we'll just have to set a maximum and loop them with hardcoded class names.

                if ( $key == 1 ) {
                    class CCGP_Pages_Tab_One_Extension extends BP_Group_Extension {
                        function __construct() {

                        $tab_details = ccgp_get_group_extension_params( 1 );
                        $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $tab_details['visibility'], // BP 2.1
                                    'show_tab'          => $tab_details['visibility'], // BP 2.1
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
                            $towrite = PHP_EOL . 'tab details: ' . print_r( $tab_details, TRUE );
                            $towrite .= PHP_EOL . 'init args: ' . print_r( $args, TRUE );
                            $fp = fopen('ccgp-dynamic-classes.txt', 'a');
                            fwrite($fp, $towrite);
                            fclose($fp);
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
                            bp_get_template_part( 'groups/single/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_One_Extension' );
                } else if ( $key == 2 ) {
                    class CCGP_Pages_Tab_Two_Extension extends BP_Group_Extension {

                        function __construct() {

                        $tab_details = ccgp_get_group_extension_params( 2 );

                        $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $tab_details['visibility'], // BP 2.1
                                    'show_tab'          => $tab_details['visibility'], // BP 2.1
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
                            bp_get_template_part( 'groups/single/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Two_Extension' );
                } else if ( $key == 3 ) {
                    class CCGP_Pages_Tab_Three_Extension extends BP_Group_Extension {

                        function __construct() {

                        $tab_details = ccgp_get_group_extension_params( 3 );

                        $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $tab_details['visibility'], // BP 2.1
                                    'show_tab'          => $tab_details['visibility'], // BP 2.1
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
                            bp_get_template_part( 'groups/single/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Three_Extension' );
                } else if ( $key == 4 ) {
                    class CCGP_Pages_Tab_Four_Extension extends BP_Group_Extension {

                        function __construct() {

                        $tab_details = ccgp_get_group_extension_params( 4 );

                        $args = array(
                                    'slug'              => $tab_details['slug'],
                                    'name'              => $tab_details['label'],
                                    'access'            => $tab_details['visibility'], // BP 2.1
                                    'show_tab'          => $tab_details['visibility'], // BP 2.1
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
                            bp_get_template_part( 'groups/single/pages' );
                        }

                    }
                    bp_register_group_extension( 'CCGP_Pages_Tab_Four_Extension' );
                }

            } // end foreach

        } // if ( ! empty( $tab_structure ) && is_array( $tab_structure )  ) :
    } // if ( bp_is_group() ) :
} // class_exists( 'BP_Group_Extension' )

function ccgp_get_group_extension_params( $key ) {
    $tab_structure = ccgp_get_page_order( bp_get_current_group_id(), $jsonify = false );
    return $tab_structure[$key];
}