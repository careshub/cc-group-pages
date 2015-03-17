<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
// On the single view, we want WP to ignore any more tags.
global $more;
$more = 1;
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php //if ( is_sticky() && is_home() && ! is_paged() ) : ?>
		<!-- <div class="featured-post">
			<?php // _e( 'Featured post', 'twentytwelve' ); ?>
		</div> -->
		<?php //endif; ?>

		<header class="entry-header">

				<?php //the_post_thumbnail(); ?>
				<h1 class="entry-title"><?php the_title(); ?></h1>

			<?php //if ( comments_open() ) : ?>
				<!-- <div class="comments-link">
					<?php comments_popup_link( '<span class="leave-reply">' . __( 'Leave a reply', 'twentytwelve' ) . '</span>', __( '1 Reply', 'twentytwelve' ), __( '% Replies', 'twentytwelve' ) ); ?>
				</div> --><!-- .comments-link -->
			<?php //endif; // comments_open() ?>
		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php the_content( __( 'Read more', 'twentytwelve' ) ); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'twentytwelve' ), 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->

		<?php
		// if ( function_exists('bp_share_favorite_post_button') ) {
		// 		bp_share_favorite_post_button( $post->ID );
		// 	}
			if ( function_exists('cc_add_comment_button') ) {
					cc_add_comment_button( get_the_ID() );
				}
			if ( function_exists('love_it_button') ) {
					love_it_button();
				}
			if ( function_exists('bp_share_post_button') ) {
					bp_share_post_button();
				}
		?>

		<footer class="entry-meta">
			<?php twentytwelve_entry_meta(); ?>
			<?php //edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
			<?php if ( is_singular() && get_the_author_meta( 'description' ) && is_multi_author() ) : // If a user has filled out their description and this is a multi-author blog, show a bio on their entries. ?>
				<div class="author-info">
					<div class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentytwelve_author_bio_avatar_size', 68 ) ); ?>
					</div><!-- .author-avatar -->
					<div class="author-description">
						<h2><?php printf( __( 'About %s', 'twentytwelve' ), get_the_author() ); ?></h2>
						<p><?php the_author_meta( 'description' ); ?></p>
						<div class="author-link">
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
								<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentytwelve' ), get_the_author() ); ?>
							</a>
						</div><!-- .author-link	-->
					</div><!-- .author-description -->
				</div><!-- .author-info -->
			<?php endif; ?>
		</footer><!-- .entry-meta -->
		<div class="post-actions">
			<?php do_action( 'ccgp_post_actions' ) ; ?>
		</div>
	</article><!-- #post -->
