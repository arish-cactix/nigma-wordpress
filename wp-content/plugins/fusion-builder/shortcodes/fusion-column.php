<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_Column' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class FusionSC_Column extends Fusion_Column_Element {

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function __construct() {
			$shortcode         = 'fusion_builder_column';
			$shortcode_attr_id = 'fusion-column';
			$classname         = 'fusion-builder-column';
			$content_filter    = 'fusion_element_column_content';
			parent::__construct( $shortcode, $shortcode_attr_id, $classname, $content_filter );
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since 2.2
		 * @return array An array of classes, one for parent columns, one for child columns.
		 */
		final public static function get_instance() {
			$called_class = get_called_class();

			if ( ! isset( self::$instances[ $called_class ] ) ) {
				self::$instances[ $called_class ] = new $called_class();
			}

			return self::$instances[ $called_class ];
		}
	}
}

/**
 * Instantiates the container class.
 *
 * @return FusionSC_Column
 */
function fusion_builder_column() { // phpcs:ignore WordPress.NamingConventions
	return FusionSC_Column::get_instance();
}

// Instantiate container.
fusion_builder_column();


/**
 * Map column shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_column() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Column',
			[
				'name'              => esc_attr__( 'Column', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_column',
				'hide_from_builder' => true,
				'help_url'          => 'https://avada.com/documentation/column-element/',
				'params'            => fusion_get_column_params(),
				'subparam_map'      => fusion_get_column_subparam_map(),
			]
		)
	);
}
add_action( 'fusion_builder_wp_loaded', 'fusion_element_column' );
