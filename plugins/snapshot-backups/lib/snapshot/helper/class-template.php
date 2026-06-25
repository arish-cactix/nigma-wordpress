<?php // phpcs:ignore
/**
 * Snapshot templating: Template helper class
 *
 * @package snapshot
 */

namespace WPMUDEV\Snapshot4\Helper;

/**
 * Template class
 */
class Template {

	/**
	 * Resolves relative template path to an actual absolute path
	 *
	 * @param string $relpath Relative template path.
	 *
	 * @return string
	 */
	public function get_template_path( $relpath ) {
		$root = realpath( trailingslashit( dirname( SNAPSHOT_PLUGIN_FILE ) ) . 'tpl' ) . DIRECTORY_SEPARATOR;
		$path = realpath( "{$root}{$relpath}.php" );

		return $path && preg_match( '/' . preg_quote( $root, '/' ) . '/', $path )
			? $path
			: '';
	}

	/**
	 * Renders the template with supplied arguments
	 *
	 * @param string $relpath Relative template path.
	 * @param array  $args Optional arguments.
	 *
	 * @return bool
	 */
	public function render( $relpath, $args = array() ) {
		$template = $this->get_template_path( $relpath );
		if ( empty( $template ) ) {
			return false;
		}

		if ( ! empty( $args ) ) {
			extract( $args, EXTR_PREFIX_SAME, 'view_' ); // @phpcs:ignore
		}
		include $template;
		return true;
	}
}