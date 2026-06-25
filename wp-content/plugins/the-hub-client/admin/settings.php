<?php
require_once plugin_dir_path( __FILE__ ) . 'abstract.php';

class WPMUDEV_HUB_Plugin_Admin_Module_Settings extends WPMUDEV_HUB_Plugin_Admin_Module_Abstract {
	protected $is_main_menu = false;
	protected $menu_slug    = 'wpmudev-hub-settings';

	public static function get_menu_title() {
		return esc_html__( 'Settings', 'thc' );
	}

	public static function get_menu_title_slug() {
		return sanitize_title( self::get_menu_title() );
	}

	public static function get_admin_menu_hook() {
		return WPMUDEV_HUB_Plugin::is_multisite() ? 'network_admin_menu' : 'admin_menu';
	}

	protected function menu_cap() {
		return WPMUDEV_HUB_Plugin::get_manage_plugin_cap();
	}

	protected function process_request() {
	}
}

new WPMUDEV_HUB_Plugin_Admin_Module_Settings();