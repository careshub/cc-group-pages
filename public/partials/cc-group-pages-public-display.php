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

function ccgp_add_edit_link_to_toc( $post_id ){
	$ccgp_class = new CC_Group_Pages(); 
	?>
	<a href="<?php echo $ccgp_class->get_edit_permalink( $post_id ); ?>" class="button edit-link">Edit</a>
	<?php
}
add_action( 'cc_group_pages_toc_post_actions', 'ccgp_add_edit_link_to_toc', 12 );

function ccgp_add_post_status_to_toc( $post_id ){
	if ( 'draft' == get_post_status( $post_id ) ) :
		?>
		<span class="post-status">Draft</span>
		<?php
	endif;
}
add_action( 'cc_group_pages_toc_post_actions', 'ccgp_add_post_status_to_toc', 9 );