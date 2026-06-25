<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.9
 */

if ( fusion_is_element_enabled( 'fusion_stripe_button' ) ) {

	if ( ! class_exists( 'FusionSC_Stripe_Button_API_Handler' ) ) {

		/**
		 * Class Stripe API Handler.
		 *
		 * @since 3.9
		 */
		class FusionSC_Stripe_Button_API_Handler {
			/**
			 * Stripe Endpoint URL.
			 *
			 * @access private
			 * @since 3.9
			 * @var string
			 */
			private static $stripe_endpoint_url = 'https://api.stripe.com/v1/';

			/**
			 * Stripe Tax Endpoint.
			 *
			 * @access private
			 * @since 3.9
			 * @var string
			 */
			private static $stripe_tax_endpoint_url = 'tax_rates';

			/**
			 * Stripe Checkout Endpoint.
			 *
			 * @access private
			 * @since 3.9
			 * @var string
			 */
			private static $stripe_checkout_url_ext = 'checkout/sessions';

			/**
			 * API Get Request handler.
			 *
			 * @access public
			 * @since 3.9
			 * @param string $secret_key The secret key.
			 * @param string $endpoint The endpoint.
			 * @param array  $body The body.
			 * @return array|\WP_Error
			 */
			public function get( $secret_key, $endpoint = '', $body = [] ) {
				$headers = [ 'Authorization' => 'Bearer ' . $secret_key ];
				return wp_remote_get(
					self::$stripe_endpoint_url . $endpoint,
					[
						'headers' => $headers,
						'body'    => $body,
					]
				);
			}

			/**
			 * API Post Request handler.
			 *
			 * @access public
			 * @since 3.9
			 * @param array  $headers The headers.
			 * @param array  $body The body.
			 * @param string $endpoint The endpoint.
			 * @return array|\WP_Error
			 */
			public function post( $headers, $body, $endpoint ) {
				return wp_remote_post(
					self::$stripe_endpoint_url . $endpoint,
					[
						'headers' => $headers,
						'body'    => $body,
					]
				);
			}

			/**
			 * Get correct price based on currencies.
			 * Zero decimal currencies by stripe https://stripe.com/docs/currencies#zero-decimal
			 *
			 * @access public
			 * @since 3.9
			 * @param string $currency The currency.
			 * @param float  $product_price The product price.
			 * @return false|float
			 */
			public function set_price( $currency, $product_price ) {
				$zero_decimal = [ 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ];
				if ( in_array( $currency, $zero_decimal, true ) ) {
					// There is no need to multiply $product_price by 100.
					return floor( $product_price );
				} else {
					return floor( $product_price * 100 );
				}
			}

			/**
			 * Validate API KEY.
			 *
			 * @access public
			 * @since 3.9
			 * @param string $secret_key The secret key.
			 * @return array|WP_Error
			 */
			public function validate_api_key_endpoint( $secret_key ) {
				return $this->get( $secret_key, self::$stripe_tax_endpoint_url, [ 'limit' => 0 ] );
			}

			/**
			 * Call API Checkout.
			 *
			 * @access public
			 * @since 3.9
			 * @param string $secret_key The secret key.
			 * @param array  $args The arguments.
			 * @return void
			 */
			public function post_checkout_endpoint( $secret_key, $args ) {
				$headers = [ 'Authorization' => 'Bearer ' . $secret_key ];
				$body    = [
					'success_url'          => $args['success_url'],
					'cancel_url'           => $args['cancel_url'],
					'payment_method_types' => [ 'card' ],
					'mode'                 => 'payment',
				];

				if ( ! empty( $args['product_name'] ) && ! empty( $args['product_price'] ) ) {
					$body['line_items[0][quantity]']                       = $args['product_qty'];
					$body['line_items[0][price_data][currency]']           = $args['currency'];
					$body['line_items[0][price_data][product_data][name]'] = $args['product_name'];
					$body['line_items[0][price_data][unit_amount]']        = $this->set_price( $args['currency'], $args['product_price'] );
				}

				if ( ! empty( $args['shipping_price'] ) ) {
					$body['shipping_options'][0]['shipping_rate_data']['type']                     = 'fixed_amount';
					$body['shipping_options'][0]['shipping_rate_data']['fixed_amount']['amount']   = $this->set_price( $args['currency'], $args['shipping_price'] );
					$body['shipping_options'][0]['shipping_rate_data']['fixed_amount']['currency'] = $args['currency'];
					$body['shipping_options'][0]['shipping_rate_data']['display_name']             = esc_html__( 'shipping fee', 'fusion-builder' );
				}

				if ( ! empty( $args['shipping_address'] ) && 'yes' === $args['shipping_address'] ) {
					$body['shipping_address_collection'] = [
						'allowed_countries' => self::get_available_shipping_address_countries(),
					];
				}

				if ( isset( $args['tax_rate'] ) ) {
					$tax_rate     = explode( '|', $args['tax_rate'] );
					$tax_id       = [ $tax_rate[0] ];
					$tax_behavior = $tax_rate[1];

					if ( ! empty( $tax_behavior ) && ! empty( $tax_id ) ) {
						$body['line_items'][0]['price_data']['tax_behavior'] = $tax_behavior;
						$body['line_items'][0]['tax_rates']                  = $tax_id;
					}
				}

				$response = $this->post( $headers, $body, self::$stripe_checkout_url_ext );
				wp_send_json( $response );
			}

			/**
			 * Call API Tax rates.
			 *
			 * @since 3.9
			 * @param string $secret_key The secret key.
			 * @return array
			 */
			public function post_tax_rates_endpoint( $secret_key ) {
				$response = $this->get( $secret_key, self::$stripe_tax_endpoint_url, [ 'active' => 'true' ] );
				$options  = [];
				$data     = [];

				if ( ! is_wp_error( $response ) ) {
					$decoded_response = json_decode( $response['body'], true );

					if ( isset( $decoded_response['error'] ) || 0 === count( $decoded_response['data'] ) ) {
						$data = [];
					} else {
						$data = $decoded_response['data'];
					}
				}

				if ( ! empty( $data ) ) {
					foreach ( $data as $tax ) {
						$rate_type                                = $tax['inclusive'] ? 'inclusive' : 'exclusive';
						$options[ $tax['id'] . '|' . $rate_type ] = sprintf( '%s (%d%%) %s', $tax['display_name'], $tax['percentage'], $tax['description'] );
					}
				}

				return $options;
			}

			/**
			 * Returns a list of country codes and countries.
			 *
			 * @since 3.9
			 * @return array $countries A list of the available countries
			 */
			public static function get_countries() {
				$countries = [
					'AF' => __( 'Afghanistan', 'fusion-builder' ),
					'AX' => __( 'Åland Islands', 'fusion-builder' ),
					'AL' => __( 'Albania', 'fusion-builder' ),
					'DZ' => __( 'Algeria', 'fusion-builder' ),
					'AS' => __( 'American Samoa', 'fusion-builder' ),
					'AD' => __( 'Andorra', 'fusion-builder' ),
					'AO' => __( 'Angola', 'fusion-builder' ),
					'AI' => __( 'Anguilla', 'fusion-builder' ),
					'AQ' => __( 'Antarctica', 'fusion-builder' ),
					'AG' => __( 'Antigua and Barbuda', 'fusion-builder' ),
					'AR' => __( 'Argentina', 'fusion-builder' ),
					'AM' => __( 'Armenia', 'fusion-builder' ),
					'AW' => __( 'Aruba', 'fusion-builder' ),
					'AU' => __( 'Australia', 'fusion-builder' ),
					'AT' => __( 'Austria', 'fusion-builder' ),
					'AZ' => __( 'Azerbaijan', 'fusion-builder' ),
					'BS' => __( 'Bahamas', 'fusion-builder' ),
					'BH' => __( 'Bahrain', 'fusion-builder' ),
					'BD' => __( 'Bangladesh', 'fusion-builder' ),
					'BB' => __( 'Barbados', 'fusion-builder' ),
					'BY' => __( 'Belarus', 'fusion-builder' ),
					'BE' => __( 'Belgium', 'fusion-builder' ),
					'PW' => __( 'Belau', 'fusion-builder' ),
					'BZ' => __( 'Belize', 'fusion-builder' ),
					'BJ' => __( 'Benin', 'fusion-builder' ),
					'BM' => __( 'Bermuda', 'fusion-builder' ),
					'BT' => __( 'Bhutan', 'fusion-builder' ),
					'BO' => __( 'Bolivia', 'fusion-builder' ),
					'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'fusion-builder' ),
					'BA' => __( 'Bosnia and Herzegovina', 'fusion-builder' ),
					'BW' => __( 'Botswana', 'fusion-builder' ),
					'BV' => __( 'Bouvet Island', 'fusion-builder' ),
					'BR' => __( 'Brazil', 'fusion-builder' ),
					'IO' => __( 'British Indian Ocean Territory', 'fusion-builder' ),
					'BN' => __( 'Brunei', 'fusion-builder' ),
					'BG' => __( 'Bulgaria', 'fusion-builder' ),
					'BF' => __( 'Burkina Faso', 'fusion-builder' ),
					'BI' => __( 'Burundi', 'fusion-builder' ),
					'KH' => __( 'Cambodia', 'fusion-builder' ),
					'CM' => __( 'Cameroon', 'fusion-builder' ),
					'CA' => __( 'Canada', 'fusion-builder' ),
					'CV' => __( 'Cape Verde', 'fusion-builder' ),
					'KY' => __( 'Cayman Islands', 'fusion-builder' ),
					'CF' => __( 'Central African Republic', 'fusion-builder' ),
					'TD' => __( 'Chad', 'fusion-builder' ),
					'CL' => __( 'Chile', 'fusion-builder' ),
					'CN' => __( 'China', 'fusion-builder' ),
					'CX' => __( 'Christmas Island', 'fusion-builder' ),
					'CC' => __( 'Cocos (Keeling) Islands', 'fusion-builder' ),
					'CO' => __( 'Colombia', 'fusion-builder' ),
					'KM' => __( 'Comoros', 'fusion-builder' ),
					'CG' => __( 'Congo (Brazzaville)', 'fusion-builder' ),
					'CD' => __( 'Congo (Kinshasa)', 'fusion-builder' ),
					'CK' => __( 'Cook Islands', 'fusion-builder' ),
					'CR' => __( 'Costa Rica', 'fusion-builder' ),
					'HR' => __( 'Croatia', 'fusion-builder' ),
					'CU' => __( 'Cuba', 'fusion-builder' ),
					'CW' => __( 'Cura&ccedil;ao', 'fusion-builder' ),
					'CY' => __( 'Cyprus', 'fusion-builder' ),
					'CZ' => __( 'Czech Republic', 'fusion-builder' ),
					'DK' => __( 'Denmark', 'fusion-builder' ),
					'DJ' => __( 'Djibouti', 'fusion-builder' ),
					'DM' => __( 'Dominica', 'fusion-builder' ),
					'DO' => __( 'Dominican Republic', 'fusion-builder' ),
					'EC' => __( 'Ecuador', 'fusion-builder' ),
					'EG' => __( 'Egypt', 'fusion-builder' ),
					'SV' => __( 'El Salvador', 'fusion-builder' ),
					'GQ' => __( 'Equatorial Guinea', 'fusion-builder' ),
					'ER' => __( 'Eritrea', 'fusion-builder' ),
					'EE' => __( 'Estonia', 'fusion-builder' ),
					'ET' => __( 'Ethiopia', 'fusion-builder' ),
					'FK' => __( 'Falkland Islands', 'fusion-builder' ),
					'FO' => __( 'Faroe Islands', 'fusion-builder' ),
					'FJ' => __( 'Fiji', 'fusion-builder' ),
					'FI' => __( 'Finland', 'fusion-builder' ),
					'FR' => __( 'France', 'fusion-builder' ),
					'GF' => __( 'French Guiana', 'fusion-builder' ),
					'PF' => __( 'French Polynesia', 'fusion-builder' ),
					'TF' => __( 'French Southern Territories', 'fusion-builder' ),
					'GA' => __( 'Gabon', 'fusion-builder' ),
					'GM' => __( 'Gambia', 'fusion-builder' ),
					'GE' => __( 'Georgia', 'fusion-builder' ),
					'DE' => __( 'Germany', 'fusion-builder' ),
					'GH' => __( 'Ghana', 'fusion-builder' ),
					'GI' => __( 'Gibraltar', 'fusion-builder' ),
					'GR' => __( 'Greece', 'fusion-builder' ),
					'GL' => __( 'Greenland', 'fusion-builder' ),
					'GD' => __( 'Grenada', 'fusion-builder' ),
					'GP' => __( 'Guadeloupe', 'fusion-builder' ),
					'GU' => __( 'Guam', 'fusion-builder' ),
					'GT' => __( 'Guatemala', 'fusion-builder' ),
					'GG' => __( 'Guernsey', 'fusion-builder' ),
					'GN' => __( 'Guinea', 'fusion-builder' ),
					'GW' => __( 'Guinea-Bissau', 'fusion-builder' ),
					'GY' => __( 'Guyana', 'fusion-builder' ),
					'HT' => __( 'Haiti', 'fusion-builder' ),
					'HM' => __( 'Heard Island and McDonald Islands', 'fusion-builder' ),
					'HN' => __( 'Honduras', 'fusion-builder' ),
					'HK' => __( 'Hong Kong', 'fusion-builder' ),
					'HU' => __( 'Hungary', 'fusion-builder' ),
					'IS' => __( 'Iceland', 'fusion-builder' ),
					'IN' => __( 'India', 'fusion-builder' ),
					'ID' => __( 'Indonesia', 'fusion-builder' ),
					'IR' => __( 'Iran', 'fusion-builder' ),
					'IQ' => __( 'Iraq', 'fusion-builder' ),
					'IE' => __( 'Ireland', 'fusion-builder' ),
					'IM' => __( 'Isle of Man', 'fusion-builder' ),
					'IL' => __( 'Israel', 'fusion-builder' ),
					'IT' => __( 'Italy', 'fusion-builder' ),
					'CI' => __( 'Ivory Coast', 'fusion-builder' ),
					'JM' => __( 'Jamaica', 'fusion-builder' ),
					'JP' => __( 'Japan', 'fusion-builder' ),
					'JE' => __( 'Jersey', 'fusion-builder' ),
					'JO' => __( 'Jordan', 'fusion-builder' ),
					'KZ' => __( 'Kazakhstan', 'fusion-builder' ),
					'KE' => __( 'Kenya', 'fusion-builder' ),
					'KI' => __( 'Kiribati', 'fusion-builder' ),
					'KW' => __( 'Kuwait', 'fusion-builder' ),
					'KG' => __( 'Kyrgyzstan', 'fusion-builder' ),
					'LA' => __( 'Laos', 'fusion-builder' ),
					'LV' => __( 'Latvia', 'fusion-builder' ),
					'LB' => __( 'Lebanon', 'fusion-builder' ),
					'LS' => __( 'Lesotho', 'fusion-builder' ),
					'LR' => __( 'Liberia', 'fusion-builder' ),
					'LY' => __( 'Libya', 'fusion-builder' ),
					'LI' => __( 'Liechtenstein', 'fusion-builder' ),
					'LT' => __( 'Lithuania', 'fusion-builder' ),
					'LU' => __( 'Luxembourg', 'fusion-builder' ),
					'MO' => __( 'Macao', 'fusion-builder' ),
					'MK' => __( 'North Macedonia', 'fusion-builder' ),
					'MG' => __( 'Madagascar', 'fusion-builder' ),
					'MW' => __( 'Malawi', 'fusion-builder' ),
					'MY' => __( 'Malaysia', 'fusion-builder' ),
					'MV' => __( 'Maldives', 'fusion-builder' ),
					'ML' => __( 'Mali', 'fusion-builder' ),
					'MT' => __( 'Malta', 'fusion-builder' ),
					'MH' => __( 'Marshall Islands', 'fusion-builder' ),
					'MQ' => __( 'Martinique', 'fusion-builder' ),
					'MR' => __( 'Mauritania', 'fusion-builder' ),
					'MU' => __( 'Mauritius', 'fusion-builder' ),
					'YT' => __( 'Mayotte', 'fusion-builder' ),
					'MX' => __( 'Mexico', 'fusion-builder' ),
					'FM' => __( 'Micronesia', 'fusion-builder' ),
					'MD' => __( 'Moldova', 'fusion-builder' ),
					'MC' => __( 'Monaco', 'fusion-builder' ),
					'MN' => __( 'Mongolia', 'fusion-builder' ),
					'ME' => __( 'Montenegro', 'fusion-builder' ),
					'MS' => __( 'Montserrat', 'fusion-builder' ),
					'MA' => __( 'Morocco', 'fusion-builder' ),
					'MZ' => __( 'Mozambique', 'fusion-builder' ),
					'MM' => __( 'Myanmar', 'fusion-builder' ),
					'NA' => __( 'Namibia', 'fusion-builder' ),
					'NR' => __( 'Nauru', 'fusion-builder' ),
					'NP' => __( 'Nepal', 'fusion-builder' ),
					'NL' => __( 'Netherlands', 'fusion-builder' ),
					'NC' => __( 'New Caledonia', 'fusion-builder' ),
					'NZ' => __( 'New Zealand', 'fusion-builder' ),
					'NI' => __( 'Nicaragua', 'fusion-builder' ),
					'NE' => __( 'Niger', 'fusion-builder' ),
					'NG' => __( 'Nigeria', 'fusion-builder' ),
					'NU' => __( 'Niue', 'fusion-builder' ),
					'NF' => __( 'Norfolk Island', 'fusion-builder' ),
					'MP' => __( 'Northern Mariana Islands', 'fusion-builder' ),
					'KP' => __( 'North Korea', 'fusion-builder' ),
					'NO' => __( 'Norway', 'fusion-builder' ),
					'OM' => __( 'Oman', 'fusion-builder' ),
					'PK' => __( 'Pakistan', 'fusion-builder' ),
					'PS' => __( 'Palestinian Territory', 'fusion-builder' ),
					'PA' => __( 'Panama', 'fusion-builder' ),
					'PG' => __( 'Papua New Guinea', 'fusion-builder' ),
					'PY' => __( 'Paraguay', 'fusion-builder' ),
					'PE' => __( 'Peru', 'fusion-builder' ),
					'PH' => __( 'Philippines', 'fusion-builder' ),
					'PN' => __( 'Pitcairn', 'fusion-builder' ),
					'PL' => __( 'Poland', 'fusion-builder' ),
					'PT' => __( 'Portugal', 'fusion-builder' ),
					'PR' => __( 'Puerto Rico', 'fusion-builder' ),
					'QA' => __( 'Qatar', 'fusion-builder' ),
					'RE' => __( 'Reunion', 'fusion-builder' ),
					'RO' => __( 'Romania', 'fusion-builder' ),
					'RU' => __( 'Russia', 'fusion-builder' ),
					'RW' => __( 'Rwanda', 'fusion-builder' ),
					'BL' => __( 'Saint Barth&eacute;lemy', 'fusion-builder' ),
					'SH' => __( 'Saint Helena', 'fusion-builder' ),
					'KN' => __( 'Saint Kitts and Nevis', 'fusion-builder' ),
					'LC' => __( 'Saint Lucia', 'fusion-builder' ),
					'MF' => __( 'Saint Martin (French part)', 'fusion-builder' ),
					'SX' => __( 'Saint Martin (Dutch part)', 'fusion-builder' ),
					'PM' => __( 'Saint Pierre and Miquelon', 'fusion-builder' ),
					'VC' => __( 'Saint Vincent and the Grenadines', 'fusion-builder' ),
					'SM' => __( 'San Marino', 'fusion-builder' ),
					'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'fusion-builder' ),
					'SA' => __( 'Saudi Arabia', 'fusion-builder' ),
					'SN' => __( 'Senegal', 'fusion-builder' ),
					'RS' => __( 'Serbia', 'fusion-builder' ),
					'SC' => __( 'Seychelles', 'fusion-builder' ),
					'SL' => __( 'Sierra Leone', 'fusion-builder' ),
					'SG' => __( 'Singapore', 'fusion-builder' ),
					'SK' => __( 'Slovakia', 'fusion-builder' ),
					'SI' => __( 'Slovenia', 'fusion-builder' ),
					'SB' => __( 'Solomon Islands', 'fusion-builder' ),
					'SO' => __( 'Somalia', 'fusion-builder' ),
					'ZA' => __( 'South Africa', 'fusion-builder' ),
					'GS' => __( 'South Georgia/Sandwich Islands', 'fusion-builder' ),
					'KR' => __( 'South Korea', 'fusion-builder' ),
					'SS' => __( 'South Sudan', 'fusion-builder' ),
					'ES' => __( 'Spain', 'fusion-builder' ),
					'LK' => __( 'Sri Lanka', 'fusion-builder' ),
					'SD' => __( 'Sudan', 'fusion-builder' ),
					'SR' => __( 'Suriname', 'fusion-builder' ),
					'SJ' => __( 'Svalbard and Jan Mayen', 'fusion-builder' ),
					'SZ' => __( 'Eswatini', 'fusion-builder' ),
					'SE' => __( 'Sweden', 'fusion-builder' ),
					'CH' => __( 'Switzerland', 'fusion-builder' ),
					'SY' => __( 'Syria', 'fusion-builder' ),
					'TW' => __( 'Taiwan', 'fusion-builder' ),
					'TJ' => __( 'Tajikistan', 'fusion-builder' ),
					'TZ' => __( 'Tanzania', 'fusion-builder' ),
					'TH' => __( 'Thailand', 'fusion-builder' ),
					'TL' => __( 'Timor-Leste', 'fusion-builder' ),
					'TG' => __( 'Togo', 'fusion-builder' ),
					'TK' => __( 'Tokelau', 'fusion-builder' ),
					'TO' => __( 'Tonga', 'fusion-builder' ),
					'TT' => __( 'Trinidad and Tobago', 'fusion-builder' ),
					'TN' => __( 'Tunisia', 'fusion-builder' ),
					'TR' => __( 'Turkey', 'fusion-builder' ),
					'TM' => __( 'Turkmenistan', 'fusion-builder' ),
					'TC' => __( 'Turks and Caicos Islands', 'fusion-builder' ),
					'TV' => __( 'Tuvalu', 'fusion-builder' ),
					'UG' => __( 'Uganda', 'fusion-builder' ),
					'UA' => __( 'Ukraine', 'fusion-builder' ),
					'AE' => __( 'United Arab Emirates', 'fusion-builder' ),
					'GB' => __( 'United Kingdom (UK)', 'fusion-builder' ),
					'US' => __( 'United States (US)', 'fusion-builder' ),
					'UM' => __( 'United States (US) Minor Outlying Islands', 'fusion-builder' ),
					'UY' => __( 'Uruguay', 'fusion-builder' ),
					'UZ' => __( 'Uzbekistan', 'fusion-builder' ),
					'VU' => __( 'Vanuatu', 'fusion-builder' ),
					'VA' => __( 'Vatican', 'fusion-builder' ),
					'VE' => __( 'Venezuela', 'fusion-builder' ),
					'VN' => __( 'Vietnam', 'fusion-builder' ),
					'VG' => __( 'Virgin Islands (British)', 'fusion-builder' ),
					'VI' => __( 'Virgin Islands (US)', 'fusion-builder' ),
					'WF' => __( 'Wallis and Futuna', 'fusion-builder' ),
					'EH' => __( 'Western Sahara', 'fusion-builder' ),
					'WS' => __( 'Samoa', 'fusion-builder' ),
					'YE' => __( 'Yemen', 'fusion-builder' ),
					'ZM' => __( 'Zambia', 'fusion-builder' ),
					'ZW' => __( 'Zimbabwe', 'fusion-builder' ),
				];

				return apply_filters( 'fusion_stripe_countries', $countries );
			}

			/**
			 * Retrieves a list of countries that support Shipping Address collection.
			 *
			 * @since 3.9
			 * @return array List of country codes.
			 */
			public static function get_available_shipping_address_countries() {
				// Built in countries.
				$countries = self::get_countries();

				// Remove unsupported countries.
				unset( $countries['AS'] );
				unset( $countries['CX'] );
				unset( $countries['CC'] );
				unset( $countries['CU'] );
				unset( $countries['TP'] );
				unset( $countries['HM'] );
				unset( $countries['IR'] );
				unset( $countries['MH'] );
				unset( $countries['FM'] );
				unset( $countries['AN'] );
				unset( $countries['NF'] );
				unset( $countries['KP'] );
				unset( $countries['MP'] );
				unset( $countries['PW'] );
				unset( $countries['SD'] );
				unset( $countries['SY'] );
				unset( $countries['UM'] );
				unset( $countries['VI'] );

				return array_keys( $countries );
			}
		}
	}

	if ( ! class_exists( 'FusionSC_Stripe_Button' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.9
		 */
		class FusionSC_Stripe_Button extends Fusion_Element { // phpcs:ignore Generic.Files.OneObjectStructurePerFile, Generic.Classes.OpeningBraceSameLine

			/**
			 * The one, true instance of this object.
			 *
			 * @static
			 * @access private
			 * @since 3.9
			 * @var object
			 */
			private static $instance;

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.9
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Secret key selected.
			 *
			 * @access protected
			 * @since 3.9
			 * @var string
			 */
			protected $secret_key;

			/**
			 * Stripe Api Handler.
			 *
			 * @access public
			 * @since 3.9
			 * @var Object
			 */
			public $api;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.9
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_stripe_button-shortcode', [ $this, 'attr' ] );

				// Validate API KEY.
				add_action( 'wp_ajax_awb_validate_stripe_api_key', [ $this, 'validate_api_key' ] );
				add_action( 'wp_ajax_awb_stripe_button_submit', [ $this, 'submit_form_ajax' ] );
				add_action( 'wp_ajax_nopriv_awb_stripe_button_submit', [ $this, 'submit_form_ajax' ] );

				add_shortcode( 'fusion_stripe_button', [ $this, 'render' ] );

				$this->api = new FusionSC_Stripe_Button_API_Handler();
			}

			/**
			 * Creates or returns an instance of this class.
			 *
			 * @static
			 * @access public
			 * @since 3.9
			 */
			public static function get_instance() {

				// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
				if ( null === self::$instance ) {
					self::$instance = new FusionSC_Stripe_Button();
				}
				return self::$instance;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.9
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'api_mode'                           => $fusion_settings->get( 'stripe_button_api_mode' ),
					'shipping_address'                   => 'yes',
					'product_name'                       => esc_attr__( 'My Product', 'fusion-builder' ),
					'currency'                           => 'USD',
					'product_price'                      => '3.99',
					'product_qty'                        => '1',
					'shipping_price'                     => '',
					'test_tax_rate'                      => '',
					'live_tax_rate'                      => '',
					'success_url'                        => '',
					'cancel_url'                         => '',
					'button_el_type'                     => 'submit',
					'tab_index'                          => '',
					'hide_on_mobile'                     => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                     => '',
					'class'                              => '',
					'id'                                 => '',
					'accent_color'                       => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_color' ) ) : '#ffffff',
					'accent_hover_color'                 => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_hover_color' ) ) : '#ffffff',
					'bevel_color'                        => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color' ) ) : '#54770F',
					'bevel_color_hover'                  => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color_hover' ) ) : '#54770F',
					'border_color'                       => ( '' !== $fusion_settings->get( 'button_border_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_color' ) ) : '#ffffff',
					'border_hover_color'                 => ( '' !== $fusion_settings->get( 'button_border_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_hover_color' ) ) : '#ffffff',
					'color'                              => 'default',
					'gradient_colors'                    => '',
					'icon'                               => '',
					'icon_divider'                       => 'no',
					'icon_position'                      => 'left',
					'link'                               => '',
					'link_attributes'                    => '',
					'modal'                              => '',
					'size'                               => '',
					'margin_bottom'                      => '',
					'margin_left'                        => '',
					'margin_right'                       => '',
					'margin_top'                         => '',
					'stretch'                            => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'default_stretch_value'              => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'target'                             => '_self',
					'text_transform'                     => '',
					'title'                              => '',
					'type'                               => ( '' !== $fusion_settings->get( 'button_type' ) ) ? strtolower( $fusion_settings->get( 'button_type' ) ) : 'flat',
					'alignment'                          => '',
					'alignment_medium'                   => '',
					'alignment_small'                    => '',
					'animation_type'                     => '',
					'animation_direction'                => 'down',
					'animation_speed'                    => '',
					'animation_delay'                    => '',
					'animation_offset'                   => $fusion_settings->get( 'animation_offset' ),
					'animation_color'                    => '',
					'padding_top'                        => '',
					'padding_right'                      => '',
					'padding_bottom'                     => '',
					'padding_left'                       => '',
					'font_size'                          => '',
					'line_height'                        => '',
					'letter_spacing'                     => '',
					'fusion_font_family_button_font'     => '',
					'fusion_font_variant_button_font'    => '',
					'gradient_start_position'            => $fusion_settings->get( 'button_gradient_start' ),
					'gradient_end_position'              => $fusion_settings->get( 'button_gradient_end' ),
					'gradient_type'                      => $fusion_settings->get( 'button_gradient_type' ),
					'radial_direction'                   => $fusion_settings->get( 'button_radial_direction' ),
					'linear_angle'                       => $fusion_settings->get( 'button_gradient_angle' ),
					'border_radius_top_left'             => $fusion_settings->get( 'button_border_radius', 'top_left' ),
					'border_radius_top_right'            => $fusion_settings->get( 'button_border_radius', 'top_right' ),
					'border_radius_bottom_right'         => $fusion_settings->get( 'button_border_radius', 'bottom_right' ),
					'border_radius_bottom_left'          => $fusion_settings->get( 'button_border_radius', 'bottom_left' ),
					'border_top'                         => '',
					'border_right'                       => '',
					'border_bottom'                      => '',
					'border_left'                        => '',
					'icon_color'                         => '',
					'text_color'                         => '',
					'icon_hover_color'                   => '',
					'text_hover_color'                   => '',
					'gradient_hover_colors'              => '',
					'button_gradient_top_color'          => ( '' !== $fusion_settings->get( 'button_gradient_top_color' ) ) ? $fusion_settings->get( 'button_gradient_top_color' ) : '#65bc7b',
					'button_gradient_bottom_color'       => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color' ) : '#65bc7b',
					'button_gradient_top_color_hover'    => ( '' !== $fusion_settings->get( 'button_gradient_top_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_top_color_hover' ) : '#5aa86c',
					'button_gradient_bottom_color_hover' => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color_hover' ) : '#5aa86c',
					'button_accent_color'                => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? $fusion_settings->get( 'button_accent_color' ) : '#ffffff',
					'button_accent_hover_color'          => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? $fusion_settings->get( 'button_accent_hover_color' ) : '#ffffff',
					'button_bevel_color'                 => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? $fusion_settings->get( 'button_bevel_color' ) : '#54770F',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.9
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'stripe_button_api_mode' => 'api_mode',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.9
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $shortcode_tags;

				$this->set_element_id( $this->counter );
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_stripe_button' );

				$html  = '<form ' . FusionBuilder::attributes( 'stripe_button-shortcode' ) . '>';
				$html .= $this->generate_hidden_fields();
				$html .= call_user_func( $shortcode_tags['fusion_button'], $this->args, $content, 'fusion_button' );

				$html .= $this->render_notice( '&nbsp;', 'error' );

				$html .= '</form>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_stripe_button_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.9
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'awb-stripe-button-form awb-strip-button-form-' . $this->counter,
					'style' => '',
				];

				$attr                = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );
				$attr['data-target'] = $this->args['target'];

				return $attr;
			}

			/**
			 * Renders notice.
			 *
			 * @since 3.9
			 * @access private
			 * @param string $notice      The submission notice.
			 * @param string $notice_type Can be error|success.
			 * @return string The notices.
			 */
			private function render_notice( $notice, $notice_type ) {

				// If form was not sent yet, $notice will be empty, so return early.
				if ( ! $notice ) {
					return '';
				}

				if ( class_exists( 'FusionSC_Alert' ) ) {
					$shortcode = '[fusion_alert type="' . $notice_type . '" class="fusion-hidden awb-stripe-button-response awb-stripe-button-response-' . $notice_type . '" ';
					foreach ( [ 'margin_top', 'margin_right', 'margin_bottom', 'margin_left' ] as $param ) {
						if ( isset( $this->args[ $param ] ) && '' !== $this->args[ $param ] ) {
							$shortcode .= $param . '="' . $this->args[ $param ] . '" ';
						}
					}
					$shortcode .= ']' . $notice . '[/fusion_alert]';
					$notice     = do_shortcode( $shortcode );
				} else {
					$notice = '<div class="fusion-hidden awb-stripe-button-response awb-stripe-button-response-' . $notice_type . '">' . $notice . '</div>';
				}

				return apply_filters( 'fusion_stripe_button_notice', $notice, $notice_type );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 3.9
			 * @return array $sections Button settings.
			 */
			public function add_options() {
				$api_key_link = 'https://stripe.com/docs/keys#obtain-api-keys';
				return [
					'stripe_button_shortcode_section' => [
						'label'  => esc_html__( 'Stripe Button', 'fusion-builder' ),
						'id'     => 'stripe_button_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-check-empty',
						'fields' => [
							'stripe_button_shortcode_important_note_info' => [
								'label'       => '',
								'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> Insert API keys to authenticate requests with Stripe. You can find the keys in <a href="https://stripe.com/docs/keys" target="_blank">Stripe Account Dashboard</a>.', 'fusion-builder' ) . '</div>',
								'id'          => 'stripe_button_shortcode_important_note_info',
								'type'        => 'custom',
							],
							'stripe_button_api_mode' => [
								'type'        => 'radio-buttonset',
								'label'       => esc_attr__( 'API Mode', 'fusion-builder' ),
								'description' => esc_attr__( 'Build your integration in Test mode, and switch to Live mode when you are ready.', 'fusion-builder' ),
								'id'          => 'stripe_button_api_mode',
								'transport'   => 'postMessage',
								'choices'     => [
									'live' => esc_attr__( 'Live', 'fusion-builder' ),
									'test' => esc_attr__( 'Test', 'fusion-builder' ),
								],
								'default'     => 'test',
							],
							'stripe_button_test_secret_key' => [
								'label'       => esc_html__( 'Stripe Test Secret Key', 'fusion-builder' ),
								/* translators: %s - Link to the docs. */
								'description' => sprintf( __( 'Enter your Stripe Test Secret Key. For more information please see <a href="%s" target="_blank">Stripe API Guide</a>.', 'fusion-builder' ), $api_key_link ),
								'id'          => 'stripe_button_test_secret_key',
								'default'     => '',
								'type'        => 'text',
							],
							'stripe_button_validate_test_sk' => [
								'label'       => '',
								'description' => esc_html__( 'Test Secret Key Status.', 'fusion-builder' ),
								'id'          => 'stripe_button_validate_test_sk',
								'default'     => '',
								'type'        => 'raw',
								'content'     => '<a class="button button-secondary" href="#" onclick="awbValidateStripeApiKey(event);" data-mode="test" target="_self" >' . esc_html__( 'Test Connection', 'fusion-builder' ) . '</a><span class="spinner fusion-spinner"></span>',
								'full_width'  => false,
								'transport'   => 'postMessage', // No need to refresh the page.
							],
							'stripe_button_live_secret_key' => [
								'label'       => esc_html__( 'Stripe Live Secret Key', 'fusion-builder' ),
								/* translators: Link to the docs. */
								'description' => sprintf( __( 'Enter your Live Secret Key. For more information please see <a href="%s" target="_blank">Stripe API Guide</a>.', 'fusion-builder' ), $api_key_link ),
								'id'          => 'stripe_button_live_secret_key',
								'default'     => '',
								'type'        => 'text',
							],
							'stripe_button_validate_live_sk' => [
								'label'       => '',
								'description' => esc_html__( 'Live Secret Key Status.', 'fusion-builder' ),
								'id'          => 'stripe_button_validate_live_sk',
								'default'     => '',
								'type'        => 'raw',
								'content'     => '<a class="button button-secondary" href="#" onclick="awbValidateStripeApiKey(event);" data-mode="live" target="_self" >' . esc_html__( 'Test Connection', 'fusion-builder' ) . '</a><span class="spinner fusion-spinner"></span>',
								'full_width'  => false,
								'transport'   => 'postMessage', // No need to refresh the page.
							],
						],
					],
				];
			}

			/**
			 * Validate API KEY.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function validate_api_key() {
				$secret_key = '';
				if ( empty( $_POST['secretkey'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					wp_send_json_error( esc_html__( 'Secret Key is empty.', 'fusion-builder' ) );
				} else {
					$secret_key = wp_unslash( $_POST['secretkey'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
				}

				$response = $this->api->validate_api_key_endpoint( $secret_key );
				$code     = $response['response']['code'];

				if ( 200 !== $code ) {
					wp_send_json_error( sprintf( '%s: %s. %s', $code, $response['response']['message'], esc_html__( 'Please make sure you are entering a valid API KEY.', 'fusion-builder' ) ) );
				} else {
					wp_send_json_success( esc_html__( 'API is working properly.', 'fusion-builder' ) );
				}
			}

			/**
			 * Generate form hidden fields.
			 *
			 * @access public
			 * @since 3.9
			 * @return string
			 */
			public function generate_hidden_fields() {
				global $wp;
				$current_url = home_url( add_query_arg( [], $wp->request ) );
				$html        = '';

				$fields = [
					'api_mode'         => $this->args['api_mode'],
					'url'              => admin_url( 'admin-ajax.php' ),
					'action'           => 'submit_stripe_button',
					'post_id'          => get_the_ID(),
					'cancel_url'       => '' !== $this->args['cancel_url'] ? esc_url( $this->args['cancel_url'] ) : $current_url,
					'success_url'      => '' !== $this->args['success_url'] ? esc_url( $this->args['success_url'] ) : $current_url,
					'product_name'     => $this->args['product_name'],
					'currency'         => $this->args['currency'],
					'product_price'    => $this->args['product_price'],
					'product_qty'      => $this->args['product_qty'],
					'shipping_price'   => $this->args['shipping_price'],
					'shipping_address' => $this->args['shipping_address'],
					'tax_rate'         => $this->args[ $this->args['api_mode'] . '_tax_rate' ],
				];

				foreach ( $fields as $key => $value ) {
					$html .= sprintf( '<input type="hidden" name="%s" value="%s" />', $key, esc_attr( $value ) );
				}
				$html .= wp_nonce_field( 'stripe_button_form_submit', 'stripe_button_form_submit_nonce', true, false );
				return $html;
			}

			/**
			 * Submit Stripe Form.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function submit_form_ajax() {
				check_ajax_referer( 'awb-stripe-button-nonce', 'nonce' );
				if ( isset( $_POST['data'] ) ) {
					wp_parse_str( wp_unslash( $_POST['data'] ), $fields ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				} else {
					wp_parse_str( '', $fields );
				}

				$args            = wp_parse_args( $fields, self::get_element_defaults() );
				$fusion_settings = awb_get_fusion_settings();
				$secret_key      = $fusion_settings->get( "stripe_button_{$args['api_mode']}_secret_key" );

				if ( ! empty( $secret_key ) ) {
					$this->api->post_checkout_endpoint( $secret_key, $args );
				} else {
					$this->error_handler( 401, esc_html__( 'Secret Key is empty. Please enter a valid secret key', 'fusion-builder' ) );
				}
			}

			/**
			 * Error handler
			 *
			 * @since 3.9
			 *
			 * @param int    $status_code The status code.
			 * @param string $error_message The error message.
			 */
			protected function error_handler( $status_code, $error_message ) {
				$resp['response']['code'] = $status_code;
				$resp['body']             = wp_json_encode(
					[ 'error' => [ 'message' => $error_message ] ],
					JSON_PRETTY_PRINT
				);

				wp_send_json( $resp );
			}

			/**
			 * Get list of supported currencies.
			 *
			 * @access public
			 * @since 3.9
			 * @return array
			 */
			public function get_currencies() {
				return [
					'USD' => _x( 'USD', 'Currency', 'fusion-builder' ),
					'AED' => _x( 'AED', 'Currency', 'fusion-builder' ),
					'AFN' => _x( 'AFN', 'Currency', 'fusion-builder' ),
					'ALL' => _x( 'ALL', 'Currency', 'fusion-builder' ),
					'AMD' => _x( 'AMD', 'Currency', 'fusion-builder' ),
					'ANG' => _x( 'ANG', 'Currency', 'fusion-builder' ),
					'AOA' => _x( 'AOA', 'Currency', 'fusion-builder' ),
					'ARS' => _x( 'ARS', 'Currency', 'fusion-builder' ),
					'AUD' => _x( 'AUD', 'Currency', 'fusion-builder' ),
					'AWG' => _x( 'AWG', 'Currency', 'fusion-builder' ),
					'AZN' => _x( 'AZN', 'Currency', 'fusion-builder' ),
					'BAM' => _x( 'BAM', 'Currency', 'fusion-builder' ),
					'BBD' => _x( 'BBD', 'Currency', 'fusion-builder' ),
					'BDT' => _x( 'BDT', 'Currency', 'fusion-builder' ),
					'BGN' => _x( 'BGN', 'Currency', 'fusion-builder' ),
					'BIF' => _x( 'BIF', 'Currency', 'fusion-builder' ),
					'BMD' => _x( 'BMD', 'Currency', 'fusion-builder' ),
					'BND' => _x( 'BND', 'Currency', 'fusion-builder' ),
					'BOB' => _x( 'BOB', 'Currency', 'fusion-builder' ),
					'BRL' => _x( 'BRL', 'Currency', 'fusion-builder' ),
					'BSD' => _x( 'BSD', 'Currency', 'fusion-builder' ),
					'BWP' => _x( 'BWP', 'Currency', 'fusion-builder' ),
					'BYN' => _x( 'BYN', 'Currency', 'fusion-builder' ),
					'BZD' => _x( 'BZD', 'Currency', 'fusion-builder' ),
					'CAD' => _x( 'CAD', 'Currency', 'fusion-builder' ),
					'CDF' => _x( 'CDF', 'Currency', 'fusion-builder' ),
					'CHF' => _x( 'CHF', 'Currency', 'fusion-builder' ),
					'CLP' => _x( 'CLP', 'Currency', 'fusion-builder' ),
					'CNY' => _x( 'CNY', 'Currency', 'fusion-builder' ),
					'COP' => _x( 'COP', 'Currency', 'fusion-builder' ),
					'CRC' => _x( 'CRC', 'Currency', 'fusion-builder' ),
					'CVE' => _x( 'CVE', 'Currency', 'fusion-builder' ),
					'CZK' => _x( 'CZK', 'Currency', 'fusion-builder' ),
					'DJF' => _x( 'DJF', 'Currency', 'fusion-builder' ),
					'DKK' => _x( 'DKK', 'Currency', 'fusion-builder' ),
					'DOP' => _x( 'DOP', 'Currency', 'fusion-builder' ),
					'DZD' => _x( 'DZD', 'Currency', 'fusion-builder' ),
					'EGP' => _x( 'EGP', 'Currency', 'fusion-builder' ),
					'ETB' => _x( 'ETB', 'Currency', 'fusion-builder' ),
					'EUR' => _x( 'EUR', 'Currency', 'fusion-builder' ),
					'FJD' => _x( 'FJD', 'Currency', 'fusion-builder' ),
					'FKP' => _x( 'FKP', 'Currency', 'fusion-builder' ),
					'GBP' => _x( 'GBP', 'Currency', 'fusion-builder' ),
					'GEL' => _x( 'GEL', 'Currency', 'fusion-builder' ),
					'GIP' => _x( 'GIP', 'Currency', 'fusion-builder' ),
					'GMD' => _x( 'GMD', 'Currency', 'fusion-builder' ),
					'GNF' => _x( 'GNF', 'Currency', 'fusion-builder' ),
					'GTQ' => _x( 'GTQ', 'Currency', 'fusion-builder' ),
					'GYD' => _x( 'GYD', 'Currency', 'fusion-builder' ),
					'HKD' => _x( 'HKD', 'Currency', 'fusion-builder' ),
					'HNL' => _x( 'HNL', 'Currency', 'fusion-builder' ),
					'HRK' => _x( 'HRK', 'Currency', 'fusion-builder' ),
					'HTG' => _x( 'HTG', 'Currency', 'fusion-builder' ),
					'IDR' => _x( 'IDR', 'Currency', 'fusion-builder' ),
					'ILS' => _x( 'ILS', 'Currency', 'fusion-builder' ),
					'INR' => _x( 'INR', 'Currency', 'fusion-builder' ),
					'ISK' => _x( 'ISK', 'Currency', 'fusion-builder' ),
					'JMD' => _x( 'JMD', 'Currency', 'fusion-builder' ),
					'JPY' => _x( 'JPY', 'Currency', 'fusion-builder' ),
					'KES' => _x( 'KES', 'Currency', 'fusion-builder' ),
					'KGS' => _x( 'KGS', 'Currency', 'fusion-builder' ),
					'KHR' => _x( 'KHR', 'Currency', 'fusion-builder' ),
					'KMF' => _x( 'KMF', 'Currency', 'fusion-builder' ),
					'KRW' => _x( 'KRW', 'Currency', 'fusion-builder' ),
					'KYD' => _x( 'KYD', 'Currency', 'fusion-builder' ),
					'KZT' => _x( 'KZT', 'Currency', 'fusion-builder' ),
					'LAK' => _x( 'LAK', 'Currency', 'fusion-builder' ),
					'LBP' => _x( 'LBP', 'Currency', 'fusion-builder' ),
					'LKR' => _x( 'LKR', 'Currency', 'fusion-builder' ),
					'LRD' => _x( 'LRD', 'Currency', 'fusion-builder' ),
					'LSL' => _x( 'LSL', 'Currency', 'fusion-builder' ),
					'MAD' => _x( 'MAD', 'Currency', 'fusion-builder' ),
					'MDL' => _x( 'MDL', 'Currency', 'fusion-builder' ),
					'MGA' => _x( 'MGA', 'Currency', 'fusion-builder' ),
					'MKD' => _x( 'MKD', 'Currency', 'fusion-builder' ),
					'MMK' => _x( 'MMK', 'Currency', 'fusion-builder' ),
					'MNT' => _x( 'MNT', 'Currency', 'fusion-builder' ),
					'MOP' => _x( 'MOP', 'Currency', 'fusion-builder' ),
					'MRO' => _x( 'MRO', 'Currency', 'fusion-builder' ),
					'MUR' => _x( 'MUR', 'Currency', 'fusion-builder' ),
					'MVR' => _x( 'MVR', 'Currency', 'fusion-builder' ),
					'MWK' => _x( 'MWK', 'Currency', 'fusion-builder' ),
					'MXN' => _x( 'MXN', 'Currency', 'fusion-builder' ),
					'MYR' => _x( 'MYR', 'Currency', 'fusion-builder' ),
					'MZN' => _x( 'MZN', 'Currency', 'fusion-builder' ),
					'NAD' => _x( 'NAD', 'Currency', 'fusion-builder' ),
					'NGN' => _x( 'NGN', 'Currency', 'fusion-builder' ),
					'NIO' => _x( 'NIO', 'Currency', 'fusion-builder' ),
					'NOK' => _x( 'NOK', 'Currency', 'fusion-builder' ),
					'NPR' => _x( 'NPR', 'Currency', 'fusion-builder' ),
					'NZD' => _x( 'NZD', 'Currency', 'fusion-builder' ),
					'PAB' => _x( 'PAB', 'Currency', 'fusion-builder' ),
					'PEN' => _x( 'PEN', 'Currency', 'fusion-builder' ),
					'PGK' => _x( 'PGK', 'Currency', 'fusion-builder' ),
					'PHP' => _x( 'PHP', 'Currency', 'fusion-builder' ),
					'PKR' => _x( 'PKR', 'Currency', 'fusion-builder' ),
					'PLN' => _x( 'PLN', 'Currency', 'fusion-builder' ),
					'PYG' => _x( 'PYG', 'Currency', 'fusion-builder' ),
					'QAR' => _x( 'QAR', 'Currency', 'fusion-builder' ),
					'RON' => _x( 'RON', 'Currency', 'fusion-builder' ),
					'RSD' => _x( 'RSD', 'Currency', 'fusion-builder' ),
					'RUB' => _x( 'RUB', 'Currency', 'fusion-builder' ),
					'RWF' => _x( 'RWF', 'Currency', 'fusion-builder' ),
					'SAR' => _x( 'SAR', 'Currency', 'fusion-builder' ),
					'SBD' => _x( 'SBD', 'Currency', 'fusion-builder' ),
					'SCR' => _x( 'SCR', 'Currency', 'fusion-builder' ),
					'SEK' => _x( 'SEK', 'Currency', 'fusion-builder' ),
					'SGD' => _x( 'SGD', 'Currency', 'fusion-builder' ),
					'SHP' => _x( 'SHP', 'Currency', 'fusion-builder' ),
					'SLL' => _x( 'SLL', 'Currency', 'fusion-builder' ),
					'SOS' => _x( 'SOS', 'Currency', 'fusion-builder' ),
					'SRD' => _x( 'SRD', 'Currency', 'fusion-builder' ),
					'STD' => _x( 'STD', 'Currency', 'fusion-builder' ),
					'SZL' => _x( 'SZL', 'Currency', 'fusion-builder' ),
					'THB' => _x( 'THB', 'Currency', 'fusion-builder' ),
					'TJS' => _x( 'TJS', 'Currency', 'fusion-builder' ),
					'TOP' => _x( 'TOP', 'Currency', 'fusion-builder' ),
					'TRY' => _x( 'TRY', 'Currency', 'fusion-builder' ),
					'TTD' => _x( 'TTD', 'Currency', 'fusion-builder' ),
					'TWD' => _x( 'TWD', 'Currency', 'fusion-builder' ),
					'TZS' => _x( 'TZS', 'Currency', 'fusion-builder' ),
					'UAH' => _x( 'UAH', 'Currency', 'fusion-builder' ),
					'UYU' => _x( 'UYU', 'Currency', 'fusion-builder' ),
					'UZS' => _x( 'UZS', 'Currency', 'fusion-builder' ),
					'VND' => _x( 'VND', 'Currency', 'fusion-builder' ),
					'VUV' => _x( 'VUV', 'Currency', 'fusion-builder' ),
					'WST' => _x( 'WST', 'Currency', 'fusion-builder' ),
					'XAF' => _x( 'XAF', 'Currency', 'fusion-builder' ),
					'XCD' => _x( 'XCD', 'Currency', 'fusion-builder' ),
					'XOF' => _x( 'XOF', 'Currency', 'fusion-builder' ),
					'XPF' => _x( 'XPF', 'Currency', 'fusion-builder' ),
					'YER' => _x( 'YER', 'Currency', 'fusion-builder' ),
					'ZAR' => _x( 'ZAR', 'Currency', 'fusion-builder' ),
					'ZMW' => _x( 'ZMW', 'Currency', 'fusion-builder' ),
				];
			}

			/**
			 * Get tax rates.
			 *
			 * @access public
			 * @since 3.9
			 * @param string $mode The mode.
			 * @return array
			 */
			public function get_tax_rates( $mode ) {
				$fusion_settings = awb_get_fusion_settings();
				$options         = [
					'' => esc_html__( 'None', 'fusion-builder' ),
				];
				$secret_key      = $fusion_settings->get( "stripe_button_{$mode}_secret_key" );
				if ( ! empty( $secret_key ) ) {
					$rates   = $this->api->post_tax_rates_endpoint( $secret_key );
					$options = array_merge( $options, $rates );
				}
				return $options;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-stripe-button',
					FusionBuilder::$js_folder_url . '/general/fusion-stripe-button.js',
					FusionBuilder::$js_folder_path . '/general/fusion-stripe-button.js',
					[ 'jquery' ],
					FUSION_BUILDER_VERSION,
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-stripe-button',
					'fusionStripeButtonVars',
					[
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
						'nonce'            => wp_create_nonce( 'awb-stripe-button-nonce' ),
						'productEmptyText' => esc_html__( 'Please fill product name or price in Element Settings.', 'fusion-builder' ),
					]
				);
			}
		}
	}

	/**
	 * Instantiates the post cards class.
	 *
	 * @since 3.9
	 * @return object FusionSC_Stripe_Button
	 */
	function fusion_stripe_button() { // phpcs:ignore WordPress.NamingConventions
		return FusionSC_Stripe_Button::get_instance();
	}

	// Instantiate stripe button.
	fusion_stripe_button();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 3.9
 */
