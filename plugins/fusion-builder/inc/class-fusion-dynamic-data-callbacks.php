<?php
/**
 * Functions for retrieving dynamic data values.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://avada.com
 * @package    Avada Builder
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A wrapper for static methods.
 */
class Fusion_Dynamic_Data_Callbacks {

	/**
	 * Post ID for callbacks to use.
	 *
	 * @access public
	 * @var array
	 */
	public $post_data = [];

	/**
	 * Whether it has rendered already or not.
	 *
	 * @since 3.3
	 * @var array
	 */
	protected static $has_rendered = [];

	/**
	 * Class constructor.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_ajax_ajax_acf_get_field', [ $this, 'ajax_acf_get_field' ] );
		add_action( 'wp_ajax_ajax_acf_get_select_field', [ $this, 'ajax_acf_get_select_field' ] );
		add_action( 'wp_ajax_ajax_acf_get_repeat_field_single', [ $this, 'ajax_acf_get_repeat_field_single' ] );
		add_action( 'wp_ajax_ajax_get_post_date', [ $this, 'ajax_get_post_date' ] );

		add_action( 'wp_ajax_ajax_dynamic_data_default_callback', [ $this, 'ajax_dynamic_data_default_callback' ] );

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'woo_fragments' ] );
		}
	}

	/**
	 * Returns the post-ID.
	 *
	 * @since 6.2.0
	 * @return int
	 */
	public static function get_post_id() {

		if ( fusion_doing_ajax() && isset( $_GET['fusion_load_nonce'] ) && isset( $_GET['post_id'] ) ) {
			check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
			$post_id = sanitize_text_field( wp_unslash( $_GET['post_id'] ) );
		} else {
			$post_id = fusion_library()->get_page_id();
		}

		return apply_filters( 'fusion_dynamic_post_id', $post_id );
	}

