<?php
/**
 * Builder Component Class.
 *
 * @package fusion-builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}


/**
 * Builder Component Class.
 *
 * @since 2.2
 */
abstract class Fusion_Woo_Component extends Fusion_Element {

	/**
	 * Backup post object..
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $backup_post = false;

	/**
	 * Target live editing post if applicable.
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $post_target = false;

	/**
	 * Backup post object.
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $backup_product = false;

	/**
	 * Backup global $pages.
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $backup_pages = false;

	/**
	 * Target live editing post if applicable.
	 *
	 * @access public
	 * @since 2.2
	 * @var object
	 */
	public $product = false;

	/**
	 * Shortcode handle.
	 *
	 * @access public
	 * @since 2.2
	 * @var string
	 */
	public $shortcode_handle = '';

	/**
	 * The extras cache.
	 *
	 * @var array|null
	 */
	private static $extras_cache = null;

	/**
	 * Constructor.
	 *
	 * @since 2.2
	 * @param string $shortcode_handle Shortcode Handle.
	 */
	public function __construct( $shortcode_handle ) {
		parent::__construct();

		$this->shortcode_handle = $shortcode_handle;
		add_filter( 'fusion_component_' . $this->shortcode_handle . '_content', [ $this, 'maybe_check_render' ] );
		add_shortcode( $this->shortcode_handle, [ $this, 'render' ] );
	}

