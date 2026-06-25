<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_facebook_page' ) ) {

	if ( ! class_exists( 'FusionSC_Facebook_Page' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Facebook_Page extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $fpp_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_facebook-page-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_facebook_page', [ $this, 'render' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'app_id'               => '',

					'href'                 => 'https://www.facebook.com/ThemeFusionAvada/',
					'width'                => '340',
					'height'               => '500',
					'tabs'                 => 'timeline',
					'header'               => 'large',
					'cover'                => 'show',
					'facepile'             => 'show',
					'cta'                  => 'show',
					'lazy'                 => 'off',
					'language'             => 'en_US',

					// margin.
					'margin_top'           => '',
					'margin_right'         => '',
					'margin_bottom'        => '',
					'margin_left'          => '',
					'margin_top_medium'    => '',
					'margin_right_medium'  => '',
					'margin_bottom_medium' => '',
					'margin_left_medium'   => '',
					'margin_top_small'     => '',
					'margin_right_small'   => '',
					'margin_bottom_small'  => '',
					'margin_left_small'    => '',

					'alignment'            => '',
					// css.
					'class'                => '',
					'id'                   => '',

					// animation.
					'animation_direction'  => 'left',
					'animation_offset'     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'      => '',
					'animation_delay'      => '',
					'animation_type'       => '',
					'animation_color'      => '',

					// visibility.
					'hide_on_mobile'       => fusion_builder_default_visibility( 'string' ),
				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_facebook_page' );
			}
			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->set_element_id( $this->fpp_counter );

				$this->set_args( $args );
				$language = $this->args['language'];

				$html           = '';
				$consent_needed = class_exists( 'Avada_Privacy_Embeds' ) && Avada()->settings->get( 'privacy_embeds' ) && ! Avada()->privacy_embeds->get_consent( 'facebook' );

				if ( $consent_needed ) {
					$html .= Avada()->privacy_embeds->script_placeholder( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput
					$html .= $this->facebook_privacy_script();
					$html .= '<div ' . FusionBuilder::attributes( 'facebook-page-shortcode' ) . '></div>';
				} else {
					$html .= '<div ' . FusionBuilder::attributes( 'facebook-page-shortcode' ) . '></div>';
					$html .= '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v14.0"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				}
				$html .= '<div id="fb-root"></div>';

				$this->fpp_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_facebook_page_plugin_content', $html, $args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [];

				$attr['id']    = $this->args['id'];
				$attr['class'] = 'fusion-facebook-page fb-page fusion-facebook-page-' . $this->fpp_counter . ' ' . $this->args['class'];
				$attr['style'] = '';

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['alignment'] ) {
					$attr['style'] .= 'display:flex; justify-content:' . $this->args['alignment'] . ';';
				}

				// fix animation.
				if ( $this->args['animation_type'] ) {
					$attr['style'] .= '--awb-iframe-visibility:unset;';
				}

				if ( '' !== $this->args['href'] ) {
					$attr['data-href'] = $this->args['href'];
				}
				if ( '' !== $this->args['tabs'] ) {
					$attr['data-tabs'] = $this->args['tabs'];
				}

				if ( '' !== $this->args['width'] ) {
					$attr['data-width'] = $this->args['width'];
				}

				if ( '' !== $this->args['height'] ) {
					$attr['data-height'] = $this->args['height'];
				}

				if ( 'small' === $this->args['header'] ) {
					$attr['data-small_header'] = 'true';
				}

				if ( 'hide' === $this->args['cover'] ) {
					$attr['data-hide_cover'] = 'true';
				}

				if ( 'hide' === $this->args['cta'] ) {
					$attr['data-hide_cta'] = 'true';
				}

				if ( 'on' === $this->args['lazy'] ) {
					$attr['data-lazy'] = 'true';
				}

				if ( 'hide' === $this->args['facepile'] ) {
					$attr['data-show_facepile'] = 'false';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= $this->get_style_variables();

				return $attr;
			}

			/**
			 * Facebook Script.
			 *
			 * @access public
			 * @since 3.7
			 * @return array
			 */
			public function facebook_privacy_script() {
				ob_start();
					$language = $this->args['language'];
				?>
					<span data-privacy-script="true" data-privacy-type="facebook" class="fusion-hidden">
						( function( d, s, id ) {
							var js,
								fjs = d.getElementsByTagName( s )[0];
							if ( d.getElementById( id ) ) {
								return;
							}
							js     = d.createElement( s );
							js.id  = id;
							js.src = "https://connect.facebook.net/<?php echo esc_attr( $language ); ?>/sdk.js#xfbml=1&version=v12.0";
							fjs.parentNode.insertBefore( js, fjs );
						}( document, 'script', 'facebook-jssdk' ) );
					</span>
				<?php
				return ob_get_clean();
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.9
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/facebook-page.min.css' );

				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-facebook-page-md',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/facebook-page-md.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
					];
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-facebook-page-sm',
						FUSION_BUILDER_PLUGIN_DIR . 'assets/css/media/facebook-page-sm.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
					];
				}
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.9
			 * @return string
			 */
			protected function get_style_variables() {

				$css_vars_options = [
					'margin_top'           => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_right'         => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_bottom'        => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_left'          => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_top_medium'    => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_right_medium'  => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_bottom_medium' => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_left_medium'   => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_top_small'     => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_right_small'   => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_bottom_small'  => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
					'margin_left_small'    => [
						'callback' => [ 'Fusion_Sanitize', 'get_value_with_unit' ],
					],
				];

				$styles = $this->get_css_vars_for_options( $css_vars_options );

				return $styles;
			}
		}
	}

	new FusionSC_Facebook_Page();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_facebook_page_element() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Facebook_Page',
			[
				'name'         => esc_attr__( 'Facebook Page', 'fusion-builder' ),
				'shortcode'    => 'fusion_facebook_page',
				'icon'         => 'fusiona-facebook-feed',
				'subparam_map' => [
					'margin_top'    => 'margin',
					'margin_right'  => 'margin',
					'margin_bottom' => 'margin',
					'margin_left'   => 'margin',
				],
				'params'       => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Page URL', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the URL of the Facebook Page you want to display.', 'fusion-builder' ),
						'param_name'  => 'href',
						'value'       => 'https://www.facebook.com/ThemeFusionAvada/',
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Dimensions', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the element width and height. In Pixels. Width must be set between 180px and 500px.', 'fusion-builder' ),
						'param_name'  => 'dimension',
						'value'       => [
							'width'  => '340',
							'height' => '500',
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Language', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the language the facebook page should be displayed in.', 'fusion-builder' ),
						'param_name'  => 'language',
						'default'     => 'en_US',
						'value'       => [
							'af_ZA' => 'Afrikaans',
							'gn_PY' => 'Guaraní',
							'ay_BO' => 'Aymara',
							'az_AZ' => 'Azeri',
							'id_ID' => 'Indonesian',
							'ms_MY' => 'Malay',
							'jv_ID' => 'Javanese',
							'bs_BA' => 'Bosnian',
							'ca_ES' => 'Catalan',
							'cs_CZ' => 'Czech',
							'ck_US' => 'Cherokee',
							'cy_GB' => 'Welsh',
							'da_DK' => 'Danish',
							'se_NO' => 'Northern Sámi',
							'de_DE' => 'German',
							'et_EE' => 'Estonian',
							'en_IN' => 'English (India)',
							'en_PI' => 'English (Pirate)',
							'en_GB' => 'English (UK)',
							'en_UD' => 'English (Upside Down)',
							'en_US' => 'English (US)',
							'es_LA' => 'Spanish',
							'es_CL' => 'Spanish (Chile)',
							'es_CO' => 'Spanish (Colombia)',
							'es_ES' => 'Spanish (Spain)',
							'es_MX' => 'Spanish (Mexico)',
							'es_VE' => 'Spanish (Venezuela)',
							'eo_EO' => 'Esperanto',
							'eu_ES' => 'Basque',
							'tl_PH' => 'Filipino',
							'fo_FO' => 'Faroese',
							'fr_FR' => 'French (France)',
							'fr_CA' => 'French (Canada)',
							'fy_NL' => 'Frisian',
							'ga_IE' => 'Irish',
							'gl_ES' => 'Galician',
							'ko_KR' => 'Korean',
							'hr_HR' => 'Croatian',
							'xh_ZA' => 'Xhosa',
							'zu_ZA' => 'Zulu',
							'is_IS' => 'Icelandic',
							'it_IT' => 'Italian',
							'ka_GE' => 'Georgian',
							'sw_KE' => 'Swahili',
							'tl_ST' => 'Klingon',
							'ku_TR' => 'Kurdish',
							'lv_LV' => 'Latvian',
							'fb_LT' => 'Leet Speak',
							'lt_LT' => 'Lithuanian',
							'li_NL' => 'Limburgish',
							'la_VA' => 'Latin',
							'hu_HU' => 'Hungarian',
							'mg_MG' => 'Malagasy',
							'mt_MT' => 'Maltese',
							'nl_NL' => 'Dutch',
							'nl_BE' => 'Dutch (België)',
							'ja_JP' => 'Japanese',
							'nb_NO' => 'Norwegian (bokmal)',
							'nn_NO' => 'Norwegian (nynorsk)',
							'uz_UZ' => 'Uzbek',
							'pl_PL' => 'Polish',
							'pt_BR' => 'Portuguese (Brazil)',
							'pt_PT' => 'Portuguese (Portugal)',
							'qu_PE' => 'Quechua',
							'ro_RO' => 'Romanian',
							'rm_CH' => 'Romansh',
							'ru_RU' => 'Russian',
							'sq_AL' => 'Albanian',
							'sk_SK' => 'Slovak',
							'sl_SI' => 'Slovenian',
							'so_SO' => 'Somali',
							'fi_FI' => 'Finnish',
							'sv_SE' => 'Swedish',
							'th_TH' => 'Thai',
							'vi_VN' => 'Vietnamese',
							'tr_TR' => 'Turkish',
							'zh_CN' => 'Simplified Chinese (China)',
							'zh_TW' => 'Traditional Chinese (Taiwan)',
							'zh_HK' => 'Traditional Chinese (Hong Kong)',
							'el_GR' => 'Greek',
							'gx_GR' => 'Classical Greek',
							'be_BY' => 'Belarusian',
							'bg_BG' => 'Bulgarian',
							'kk_KZ' => 'Kazakh',
							'mk_MK' => 'Macedonian',
							'mn_MN' => 'Mongolian',
							'sr_RS' => 'Serbian',
							'tt_RU' => 'Tatar',
							'tg_TJ' => 'Tajik',
							'uk_UA' => 'Ukrainian',
							'hy_AM' => 'Armenian',
							'yi_DE' => 'Yiddish',
							'he_IL' => 'Hebrew',
							'ur_PK' => 'Urdu',
							'ar_AR' => 'Arabic',
							'ps_AF' => 'Pashto',
							'fa_IR' => 'Persian',
							'sy_SY' => 'Syriac',
							'ne_NP' => 'Nepali',
							'mr_IN' => 'Marathi',
							'sa_IN' => 'Sanskrit',
							'hi_IN' => 'Hindi',
							'bn_IN' => 'Bengali',
							'pa_IN' => 'Punjabi',
							'gu_IN' => 'Gujarati',
							'ta_IN' => 'Tamil',
							'te_IN' => 'Telugu',
							'kn_IN' => 'Kannada',
							'ml_IN' => 'Malayalam',
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Tabs', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the Facebook tabs you wish to display.', 'fusion-builder' ),
						'param_name'  => 'tabs',
						'default'     => 'timeline',
						'value'       => [
							'timeline' => esc_attr__( 'Timeline', 'fusion-builder' ),
							'events'   => esc_attr__( 'Events', 'fusion-builder' ),
							'messages' => esc_attr__( 'Messages', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Header', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the header size.', 'fusion-builder' ),
						'param_name'  => 'header',
						'default'     => 'large',
						'value'       => [
							'large' => esc_attr__( 'Large', 'fusion-builder' ),
							'small' => esc_attr__( 'Small', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cover Photo', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the cover photo in the header.', 'fusion-builder' ),
						'param_name'  => 'cover',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Friends Photos', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide profile photos of friends that like the page.', 'fusion-builder' ),
						'param_name'  => 'facepile',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Call To Action', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the custom call to action button of the page.', 'fusion-builder' ),
						'param_name'  => 'cta',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Lazy Load', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable/Disable lazy loading.', 'fusion-builder' ),
						'param_name'  => 'lazy',
						'default'     => 'off',
						'value'       => [
							'on'  => esc_attr__( 'On', 'fusion-builder' ),
							'off' => esc_attr__( 'Off', 'fusion-builder' ),
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
						'responsive' => [
							'state' => 'large',
						],
						'callback'   => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the element should align inside the Column.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'flex-start',
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'grid_layout' => true,
						'back_icons'  => true,
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-facebook-page',
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
add_action( 'fusion_builder_before_init', 'fusion_facebook_page_element' );
