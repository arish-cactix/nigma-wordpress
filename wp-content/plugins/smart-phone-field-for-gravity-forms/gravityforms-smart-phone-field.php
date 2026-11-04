<?php
/*
* Plugin Name: Smart phone field for Gravity Forms
* Plugin Url: https://pluginscafe.com/plugin/smart-phone-field-for-gravity-forms-pro
* Version: 2.2.3
* Description: This plugin adds countries flag with ip address on gravity form phone field
* Author: PluginsCafe
* Author URI: https://pluginscafe.com
* License: GPLv2 or later
* Text Domain: smart-phone-field-for-gravity-forms
* Domain Path: /languages/
*/

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('spffgfp_fs')) {
    spffgfp_fs()->set_basename(false, __FILE__);
} else {
    if (!function_exists('spffgfp_fs')) {
        // Create a helper function for easy SDK access.
        function spffgfp_fs() {
            global  $spffgfp_fs;

            if (!isset($spffgfp_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/freemius/start.php';
                $spffgfp_fs = fs_dynamic_init(array(
                    'id'             => '10264',
                    'slug'           => 'smart-phone-field-for-gravity-forms-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_435e70ac913b8fea998deacb26d86',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'premium_suffix' => 'Pro',
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'    => 'smart-phone-field-for-gravity-forms-pro',
                        'support' => false,
                        'contact'   => false,
                        'parent'  => array(
                            'slug' => 'options-general.php',
                        ),
                    ),
                    'is_live'        => true,
                ));
            }

            return $spffgfp_fs;
        }

        // Init Freemius.
        spffgfp_fs();
        // Signal that SDK was initiated.
        do_action('spffgfp_fs_loaded');
    }
}

define('GF_SMART_PHONE_FIELD_VERSION_NUM', '2.2.3');
define('GF_SMART_PHONE_FIELD_FILE', __FILE__);
define('GF_SMART_PHONE_FIELD_PATH', plugin_dir_path(__FILE__));
define('GF_SMART_PHONE_FIELD_URL', plugin_dir_url(__FILE__));

if (is_admin()) {
    require_once 'admin/class-admin.php';
}

add_action('gform_loaded', array('GF_SMART_PHONE_FIELD_FREE_Bootstrap', 'load'), 5);
class GF_SMART_PHONE_FIELD_FREE_Bootstrap {
    public static function load() {
        if (!method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        require_once 'class-helper.php';
        require_once 'class-spf-free.php';

        GFAddOn::register('GFSPFFreeAddOn');
    }
}
function GF_smart_phone_free_field() {
    return GFSPFFreeAddOn::get_instance();
}
