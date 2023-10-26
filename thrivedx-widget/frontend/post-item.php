<?php
/**
 * Post item inside a loop
 *
 * @package WordPress
 */

?>

<article <?php post_class( 'thrivedx-article' ); ?>>
	<a href="<?php the_permalink(); ?>" class="post-thumbnail">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'thrivedx_image' );
		} else {
			echo '<img src="https://via.placeholder.com/300x180?text=Thumbnail" alt="' . esc_html( get_the_title() ) . '" />';
		}
		?>
	</a>

	<div class="post-meta">
		<?php echo esc_html__( 'Published: ' ) . get_the_date( 'd/m/Y' ); ?>
	</div>

	<a href="<?php the_permalink(); ?>" class="post-title">
		<?php the_title(); ?>
	</a>
</article>
