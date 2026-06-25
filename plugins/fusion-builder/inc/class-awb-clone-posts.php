<?php
/**
 * Avada Clone Posts
 *
 * @package Avada-Builder
 * @since 3.9
 */

/**
 * AWB Clone Posts class.
 *
 * @since 3.9
 */
class AWB_Clone_Posts {

	/**
	 * Class instance.
	 *
	 * @var AWB_Clone_Posts|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @since 3.9
	 * @return AWB_Clone_Posts
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Clone_Posts();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.9
	 */
	public function __construct() {
		$fusion_settings       = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;
		$cloning_posts_enabled = intval( $fusion_settings && '' !== $fusion_settings->get( 'cloning_posts' ) ? $fusion_settings->get( 'cloning_posts' ) : '0' );

		if ( $cloning_posts_enabled ) {
			add_filter( 'post_row_actions', 'AWB_Clone_Posts::add_clone_option_to_row', 10, 2 );
			add_filter( 'page_row_actions', 'AWB_Clone_Posts::add_clone_option_to_row', 10, 2 );
			add_action( 'admin_action_awb_clone_post', 'AWB_Clone_Posts::handle_clone_post' );
		}
	}

	/**
	 * Add clone action to post row.
	 *
	 * @since 3.9
	 * @param array   $actions The current actions for post.
	 * @param WP_Post $post The Post.
	 * @return array
	 */
	public static function add_clone_option_to_row( $actions, $post ) {
		$allowed_post_types = apply_filters( 'awb_allowed_clone_post_types', [ 'post', 'page', 'avada_portfolio', 'avada_faq' ] );

		if ( ! in_array( $post->post_type, $allowed_post_types, true ) || ! self::user_can_clone_post_type( $post->post_type ) ) {
			return $actions;
		}

		$url = self::get_clone_url( $post );

		$title_attr_args = [
			'post' => $post,
			'echo' => false,
		];
		/* translators: %s - A post/page title. */
		$aria_label    = wptexturize( sprintf( __( 'Clone "%s"', 'fusion-builder' ), the_title_attribute( $title_attr_args ) ) );
		$button_action = __( 'Clone', 'fusion-builder' );

		$actions['awb-clone'] = '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $aria_label ) . '">' . esc_html( $button_action ) . '</a>';

		return $actions;
	}

	/**
	 * Function that gets called when clone button is clicked.
	 *
	 * @since 3.9
	 * @return void
	 */
	public static function handle_clone_post() {
		$message = __( 'No post to clone or no permission.', 'fusion-builder' );
		if ( ! ( isset( $_GET['item'], $_GET['_awb_clone_post_nonce'], $_GET['action'] ) ) || ! check_admin_referer( 'awb_clone_post', '_awb_clone_post_nonce' ) ) {
			wp_die( esc_html( $message ) );
		}

		$post_id = (int) $_GET['item'];
		if ( ! is_numeric( $post_id ) || ! $post_id > 0 ) {
			wp_die( esc_html( $message ) );
		}

		$post = get_post( $post_id );
		if ( ! ( $post instanceof WP_Post ) ) {
			wp_die( esc_html( $message ) );
		}

		if ( ! self::user_can_clone_post_type( $post->post_type ) ) {
			wp_die( esc_html( $message ) );
		}

		self::clone_post( $post );

		$referer = fusion_get_referer();
		if ( $referer ) {
			wp_safe_redirect( $referer );
			exit;
		}
	}

	/**
	 * Duplicate a post.
	 *
	 * @since 3.9
	 * @param WP_Post $post The post to clone.
	 * @return void
	 */
	private static function clone_post( $post ) {
		$new_post = [];

		$new_post['post_type']    = $post->post_type;
		$new_post['post_content'] = $post->post_content;
		/* translators: The post title. */
		$new_post['post_title']     = sprintf( esc_attr__( '%s ( Cloned )', 'fusion-builder' ), $post->post_title );
		$new_post['post_excerpt']   = $post->post_excerpt;
		$new_post['comment_status'] = $post->comment_status;
		$new_post['ping_status']    = $post->ping_status;
		$new_post['post_password']  = $post->post_password;
		$new_post['to_ping']        = $post->to_ping;
		$new_post['post_parent']    = $post->post_parent;
		$new_post['menu_order']     = $post->menu_order;
		$new_post['post_mime_type'] = $post->post_mime_type;
		$new_post['page_template']  = $post->page_template;

		$new_post['post_category'] = $post->post_category;
		$new_post['tags_input']    = $post->tags_input;
		$new_post['tax_input']     = self::get_taxonomies_clone( $post );

		$new_post['post_status'] = 'draft';

		$new_post['meta_input'] = self::get_cloned_post_meta( $post );

		$new_post = apply_filters( 'awb_clone_post_to_insert', $new_post, $post );

		wp_insert_post( $new_post );
	}

	/**
	 * Helper to get post additional taxonomies.
	 *
	 * @since 3.9
	 * @param WP_Post $post The post to get all taxonomies.
	 * @return array
	 */
	private static function get_taxonomies_clone( $post ) {
		$clone_taxonomies = [];

		$post_taxonomies = get_object_taxonomies( $post );
		foreach ( $post_taxonomies as $post_taxonomy ) {
			$taxonomy_names = wp_get_object_terms( $post->ID, $post_taxonomy, [ 'fields' => 'ids' ] );
			if ( ! empty( $taxonomy_names ) ) {
				$clone_taxonomies[ $post_taxonomy ] = $taxonomy_names;
			}
		}

		return $clone_taxonomies;
	}

	/**
	 * Get the meta for the cloned element.
	 *
	 * @since 3.9
	 * @param WP_Post $post The post.
	 * @return array
	 */
	private static function get_cloned_post_meta( $post ) {
		$post_meta        = get_post_meta( $post->ID );
		$new_post_meta    = [];
		$ignore_meta_keys = [ '_edit_last', '_edit_lock', 'avada_post_views_count', 'avada_today_post_views_count', 'avada_post_views_count_today_date' ];

		foreach ( $post_meta as $meta_key => $meta_value ) {
			if ( isset( $meta_value[0] ) && ! in_array( $meta_key, $ignore_meta_keys, true ) ) {
				$new_post_meta[ $meta_key ] = get_post_meta( $post->ID, $meta_key, true ); // needed to unserialize values.
			}
		}

		return $new_post_meta;
	}

	/**
	 * Get the url for the clone button.
	 *
	 * @since 3.9
	 * @param WP_Post $post The post.
	 * @return string
	 */
	private static function get_clone_url( $post ) {
		$args = [
			'_awb_clone_post_nonce' => wp_create_nonce( 'awb_clone_post' ),
			'item'                  => $post->ID,
			'action'                => 'awb_clone_post',
		];

		return add_query_arg( $args );
	}

	/**
	 * Check if user can clone a post type.
	 *
	 * @since 3.9
	 * @param string $post_type The post type to check for.
	 * @return bool
	 */
	private static function user_can_clone_post_type( $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		if ( null !== $post_type_object && current_user_can( $post_type_object->cap->edit_posts ) ) {
			return true;
		}

		return false;
	}
}

new AWB_Clone_Posts();
