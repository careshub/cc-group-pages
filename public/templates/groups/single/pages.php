<?php
/*
* Used to display the list, single and edit view of the Group Pages pane.
*/
if ( class_exists( 'CC_Group_Pages' ) ) : 

	$ccgp_class = new CC_Group_Pages();
	?>
	<div id="subnav" class="item-list-tabs no-ajax" role="navigation">
		<ul> 
			<li<?php if ( $ccgp_class->is_home() ) { echo ' class="current selected"'; } ?>><a href="<?php echo $ccgp_class->get_base_permalink(); ?>" class="table-of-contents">Table of Contents</a></li>
		<?php //ccgn_options_menu(); ?>
		<?php if ( ! $ccgp_class->is_post_edit() && $ccgp_class->current_user_can_post() ) : ?>
			<li class="last"><a href="<?php echo $ccgp_class->get_create_permalink(); ?>" class="create-new-page">Create new page</a></li>
		<?php endif; ?>
		</ul>
	</div>

	<?php
	if( $ccgp_class->is_single_post() ) {
		// BuddyPress forces comments closed on BP pages. Override that.
		remove_filter( 'comments_open', 'bp_comments_open', 10, 2 );

        // echo "is single post";
		$q = new WP_Query( $ccgp_class->get_query() );

		if ( $q->have_posts() ) : 

			do_action( 'bp_before_group_pages_content' );

			while( $q->have_posts()):$q->the_post();
				bp_get_template_part( 'groups/single/pages-single' );
				comments_template();
			endwhile;

			do_action( 'bp_after_group_pages_content' );
		
		else: 
		?>

			<div id="message" class="info">
				<p><?php _e( 'We aren\'t able to find that post.', 'bcg' ); ?></p>
			</div>

		<?php 
		endif;
		// BuddyPress forces comments closed on BP pages. Put the filter back.
		add_filter( 'comments_open', 'bp_comments_open', 10, 2 );

    } else if ( $ccgp_class->is_post_edit() ) {

		$ccgp_class->get_post_form( bp_get_group_id() );

    } else { // Must be the pages list
		?>
		<!-- This is the narrative list template, narrative list portion. -->
		<?php $q = new WP_Query( $ccgp_class->get_query() ); ?>

		<?php if ( $q->have_posts() ) : ?>
			<?php do_action( 'bp_before_group_pages_content' ); ?>

			<div class="pagination no-ajax">
				<div id="posts-count" class="pag-count">
					<!-- TODO: pagination -->
					<?php //bcg_posts_pagination_count($q) ?>
				</div>

				<div id="posts-pagination" class="pagination-links">
					<!-- TODO: pagination -->
					<?php //bcg_pagination($q) ?>
				</div>

			</div>
			<div class="hub-pages-toc">
				<?php 
				do_action( 'bp_before_group_pages_list' );
				$i = 0;
				while ( $q->have_posts() ) : $q->the_post();
				if ( $i % 3 == 0 ) {
					echo '<div class="container-row">';
				}
				$featured_image = get_the_post_thumbnail( get_the_ID(), 'feature-front-sub');
				?>
				<div class="third-block">
					<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark" class="front"><?php 
						if ( $featured_image ) {
							echo $featured_image; 
						} else {
							ccgp_the_fallback_thumbnail();
						}?></a>
					<a href="<?php the_permalink(); ?>" class="cc-pages-title"><?php the_title(); ?></a>
					<?php  if ( $ccgp_class->current_user_can_post() ) : ?>
						<div class="actions">
							<?php do_action( 'cc_group_pages_toc_post_actions', get_the_ID() ); ?>
						</div>
					<?php endif; ?>
				</div>
				<?php
				if ( $i % 3 == 2 ) {
					echo '</div> <!-- end .third-block -->';
				}
				$i++;
				endwhile;

				do_action( 'bp_after_group_pages_content' );
				?>
			</div>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'No pages have been published yet.', 'bcg' ); ?></p>
			</div>

		<?php endif;
	}// End display checks.
endif; // if ( class_exists( 'CC_Group_Pages' ) )