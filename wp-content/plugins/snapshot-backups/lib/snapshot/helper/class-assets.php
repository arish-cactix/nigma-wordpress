<?php // phpcs:ignore
/**
 * Snapshot helpers: asset helper class
 *
 * Does asset-related work - resolving paths, resolving URLs, loading.
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Asset helper class
 */
class Assets {

	/**
	 * Gets the asset URL
	 *
	 * For script/style assets, attempts resolving best possible version,
	 * according to minimization state requests.
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string|bool Full asset URL on success, (bool)false on failure
	 */
	public function get_asset( $relpath ) {
		$relpath = 'assets/' . ltrim( $relpath, '/' );

		$relpath = $this->get_minified_asset_relpath( $relpath );

		return plugins_url( $relpath, SNAPSHOT_PLUGIN_FILE );
	}

	/**
	 * Gets relative path to the minified version of the asset - if applicable
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string Minified asset version, or regular asset version if not applicable
	 */
	public function get_minified_asset_relpath( $relpath ) {
		$type = $this->get_asset_type( $relpath );
		if ( 'js' !== $type && 'css' !== $type ) {
			return $relpath;
		} //end if

		return preg_replace(
			'/' . preg_quote( ".{$type}", '/' ) . '$/i',
			".min.{$type}",
			$relpath
		);
	}

	/**
	 * Gets asset type
	 *
	 * In this context, it actually means asset file extension, normalized.
	 *
	 * @param string $relpath Relative path to the asset.
	 *
	 * @return string
	 */
	public function get_asset_type( $relpath ) {
		return strtolower( pathinfo( $relpath, PATHINFO_EXTENSION ) );
	}

	/**
	 * Gets custom hero image link
	 *
	 * @return string
	 */
	public static function get_custom_hero_image() {
		return apply_filters( 'wpmudev_branding_hero_image', '' );
	}

	/**
	 * Returns the correct branding SUI class.
	 *
	 * @return string
	 */
	public static function get_sui_branding_class() {
		$sui_branding_class = '';
		if ( ! empty( Settings::is_branding_hidden() ) ) {
			$sui_branding_class = ' sui-unbranded';
			if ( ! empty( self::get_custom_hero_image() ) ) {
				$sui_branding_class = ' sui-rebranded';
			}
		}

		return $sui_branding_class;
	}
}