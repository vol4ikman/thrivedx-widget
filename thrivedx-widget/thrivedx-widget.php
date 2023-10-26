<?php
/**
 * Plugin Name:       ThriveDX Widget
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       ThriveDX Widget posts.
 * Version:           1.0
 * Requires at least: 6.3
 * Requires PHP:      7.3
 * Author:            ThriveDX Exam
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       thrivedx
 * Domain Path:       /languages
 *
 * @package WordPress
 */

if ( ! current_theme_supports( 'widgets' ) ) {
	add_theme_support( 'widgets' );
}

add_action( 'widgets_init', 'thrivedx_register_sidebars' );
add_action( 'widgets_init', 'thrivedx_register_widgets' );
add_action( 'after_setup_theme', 'thrivedx_reg_image_size' );
add_action( 'wp_enqueue_scripts', 'thrivedx_enqueue_styles' );

/**
 * Register_sidebars
 */
function thrivedx_register_sidebars() {
	register_sidebar(
		array(
			'id'            => 'thrivedx_primary',
			'name'          => __( 'ThriveDX Sidebar' ),
			'description'   => __( 'A short description of the sidebar.' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-inner">',
			'after_widget'  => '</div></div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}

/**
 * ThriveDX_Widget class
 */
class ThriveDX_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'thrivedx_widget', // Base ID.
			'ThriveDX_Widget', // Name.
			array( 'description' => __( 'ThriveDX Posts Widget', 'thrivedx' ) )
		);
	}

	/**
	 * Front-end display of widget
	 *
	 * @param  array $args Widget arguments.
	 * @param  array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title        = apply_filters( 'widget_title', $instance['title'] );
		$selected_cat = $instance['selected_cat'];
		$post_num     = $instance['post_num'];

		if ( ! $selected_cat ) {
			return;
		}

		$posts_args = array(
			'post_type'      => 'post',
			'posts_per_page' => $post_num,
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'terms'    => $selected_cat,
				),
			),
		);

		$posts = new WP_Query( $posts_args );

		echo wp_kses_post( $before_widget );
		if ( ! $posts->have_posts() ) {
			echo '<h3 class="no-widget-posts">' . esc_html__( 'There is no posts in selected category...', 'text_domain' ) . '</h3>';
		} else {
			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}
			ob_start();
			?>
			<div class="thrivedx-posts-widget total-<?php echo count( $posts->posts ) <= 3 ? 'x' : 'y'; ?>">
				<?php
				while ( $posts->have_posts() ) :
					$posts->the_post();
					include plugin_dir_path( __FILE__ ) . 'frontend/post-item.php';
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php
			$html = ob_get_clean();
			echo wp_kses_post( $html );
		}
		echo wp_kses_post( $after_widget );

	}

	/**
	 * Back-end widget form.
	 *
	 * @param  array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title        = isset( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
		$selected_cat = isset( $instance['selected_cat'] ) ? $instance['selected_cat'] : '';
		$post_num     = isset( $instance['post_num'] ) ? $instance['post_num'] : 3;

		$categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>">
				<?php _e( 'Title:' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
				value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_name( 'selected_cat' ); ?>">
				<?php _e( 'Select category:' ); ?>
			</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'selected_cat' ); ?>"
				name="<?php echo $this->get_field_name( 'selected_cat' ); ?>">
				<option>Please select category</option>
				<?php if ( $categories ) : ?>
					<?php foreach ( $categories as $item ) : ?>
						<option <?php echo ( $item->term_id == $selected_cat ) ? 'selected="selected"' : ''; ?>
							value="<?php echo esc_attr( $item->term_id ); ?>">
							<?php echo esc_attr( $item->name ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_name( 'post_num' ); ?>"><?php _e( 'Posts number:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'post_num' ); ?>"
				name="<?php echo $this->get_field_name( 'post_num' ); ?>" type="number"
				value="<?php echo esc_attr( $post_num ); ?>" min="1" max="12" />
		</p>
		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values to be saved.
	 * @param array $old_instance Previously saved values.
	 *
	 * @return array Updated safe values.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                 = array();
		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['selected_cat'] = ( ! empty( $new_instance['selected_cat'] ) ) ? strip_tags( $new_instance['selected_cat'] ) : '';
		$instance['post_num']     = ( ! empty( $new_instance['post_num'] ) ) ? strip_tags( $new_instance['post_num'] ) : 0;
		return $instance;
	}
}

/**
 * Register_widget
 */
function thrivedx_register_widgets() {
	register_widget( 'ThriveDX_Widget' );
}
/**
 * Add image_size
 */
function thrivedx_reg_image_size() {
	add_image_size( 'thrivedx_image', 400, 200, true );
}
/**
 * Add styles
 */
function thrivedx_enqueue_styles() {
	wp_register_style( 'thrivedx-style', plugins_url( '/frontend/style.css', __FILE__ ), array(), THEME_VER, 'all' );
	wp_enqueue_style( 'thrivedx-style' );
}
