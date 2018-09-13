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

$ccgp_class = new CC_Group_Pages();
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php do_action( 'ccgp_post_before_content', get_the_ID() ); ?>

		<div class="entry-content clear">
			<?php the_content( __( 'Read more', 'twentytwelve' ) ); ?>
		</div><!-- .entry-content -->

		<?php do_action( 'ccgp_post_after_content', get_the_ID() ); ?>

		<footer class="entry-meta">
			<?php ccgp_entry_meta(); ?>
			<?php
			if ( $ccgp_class->current_user_can_post( get_the_ID() ) ) {
				echo '&emsp;';
				ccgp_the_post_edit_link( get_the_ID() );
			}
			?>
			<div class="post-actions">
				<?php do_action( 'ccgp_post_after_footer', get_the_ID() ) ; ?>
			</div>
		</footer><!-- .entry-meta -->

	</article><!-- #post -->