	/**
	 * Maybe check render.
	 *
	 * @since 2.2
	 * @param string $content Content.
	 * @return string
	 */
	public function maybe_check_render( $content ) {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() || ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $is_builder ) {
			return $content;
		}
		return $this->should_render() ? $content : '';
	}

	/**
	 * Maybe check render.
	 *
	 * @since 2.2
	 * @return string
	 */
	public function is_product() {
		return $this->product && ! empty( $this->product );
	}

	/**
	 * Check if component should render.
	 *
	 * @access public
	 * @since 2.2
	 * @return boolean
	 */
	abstract public function should_render();

	/**
	 * Render the shortcode.
	 *
	 * @access public
	 * @since 2.2.0
	 * @param  array  $args    Shortcode parameters.
	 * @param  string $content Content between shortcode.
	 * @return string          HTML output.
	 */
	abstract public function render( $args, $content );

	/**
	 * Returns the post-ID.
	 *
	 * @since 6.2.0
	 * @return int
	 */
	public function get_post_id() {
		$id = get_the_ID();
		if ( isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$id = (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		return apply_filters( 'fusion_dynamic_post_id', $id );
	}

	/**
	 * Emulates post with another.
	 *
	 * @access public
	 */
	public function get_target_post() {
		if ( $this->post_target ) {
			return $this->post_target;
		}
		if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && 'fusion_tb_section' === get_post_type() || is_singular( 'fusion_tb_section' ) ) {
			$target = Fusion_Template_Builder()->get_target_example();

			if ( $target ) {
				$this->post_target = $target;
				return $target;
			}
		} elseif ( fusion_doing_ajax() && isset( $_POST['target_post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id           = (int) sanitize_text_field( wp_unslash( $_POST['target_post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$this->post_target = get_post( $post_id );
			return $this->post_target;
		} elseif ( fusion_doing_ajax() && isset( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id           = (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$this->post_target = get_post( $post_id );
			return $this->post_target;
		} elseif ( is_product() ) {
			$this->product = wc_get_product();
		} elseif ( FusionBuilder()->post_card_data['is_rendering'] && function_exists( 'wc_get_product' ) ) {
			$this->product = 'product' === get_post_type( get_the_ID() ) ? wc_get_product() : false;
		}
		return false;
	}


	/**
	 * Emulates post with another.
	 *
	 * @access public
	 */
	public function emulate_product() {
		global $post, $product, $pages;

		// Exclude full refresh as content is generated server side anyway.
		$live_request = isset( $_POST['post_id'] ) && isset( $_POST['action'] ) && 'fusion_app_full_refresh' !== $_POST['action'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

		// Live editor and editing template.
		if ( $this->get_target_post() ) {
			$this->backup_post = $post;
			$post              = $this->post_target;

			$this->backup_pages = $pages;

			if ( ! is_array( $pages ) ) {
				$pages = [];
			}

			$pages[0] = $post->post_content;

			if ( class_exists( 'Fusion_App' ) && $live_request ) {
				Fusion_App()->emulate_wp_query();
			}

			if ( 'product' === $post->post_type ) {
				$this->product = wc_get_product( $post->ID );
			} else {
				$sample_product = $this->get_sample_product();
				$this->product  = $sample_product['product'];
			}

			if ( $this->is_product() ) {
				$this->backup_product = $product;
				$product              = $this->product;

				if ( ! empty( $sample_product ) ) {
					$this->backup_post = $post;
					$post              = $sample_product['post'];
					$pages[0]          = $post->post_content;
				}
			}

			// Its a live editor request.
		} elseif ( ( fusion_doing_ajax() && isset( $_POST['model'] ) ) || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() && 'fusion_tb_section' === get_post_type() || is_singular( 'fusion_tb_section' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$sample_product = $this->get_sample_product();
			$this->product  = $sample_product['product'];
			if ( $this->is_product() ) {
				$this->backup_product = $product;
				$product              = $this->product;

				if ( ! empty( $sample_product ) ) {
					$this->backup_post = $post;
					$post              = $sample_product['post'];
					$pages[0]          = $post->post_content;
				}
			}
		}
	}

	/**
	 * Get a sample product.
	 *
	 * @access public
	 * @return mixed
	 */
	public function get_sample_product() {
		$products = fusion_cached_query(
			[
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
			]
		);

		if ( $products->have_posts() ) {
			return [
				'product' => wc_get_product( $products->posts[0]->ID ),
				'post'    => $products->posts[0],
			];
		}
		return false;
	}

	/**
	 * Restores post object.
	 *
	 * @access public
	 */
	public function restore_product() {
		global $post, $product, $pages;
		if ( $this->backup_post ) {
			$post = $this->backup_post;
		}
		$this->backup_post = false;

		if ( $this->backup_product ) {
			$product = $this->backup_product;
		}
		$this->backup_product = false;

		if ( $this->backup_pages ) {
			$pages = $this->backup_pages;
		}

		$this->backup_pages = false;

		// Exclude full refresh as content is generated server side anyway.
		$live_request = isset( $_POST['post_id'] ) && isset( $_POST['action'] ) && 'fusion_app_full_refresh' !== $_POST['action'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

		if ( class_exists( 'Fusion_App' ) && $live_request ) {
			Fusion_App()->restore_wp_query();
		}
	}

	/**
	 * Get the order object, dummy order if in LE, or false if is not set.
	 *
	 * @return false|WC_Order|WC_Order_Refund
	 */
	protected function get_order_object() {
		global $wp;

		if ( is_object( $wp ) && property_exists( $wp, 'query_vars' ) && isset( $wp->query_vars['order-received'] ) ) {
			return wc_get_order( apply_filters( 'woocommerce_thankyou_order_id', absint( $wp->query_vars['order-received'] ) ) );
		}

		return false;
	}

	/**
	 * Used to get all order extra variables.
	 *
	 * @since 3.5
	 * @return array
	 */
	public static function get_order_extras() {
		if ( ! fusion_is_builder_frame() ) {
			return null;
		}

		if ( self::$extras_cache ) {
			return self::$extras_cache;
		}

		$total_price    = wc_price( ( 123.45 * 3 ), [ 'currency' => get_woocommerce_currency() ] );
		$subtotal_price = wc_price( ( 123.45 * 3 ), [ 'currency' => get_woocommerce_currency() ] );

		// Set payment method.
		$gateways       = WC()->payment_gateways->payment_gateways();
		$payment_method = reset( $gateways ); // get first item from associative array.
		if ( $payment_method ) {
			$payment_method_title = wp_kses_post( $payment_method->get_method_title() );
		} else {
			$payment_method_title = esc_html__( 'N/A', 'woocommerce' );
		}

		$extras = [
			'order_number'      => '1234',
			'date'              => wc_format_datetime( new WC_DateTime() ),
			'billing_email'     => 'example@no-reply.com',
			'total'             => $total_price,
			'payment_method'    => $payment_method_title,

			'billing_phone'     => '1-800-000-0000',
			'shipping_phone'    => '1-800-000-0000',
			'billing_address'   => 'John John<br>123 Street<br>Honolulu<br>Hawaii<br>0123',
			'shipping_address'  => 'John John<br>321 Street<br>Honolulu<br>Hawaii<br>3210',
			'order_note'        => esc_html__( 'This is an example of an order note left by the customer.', 'fusion-builder' ),
			'downloads_columns' => [
				[
					'id'   => 'download-product',
					'name' => __( 'Product', 'woocommerce' ),
				],
				[
					'id'   => 'download-remaining',
					'name' => __( 'Downloads remaining', 'woocommerce' ),
				],
				[
					'id'   => 'download-expires',
					'name' => __( 'Expires', 'woocommerce' ),
				],
				[
					'id'   => 'download-file',
					'name' => __( 'Download', 'woocommerce' ),
				],
			],

			'items'             => [],
			'item_totals'       => [
				[
					'label' => __( 'Subtotal:', 'woocommerce' ),
					'value' => $subtotal_price,
				],
				[
					'label' => __( 'Shipping:', 'woocommerce' ),
					'value' => __( 'Free!', 'woocommerce' ),
				],
				[
					'label' => __( 'Payment method:', 'woocommerce' ),
					'value' => $payment_method_title,
				],
				[
					'label' => __( 'Total:', 'woocommerce' ),
					'value' => $total_price,
				],
			],

		];

		for ( $i = 0; $i < 2; $i++ ) {
			$item_info = [];

			$item_info['is_visible']        = true;
			$item_info['product_permalink'] = '#';
			$item_info['thumbnail']         = wc_placeholder_img();

			$item_info['quantity_html']  = ' <strong class="product-quantity">' . sprintf( '&times; %s', $i + 1 ) . '</strong>';
			$item_info['product_total']  = wc_price( ( 123.45 * ( $i + 1 ) ), [ 'currency' => get_woocommerce_currency() ] );
			$item_info['item_name_html'] = sprintf( '<a href="%s">%s</a>', $item_info['product_permalink'], esc_html__( 'Product', 'woocommerce' ) );

			$item_info['item_downloads'] = '';
			$item_info['item_meta']      = '';

			if ( $i > 0 ) {
				$item_info['item_downloads'] = '<ul class ="wc-item-downloads"><li><strong class="wc-item-download-label">' . __( 'Download', 'woocommerce' ) . ':</strong> <a href="#" target="_blank">' . esc_html__( 'Product', 'woocommerce' ) . '</a></li></ul>';
			} else {
				$item_info['item_meta'] = '<ul class="wc-item-meta"><li><strong class="wc-item-meta-label">' . esc_html__( 'Color', 'fusion-builder' ) . ':</strong> ' . esc_html__( 'Black', 'fusion-builder' ) . '</li><li><strong class="wc-item-meta-label">' . esc_html__( 'Size', 'fusion-builder' ) . ':</strong> ' . esc_html__( 'Medium', 'fusion-builder' ) . '</li></ul>';

			}

			$extras['items'][] = $item_info;
		}

		// Add some product images.
		$query_args = [
			'limit'  => 2,
			'status' => 'publish',
		];
		$products   = wc_get_products( $query_args );

		if ( is_array( $products ) && count( $products ) > 1 ) {
			$i = 0;
			foreach ( $products as $product ) {
				$extras['items'][ $i ]['thumbnail'] = $product->get_image();
				$i++;
			}
		}

		self::$extras_cache = $extras;

		return $extras;
	}
}
