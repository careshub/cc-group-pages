<?php
/*
* Used to display the list, single and edit view of the Group Pages pane.
*/
if ( class_exists( 'CC_Group_Pages' ) ) :

	$ccgp_class = new CC_Group_Pages();
    $pages_query = $ccgp_class->get_pages_query_for_tab();
    $pages_in_tab = new WP_Query( $pages_query );
    $requested_page = bp_action_variable();
  	// var_dump($requested_page);


	// $page_ids_in_tab = wp_list_pluck( $pages_in_tab, 'post_id' );
	// Do one WP_Query to build the subnav and the page content.
	// $group_tabs = ccgp_get_page_order( bp_get_current_group_id() );

	// echo '<pre>';
	// var_dump($group_tabs);
	// echo "all page ids ";
	// var_dump( $page_ids_in_tab );
	// echo 'user_access ';
	// var_dump($user_access);
	// echo "user can see ";
	// var_dump( $pages_to_fetch );
	// var_dump( $pages_in_tab );
	// echo '</pre>';

	// Build the subnavigation
	if ( ! $ccgp_class->is_page_management_tab() || $ccgp_class->current_user_can_manage() ) :
	?>
		<div id="subnav" class="item-list-tabs no-ajax" role="navigation">
			<ul>
				<?php if ( ! $ccgp_class->is_page_management_tab() && ! $ccgp_class->is_post_edit() && $pages_in_tab->have_posts() ) {
					$nav_count = 1;
					while ( $pages_in_tab->have_posts() ): $pages_in_tab->the_post();
						global $post;
						// var_dump( $post );
						// var_dump( bp_action_variable() );
						?>
						<li<?php if ( $requested_page == $post->post_name || ( empty( $requested_page)  && $nav_count == 1 ) ) {
							echo ' class="current selected"';
						} ?>>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</li>
				<?php
					$nav_count++;
					endwhile;
					 // We're going to use the results set again, so rewind.
					rewind_posts();
				} //endif
				?>
			<?php //ccgn_options_menu(); ?>
			<?php if ( $ccgp_class->current_user_can_manage() ) : ?>
				<li class="last"><a href="<?php echo $ccgp_class->get_manage_permalink(); ?>" class="">Manage Tabs</a></li>
			<?php endif; ?>
			<?php if ( ! $ccgp_class->is_page_management_tab() && $ccgp_class->current_user_can_post() ) : ?>
				<li class="last"><a href="<?php echo $ccgp_class->get_base_permalink(); ?>" class="">Manage Pages</a></li>
			<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php
	if( $ccgp_class->is_single_post( $requested_page ) ) {
		// This means that a post has been specified. So we should figure out which one it is and show it.

		// BuddyPress forces comments closed on BP pages. Override that.
		remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );

		if ( $pages_in_tab->have_posts() ) {

			do_action( 'bp_before_group_pages_content' );

			while ( $pages_in_tab->have_posts() ): $pages_in_tab->the_post();
				global $post;
				if ( $requested_page == $post->post_name ) {
					bp_get_template_part( 'groups/single/pages/pages-single' );
					comments_template();
					break;
				}
			endwhile;

			do_action( 'bp_after_group_pages_content' );

		} else {
		?>
			<div id="message" class="info">
				<p><?php _e( 'We aren\'t able to find that post.', 'bcg' ); ?></p>
			</div>
		<?php
		} // endif;
		// BuddyPress forces comments closed on BP pages. Put the filter back.
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );

    } else if ( $ccgp_class->is_post_edit() ) {

		$ccgp_class->get_post_form( bp_get_group_id() );

	} else if ( $ccgp_class->is_page_management_tab() ) {
		// This is a flat list of all the group's pages for easy links to editing, etc.
		$manage_tab_label = $ccgp_class->get_manage_pages_slug();
		?>
		<div id="message" class="info">
			<p class="info">Note: This tab is only accessible to hub admins and moderators.</p>
		</div>
		<h5>Pages associated with your hub.</h5>
		<ul>
		<?php
		while ( $pages_in_tab->have_posts() ): $pages_in_tab->the_post();
			global $post;
			if ( $ccgp_class->current_user_can_post( $post->ID ) ) :
				?>
				<li>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<?php
					$tab_label = $ccgp_class->get_tab_label_from_group_post_ids( bp_get_current_group_id(), $post->ID );
					if ( $tab_label != $manage_tab_label ) {
						echo 'in the tab: ' . $tab_label;
					} else {
						echo "not assigned to a tab";
					}
					?> |
					<?php ccgp_the_post_edit_link( $post->ID ); ?>
				</li>
				<?php
			endif;
		endwhile;
		echo "</ul>";

    } else { // Must be the default view
		?>

		<?php if ( $pages_in_tab->have_posts() ) {
			do_action( 'bp_before_group_pages_content' );

			while ( $pages_in_tab->have_posts() ): $pages_in_tab->the_post();
				global $post;
				bp_get_template_part( 'groups/single/pages/pages-single' );
				comments_template();
				break; // We break after the first result.
			endwhile;

			do_action( 'bp_after_group_pages_content' );

		} else { ?>

			<div id="message" class="info">
				<p><?php _e( 'No pages have been published yet.', 'bcg' ); ?></p>
			</div>

		<?php } // endif $pages_in_tab->have_posts()
	}// End display checks.
endif; // if ( class_exists( 'CC_Group_Pages' ) )