	/**
	 * Runs the defined callback.
	 *
	 * @access public
	 * @since 2.1
	 * @return void
	 */
	public function ajax_dynamic_data_default_callback() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		$callback_function = ( isset( $_GET['callback'] ) ) ? sanitize_text_field( wp_unslash( $_GET['callback'] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$callback_exists   = $callback_function && ( is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) || ( 0 === strpos( $callback_function, 'awb_' ) && is_callable( $callback_function ) ) ) ? true : false;
		$can_execute       = apply_filters( 'fusion_load_live_editor', current_user_can( 'edit_files' ) );
		$post_id           = ( isset( $_GET['post_id'] ) ) ? apply_filters( 'fusion_dynamic_post_id', wp_unslash( $_GET['post_id'] ) ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// If its a term of some kind.
		if ( isset( $_GET['is_term'] ) && $_GET['is_term'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$term = get_term( $post_id );
			if ( $term ) {
				$GLOBALS['wp_query']->is_tax         = true;
				$GLOBALS['wp_query']->is_archive     = true;
				$GLOBALS['wp_query']->queried_object = $term;
			}
		}

		if ( $can_execute && $callback_function && $callback_exists && $post_id && isset( $_GET['args'] ) ) {
			$return_data['content'] = is_callable( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function ) ? call_user_func_array( 'Fusion_Dynamic_Data_Callbacks::' . $callback_function, [ wp_unslash( $_GET['args'] ), $post_id ] ) : call_user_func_array( $callback_function, [ wp_unslash( $_GET['args'] ), $post_id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Shortcode.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param arrsy $args Arguments.
	 * @return string
	 */
	public static function dynamic_shortcode( $args ) {
		(string) $shortcode_string = isset( $args['shortcode'] ) ? $args['shortcode'] : '';
		return do_shortcode( $shortcode_string );
	}

	/**
	 * eturns the output of an action hook..
	 *
	 * @static
	 * @access public
	 * @since 3.11.10
	 * @param arrsy $args Arguments.
	 * @return string
	 */
	public static function output_action_hook( $args ) {
		$action_names = explode( "\n", $args['action_name'] );

		ob_start();
		foreach ( $action_names as $action_name ) {
			do_action( $action_name );
		}

		return ob_get_clean();
	}


	/**
	 * Featured image.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function post_featured_image( $args ) {
		$src = '';

		if ( is_tax() || is_category() || is_tag() ) {
			return fusion_get_term_image();
		}

		if ( isset( $args['type'] ) && 'main' !== $args['type'] ) {
			$attachment_id = fusion_get_featured_image_id( $args['type'], get_post_type( self::get_post_id() ) );
			$attachment    = wp_get_attachment_image_src( $attachment_id, 'full' );
			$src           = isset( $attachment[0] ) ? $attachment[0] : '';
		} else {
			$src = get_the_post_thumbnail_url( self::get_post_id() );
		}

		return $src;
	}

	/**
	 * Product category thumbnail image.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_category_thumbnail( $args ) {
		if ( is_tax( 'product_cat' ) ) {
			$thumbnail_id = get_term_meta( get_queried_object()->term_id, 'thumbnail_id', true );
			if ( $thumbnail_id ) {
				return wp_get_attachment_url( $thumbnail_id );
			}

			// Fallback.
			if ( function_exists( 'wc_placeholder_img_src' ) ) {
				return wc_placeholder_img_src();
			}
		}
		return '';
	}

	/**
	 * Featured images.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function post_gallery( $args ) {
		$images          = [];
		$fusion_settings = awb_get_fusion_settings();
		$post_type       = get_post_type( self::get_post_id() );

		// Check if we should add featured image.
		if ( isset( $args['include_main'] ) && 'yes' === $args['include_main'] ) {
			$post_thumbnail_id = get_post_thumbnail_id( self::get_post_id() );
			$image_src         = $post_thumbnail_id ? wp_get_attachment_image_src( $post_thumbnail_id, 'full' ) : false;

			if ( $post_thumbnail_id ) {
				$images[] = [
					'ID'  => $post_thumbnail_id,
					'url' => $image_src[0],
				];
			}
		}

		// Check if we should add Avada featured images.
		$i = 2;
		while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) {
			$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, $post_type, self::get_post_id() );
			if ( $attachment_new_id ) {
				$image_src = wp_get_attachment_image_src( $attachment_new_id, 'full' );
				$images[]  = [
					'id'  => $attachment_new_id,
					'url' => $image_src[0],
				];
			}
			$i++;
		}

		return $images;
	}

	/**
	 * Get post or archive title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_object_title( $args ) {
		$include_context = ( isset( $args['include_context'] ) && 'yes' === $args['include_context'] ) ? true : false;

		if ( FusionBuilder()->post_card_data['is_rendering'] && FusionBuilder()->post_card_data['is_post_card_archives'] ) {
			$title = self::fusion_get_post_title( $args );
		} elseif ( is_search() ) {
			/* translators: The search keyword(s). */
			$title = sprintf( __( 'Search: %s', 'fusion-builder' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s is the page number. */
				$title .= sprintf( __( '&nbsp;&ndash; Page %s', 'fusion-builder' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $include_context ) {
				/* translators: Category archive title. */
				$title = sprintf( __( 'Category: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
			if ( $include_context ) {
				/* translators: Tag archive title. */
				$title = sprintf( __( 'Tag: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_author() ) {
			$author = get_user_by( 'id', get_query_var( 'author' ) );
			$title  = get_the_author_meta( 'nickname', (int) $author->ID );

			if ( $include_context ) {
				/* translators: Author archive title. */
				$title = sprintf( __( 'Author: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Yearly archive title. */
				$title = sprintf( __( 'Year: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Monthly archive title. */
				$title = sprintf( __( 'Month: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'fusion-builder' ) );

			if ( $include_context ) {
				/* translators: Daily archive title. */
				$title = sprintf( __( 'Day: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'fusion-builder' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'fusion-builder' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $include_context ) {
				/* translators: Post type archive title. */
				$title = sprintf( __( 'Archives: %s', 'fusion-builder' ), $title );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $include_context ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );

				if ( $tax ) {
					/* translators: Taxonomy term archive title. %1$s: Taxonomy singular name, %2$s: Current taxonomy term. */
					$title = sprintf( __( '%1$s: %2$s', 'fusion-builder' ), $tax->labels->singular_name, $title );
				}
			}
		} elseif ( is_archive() ) {
			$title = __( 'Archives', 'fusion-builder' );
		} elseif ( is_404() ) {
			$title = __( '404', 'fusion-builder' );
		} else {
			$title = self::fusion_get_post_title( $args );
		}

		return $title;
	}

	/**
	 * Post title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string The post title.
	 */
	public static function fusion_get_post_title( $args ) {
		$include_context = ( isset( $args['include_context'] ) && 'yes' === $args['include_context'] ) ? true : false;

		/* translators: %s: Search term. */
		$title = get_the_title( self::get_post_id() );

		if ( $include_context ) {
			$post_type_obj = get_post_type_object( get_post_type( self::get_post_id() ) );

			if ( $post_type_obj ) {
				/* translators: %1$s: Post Object Label. %2$s: Post Title. */
				$title = sprintf( '%s: %s', $post_type_obj->labels->singular_name, $title );
			}
		}

		return $title;
	}

	/**
	 * Post ID.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return int
	 */
	public static function fusion_get_post_id( $args ) {
		return (string) self::get_post_id();
	}

	/**
	 * Get post excerpt or archive description.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return int
	 */
	public static function fusion_get_object_excerpt( $args ) {
		return is_archive() ? get_the_archive_description() : get_the_excerpt( self::get_post_id() );
	}

	/**
	 * Post date.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_date( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$format = isset( $args['format'] ) ? $args['format'] : '';
		$date   = 'modified' === $args['type'] ? get_the_modified_date( $format, $post_id ) : get_the_date( $format, $post_id );

		if ( ! $date ) {
			$date = self::fusion_get_date( $args );
		}

		return $date;
	}

	/**
	 * Current date.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_date( $args ) {
		$format = isset( $args['format'] ) ? $args['format'] : '';
		return wp_date( $format );
	}

	/**
	 * Get dynamic heading.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_dynamic_heading( $args, $post_id = 0 ) {
		$title = self::fusion_get_dynamic_option( $args, $post_id );
		if ( ! $title ) {
			$title = self::fusion_get_object_title( $args );
		}
		return $title;
	}

	/**
	 * Get Dynamic Content Page Option.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_dynamic_option( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$post_type   = get_post_type( $post_id );
		$pause_metas = ( 'fusion_tb_section' === $post_type || ( isset( $_POST['meta_values'] ) && strpos( $_POST['meta_values'], 'dynamic_content_preview_type' ) ) ); // phpcs:ignore WordPress.Security

		if ( $pause_metas ) {
			do_action( 'fusion_pause_meta_filter' );
		}

		$data = fusion_get_page_option( $args['data'], $post_id );

		if ( $pause_metas ) {
			do_action( 'fusion_resume_meta_filter' );
		}

		// For image data.
		if ( is_array( $data ) && isset( $data['url'] ) ) {
			$data = $data['url'];
		}

		return $data;
	}

	/**
	 * Post time.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_time( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$format = isset( $args['format'] ) && '' !== $args['format'] ? $args['format'] : 'U';
		return get_post_time( $format, false, $post_id );
	}

	/**
	 * Get post total views.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string Empty string if no total views exist.
	 */
	public static function get_post_total_views( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$total_views = avada_get_post_views( $post_id );

		if ( empty( $total_views ) ) {
			return '';
		}

		return number_format_i18n( $total_views );
	}

	/**
	 * Get post today views.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string Empty string if no today views exist.
	 */
	public static function get_post_today_views( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$today_views = avada_get_today_post_views( $post_id );

		if ( empty( $today_views ) ) {
			return '';
		}

		return number_format_i18n( $today_views );
	}

	/**
	 * Get the post reading time.
	 *
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string The reading time.
	 */
	public static function get_post_reading_time( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		return awb_get_reading_time_for_display( $post_id, $args );
	}

	/**
	 * Post type.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_type( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$post_type_label = '';
		$post_type_obj   = get_post_type_object( get_post_type( $post_id ) );

		if ( $post_type_obj ) {
			$post_type_label = $post_type_obj->labels->singular_name;
		}

		return $post_type_label;
	}

	/**
	 * Post terms.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_terms( $args, $post_id = 0 ) {
		$output = '';
		if ( ! isset( $args['type'] ) ) {
			return $output;
		}

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$terms = wp_get_object_terms( $post_id, $args['type'] );
		if ( ! is_wp_error( $terms ) ) {
			$separator   = isset( $args['separator'] ) ? $args['separator'] : '';
			$should_link = isset( $args['link'] ) && 'no' === $args['link'] ? false : true;

			foreach ( $terms as $term ) {
				if ( $should_link ) {
					$output .= '<a href="' . get_term_link( $term->slug, $args['type'] ) . '" title="' . esc_attr( $term->name ) . '">';
				}

				$output .= esc_html( $term->name );

				if ( $should_link ) {
					$output .= '</a>';
				}

				$output .= $separator;
			}

			return '' !== $separator ? rtrim( $output, $separator ) : $output;
		}

		return $output;
	}

	/**
	 * Post permalink.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_post_permalink( $args ) {
		if ( is_tax() ) {
			$term_link = get_term_link( get_queried_object() );
			if ( ! is_wp_error( $term_link ) ) {
				return $term_link;
			}
		}
		return get_permalink( self::get_post_id() );
	}

	/**
	 * Post meta.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_post_custom_field( $args ) {
		do_action( 'fusion_pause_meta_filter' );

		$post_id   = isset( $args['post_id'] ) && ! empty( $args['post_id'] && 0 !== $args['post_id'] ) ? $args['post_id'] : self::get_post_id();
		$post_meta = get_post_meta( $post_id, $args['key'], true );

		do_action( 'fusion_resume_meta_filter' );

		if ( ! is_array( $post_meta ) ) {
			return $post_meta;
		}
		return '';
	}

	/**
	 * Site title.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_title( $args ) {
		return get_bloginfo( 'name' );
	}

	/**
	 * Site tagline.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_tagline( $args ) {
		return get_bloginfo( 'description' );
	}

	/**
	 * Site URL.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_url( $args ) {
		return home_url( '/' );
	}

	/**
	 * Site Logo.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_logo( $args ) {
		$type = isset( $args['type'] ) ? $args['type'] : false;

		if ( ! $type ) {
			return '';
		}

		switch ( $type ) {

			case 'default_normal':
				return fusion_get_theme_option( 'logo', 'url' );

			case 'default_retina':
				return fusion_get_theme_option( 'logo_retina', 'url' );

			case 'sticky_normal':
				return fusion_get_theme_option( 'sticky_header_logo', 'url' );

			case 'sticky_retina':
				return fusion_get_theme_option( 'sticky_header_logo_retina', 'url' );

			case 'mobile_normal':
				return fusion_get_theme_option( 'mobile_logo', 'url' );

			case 'mobile_retina':
				return fusion_get_theme_option( 'mobile_logo_retina', 'url' );

			case 'all':
				return wp_json_encode(
					[
						'default' => [
							'normal' => fusion_get_theme_option( 'logo' ),
							'retina' => fusion_get_theme_option( 'logo_retina' ),
						],
						'sticky'  => [
							'normal' => fusion_get_theme_option( 'sticky_header_logo' ),
							'retina' => fusion_get_theme_option( 'sticky_header_logo_retina' ),
						],
						'mobile'  => [
							'normal' => fusion_get_theme_option( 'mobile_logo' ),
							'retina' => fusion_get_theme_option( 'mobile_logo_retina' ),
						],
					]
				);
		}

		return '';
	}


	/**
	 * Site request parameter.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_site_request_param( $args ) {
		$type  = isset( $args['type'] ) ? strtoupper( $args['type'] ) : false;
		$name  = isset( $args['name'] ) ? $args['name'] : false;
		$value = '';

		if ( ! $name || ! $type ) {
			return '';
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		switch ( $type ) {
			case 'POST':
				if ( ! isset( $_POST[ $name ] ) ) {
					return '';
				}
				$value = wp_unslash( $_POST[ $name ] );
				break;
			case 'GET':
				if ( ! isset( $_GET[ $name ] ) ) {
					return '';
				}
				$value = wp_unslash( $_GET[ $name ] );
				break;
			case 'QUERY_VAR':
				$value = get_query_var( $name );
				break;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		return htmlentities( wp_kses_post( $value ) );
	}

	/**
	 * Toggle Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_toggle_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}

		// Add Off Canvas to stack, so it's markup is added to the page.
		AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $args['off_canvas_id'] );

		return '#awb-oc__' . $args['off_canvas_id'];
	}

	/**
	 * Open Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_open_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}

		// Add Off Canvas to stack, so it's markup is added to the page.
		AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $args['off_canvas_id'] );

		return '#awb-open-oc__' . $args['off_canvas_id'];
	}

	/**
	 * Close Off Canvas.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_close_off_canvas( $args ) {
		if ( ! isset( $args['off_canvas_id'] ) ) {
			return '';
		}
		return '#awb-close-oc__' . $args['off_canvas_id'];
	}

	/**
	 * Gets a global ACF option page field. Returns false otherwise.
	 *
	 * @static
	 * @access public
	 * @since 3.11.10
	 * @param string $field_name The ACF field name.
	 * @return mixed|bool The vallue of teh ACF field or false when it isn't from an option page.
	 */
	public static function get_acf_op_option( $field_name ) {
		if ( 'awb_acfop_' === substr( $field_name, 0, 10 ) ) {
			$option_page_id = 'option';
			if ( 1 === preg_match( '/awb_acfop_.+__/', $field_name, $check ) ) {
				$option_page_id = trim( str_replace( 'awb_acfop_', '', $check[0] ), '__' );
			}

			return get_field( str_replace( 'awb_acfop_', '', $field_name ), $option_page_id );
		}

		return false;
	}

	/**
	 * ACF text field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_field( $args ) {
		if ( ! isset( $args['field'] ) ) {
			return '';
		}

		if ( $op_field = self::get_acf_op_option( $args['field'] ) ) {
			return $op_field;
		}

		$post_id = self::get_post_id();

		if ( false !== strpos( $post_id, '-archive' ) ) {
			if ( is_author() ) {
				$post_id = 'user_' . str_replace( '-archive', '', $post_id );
			} else {
				$post_id = get_term_by( 'term_taxonomy_id', str_replace( '-archive', '', $post_id ) );
			}

			return get_field( $args['field'], $post_id );
		}

		return get_field( $args['field'], get_post( $post_id ) );
	}

	/**
	 * ACF select field.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_select_field( $args ) {
		$select_data = self::acf_get_field( $args );

		if ( is_array( $select_data ) ) {
			$separator   = isset( $args['separator'] ) ? (string) $args['separator'] : ', ';
			$select_data = implode( $separator, $select_data );
		}
		return $select_data;
	}

	/**
	 * ACF get link field.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_link_field( $args ) {
		$link_data = self::acf_get_field( $args );
		$link      = '';

		if ( is_array( $link_data ) && isset( $link_data['url'] ) ) {
			$link = $link_data['url'];
		} elseif ( is_string( $link_data ) ) {
			$link = $link_data;
		}

		return $link;
	}

	/**
	 * ACF get color field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_color_field( $args ) {
		$color_data = self::acf_get_field( $args );
		$color      = '';

		if ( is_array( $color_data ) ) {
			$defaults = [
				'red'   => 255,
				'green' => 255,
				'blue'  => 255,
				'alpha' => 1,
			];
			$args     = array_merge( $defaults, $color_data );
			$color    = 'rgba(' . $color_data['red'] . ',' . $color_data['green'] . ',' . $color_data['blue'] . ',' . $color_data['alpha'] . ')';
		} elseif ( is_string( $color_data ) ) {
			$color = $color_data;
		}

		return $color;
	}

	/**
	 * ACF get image field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_image_field( $args ) {
		$image_data = self::acf_get_field( $args );
		$image      = '';

		if ( is_array( $image_data ) && isset( $image_data['url'] ) ) {
			$image = $image_data['url'];
		} elseif ( is_integer( $image_data ) ) {
			$image = wp_get_attachment_url( $image_data );
		} elseif ( is_string( $image_data ) ) {
			$image = $image_data;
		}

		return $image;
	}

	/**
	 * ACF get icon picker field.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_iconpicker_field( $args ) {
		$icon_data = self::acf_get_field( $args );
		$icon      = '';

		if ( is_array( $icon_data ) ) {
			if ( 'dashicons' === $icon_data['type'] ) {
				wp_enqueue_style( 'dashicons' );
				$icon = $icon_data['value'];
			} elseif ( 'media_library' === $icon['type'] ) {
				$icon = wp_get_attachment_url( $icon_data['value'] );
			} elseif ( 'url' === $icon['type'] ) {
				$icon = $icon_data['value'];
			} elseif ( 'avada_icon' === $icon['type'] ) {
				$icon = 'fusion-prefix-' . str_replace( 'fusion-prefix-', '', $icon_data['value'] );
			}
		} else {
			if ( 'dashicons-' !== substr( $icon_data, 0, 10 ) ) {
				wp_enqueue_style( 'dashicons' );
			}

			if ( 'fusion-prefix-' !== substr( $icon_data, 0, 14 ) && 'dashicons-' !== substr( $icon_data, 0, 10 ) && 'http://' !== substr( $icon_data, 0, 7 ) && 'https://' !== substr( $icon_data, 0, 8 ) ) {
				$icon = 'fusion-prefix-' . $icon_data;
			}

			$icon = $icon_data;
		}

		return $icon;
	}

	/**
	 * ACF get file field.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_file_field( $args ) {
		$file_data = self::acf_get_field( $args );
		$file      = '';

		if ( is_array( $file_data ) && isset( $file_data['url'] ) ) {
			$file = $file_data['url'];
		} elseif ( is_integer( $file_data ) ) {
			$file = wp_get_attachment_url( $file_data );
		} elseif ( is_string( $file_data ) ) {
			$file = $file_data;
		}

		return $file;
	}

	/**
	 * ACF Repeater parent.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @param array $args Arguments.
	 * @return void
	 */
	public static function acf_get_repeater_parent( $args ) {
	}

	/**
	 * ACF Repeater field.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_repeater_sub_field( $args ) {
		$content = get_sub_field( $args['sub_field'] );

		if ( is_array( $content ) ) {
			$type = isset( $content['type'] ) ? $content['type'] : '';

			if ( 'image' === $type ) {
				$content = $content['url'];
			}
		}

		return $content;
	}

	/**
	 * ACF repeater single field.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function acf_get_repeater_single_field( $args ) {
		$field = ! empty( $args['field'] ) ? $args['field'] : false;
		$key   = ! empty( $args['key'] ) ? str_replace( 'awb_acfop_', '', $args['key'] ) : false;
		$index = ! empty( $args['index'] ) ? $args['index'] : 1;

		if ( ! $field || ! $key ) {
			return '';
		}

		$repeater_data = self::acf_get_field( $args );

		$index = intval( $index ) - 1;

		$value = isset( $repeater_data[ $index ][ $key ] ) ? $repeater_data[ $index ][ $key ] : '';

		if ( is_array( $value ) ) {
			$type = isset( $value['type'] ) ? $value['type'] : '';
			if ( 'image' === $type || 'link' === $type ) {
				$value = isset( $value['url'] ) ? $value['url'] : '';
			}
		}

		return $value;
	}

	/**
	 * ACF Relationship.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @return string
	 */
	public static function acf_get_relationship( $args ) {
		$output = '';
		if ( ! isset( $args['field'] ) ) {
			return $output;
		}
		$post_id = self::get_post_id();

		$posts_ids = get_field( $args['field'], $post_id, false );

		if ( ! empty( $posts_ids ) ) {
			$output      = [];
			$separator   = isset( $args['separator'] ) ? $args['separator'] : '';
			$should_link = isset( $args['link'] ) && 'no' === $args['link'] ? false : true;

			foreach ( $posts_ids as $id ) {
				$str = '';
				if ( $should_link ) {
					$str .= '<a href="' . get_permalink( $id ) . '" title="' . esc_attr( get_the_title( $id ) ) . '">';
				}

				$str .= esc_html( get_the_title( $id ) );

				if ( $should_link ) {
					$str .= '</a>';
				}

				$output[] = $str;
			}

			return '' !== $separator ? join( $separator . ' ', $output ) : join( ' ', $output );
		}

		return $output;
	}

	/**
	 * Get ACF select field value.
	 *
	 * @since 3.9
	 */
	public function ajax_acf_get_select_field() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		if ( isset( $_POST['field'] ) && isset( $_POST['post_id'] ) && function_exists( 'get_field' ) ) {
			$return_data['content'] = get_field( wp_unslash( $_POST['field'] ), wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$separator              = isset( $_POST['separator'] ) ? (string) wp_unslash( $_POST['separator'] ) : ', '; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( is_array( $return_data['content'] ) ) {
				$return_data['content'] = implode( $separator, $return_data['content'] );
			}
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Get ACF repeat field single value.
	 *
	 * @since 3.9
	 */
	public function ajax_acf_get_repeat_field_single() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		if ( isset( $_POST['field'] ) && isset( $_POST['post_id'] ) && function_exists( 'get_field' ) ) {
			$return_data['content'] = get_field( wp_unslash( $_POST['field'] ), wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Get ACF field value.
	 *
	 * @since 2.1
	 */
	public function ajax_acf_get_field() {
		check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
		$return_data = [];

		if ( isset( $_POST['field'] ) && isset( $_POST['post_id'] ) && function_exists( 'get_field' ) ) {
			$field_value = get_field( wp_unslash( $_POST['field'] ), wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! isset( $_POST['image'] ) || ! $_POST['image'] ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$return_data['content'] = $field_value;
			} elseif ( is_array( $field_value ) && isset( $field_value['url'] ) ) {
				$return_data['content'] = $field_value['url'];
			} elseif ( is_integer( $field_value ) ) {
				$return_data['content'] = wp_get_attachment_url( $field_value );
			} elseif ( is_string( $field_value ) ) {
				$return_data['content'] = $field_value;
			} else {
				$return_data['content'] = $field_value;
			}
		}

		echo wp_json_encode( $return_data );
		wp_die();
	}

	/**
	 * Gets Events Calendar date of the event. Return a string with the date.
	 *
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_date_to_display( $args, $post_id = 0 ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		$post_is_event_type = ( 'tribe_events' === $post->post_type ? true : false );
		if ( ! $post_is_event_type ) {
			return '';
		}

		$args['event_date_type'] = isset( $args['event_date_type'] ) ? $args['event_date_type'] : 'both';
		$args['format']          = ! empty( $args['format'] ) ? $args['format'] : '';

		if ( 'start_event_date' === $args['event_date_type'] ) {
			$date = tribe_get_start_date( $post_id, true, $args['format'] );
		} elseif ( 'end_event_date' === $args['event_date_type'] ) {
			$date = tribe_get_end_date( $post_id, true, $args['format'] );
		} else {
			add_filter( 'tribe_events_recurrence_tooltip', [ self::class, 'remove_event_recurring_info' ], 999 );
			$date = tribe_events_event_schedule_details( $post_id, '', '', false );
			remove_filter( 'tribe_events_recurrence_tooltip', [ self::class, 'remove_event_recurring_info' ], 999 );

			$time_separator  = tribe_get_option( 'dateTimeSeparator', ' @ ' );
			$range_separator = tribe_get_option( 'timeRangeSeparator', ' - ' );

			if ( $args['format'] ) {

				// Single day event, make sure they have date added to the end time.
				if ( ! tribe_event_is_multiday( $post_id ) && ! tribe_event_is_all_day( $post_id ) ) {
					$date_without_time = preg_replace( '~' . preg_quote( $time_separator, '~' ) . '.+~', '', $date );
					$date              = str_replace( $range_separator, $range_separator . $date_without_time . $time_separator, $date );
				}

				$date           = explode( $range_separator, $date );
				$formatted_date = '';

				foreach ( $date as $date_part ) {

					// Remove dummy time in case it gets added because of user set format and apply the format to the date.
					$formatted_date .= str_replace( [ '00:00', '0:00', '00.00', '0.00' ], '', date( $args['format'], strtotime( str_replace( $time_separator, ' ', $date_part ) ) ) ) . $range_separator;
				}

				$date = trim( $formatted_date, $range_separator );

				// Remove the time separator set in TEC settings, in case of full day event and same separator being set in date format.
				if ( tribe_event_is_all_day( $post_id ) ) {
					$date = str_replace( trim( $time_separator ), '', $date );
				}
			}
		}

		if ( ! empty( $args['time_range_sep'] ) ) {
			$date = str_replace( $range_separator, $args['time_range_sep'], $date );
		}

		$date = $date ? $date : '';

		return $date;
	}

	/**
	 * Gets Events Calendar date value. Returns an array with a date.
	 *
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return array
	 */
	public static function get_event_date( $args, $post_id = 0 ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		if ( 'end_event_date' === $args['event_date'] ) {
			$date               = tribe_get_end_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
			$args['start_date'] = tribe_get_start_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
		} else {
			$date = tribe_get_start_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		return [
			'date' => $date,
			'args' => $args,
		];
	}

	/**
	 * Gets Events Calendar event cost.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_cost( $args ) {
		$post_id         = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();
		$cost            = tribe_get_cost( $post_id, 'none' !== $args['currency'] );
		$currency_symbol = get_post_meta( $post_id, '_EventCurrencySymbol', true );
		$currency_symbol = $currency_symbol ? $currency_symbol : tribe_get_option( 'defaultCurrencySymbol', '$' );

		if ( '' !== $args['currency_position'] ) {

			// If there is several tickets, there will be a cost range sep.
			$cost_parts = explode( _x( ' – ', 'Cost range separator', 'the-events-calendar' ), $cost );

			if ( isset( $cost_parts[1] ) ) {

				// Filter each cost part. If a cost püart is empty after filtering, it will have been "FREE".
				$cost_parts_0 = filter_var( str_replace( $currency_symbol, '', $cost_parts[0] ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				if ( $cost_parts_0 ) {
					$cost_parts[0] = $cost_parts_0;
					$cost_parts[0] = 'prefix' === $args['currency_position'] ? $currency_symbol . $cost_parts[0] : $cost_parts[0] . $currency_symbol;
				}

				$cost_parts_1 = filter_var( str_replace( $currency_symbol, '', $cost_parts[1] ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				if ( $cost_parts_1 ) {
					$cost_parts[1] = $cost_parts_1;
					$cost_parts[1] = 'prefix' === $args['currency_position'] ? $currency_symbol . $cost_parts[1] : $cost_parts[1] . $currency_symbol;
				}

				// Put it together again.
				$cost = implode( _x( ' – ', 'Cost range separator', 'the-events-calendar' ), $cost_parts );
			} else {
				$cost_val = filter_var( $cost, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

				if ( $cost_val ) {
					$cost = 'prefix' === $args['currency_position'] ? $currency_symbol . $cost_val : $cost_val . $currency_symbol;
				}
			}
		}

		if ( 'code' === $args['currency'] ) {
			$currency_code = get_post_meta( $post_id, '_EventCurrencyCode', true );
			$currency_code = $currency_code ? $currency_code : tribe_get_option( 'defaultCurrencyCode', 'USD' );

			$cost = trim( str_replace( $currency_symbol, ' ' . $currency_code . ' ', $cost ) );
		}

		return $cost;
	}

	/**
	 * Gets Events Calendar event status.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_status( $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		$status = '<span class="awb-tec-status">' . ucwords( get_post_meta( $post_id, '_tribe_events_status', true ) ) . '</span>';

		if ( 'yes' === $args['display_reason'] ) {
			$status .= '<br /><span class="awb-tec-status__description">' . wp_kses_post( get_post_meta( $post_id, '_tribe_events_status_reason', true ) ) . '</span>';
		}

		return $status;
	}

	/**
	 * Gets Events Calendar event series name.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_series_name( $args ) {
		$post_id     = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();
		$post_id     = tribe( TEC\Events_Pro\Custom_Tables\V1\Templates\Single_Event_Modifications::class )->normalize_post_id( $post_id );
		$series_post = function_exists( 'tec_event_series' ) ? tec_event_series( $post_id ) : '';

		if ( ! $series_post instanceof WP_Post ) {
			return '';
		}

		$name = apply_filters( 'the_title', $series_post->post_title, $series_post->ID );

		return $name;
	}

	/**
	 * Gets Events Calendar event series URL.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_series_url( $args ) {
		$post_id     = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();
		$post_id     = tribe( TEC\Events_Pro\Custom_Tables\V1\Templates\Single_Event_Modifications::class )->normalize_post_id( $post_id );
		$series_post = function_exists( 'tec_event_series' ) ? tec_event_series( $post_id ) : '';

		if ( ! $series_post instanceof WP_Post ) {
			return '';
		}

		$url = get_post_permalink( $series_post->ID );

		return $url;
	}

	/**
	 * Gets Events Calendar event website.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_website( $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();
		return tribe_get_event_website_url( $post_id );
	}


	/**
	 * Gets Events Calendar event subscribe link.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_subscribe_link( $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		$handler = tribe( Tribe\Events\Views\V2\iCalendar\iCalendar_Handler::class );
		$links   = $handler->get_subscribe_links();

		if ( isset( $links[ $args['calendar'] ] ) ) {
			return $links[ $args['calendar'] ]->get_uri();
		}
	}

	/**
	 * Remove the recurring event after the meta, since the HTML will take a
	 * lot of space.
	 *
	 * @param string $tooltip The recurring tooltip.
	 * @return string Empty string, containing no tooltip.
	 */
	public static function remove_event_recurring_info( $tooltip ) {
		return '';
	}

	/**
	 * Gets Events Calendar event venue data.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @param int   $post_id The post ID.
	 * @return string
	 */
	public static function get_event_venue_data( $type, $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		switch ( $type ) {
			case 'name':
				return tribe_get_venue( $post_id );
			case 'url':
				return tribe_get_venue_link( $post_id, false );
				break;
			case 'post_content':
				return self::get_event_related_post_content( tribe_get_venue_id( $post_id ), $args );
				break;
			case 'address':
				return tribe_get_address( $post_id );
				break;
			case 'city':
				return tribe_get_city( $post_id );
				break;
			case 'country':
				return tribe_get_country( $post_id );
				break;
			case 'state':
				return tribe_get_stateprovince( $post_id );
				break;
			case 'zip':
				return tribe_get_zip( $post_id );
				break;
			case 'phone':
				return tribe_get_phone( $post_id );
				break;
			case 'website':
				return tribe_get_venue_website_url( $post_id );
				break;
			case 'gmap_link':
				return tribe_get_map_link( $post_id );
				break;
			case 'full_address':
				return tribe_get_venue_single_line_address( $post_id, false );
				break;
			case 'coordinates':
				return tribe_get_coordinates( $post_id );
				break;
		}
	}

	/**
	 * Gets Events Calendar event venue name.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_name( $args ) {
		$venue_name = self::get_event_venue_data( 'name', $args );
		return is_null( $venue_name ) ? '' : $venue_name;
	}

	/**
	 * Gets Events Calendar event venue URL.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_url( $args ) {
		return self::get_event_venue_data( 'url', $args );
	}

	/**
	 * Gets Events Calendar event venue post content.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_post_content( $args ) {
		return self::get_event_venue_data( 'post_content', $args );
	}

	/**
	 * Gets Events Calendar event related post content.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_related_post_content( $post_id, $args ) {
		if ( has_excerpt( $post_id ) ) {
			$content = do_shortcode( get_the_excerpt( $post_id ) );
		} else {
			$content = get_the_content( null, false, $post_id );
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );

			if ( 'excerpt' === $args['content_type'] ) {
				$content = wp_strip_all_tags( $content );

				if ( 'characters' === fusion_get_option( 'excerpt_base' ) ) {
					$content = mb_substr( $content, 0, $args['excerpt_length'] );
				} else {
					if ( str_word_count( $content, 0 ) > $args['excerpt_length'] ) {
						$pos     = array_keys( str_word_count( $content, 2 ) );
						$content = substr( $content, 0, $pos[ $args['excerpt_length'] ] ) . fusion_get_option( 'excerpt_read_more_symbol' );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Gets Events Calendar event venue address.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_address( $args ) {
		return self::get_event_venue_data( 'address', $args );
	}

	/**
	 * Gets Events Calendar event venue city.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_city( $args ) {
		return self::get_event_venue_data( 'city', $args );
	}

	/**
	 * Gets Events Calendar event venue country.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_country( $args ) {
		return self::get_event_venue_data( 'country', $args );
	}

	/**
	 * Gets Events Calendar event venue state.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_state_province( $args ) {
		return self::get_event_venue_data( 'state', $args );
	}

	/**
	 * Gets Events Calendar event venue zip code.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_zip( $args ) {
		return self::get_event_venue_data( 'zip', $args );
	}


	/**
	 * Gets Events Calendar event venue phone.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_phone( $args ) {
		return self::get_event_venue_data( 'phone', $args );
	}

	/**
	 * Gets Events Calendar event venue website.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_website( $args ) {
		return self::get_event_venue_data( 'website', $args );
	}

	/**
	 * Gets Events Calendar event venue Google maps link.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_gmap_link( $args ) {
		return self::get_event_venue_data( 'gmap_link', $args );
	}

	/**
	 * Gets Events Calendar event venue full address.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_full_address( $args ) {
		add_filter( 'tribe_events_venue_single_line_address_parts', [ self::class, 'add_venue_address' ], 10, 2 );
		$address = self::get_event_venue_data( 'full_address', $args );
		remove_filter( 'tribe_events_venue_single_line_address_parts', [ self::class, 'add_venue_address' ], 10, 2 );

		return is_null( $address ) ? '' : $address;
	}

	/**
	 * Adds the street address to the TEC single line address.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array Array of address parts
	 * @param int Event ID
	 * @return string The full address including venue name.
	 */
	public static function add_venue_address( $venue_address, $event_id ) {
		$args = [
			'event_id' => $event_id,
		];

		$address = self::get_event_venue_address( $args );

		array_unshift( $venue_address, $address );

		return $venue_address;
	}

	/**
	 * Gets Events Calendar event venue address longitude.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_address_longitude( $args ) {
		return self::get_event_venue_address_coordinates( 'lng', $args );
	}

	/**
	 * Gets Events Calendar event venue address latitude.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_venue_address_latitude( $args ) {
		return self::get_event_venue_address_coordinates( 'lat', $args );
	}

	/**
	 * Gets Events Calendar event venue address coorinates.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param string $type   The coordinate type to retunr. Either lat|lng|both.
	 * @param array  $args    The args.
	 * @return string|array
	 */
	public static function get_event_venue_address_coordinates( $type, $args ) {
		$coordinates = self::get_event_venue_data( 'coordinates', $args );

		return 'both' === $type ? $coordinates : $coordinates[ $type ];
	}

	/**
	 * Gets Events Calendar event organizer data.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param string $type   The type of data to be retrieved.
	 * @param array  $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_data( $type, $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		switch ( $type ) {
			case 'name':
				return tribe_get_organizer( $post_id );
				break;
			case 'url':
				return tribe_get_organizer_link( $post_id, false );
				break;
			case 'post_content':
				return self::get_event_related_post_content( tribe_get_organizer_id( $post_id ), $args );
				break;
			case 'phone':
				return tribe_get_organizer_phone( $post_id );
				break;
			case 'email':
				return tribe_get_organizer_email( $post_id );
				break;
			case 'website':
				return tribe_get_organizer_website_url( $post_id );
				break;
		}
	}

	/**
	 * Gets Events Calendar event organizer name.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_name( $args ) {
		return self::get_event_organizer_data( 'name', $args );
	}

	/**
	 * Gets Events Calendar event organizer URL.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_url( $args ) {
		return self::get_event_organizer_data( 'url', $args );
	}

	/**
	 * Gets Events Calendar event organizer post content.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_post_content( $args ) {
		return self::get_event_organizer_data( 'post_content', $args );
	}

	/**
	 * Gets Events Calendar event organizer phone number.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_phone( $args ) {
		return self::get_event_organizer_data( 'phone', $args );
	}

	/**
	 * Gets Events Calendar event organizer email.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_email( $args ) {
		return self::get_event_organizer_data( 'email', $args );
	}

	/**
	 * Gets Events Calendar event organizer website.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_organizer_website( $args ) {
		return self::get_event_organizer_data( 'website', $args );
	}

	/**
	 * Gets Events Calendar event ticket capacity.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_ticket_capacity( $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		if ( ! empty( $args['ticket_id'] ) ) {
			return tribe_tickets_get_readable_amount( tribe_tickets_get_capacity( $args['ticket_id'] ) );
		}

		return tribe_tickets_get_readable_amount( tribe_get_event_capacity( $post_id ) );
	}

	/**
	 * Gets Events Calendar event ticket availability.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_event_ticket_availability( $args ) {
		$post_id = ! empty( $args['event_id'] ) ? $args['event_id'] : self::get_post_id();

		if ( ! empty( $args['ticket_id'] ) ) {
			return tribe_tickets_get_readable_amount( tribe( 'tickets.handler' )->get_ticket_max_purchase( $args['ticket_id'] ) );
		}

		return tribe_tickets_get_readable_amount( tribe_events_count_available_tickets( $post_id ) );
	}

	/**
	 * Gets Events Calendar main events page URL.
	 *
	 * @static
	 * @since 7.11.10
	 * @access public
	 * @param array $args    The args.
	 * @return string
	 */
	public static function get_main_events_page_url( $args ) {
		return esc_url( tribe_get_events_link() );
	}

	/**
	 * Generates the update card link.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_update_cart_class( $args, $post_id = 0 ) {
		return '#updateCart';
	}

	/**
	 * Gets Woo start or end sale date value
	 *
	 * @param array   $args    The args.
	 * @param integer $post_id The post ID.
	 * @return string
	 */
	public static function woo_sale_date( $args, $post_id = 0 ) {

		if ( isset( $args['product_id'] ) && ! empty( $args['product_id'] ) ) {
			$post_id = $args['product_id'];
		}
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		if ( 'end_date' === $args['sale_date'] ) {
			$field_name         = '_sale_price_dates_to';
			$start_date         = $date = self::fusion_get_post_custom_field(
				[
					'key'     => '_sale_price_dates_from',
					'post_id' => $post_id,
				]
			);
			$args['start_date'] = $start_date;
		} else {
			$field_name = '_sale_price_dates_from';
		}

		$date = self::fusion_get_post_custom_field(
			[
				'key'     => $field_name,
				'post_id' => $post_id,
			]
		);
		return ! empty( $date ) ? [
			'date' => date( 'Y-m-d H:i:s', $date ),
			'args' => $args,
		] : '';
	}


	/**
	 * Get product price.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_price( $args, $post_id = 0 ) {

		if ( ! isset( $args['format'] ) ) {
			$args['format'] = '';
		}

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );
		$price    = '';

		if ( ! $_product ) {
			return;
		}

		if ( '' === $args['format'] ) {
			$price = $_product->get_price_html();
		}

		if ( 'original' === $args['format'] ) {
			$price = wc_price( wc_get_price_to_display( $_product, [ 'price' => $_product->get_regular_price() ] ) );
		}

		if ( 'sale' === $args['format'] ) {
			$price = wc_price( wc_get_price_to_display( $_product, [ 'price' => $_product->get_sale_price() ] ) );
		}

		if ( 'original_float' === $args['format'] ) {
			$price = wc_get_price_to_display( $_product, [ 'price' => $_product->get_regular_price() ] );
		}

		if ( 'sale_float' === $args['format'] ) {
			$price = wc_get_price_to_display( $_product, [ 'price' => $_product->get_sale_price() ] );
		}

		return $price;
	}

	/**
	 * Get product SKU.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_sku( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		return '<span class="awb-sku product_meta"><span class="sku">' . esc_html( $_product->get_sku() ) . '</span></span>';
	}

	/**
	 * Get product stock.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_stock( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		$stock = $_product->get_stock_quantity();

		return null !== $stock ? $stock : '';
	}

	/**
	 * Get product gallery.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_gallery( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		$gallery = $_product->get_gallery_image_ids();

		return $gallery;
	}

	/**
	 * Get term count.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_term_count( $args, $post_id = 0 ) {
		$term_count = '0';

		if ( is_tax() || is_category() || is_tag() || is_author() ) {
			if ( is_author() ) {
				$author     = get_user_by( 'slug', get_query_var( 'author_name' ) );
				$term_count = isset( $author->ID ) ? (string) count_user_posts( $author->ID ) : '0';
			} elseif ( isset( get_queried_object()->count ) ) {
				$term       = get_queried_object();
				$term_count = isset( get_queried_object()->count ) ? get_queried_object()->count : 0;

				if ( isset( $args['include_child_terms'] ) && 'yes' === $args['include_child_terms'] ) {
					$term_children = get_term_children( $term->term_id, $term->taxonomy );
					if ( ! is_wp_error( $term_children ) ) {
						foreach ( $term_children as $child ) {
							$term        = get_term_by( 'id', $child, $term->taxonomy );
							$term_count += isset( $term->count ) ? $term->count : 0;
						}
					}
				}

				$term_count = (string) $term_count;
			}
		}

		if ( isset( $args['display_zero_terms'] ) && 'no' === $args['display_zero_terms'] && 0 === (int) $term_count ) {
			return '';
		}

		if ( isset( $args['singular_text'] ) && isset( $args['plural_text'] ) ) {
			$term_count .= '1' === $term_count ? $args['singular_text'] : $args['plural_text'];
		}

		return $term_count;
	}

	/**
	 * Get cart count.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_count( $args, $post_id = 0 ) {
		$cart_count  = 0;
		$opening_tag = '<span class="fusion-dynamic-cart-count-wrapper"';

		if ( is_object( WC()->cart ) ) {
			$cart_count = WC()->cart->get_cart_contents_count();
		}

		if ( isset( $args['singular_text'] ) && isset( $args['plural_text'] ) ) {
			$cart_count .= 1 === $cart_count ? $args['singular_text'] : $args['plural_text'];

			$opening_tag .= ' data-singular="' . esc_attr( $args['singular_text'] ) . '" data-plural="' . esc_attr( $args['plural_text'] ) . '"';

			if ( ! isset( self::$has_rendered['woo_cart_count'] ) || true !== self::$has_rendered['woo_cart_count'] ) {
				self::$has_rendered['woo_cart_count'] = true;

				// Enqueue only if we use singular and plural texts.
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-woo-cart-count',
					FusionBuilder::$js_folder_url . '/general/woo-cart-count.js',
					FusionBuilder::$js_folder_path . '/general/woo-cart-count.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);
			}
		}

		return $opening_tag . '><span class="fusion-dynamic-cart-count">' . $cart_count . '</span></span>';
	}

	/**
	 * Get cart total.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_total( $args, $post_id = 0 ) {
		$cart_total  = 0;
		$opening_tag = '<span class="fusion-dynamic-cart-total-wrapper"';

		if ( is_object( WC()->cart ) ) {
			$cart_total = WC()->cart->get_cart_total();
		}

		return '<span class="fusion-dynamic-cart-total-wrapper"><span class="fusion-dynamic-cart-total">' . $cart_total . '</span></span>';
	}

	/**
	 * Get add to cart link.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_cart_link( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return '';
		}

		return $_product->add_to_cart_url();
	}

	/**
	 * Generates the next step link
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_form_get_next_step( $args, $post_id = 0 ) {
		return '#nextStep';
	}

	/**
	 * Generates the previous step link
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_form_get_previous_step( $args, $post_id = 0 ) {
		return '#previousStep';
	}

	/**
	 * Modify the cart ajax.
	 *
	 * @access public
	 * @since 3.3
	 * @param array $fragments Ajax fragments handled by WooCommerce.
	 * @return array
	 */
	public function woo_fragments( $fragments ) {
		$cart_contents_count = '';
		$cart_total          = '';

		if ( is_object( WC()->cart ) ) {
			$cart_contents_count = WC()->cart->get_cart_contents_count();
			$cart_total          = WC()->cart->get_cart_total();
		}

		$fragments['.fusion-dynamic-cart-count'] = '<span class="fusion-dynamic-cart-count">' . $cart_contents_count . '</span>';
		$fragments['.fusion-dynamic-cart-total'] = '<span class="fusion-dynamic-cart-total">' . $cart_total . '</span>';

		return $fragments;
	}

	/**
	 * Get product rating.
	 *
	 * @static
	 * @access public
	 * @since 2.1
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function woo_get_rating( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$_product = wc_get_product( $post_id );

		if ( ! $_product ) {
			return;
		}

		if ( '' === $args['format'] ) {
			$output = $_product->get_average_rating();
		}

		if ( 'rating' === $args['format'] ) {
			$output = $_product->get_rating_count();
		}

		if ( 'review' === $args['format'] ) {
			$output = $_product->get_review_count();
		}

		return $output;
	}

	/**
	 * Author Name.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_name( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_the_author_meta( 'display_name', $user_id );
	}

	/**
	 * Author Description.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_description( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_the_author_meta( 'description', $user_id );
	}

	/**
	 * Author Avatar.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_avatar( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return get_avatar_url( get_the_author_meta( 'email', $user_id ) );
	}

	/**
	 * Author URL.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_url( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$user_id = get_post_field( 'post_author', $post_id );
		return esc_url( get_author_posts_url( $user_id ) );
	}

	/**
	 * Author Social Link.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_author_social( $args, $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}

		$type    = isset( $args['type'] ) ? $args['type'] : 'author_email';
		$user_id = get_post_field( 'post_author', $post_id );
		$url     = get_the_author_meta( $type, $user_id );

		if ( 'author_email' === $type ) {
			$url = 'mailto:' . $url;
		}
		return esc_url( $url );
	}

	/**
	 * Post comments number.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function fusion_get_post_comments( $args, $post_id = 0 ) {
		$output      = '';
		$should_link = isset( $args['link'] ) && 'no' === $args['link'] ? false : true;

		if ( ! $post_id ) {
			$post_id = self::get_post_id();
		}
		$number = get_comments_number( $post_id );

		if ( 0 === $number ) {
			$output = esc_html__( 'No Comments', 'fusion-builder' );
		} elseif ( 1 === $number ) {
			$output = esc_html__( 'One Comment', 'fusion-builder' );
		} else {
			/* Translators: Number of comments */
			$output = sprintf( _n( '%s Comment', '%s Comments', $number, 'fusion-builder' ), number_format_i18n( $number ) );

		}

		if ( $should_link ) {
			$output = '<a class="fusion-one-page-text-link" href="' . get_comments_link( $post_id ) . '">' . $output . '</a>';
		}
		return $output;
	}

	/**
	 * Author Name.
	 *
	 * @static
	 * @access public
	 * @since 3.3
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_get_logged_in_username( $args ) {
		$user = wp_get_current_user();
		return is_user_logged_in() ? $user->display_name : '';
	}

	/**
	 * User Avatar.
	 *
	 * @static
	 * @access public
	 * @since 3.9
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_get_user_avatar( $args ) {
		$user = wp_get_current_user();

		$size = isset( $args['size'] ) && $args['size'] ? $args['size'] : '96';

		$avatar = is_user_logged_in() ? get_avatar( $user->ID, $size, '', $user->display_name ) : '';

		return $avatar;
	}

	/**
	 * Get search count.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 * @param array $args    Arguments.
	 * @param int   $post_id The post-ID.
	 * @return string
	 */
	public static function get_search_count( $args, $post_id = 0 ) {
		$search_count = 0;

		if ( is_search() ) {
			global $wp_query;
			$search_count = $wp_query->found_posts;
		} elseif ( isset( $_GET['awb-studio-content'] ) && isset( $_GET['search'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query        = fusion_cached_query( Fusion_Template_Builder()->archives_type( [] ) );
			$search_count = $query->found_posts;
		}

		if ( ! isset( $args['plural_text'] ) ) {
			$args['plural_text'] = '';
		}

		if ( ! isset( $args['singular_text'] ) ) {
			$args['singular_text'] = '';
		}

		$search_string = ( 1 === $search_count ? $args['singular_text'] : $args['plural_text'] );
		$space_before  = ( ! empty( $args['before'] ) ) ? ' ' : '';
		$space_after   = ( ! empty( $args['after'] ) ) ? ' ' : '';

		/* translators: 1: The search count, 2: The search string. */
		return $space_before . sprintf( __( '%1$d %2$s', 'fusion-builder' ), $search_count, $search_string ) . $space_after;
	}

	/**
	 * Woo Shop Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_shop_page_url( $args ) {
		return get_permalink( wc_get_page_id( 'shop' ) );
	}

	/**
	 * Woo Cart Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_cart_page_url( $args ) {
		return wc_get_cart_url();
	}

	/**
	 * Woo Checkout Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_checkout_page_url( $args ) {
		return wc_get_checkout_url();
	}

	/**
	 * Woo My Account Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_myaccount_page_url( $args ) {
		return wc_get_page_permalink( 'myaccount' );
	}

	/**
	 * Woo Terms & Conditions Page URL.
	 *
	 * @static
	 * @access public
	 * @since 3.7
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function woo_tnc_page_url( $args ) {
		return get_permalink( wc_terms_and_conditions_page_id() );
	}

	/**
	 * Woo order number.
	 *
	 * @since 3.10
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_woo_order_number( $args ) {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			$wc_order = wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		} else {
			$is_le_preview = isset( $_GET['action'] ) && 'ajax_dynamic_data_default_callback' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_le_preview ) {
				return '1234';
			}
			return '';
		}

		return $wc_order->get_order_number();
	}


	/**
	 * Woo order date.
	 *
	 * @since 3.10
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_woo_order_date( $args ) {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			$wc_order = wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		} else {
			$is_le_preview = isset( $_GET['action'] ) && 'ajax_dynamic_data_default_callback' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_le_preview ) {
				return wc_format_datetime( new WC_DateTime() );
			}
			return '';
		}

		return wc_format_datetime( $wc_order->get_date_created() );
	}

	/**
	 * Woo order email.
	 *
	 * @since 3.10
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_woo_order_billing_email( $args ) {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			$wc_order = wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		} else {
			$is_le_preview = isset( $_GET['action'] ) && 'ajax_dynamic_data_default_callback' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_le_preview ) { // phpcs:ignore WordPress.Security.NonceVerification
				return 'example@no-reply.com';
			}
			return '';
		}

		if ( is_user_logged_in() && $wc_order->get_user_id() === get_current_user_id() && $wc_order->get_billing_email() ) {
			return $wc_order->get_billing_email();
		}

		return '';
	}

	/**
	 * Woo order total.
	 *
	 * @since 3.10
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_woo_order_total( $args ) {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			$wc_order = wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		} else {
			$is_le_preview = isset( $_GET['action'] ) && 'ajax_dynamic_data_default_callback' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_le_preview ) { // phpcs:ignore WordPress.Security.NonceVerification
				return wc_price( 12345.67, [ 'currency' => get_woocommerce_currency() ] );
			}
			return '';
		}

		return $wc_order->get_formatted_order_total();
	}

	/**
	 * Woo order payment method.
	 *
	 * @since 3.10
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function awb_woo_order_payment_method( $args ) {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			$wc_order = wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		} else {
			$is_le_preview = isset( $_GET['action'] ) && 'ajax_dynamic_data_default_callback' === $_GET['action']; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_le_preview ) {
				$gateways       = WC()->payment_gateways->payment_gateways();
				$payment_method = reset( $gateways ); // get first item from associative array.
				if ( $payment_method ) {
					return $payment_method->get_method_title();
				}
			}
			return '';
		}

		if ( $wc_order->get_payment_method_title() ) {
			return $wc_order->get_payment_method_title();
		}

		return '';
	}

	/**
	 * Open HubSpot chat.
	 *
	 * @static
	 * @access public
	 * @since 3.7.1
	 * @param array $args Arguments.
	 * @return string
	 */
	public static function fusion_open_hubspot_chat() {

		// Enqueue js file.
		Fusion_Dynamic_JS::enqueue_script(
			'fusion-hubspot',
			FusionBuilder::$js_folder_url . '/general/fusion-hubspot.js',
			FusionBuilder::$js_folder_path . '/general/fusion-hubspot.js',
			[ 'jquery' ],
			FUSION_BUILDER_VERSION,
			true
		);

		return '#hubspot-open-chat';
	}
}
