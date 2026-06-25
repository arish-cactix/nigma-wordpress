<?php
/**
 * WP Hide & Security Enhancer plugin integrations.
 *
 * @package snapshot
 * @since   4.3.5
 */

namespace WPMUDEV\Snapshot4\Integrations;

/**
 * Compatibility with WP Hide & Security Enhancer plugin.
 */
class Wphse {

	/**
	 * Map the modified path to actual path.
	 *
	 * @var array
	 */
	protected $map = array(
		'new_style_file_path'  => 'wp-content/themes/%s/style.css',
		'new_wp_comments_post' => 'wp-comments-post.php',
		'new_upload_path'      => 'wp-content/uploads',
		'new_plugin_path'      => 'wp-content/plugins',
		'new_content_path'     => 'wp-content',
		'new_include_path'     => 'wp-includes',
		'new_theme_child_path' => 'wp-content/themes/%s',
		'new_theme_path'       => 'wp-content/themes/%s',
	);

	/**
	 * WP Hide & Security Enhancer conditions
	 *
	 * @var array
	 */
	protected $conditions = array();

	/**
	 * List of files.
	 *
	 * @var array
	 */
	protected $files = array();

	/**
	 * WP Hide & Security Enhancer integration constructor.
	 */
	public function __construct() {
		if ( $this->is_active() ) {
			add_filter( 'snapshot4_zipstream_requested_files', array( $this, 'check_requested_files' ), 10, 1 );
		}
	}

	/**
	 * Checks for "WP Hide & Security Enhancer" plugin
	 *
	 * @return boolean
	 */
	public function is_active() {
		$state = false;

		if ( defined( 'WPH_PATH' ) && class_exists( 'WPH' ) ) {
			$wph_settings = get_option( 'wph_settings' );
			$modules      = ( $wph_settings && isset( $wph_settings['module_settings'] ) ) ? $wph_settings['module_settings'] : array();

			foreach ( array_keys( $this->map ) as $key ) {
				if ( isset( $modules[ $key ] ) && '' !== $modules[ $key ] ) {
					$state = true;
					break;
				}
			}
		}

		return $state;
	}

	/**
	 * Checks for rewrite rules on individual plugins.
	 *
	 * @param array $settings WordPress Hide & Security plugin settings.
	 *
	 * @return bool
	 */
	public function is_individual_plugins_renamed( $settings ) {
		$renamed = false;
		foreach ( $settings as $key => $value ) {
			if ( false !== strpos( "{$key}", 'new_plugin_path_' ) ) {
				$renamed = true;
				break;
			}
		}

		return $renamed;
	}

	/**
	 * Make necessary changes to plugins to include in the mapping list.
	 *
	 * Updates the "map" property of the class.
	 *
	 * @return void
	 */
	public function add_renamed_plugins_to_mapping_lists() {
		$active_plugins = get_option( 'active_plugins' );

		if ( $active_plugins ) {
			foreach ( $active_plugins as $active_plugin ) {
				if ( 'wp-hide-security-enhancer/wp-hide.php' === $active_plugin ) {
					continue;
				}

				$sanitized_title                = sanitize_title( $active_plugin );
				$plugin_namespace               = "new_plugin_path_{$sanitized_title}";
				$this->map[ $plugin_namespace ] = sprintf( 'wp-content/plugins/%s', dirname( $active_plugin ) );
			}
		}
	}

	/**
	 * Check the requested files.
	 *
	 * @param array $files List of files.
	 *
	 * @return array
	 */
	public function check_requested_files( $files ) {
		$this->files = $files;

		if ( empty( $this->files ) ) {
			return $files;
		}

		$mapped_lists = $this->get_mapped_plugin_settings();
		$files_list   = array();
		// Loop through the files and fix the path.
		foreach ( $this->files as $file ) {
			if ( in_array( strpos( $file, 'wp-admin' ), array( 0, 1 ), true ) ) {
				array_push( $files_list, $file );
				continue;
			}

			// Loop through the mapped lists to map the files to its correct location.
			foreach ( $mapped_lists as $key => $value ) {
				if ( ( ! empty( $value ) && in_array( strpos( "{$file}", "{$value}" ), array( 0, 1 ), true ) ) ) {
					$file = preg_replace( '/' . $value . '/', $this->map[ $key ], $file, 1 );
					if ( false !== strpos( "{$file}", '%s' ) ) {
						$replace = get_template();
						if ( 'new_theme_child_path' === $key ) {
							$replace = get_stylesheet();
						}

						$file = sprintf( $file, $replace );
					}
					break;
				}
			}
			array_push( $files_list, $file );
		}//end foreach
		return $files_list;
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array
	 */
	private function get_mapped_plugin_settings() {
		$settings = get_option( 'wph_settings' );
		// phpcs:ignore Universal.Operators.DisallowShortTernary.Found
		$modules = $settings['module_settings'] ? : array();

		/**
		 * Check if individual plugins are renamed.
		 */
		if ( $this->is_individual_plugins_renamed( $modules ) ) {
			$this->add_renamed_plugins_to_mapping_lists();
		}

		$lists = array();
		if ( ! empty( $modules ) ) {
			foreach ( array_keys( $this->map ) as $key ) {
				// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				if ( isset( $modules[ $key ] ) && in_array( $key, array_keys( $modules ) ) ) {
					$lists[ $key ] = $modules[ $key ];
				}
			}
		}
		return $lists;
	}
}