<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WPMUDEV_HUB_Hosting_Reseller extends WPMUDEV_HUB_Reseller {

	/**
	 * @var null|self
	 */
	protected static $instance = null;

	const OPTION_NAME                   = 'wpmudev_hub_hosting_reseller_settings';
	const LAST_SYNC_TRANSIENT_SYNC_NAME = 'wpmudev_hub_hosting_reseller_last_sync';
	/**
	 * API responses memoize
	 *
	 * @var [][]
	 */
	protected static $api_products_responses = array();
	protected static $api_products_headers   = array();

	/**
	 * API responses memoize
	 *
	 * @var [][]
	 */
	protected static $api_product_responses = array();

	const DEFAULT_PRICING_TABLE_PRODUCT_FIELDS           = array( 'image', 'name', 'description', 'feature_list' );
	const DEFAULT_PRICING_TABLE_LAYOUT                   = 'grid';
	const DEFAULT_PRICING_TABLE_ORDER_BUTTON_LABEL_TEXT  = 'Order Now';
	const DEFAULT_PRICING_TABLE_ORDER_BUTTON_LABEL_COLOR = '#ffffff';
	const DEFAULT_PRICING_TABLE_ORDER_BUTTON_BG_COLOR    = '#286EF1';
	const DEFAULT_PRICING_TABLE_ORDER_CONTAINER_BG_COLOR = '#ffffff';

	/**
	 * @return self
	 */
	public static function get_instance() {
		/**
		 * Filter Hub Hosting Reseller adapter
		 *
		 * @param WPMUDEV_HUB_Hosting_Reseller|null $instance
		 *
		 * @since 2.0.0
		 */
		self::$instance = apply_filters( 'wpmudev_hub_hosting_reseller_adapter', self::$instance );
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
	}

	protected function get_raw_data( $key = '', $fallback = false ) {
		$raw_data = get_site_option( self::OPTION_NAME, array() );
		$raw_data = is_array( $raw_data ) ? $raw_data : array();

		if ( $key ) {
			return ( isset( $raw_data[ $key ] ) ? $raw_data[ $key ] : $fallback );
		}

		return ! is_null( $raw_data ) ? $raw_data : $fallback;
	}

	protected function update_raw_data( $data ) {
		return update_site_option(
			self::OPTION_NAME,
			wp_parse_args( $data, $this->get_raw_data() )
		);
	}

	public function is_active() {
		/**
		 * Filter whether hosting reseller active
		 *
		 * @param bool $active current active state ( depends on HUB FE ready + hosting reseller settings )
		 *
		 * @since 2.0.0
		 */
		$active = apply_filters( 'wpmudev_hub_is_hosting_reseller_active', $this->get_raw_data( 'is_active' ) );

		return wp_validate_boolean( $active );
	}

	public function set_active( $active = true ) {
		$active = wp_validate_boolean( $active );

		return $this->update_raw_data( array( 'is_active' => $active ) );
	}

	public function get_default_pricing_table_customizations() {
		return array(
			'product_fields'                => self::DEFAULT_PRICING_TABLE_PRODUCT_FIELDS,
			'layout'                        => self::DEFAULT_PRICING_TABLE_LAYOUT,
			'order_button_label_text'       => self::DEFAULT_PRICING_TABLE_ORDER_BUTTON_LABEL_TEXT,
			'order_button_label_color'      => self::DEFAULT_PRICING_TABLE_ORDER_BUTTON_LABEL_COLOR,
			'order_button_background_color' => self::DEFAULT_PRICING_TABLE_ORDER_BUTTON_BG_COLOR,
			'container_background_color'    => self::DEFAULT_PRICING_TABLE_ORDER_CONTAINER_BG_COLOR,
		);
	}

	public function get_pricing_table_customizations() {
		$pricing_table_customizations = wp_parse_args(
			$this->get_raw_data( 'pricing_table_customizations', array() ),
			// defaults
			$this->get_default_pricing_table_customizations()
		);

		/**
		 * Filter pricing table customization for hosting reseller
		 *
		 * @param array $pricing_table_customization current pricing table customization
		 *
		 * @since 2.0.0
		 */
		$pricing_table_customizations = apply_filters( 'wpmudev_hub_hosting_reseller_pricing_table_customizations', $pricing_table_customizations );

		return is_array( $pricing_table_customizations ) ? $pricing_table_customizations : array();
	}

	public function set_pricing_table_customizations( $customizations ) {
		$customizations = wp_parse_args(
			$customizations,
			$this->get_pricing_table_customizations()
		);

		return $this->update_raw_data( array( 'pricing_table_customizations' => $customizations ) );
	}


	/**
	 * Short hand of get_products
	 * This one will auto decide whether to sync with api or not, based on transient
	 *
	 * @return array
	 */
	public function get_products_maybe_sync() {
		$synced = get_site_transient( self::LAST_SYNC_TRANSIENT_SYNC_NAME );

		return $this->get_products( ! $synced );
	}

	public function get_products( $sync_with_api = false ) {
		$current_products = $this->get_raw_data( 'products', array() );
		$current_products = is_array( $current_products ) ? $current_products : array();

		if ( $current_products && $sync_with_api ) {
			// re-sync with API, check for inconsistencies, active vs archive and values
			$api_products = $this->get_api_products(
				array(
					'ids'      => array_values( wp_list_pluck( $current_products, 'id' ) ),
					'per_page' => count( $current_products ),
				)
			);

			// whatever happen update last_sync, so we are not stuck doing it
			set_site_transient( self::LAST_SYNC_TRANSIENT_SYNC_NAME, time(), HOUR_IN_SECONDS );

			// something messed up
			if ( is_wp_error( $api_products ) ) {
				// return internal data, whatever it is
				return $this->get_products( false );
			}

			$api_products_map = array();
			foreach ( $api_products as $api_product ) {
				$product_id                      = isset( $api_product['id'] ) ? $api_product['id'] : 0;
				$api_products_map[ $product_id ] = $api_product;
			}

			// key uid with id, for easy traverse
			$current_product_uid_ids_map = wp_list_pluck( $current_products, 'id' );

			// product existence check ( since we only request active products, this will also means when product archived, it won't be here )
			foreach ( $current_product_uid_ids_map as $current_product_uid => $current_product_id ) {
				// product gone, delete internal data
				if ( ! isset( $api_products_map[ $current_product_id ] ) ) {
					$this->delete_product( $current_product_uid );
					unset( $current_products[ $current_product_uid ] );
				}
			}

			// plan existence now
			foreach ( $current_products as $current_product_uid => $current_product ) {
				$current_plans      = isset( $current_product['plans'] ) ? $current_product['plans'] : array();
				$current_plans      = is_array( $current_plans ) ? $current_plans : array();
				$current_product_id = isset( $current_product['id'] ) ? $current_product['id'] : 0;

				$api_product = $api_products_map[ $current_product_id ]; // no need for isset here, its must be set, no doubt, see product existence check above
				$api_plans   = isset( $api_product['plans'] ) ? $api_product['plans'] : array();
				$api_plans   = is_array( $api_plans ) ? $api_plans : array();

				$api_plans_map = array();
				foreach ( $api_plans as $api_plan ) {
					$api_plan_id                   = isset( $api_plan['id'] ) ? $api_plan['id'] : 0;
					$api_plans_map[ $api_plan_id ] = $api_plan;
				}

				// now we can replace current product with api product ( this will update outdated values )
				unset( $api_product['plans'] );
				$current_product = wp_parse_args( $api_product, $current_product );

				// now check plans existence
				foreach ( $current_plans as $key => $current_plan ) {
					$current_plan_id = isset( $current_plan['id'] ) ? $current_plan['id'] : 0;
					if ( ! isset( $api_plans_map[ $current_plan_id ] ) ) {
						unset( $current_plans[ $key ] );
						continue;
					}
					// replace plan data from API
					$current_plans[ $key ] = $api_plans_map[ $current_plan_id ];
				}

				$current_product['plans'] = array_values( $current_plans );

				if ( empty( $current_product['plans'] ) ) {
					$this->delete_product( $current_product_uid );
					continue;
				}

				$this->update_products( array( $current_product_uid => $current_product ) );
			}

			// reload skip sync
			return $this->get_products( false );
		}

		// sorting
		// force sort_order and visibility be there
		foreach ( $current_products as $key => $product ) {
			$product                                = is_array( $product ) ? $product : array();
			$product['sort_order']                  = (int) ( isset( $product['sort_order'] ) ? $product['sort_order'] : 0 );
			$product['is_visible_in_pricing_table'] = wp_validate_boolean( isset( $product['is_visible_in_pricing_table'] ) ? $product['is_visible_in_pricing_table'] : true );
			$product['is_visible_in_hub_embed']     = wp_validate_boolean( isset( $product['is_visible_in_hub_embed'] ) ? $product['is_visible_in_hub_embed'] : true );
			$current_products[ $key ]               = $product;
		}

		// order by sort_order
		$current_products = wp_list_sort( $current_products, 'sort_order', 'ASC', true );

		// checkout links
		$base_url = WPMUDEV_HUB_Plugin_Front::get_client_page_base_url();
		if ( $base_url ) {
			foreach ( $current_products as $key => $product ) {
				$product = is_array( $product ) ? $product : array();
				if ( ! isset( $product['id'] ) ) {
					continue;
				}
				$product_id = $product['id'];
				if ( ! isset( $product['plans'] ) ) {
					continue;
				}
				$plans = $product['plans'];
				$plans = is_array( $plans ) ? $plans : array();
				foreach ( $plans as $plan_key => $plan ) {
					$plan                 = is_array( $plan ) ? $plan : array();
					$plan['checkout_url'] = $base_url;
					if ( ! isset( $plan['id'] ) ) {
						continue;
					}
					$plan_id              = $plan['id'];
					$plan['checkout_url'] = add_query_arg(
						array(
							'_path' => esc_url_raw( trailingslashit( '/hosting-create/' . $product_id . '/' . $plan_id ) ),
						),
						$base_url
					);

					$plans[ $plan_key ] = $plan;
				}

				$current_products[ $key ]['plans'] = $plans;
			}
		}

		/**
		 * Filter products list for hosting reseller
		 *
		 * @param array $products current enabled products
		 *
		 * @since 2.0.0
		 */
		$products = apply_filters( 'wpmudev_hub_hosting_reseller_products', $current_products );

		// in case data type  messed up after filter
		return is_array( $products ) ? $products : array();
	}

	public function update_products( $products ) {
		// structure
		$struct = array(
			'internal_id'   => array(
				'id'                          => 'product_id',
				'name'                        => 'product_name',
				'etc'                         => 'etc',
				'plans'                       => array(
					array(
						'id'   => 'plan_id',
						'name' => 'plan_name',
						'etc'  => 'etc',
					),
				),
				'sort_order'                  => 'sort_order',
				'is_visible_in_pricing_table' => true,
				'is_visible_in_hub_embed'     => true,
			),
			'internal_id_2' => array(
				'id'                          => 'product_id',
				'name'                        => 'product_name',
				'etc'                         => 'etc',
				'plans'                       => array(
					array(
						'id'   => 'plan_id',
						'name' => 'plan_name',
						'etc'  => 'etc',
					),
				),
				'sort_order'                  => 'sort_order',
				'is_visible_in_pricing_table' => true,
				'is_visible_in_hub_embed'     => true,
			),
		);
		unset( $struct );

		return $this->update_raw_data(
			array(
				'products' => wp_parse_args(
					$products,
					$this->get_products()
				),
			)
		);
	}

	public function add_product( $product_id, $plan_ids = null, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'sort_order'                  => null,
				'is_visible_in_pricing_table' => null,
				'is_visible_in_hub_embed'     => null,
			)
		);

		$product = $this->prepare_product_setup(
			$product_id,
			$plan_ids,
			$args
		);
		if ( is_wp_error( $product ) ) {
			return $product;
		}

		// generate uid
		$id               = wp_generate_uuid4();
		$current_products = $this->get_products( false );
		if ( isset( $current_products[ $id ] ) ) {
			return new WP_Error( 'id_conflict', __( 'Unable to add product, please try again.', 'thc' ), array( 'status' => 400 ) );
		}

		return $this->update_products( array( $id => $product ) );
	}

	public function update_product( $uid, $product_id = null, $plan_ids = null, $args = array(), $sync_with_api = false ) {
		$args    = wp_parse_args(
			$args,
			array(
				'sort_order'                  => null,
				'is_visible_in_pricing_table' => null,
				'is_visible_in_hub_embed'     => null,
			)
		);
		$product = $this->get_product( $uid, $sync_with_api );
		if ( is_wp_error( $product ) ) {
			return $product;
		}

		if ( is_null( $product_id ) ) {
			// no product update
			$product_id = isset( $product['id'] ) ? $product['id'] : 0;
			if ( ! $product_id ) {
				/* translators: %s: Hosting reseller product ID. */
				return new WP_Error( 'hub_product_id_not_found', __( 'Hub product ID not found.', 'thc' ), array( 'status' => 404 ) );
			}
		}

		if ( is_null( $plan_ids ) ) {
			// no plan ids update, for existing
			$plans    = isset( $product['plans'] ) ? $product['plans'] : array();
			$plan_ids = wp_list_pluck( $plans, 'id' );
		}

		if ( is_null( $args['sort_order'] ) ) {
			// no sort_order update
			$args['sort_order'] = isset( $product['sort_order'] ) ? $product['sort_order'] : null;
		}
		if ( is_null( $args['is_visible_in_pricing_table'] ) ) {
			// no is_visible_in_pricing_table update
			$args['is_visible_in_pricing_table'] = isset( $product['is_visible_in_pricing_table'] ) ? $product['is_visible_in_pricing_table'] : null;
		}
		if ( is_null( $args['is_visible_in_hub_embed'] ) ) {
			// no is_visible_in_pricing_table update
			$args['is_visible_in_hub_embed'] = isset( $product['is_visible_in_hub_embed'] ) ? $product['is_visible_in_hub_embed'] : null;
		}

		$product = $this->prepare_product_setup(
			$product_id,
			$plan_ids,
			$args
		);
		if ( is_wp_error( $product ) ) {
			return $product;
		}

		return $this->update_products( array( $uid => $product ) );
	}

	/**
	 * @param int        $product_id
	 * @param array|null $plan_ids
	 * @param array      $args
	 *
	 * @return array|WP_Error
	 */
	protected function prepare_product_setup( $product_id, $plan_ids = null, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'sort_order'                  => null,
				'is_visible_in_pricing_table' => null,
				'is_visible_in_hub_embed'     => null,
			)
		);

		$api_product = $this->get_api_product( $product_id );
		if ( is_wp_error( $api_product ) ) {
			return $api_product;
		}

		// product archive check, `get_api_product` can't filter out archive, check manually here
		$product_name = isset( $api_product['name'] ) ? $api_product['name'] : '';
		$is_archived  = isset( $api_product['is_archived'] ) ? $api_product['is_archived'] : true;
		if ( $is_archived ) {
			return new WP_Error(
				'archived_product',
				/* translators: %s: Product name / ID. */
				sprintf( __( 'Product %s can not be added, as it\'s archived.', 'thc' ), $product_name ? $product_name : $product_id ),
				array( 'status' => 400 )
			);
		}

		// now check plan_ids
		$raw_api_plans = isset( $api_product['plans'] ) ? $api_product['plans'] : array();
		$raw_api_plans = is_array( $raw_api_plans ) ? $raw_api_plans : array();
		$api_plans     = array();// keyed by plan id
		foreach ( $raw_api_plans as $raw_api_plan ) {
			$api_plan_id = isset( $raw_api_plan['id'] ) ? $raw_api_plan['id'] : 0;
			// somehow doesn't have id
			if ( ! $api_plan_id ) {
				continue;
			}

			$api_plans[ $api_plan_id ] = $raw_api_plan;
		}
		unset( $raw_api_plans ); // free memory

		if ( is_null( $plan_ids ) ) {
			// plan ids null, means add all
			$plan_ids = array_keys( $api_plans );
		} else {
			// make sense the input
			$plan_ids = array_filter( $plan_ids );
			$plan_ids = array_unique( $plan_ids );
		}

		// empty plans ? should be okay, but its just weird, lets block it for now
		if ( ! $plan_ids ) {
			return new WP_Error( 'empty_plans', __( 'Please provide plans to add.', 'thc' ), array( 'status' => 400 ) );
		}

		$plans = array(); // the one that we gonna store later
		// check plan existence, since we filter out archive already, we just check its exists or not
		foreach ( $plan_ids as $plan_id ) {
			if ( ! isset( $api_plans[ $plan_id ] ) ) {
				return new WP_Error(
					'plan_not_found',
					/* translators: %s: Plan ID. */
					sprintf( __( 'Plan %d not found or already archived.', 'thc' ), $plan_id ),
					array( 'status' => 400 )
				);
			}
			$plans[ $plan_id ] = $api_plans[ $plan_id ];
		}

		// replace plans from API, we just wanna store the one the selected
		$api_product['plans'] = array_values( $plans );

		// auto increment sort_order
		if ( is_null( $args['sort_order'] ) ) {
			$api_product['sort_order'] = 0;
			$products                  = $this->get_products();
			$sort_orders               = wp_list_pluck( $products, 'sort_order' );
			if ( $sort_orders ) {
				$api_product['sort_order'] = max( $sort_orders ) + 1;
			}
		} else {
			$api_product['sort_order'] = (int) $args['sort_order'];
		}

		// default visibility is true
		$api_product['is_visible_in_pricing_table'] = ! is_null( $args['is_visible_in_pricing_table'] ) ? $args['is_visible_in_pricing_table'] : true;
		$api_product['is_visible_in_hub_embed']     = ! is_null( $args['is_visible_in_hub_embed'] ) ? $args['is_visible_in_hub_embed'] : true;

		return $api_product;
	}

	public function get_product( $uid, $sync_with_api = false ) {
		$products = $this->get_products( $sync_with_api );
		if ( ! isset( $products[ $uid ] ) ) {
			/* translators: %s: Hosting reseller product ID. */
			return new WP_Error( 'product_not_found', sprintf( __( 'Hosting reseller product with ID %s not found.', 'thc' ), $uid ), array( 'status' => 404 ) );
		}

		return $products[ $uid ];
	}

	public function delete_product( $uid ) {
		$products = $this->get_products();
		unset( $products[ $uid ] );

		return $this->update_raw_data( array( 'products' => $products ) );
	}

	public function update_product_sort_order( $uid, $sort_order ) {
		$product = $this->get_product( $uid );
		if ( is_wp_error( $product ) ) {
			return false;
		}

		$product = wp_parse_args( array( 'sort_order' => (int) $sort_order ), $product );

		return $this->update_products( array( $uid => $product ) );
	}

	private function get_api_request_key( $api_request_args ) {
		// sort to make the hash persistent
		ksort( $api_request_args, SORT_STRING );
		$cache_key = wp_json_encode( $api_request_args );
		$cache_key = md5( $cache_key );

		// limit length to 172, follow https://developer.wordpress.org/apis/handbook/transients/#saving-transients
		return substr( $cache_key, 0, 172 );
	}

	public function get_api_products( $args, $skip_memoize = true, &$headers = array() ) {
		// skip memoize for now, we can enable later if its become such issue
		$request_key = $this->get_api_request_key( $args );

		if ( $skip_memoize ) {
			unset( self::$api_products_responses[ $request_key ] );
			unset( self::$api_products_headers[ $request_key ] );
		}
		if ( isset( self::$api_products_responses[ $request_key ] ) && isset( self::$api_products_headers[ $request_key ] ) ) {
			$headers = self::$api_products_headers[ $request_key ];

			return self::$api_products_responses[ $request_key ];
		}

		$res = WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => '/client-billing/products/reseller/hosting/',
				'method' => 'GET',
				'data'   => wp_parse_args(
					$args,
					array(
						'is_archived_products' => false, // we always want active products
						'is_archived_plans'    => false, // we always want active plans
						'_fields'              => array(
							'id',
							'is_archived',
							'name',
							'type',
							'image',
							'image_180x180',
							'image_50x50',
							'stripe_product_id',
							'plans',
							'description',
							'feature_list',
							'hosting_plan',
							'wp_user_role',
							'client_role',
							'auto_suspend',
							'auto_delete',
						), // reduce size by limiting fields
					)
				),
			),
			$redirected_location,
			$headers
		);

		// don't memoize err
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		self::$api_products_responses[ $request_key ] = $res;
		self::$api_products_headers[ $request_key ]   = is_array( $headers ) ? $headers : array();

		return self::$api_products_responses[ $request_key ];
	}

	public function get_api_product( $product_id, $args = array(), $skip_memoize = true ) {
		// skip memoize for now, we can enable later if its become such issue
		$request_key = $this->get_api_request_key( wp_parse_args( $args, array( 'product_id' => $product_id ) ) );

		if ( $skip_memoize ) {
			unset( self::$api_product_responses[ $request_key ] );
		}
		if ( isset( self::$api_product_responses[ $request_key ] ) ) {
			return self::$api_product_responses[ $request_key ];
		}

		$res = WPMUDEV_HUB_API_Request::get_instance()->exec(
			array(
				'path'   => sprintf( '/client-billing/products/reseller/hosting/%d', $product_id ),
				'method' => 'GET',
				'data'   => wp_parse_args(
					$args,
					array(
						'is_archived_plans' => false, // we always want active plans
						'_fields'           => array(
							'id',
							'is_archived',
							'name',
							'type',
							'image',
							'image_180x180',
							'image_50x50',
							'stripe_product_id',
							'plans',
							'description',
							'feature_list',
							'hosting_plan',
							'wp_user_role',
							'client_role',
							'auto_suspend',
							'auto_delete',
						), // reduce size by limiting fields
					)
				),
			)
		);

		// don't memoize err
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		self::$api_product_responses[ $request_key ] = $res;

		return self::$api_product_responses[ $request_key ];
	}

	public function reset() {
		delete_site_option( self::OPTION_NAME );
		delete_site_transient( self::LAST_SYNC_TRANSIENT_SYNC_NAME );
	}
}