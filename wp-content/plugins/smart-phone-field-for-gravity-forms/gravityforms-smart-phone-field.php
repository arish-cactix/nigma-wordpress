<?php
/*
* Plugin Name: Smart phone field for Gravity Forms
* Plugin Url: https://pluginscafe.com/plugin/smart-phone-field-for-gravity-forms-pro
* Version: 2.1.4
* Description: This plugin adds countries flag with ip address on gravity form phone field
* Author: Pluginscafe
* Author URI: https://pluginscafe.com
* License: GPLv2 or later
* Text Domain: gravityforms
* Domain Path: /languages/
*/

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('GF_Int_Phone_AddOn_Bootstrap')) {
    return;
}

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


if (!defined('GF_SMART_PHONE_FIELD_VERSION_NUM'))
    define('GF_SMART_PHONE_FIELD_VERSION_NUM', '2.1.4');

if (!defined('GF_SMART_PHONE_FIELD_FILE'))
    define('GF_SMART_PHONE_FIELD_FILE', __FILE__);

if (!defined('GF_SMART_PHONE_FIELD_PATH'))
    define('GF_SMART_PHONE_FIELD_PATH', plugin_dir_path(__FILE__));

if (!defined('GF_SMART_PHONE_FIELD_URL'))
    define('GF_SMART_PHONE_FIELD_URL', plugin_dir_url(__FILE__));

if (!defined('GF_SMART_PHONE_FIELD_DEBUG_MODE'))
    define('GF_SMART_PHONE_FIELD_DEBUG_MODE', false);

// Translate direction
function spf_free_localization_setup() {
    load_plugin_textdomain('gravityforms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'spf_free_localization_setup');

class GF_smart_phone_field {

    function __construct() {

        if (is_admin()) {
            add_action('plugins_loaded', array($this, 'GF_admin_init'), 14);
        } else {
            add_action('plugins_loaded', array($this, 'frontend_init'), 14);
        }
    }


    /**
     * Init frontend
     */
    function frontend_init() {
        require_once(GF_SMART_PHONE_FIELD_PATH . 'frontend/class-frontend.php');
    }

    /**
     * Init admin side
     */
    function GF_admin_init() {
        require_once(GF_SMART_PHONE_FIELD_PATH . 'backend/class-menu.php');
        require_once(GF_SMART_PHONE_FIELD_PATH . 'backend/class-review.php');
        require_once(GF_SMART_PHONE_FIELD_PATH . 'backend/class-backend.php');
        require_once(GF_SMART_PHONE_FIELD_PATH . 'backend/class-helper.php');
    }
}

new GF_smart_phone_field();
