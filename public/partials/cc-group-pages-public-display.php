<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public/partials
 */

function ccgp_the_post_edit_link( $post_id ){
	$ccgp_class = new CC_Group_Pages();
	?>
	<a href="<?php echo $ccgp_class->get_edit_permalink( $post_id ); ?>" class="edit-link">Edit page.</a>
	<?php
}

function ccgp_add_post_status_to_toc( $post_id ){
	if ( 'draft' == get_post_status( $post_id ) ) :
		?>
		<span class="post-status">Draft</span>
		<?php
	endif;
}
add_action( 'cc_group_pages_toc_post_actions', 'ccgp_add_post_status_to_toc', 9 );

function ccgp_the_fallback_thumbnail(){
    ?>
    <img width="300" height="200" alt="default hub page image" class="attachment-feature-front-sub wp-post-image" src="<?php echo plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'img/cc_default_thumbnail.png'; ?>">
    <?php
}

/**
 * Output a meta area below the content of the post.
 *
 * @return string HTML
 */
function ccgp_entry_meta() {
		// Translators: used between list items, there is a space after the comma.
		$categories_list = get_the_category_list( __( ', ', 'cc-group-pages' ) );

		// Translators: used between list items, there is a space after the comma.
		$tag_list = get_the_tag_list( '', __( ', ', 'cc-group-pages' ) );

		$date = sprintf(
			'<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
			esc_url( get_permalink() ),
			esc_attr( get_the_time() ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);

		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			$author_url = bp_core_get_user_domain( get_the_author_meta( 'ID' ) );
		} else {
			$author_url = get_author_posts_url( get_the_author_meta( 'ID' ) );
		}

		$author = sprintf(
			'<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
			esc_url( $author_url ),
			esc_attr( sprintf( __( 'View all posts by %s', 'cc-group-pages' ), get_the_author() ) ),
			get_the_author()
		);

		// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
		if ( $tag_list ) {
			$printf_string = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'cc-group-pages' );
		} elseif ( $categories_list ) {
			$printf_string = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'cc-group-pages' );
		} else {
			$printf_string = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'cc-group-pages' );
		}

		printf(
			$printf_string,
			$categories_list,
			$tag_list,
			$date,
			$author
		);
	}
