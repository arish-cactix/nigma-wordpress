<?php
/**
 * Fusion Builder WooCommerce.
 *
 * @package Fusion-Builder
 * @since 3.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Woo class.
 *
 * @since 3.2
 */
class Fusion_Builder_WooCommerce {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.2
	 * @var object
	 */
	private static $instance;

	/**
	 * Class constructor.
	 *
	 * @since 3.2
	 * @access private
	 */
	private function __construct() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10 );

			add_action( 'avada_after_main_content', [ $this, 'add_woocommerce_structured_data' ] );
		}
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Builder_WooCommerce();
		}
		return self::$instance;
	}

	/**
	 * Init.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public function init() {

		if ( ! empty( $_POST['calc_shipping'] ) && class_exists( 'WC_Shortcode_Cart' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			WC_Shortcode_Cart::calculate_shipping();
			unset( $_POST['calc_shipping'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * Enqueue WooCommerce scripts.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public function enqueue_scripts() {
		if ( apply_filters( 'awb_enqueue_woocommerce_frontend_scripts', fusion_is_preview_frame() ) ) {
			wp_enqueue_script( 'zoom' );
			wp_enqueue_script( 'flexslider' );

			wp_enqueue_script( 'photoswipe-ui-default' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			add_action( 'wp_footer', 'woocommerce_photoswipe' );

			wp_enqueue_script( 'wc-single-product' );
		}
	}

	/**
	 * Get the page option from the template if not set in post.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $data Full data array.
	 * @param object $post Post object from target post.
	 * @return mixed
	 */
	public function add_product_data( $data, $post ) {
		if ( isset( $post->post_type ) && 'product' === $post->post_type ) {
			$product = wc_get_product( $post->ID );

			$data['examplePostDetails']['woo'] = [
				'featured'           => $product->get_featured(),
				'catalog_visibility' => $product->get_catalog_visibility(),
				'description'        => $product->get_description(),
				'short_description'  => $product->get_short_description(),
				'sku'                => $product->get_sku(),
				'menu_order'         => $product->get_menu_order(),
				'virtual'            => $product->get_virtual(),
				'price'              => $product->get_price(),
				'regular_price'      => $product->get_regular_price(),
				'sales_badge'        => $product->get_sale_price(),
				'data_on_sale_from'  => $product->get_date_on_sale_from(),
				'date_on_sale_to'    => $product->get_date_on_sale_to(),
				'total_sales'        => $product->get_total_sales(),
			];
		}
		return $data;
	}

	/**
	 * Add WooCommerce structured data, when using custom single product layout.
	 *
	 * @access public
	 * @since 4.5
	 * @return void
	 */
	public function add_woocommerce_structured_data() {
		global $woocommerce;

		if ( fusion_library()->woocommerce->is_product_layout() ) {
			$woocommerce->structured_data->generate_website_data();
			$woocommerce->structured_data->generate_product_data();
		}
	}
}

/**
 * Instantiates the Fusion_Woo class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.2
 * @return object Fusion_App
 */
function Fusion_Builder_WooCommerce() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Builder_WooCommerce::get_instance();
}
Fusion_Builder_WooCommerce();