function fusion_element_stripe_button() {
	$editing             = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$fusion_settings     = awb_get_fusion_settings();
	$currencies          = [];
	$test_mode_tax_rates = [];
	$live_mode_tax_rates = [];

	if ( function_exists( 'fusion_stripe_button' ) && $editing ) {
		$currencies          = fusion_stripe_button()->get_currencies();
		$test_mode_tax_rates = fusion_stripe_button()->get_tax_rates( 'test' );
		$live_mode_tax_rates = fusion_stripe_button()->get_tax_rates( 'live' );
	}

	$standard_schemes = [
		'default' => esc_attr__( 'Default', 'fusion-builder' ),
		'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
	];

	$style_option = 'radio_button_set';
	if ( apply_filters( 'awb_load_button_presets', ( '1' === $fusion_settings->get( 'button_presets' ) ) ) ) {
		$style_option     = 'select';
		$standard_schemes = [
			'default'   => esc_attr__( 'Default', 'fusion-builder' ),
			'custom'    => esc_attr__( 'Custom', 'fusion-builder' ),
			'green'     => esc_attr__( 'Green', 'fusion-builder' ),
			'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
			'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
			'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
			'red'       => esc_attr__( 'Red', 'fusion-builder' ),
			'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
			'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
			'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
		];
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Stripe_Button',
			[
				'name'          => esc_attr__( 'Stripe Button', 'fusion-builder' ),
				'shortcode'     => 'fusion_stripe_button',
				'icon'          => 'fusiona-check-empty',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-button-preview.php',
				'preview_id'    => 'fusion-builder-block-module-button-preview-template',
				'help_url'      => 'https://avada.com/documentation/stripe-button-element/',
				'inline_editor' => true,
				'subparam_map'  => [
					'fusion_font_family_button_font'  => 'main_typography',
					'fusion_font_variant_button_font' => 'main_typography',
					'font_size'                       => 'main_typography',
					'line_height'                     => 'main_typography',
					'letter_spacing'                  => 'main_typography',
					'text_transform'                  => 'main_typography',
				],
				'params'        => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'API Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Build your integration in Test mode, and switch to Live mode when you are ready.', 'fusion-builder' ),
						'param_name'  => 'api_mode',
						'default'     => '',
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'live' => esc_attr__( 'Live', 'fusion-builder' ),
							'test' => esc_attr__( 'Test', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Collect Shipping Address', 'fusion-builder' ),
						'description' => esc_attr__( 'Collect customer shipping address.', 'fusion-builder' ),
						'param_name'  => 'shipping_address',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Product Name', 'fusion-builder' ),
						'param_name'   => 'product_name',
						'value'        => esc_attr__( 'My Product', 'fusion-builder' ),
						'group'        => esc_attr__( 'Payment', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the product name that you sell.', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Currency', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the currency.', 'fusion-builder' ),
						'param_name'  => 'currency',
						'default'     => 'USD',
						'value'       => $currencies,
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Product Price', 'fusion-builder' ),
						'param_name'   => 'product_price',
						'value'        => '3.99',
						'group'        => esc_attr__( 'Payment', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the product price. Decimal numbers are supported by using the "." (period) delimiter.', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Quantity', 'fusion-builder' ),
						'param_name'  => 'product_qty',
						'value'       => '1',
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the product quantity.', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Shipping Price', 'fusion-builder' ),
						'param_name'   => 'shipping_price',
						'value'        => '',
						'group'        => esc_attr__( 'Payment', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the shipping price. Decimal numbers are supported by using the "." (period) delimiter.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Tax Rate', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the tax rate. These options can be manage on Stripe dashboard > Products > Tax Rates.', 'fusion-builder' ),
						'param_name'  => 'test_tax_rate',
						'default'     => 'none',
						'value'       => $test_mode_tax_rates,
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'api_mode',
								'value'    => 'test',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Tax Rate', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the tax rate. These options can be manage on Stripe dashboard > Products > Tax Rates.', 'fusion-builder' ),
						'param_name'  => 'live_tax_rate',
						'default'     => 'none',
						'value'       => $live_mode_tax_rates,
						'group'       => esc_attr__( 'Payment', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'api_mode',
								'value'    => 'live',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Text', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Pay Now', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the text that will display on button.', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Target', 'fusion-builder' ),
						'description' => esc_attr__( '_self = open in same browser tab, _blank = open in new browser tab.', 'fusion-builder' ),
						'param_name'  => 'target',
						'default'     => '_self',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Payment Success URL', 'fusion-builder' ),
						'param_name'   => 'success_url',
						'value'        => '',
						'description'  => esc_attr__( 'The URL the customer will be directed to after the payment is successful. ex: http://example.com.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Payment Cancel URL', 'fusion-builder' ),
						'param_name'   => 'cancel_url',
						'value'        => '',
						'description'  => esc_attr__( 'The URL the customer will be directed to if they decide to cancel payment. ex: http://example.com.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => $style_option,
						'heading'     => esc_attr__( 'Button Style', 'fusion-builder' ),
						'description' => $fusion_settings->get( 'button_presets' ) ? esc_attr__( 'Select the button\'s color. Select default or specific color name to use Global Options presets, or select custom to use advanced color options below.', 'fusion-builder' ) : esc_attr__( 'Select the button\'s color. Select default to use Global Options values, or custom to use advanced color options below.', 'fusion-builder' ),
						'param_name'  => 'color',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => $standard_schemes,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Start Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the start color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient End Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the end color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Start Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the start hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient End Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the end hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_start_position',
						'default'     => $fusion_settings->get( 'button_gradient_start' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_end_position',
						'default'     => $fusion_settings->get( 'button_gradient_end' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
						'param_name'  => 'gradient_type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
							'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
						'param_name'  => 'radial_direction',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'linear',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
						'param_name'  => 'linear_angle',
						'default'     => $fusion_settings->get( 'button_gradient_angle' ),
						'value'       => '180',
						'min'         => '',
						'max'         => '360',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'radial',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Accent Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'flat' => esc_attr__( 'Flat', 'fusion-builder' ),
							'3d'   => esc_attr__( '3D', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Hover Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_width',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Custom', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding for the button.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'size',
								'value'    => '',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the button typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'button_font',
							'font-size'      => 'font_size',
							'line-height'    => 'line_height',
							'letter-spacing' => 'letter_spacing',
							'text-transform' => 'text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
						'param_name'  => 'stretch',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'      => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Divider', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider between icon and text.', 'fusion-builder' ),
						'param_name'  => 'icon_divider',
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-button',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					'fusion_sticky_visibility_placeholder' => [],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_stripe_button' );
