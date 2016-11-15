<?php
/**
 * CC BuddyPress Group Pages
 *
 * @package   CC BuddyPress Group Pages
 * @author    CARES staff
 * @license   GPL-2.0+
 * @copyright 2014 CommmunityCommons.org
 */

/*
 * Class to add display hooks for the tabs for this group.
 *
 * @since 1.1.0
 */
class CCGP_Tab_Display {

    /**
     * The current group ID.
     *
     * @since    1.1.0
     * @access   protected
     * @var      int    $group_id    The ID of the current group.
     */
    protected $group_id;

    /**
     * The calculated access status for the tabs of this group.
     *
     * @since    1.1.0
     * @access   protected
     * @var      array    $user_has_access    The access statuses, keyed by slug.
     */
    protected $user_has_access = array();

    public function __construct( $group_id = 0 ) {
        if ( empty( $group_id ) ) {
            $group_id = bp_get_current_group_id();
        }
        $this->group_id = $group_id;
        $this->tab_creation_loop();
    }

    /**
     * Run setup routine if needed for this group.
     *
     * @since 1.1.0
     *
     * @param array $tab Tab details.
     * @return void
     */
    public function tab_creation_loop() {
        $tabs = array();
        if ( (bool) groups_get_groupmeta( $this->group_id, 'ccgp_is_enabled' ) ) {
            $tabs = ccgp_get_page_order( $this->group_id, $jsonify = false );
        }

        if ( empty( $tabs ) || ! is_array( $tabs ) ) {
            return;
        }

        foreach ( $tabs as $key => $tab ) {
            $this->add_display_hooks( $tab );
        }

    }

    /**
     * Set up nav items and register screen fucntions for this group's tabs.
     *
     * @since 1.1.0
     *
     * @param array $tab Tab details.
     * @return void
     */
    public function add_display_hooks( $tab ) {
        // If the user can visit the screen, we register it.
        if ( ! isset( $tab['show-tab'] ) ) {
            $can_access = false;
        } else {
            $can_access = $this->user_meets_access_condition( $tab['visibility'] );
        }

        $this->user_has_access[ $tab['slug'] ] = $can_access;

        $towrite = PHP_EOL . '$this->user_has_access: ' . print_r( $this->user_has_access, TRUE );
        $fp = fopen('ccgp-dynamic-classes.txt', 'a');
        fwrite($fp, $towrite);
        fclose($fp);

        $group_permalink = bp_get_group_permalink( groups_get_current_group() );
        $group_slug      = bp_get_current_group_slug();
        $nav_order = ! empty( $tab['nav_order'] ) ? (int) $tab['nav_order'] : 81;

        if ( $can_access ) {
            bp_core_create_subnav_link( array(
                'name'            => $tab['label'],
                'slug'            => $tab['slug'],
                'parent_slug'     => $group_slug,
                'parent_url'      => $group_permalink,
                'position'        => $nav_order,
                'item_css_id'     => 'nav-' . $tab['slug'],
                'screen_function' => array( $this, '_display_hook' ),
                'user_has_access' => $can_access,
                'no_access_url'   => $group_permalink,
            ), 'groups' );
        }

        // And register the screen function.
        bp_core_register_subnav_screen_function( array(
            'slug'            => $tab['slug'],
            'parent_slug'     => $group_slug,
            'screen_function' => array( $this, '_display_hook' ),
            'user_has_access' => $can_access,
            'no_access_url'   => $group_permalink,
        ), 'groups' );

        // When we are viewing the extension display page, set the title and options title.
        if ( bp_is_current_action( $tab['slug'] ) ) {
            add_filter( 'bp_group_user_has_access',   array( $this, 'group_access_protection' ), 10, 2 );
            add_action( 'bp_template_content_header', create_function( '', 'echo "' . esc_attr( $tab['label'] ) . '";' ) );
            add_action( 'bp_template_title',          create_function( '', 'echo "' . esc_attr( $tab['label'] ) . '";' ) );
        }
    }

    /**
     * Hook the main display method, and loads the template file.
     *
     * @since 1.1.0
     */
    public function _display_hook() {
        add_action( 'bp_template_content', function() {
            bp_get_template_part( 'groups/single/pages/pages' );
        } );

        // bp_get_template_part( 'groups/single/pages/pages' );

        bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'groups/single/plugins' ) );
    }

    /**
     * Check whether the current user meets an access condition.
     *
     * @since 1.1.0
     *
     * @param string $access_condition 'anyone', 'loggedin', 'member',
     *                                 'mod', 'admin' or 'noone'.
     * @return bool
     */
    protected function user_meets_access_condition( $access_condition ) {
        if ( current_user_can( 'bp_moderate' ) ) {
            return true;
        }

        switch ( $access_condition ) {
            case 'admin' :
                $meets_condition = groups_is_user_admin( bp_loggedin_user_id(), $this->group_id );
                break;

            case 'mod' :
                if ( groups_is_user_admin( bp_loggedin_user_id(), $this->group_id ) || groups_is_user_mod( bp_loggedin_user_id(), $this->group_id ) ) {
                    $meets_condition = true;
                } else {
                    $meets_condition = false;
                }
                break;

            case 'member' :
                $meets_condition = groups_is_user_member( bp_loggedin_user_id(), $this->group_id );
                break;

            case 'loggedin' :
                $meets_condition = is_user_logged_in();
                break;

            case 'noone' :
                $meets_condition = false;
                break;

            case 'anyone' :
            default :
                $meets_condition = true;
                break;
        }

        return $meets_condition;
    }

    /**
     * Filter the access check in bp_groups_group_access_protection() for this extension.
     *
     * Note that $no_access_args is passed by reference, as there are some
     * circumstances where the bp_core_no_access() arguments need to be
     * modified before the redirect takes place.
     *
     * @since 1.1.0
     *
     * @param bool  $user_can_visit Whether or not the user can visit the tab.
     * @param array $no_access_args Array of args to help determine access.
     * @return bool
     */
    public function group_access_protection( $user_can_visit, &$no_access_args ) {
        if ( isset( $this->user_has_access[ bp_current_action() ] ) ) {
            $user_can_visit = $this->user_has_access[ bp_current_action() ];
        }

        if ( ! $user_can_visit && is_user_logged_in() ) {
            $current_group = groups_get_group( $this->group_id );

            $no_access_args['message'] = __( 'You do not have access to this content.', 'buddypress' );
            $no_access_args['root'] = bp_get_group_permalink( $current_group ) . 'home/';
            $no_access_args['redirect'] = false;
        }

        return $user_can_visit;
    }

}