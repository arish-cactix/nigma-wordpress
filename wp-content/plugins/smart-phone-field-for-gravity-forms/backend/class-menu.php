<?php

if (!defined('ABSPATH')) {
    exit;
}


class GF_smart_phone_field_menu {
    function __construct() {
        add_action('admin_menu', array($this, 'gfip_admin_menu'));
        add_action('admin_notices', array($this, 'spf_admin_notice'));
        add_action('admin_enqueue_scripts', array($this, 'gfip_admin_scripts'));
        add_action('wp_ajax_spf-notice-dismiss', array($this, 'spf_ajax_fn_dismiss_notice'));
        add_action("admin_footer", array($this, 'spf_footer_script'));
        add_filter('admin_footer_text', [$this, 'admin_footer'], 1, 2);
    }

    function gfip_admin_scripts() {
        $current_screen = get_current_screen();
        if (strpos($current_screen->base, 'smart-phone-field-for-gravity-forms-pro') === false) {
            return;
        }
        wp_enqueue_style('gfip_doc_admin_css', GF_SMART_PHONE_FIELD_URL . 'backend/css/spf_doc_style.css', array(), GF_SMART_PHONE_FIELD_VERSION_NUM);
        wp_enqueue_script('gfip_doc_admin_js', GF_SMART_PHONE_FIELD_URL . 'backend/js/spf_doc_admin.js', array('jquery'), GF_SMART_PHONE_FIELD_VERSION_NUM, true);
    }

    function gfip_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Smart Phone Field Gravity Forms',
            'Smart Phone Field',
            'administrator',
            'smart-phone-field-for-gravity-forms-pro',
            [$this, 'spfield_settings_page']
        );
    }

    function spfield_settings_page() {

        $featureData = array(
            array(
                'feature'   => 'Live Validation',
                'free'      =>  'yes',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   => 'Multi step support',
                'free'      =>  'yes',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Multiple phone field',
                'free'      =>  'yes',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Add country/dial code in notification/entries',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Prevent submit form with wrong validation',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Multiple flag option',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Gravity Perks nested form support',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Custom placeholder',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Select country of address field with user IP address',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Format phone number when typing',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Get country code, name, dial code separately in text field',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
            array(
                'feature'   =>  'Get city, postal, country and more based on IP address via merge tag.',
                'free'      =>  'no',
                'pro'       =>  'yes'
            ),
        );
?>
        <div class="pc_container">
            <div class="pc_intro">
                <img src="<?php echo GF_SMART_PHONE_FIELD_URL; ?>backend/img/phone-logo.png" alt="Smart phone field">
                <div class="pc_pluginDesc">
                    <h2>Smart phone field for Gravity Forms</h2>
                    <p>A simple and nice plugin to get auto country flag from user ip address on gravity form phone field.</p>
                </div>
            </div>

            <div class="pc_pluginDetails">
                <div class="tabs">
                    <ul class="tabs-nav">
                        <li><a href="#tab-1">Features</a></li>
                        <li><a href="#tab-2">Documentation</a></li>
                        <li><a href="#tab-3">More plugin</a></li>
                    </ul>
                    <div class="tabs-stage">
                        <div id="tab-1" class="pc_tab">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Features</th>
                                        <th>Free</th>
                                        <th>Pro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($featureData as $feature) : ?>
                                        <tr>
                                            <td><?php echo $feature['feature']; ?></td>
                                            <td><img src="<?php echo GF_SMART_PHONE_FIELD_URL; ?>backend/img/<?php echo $feature['free'] ?>.svg" /></td>
                                            <td><img src="<?php echo GF_SMART_PHONE_FIELD_URL; ?>backend/img/<?php echo $feature['pro'] ?>.svg" /></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="tab-2" class="pc_tab pc_installation">
                            <div class="pc_installation_wrap">
                                <div class="pc_installation_cont">
                                    <p>For Details: </p>
                                    <a href="https://pluginscafe.com/docs/smart-phone-field-for-gravity-forms-free/" target="_blank" class="pc_btn">Read Free Plugin Documentation</a>
                                    <a href="https://pluginscafe.com/docs/smart-phone-field-for-gravity-forms-pro/" target="_blank" class="pc_btn">Read Pro Plugin Documentation</a>
                                </div>
                            </div>
                        </div>
                        <div id="tab-3" class="pc_tab pc_plugin_tab">
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/adress-auto-complete.png" alt="Adress Auto Complete">
                                <h3>Address Autocomplete via Google for Gravity Forms</h3>
                                <a href="https://wordpress.org/plugins/gf-google-address-autocomplete/" target="_blank">Try Now</a>
                            </div>
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/adress-auto-complete.png" alt="Adress Auto Complete">
                                <h3>Image Choices For Gravity Forms</h3>
                                <a href="https://wordpress.org/plugins/image-choices-for-gravity-forms/" target="_blank">Try Now</a>
                            </div>
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/restrict-dates.png" alt="Restrict Dates">
                                <h3>Restrict Dates Add-On for Gravity Forms</h3>
                                <a href="https://wordpress.org/plugins/restrict-dates-add-on-for-gravity-forms/" target="_blank">Try Now</a>
                            </div>
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/restrict-dates.png" alt="Restrict Dates">
                                <h3>Range Slider Add-On for Gravity Forms</h3>
                                <a href="https://wordpress.org/plugins/range-slider-addon-for-gravity-forms/" target="_blank">Try Now</a>
                            </div>
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/restrict-dates.png" alt="Restrict Dates">
                                <h3>Real time validation for gravity forms</h3>
                                <a href="https://wordpress.org/plugins/gf-real-time-validation/" target="_blank">Try Now</a>
                            </div>
                            <div class="pc_morePlugin">
                                <img src="<?php
                                            echo GF_SMART_PHONE_FIELD_URL;
                                            ?>backend/img/restrict-dates.png" alt="Restrict Dates">
                                <h3>Pdf Invoices for gravity forms</h3>
                                <a href="https://wordpress.org/plugins/pdf-invoices-for-gravity-forms/" target="_blank">Try Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    function spf_is_active_gravityforms() {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return false;
        }
        return true;
    }

    function spf_admin_notice() {

        $show = false;
        if (spffgfp_fs()->is_not_paying()) {
            $show = true;
        }

        if (isset($_GET['show_notices'])) {
            delete_transient('spf-notice');
            $show = true;
        }

        if (! $this->spf_is_active_gravityforms()) { ?>
            <div id="spf-notice-error" class="spf-notice-error notice notice-error">
                <div class="notice-container" style="padding:10px">
                    <span> <?php _e("Smart phone field needs to active gravity forms.", "gravityforms"); ?></span>
                </div>
            </div>
            <?php
        } else {
            if ($show && false == get_transient('spf-notice') && current_user_can('install_plugins')) {
            ?>

                <div id="spf-notice" class="spf-notice notice is-dismissible">
                    <div class="notice_container">
                        <div class="notice_wrap">
                            <div class="spf_img">
                                <img src="<?php echo GF_SMART_PHONE_FIELD_URL; ?>backend/img/phone-logo.png" class="spf_logo" alt="smart-phone-field-gravity-forms">
                            </div>
                            <div class="notice-content">
                                <div class="notice-heading">
                                    <?php _e("Hi there, Thanks for using Smart Phone Field for Gravity Forms", "gravityforms"); ?>
                                </div>
                                <?php _e("Did you know our PRO version includes the ability to use prevent submission with wrong validation and more features? Check it out!", "gravityforms"); ?>
                                <div class="spf-review-notice-container">
                                    <a href="https://pluginscafe.com/demo/smart-phone-field-for-gravity-forms/" class="spf-notice-close spf-review-notice button-primary" target="_blank">
                                        <?php _e("See The Demo", "gravityforms"); ?>
                                    </a>
                                    <span class="dashicons dashicons-smiley"></span>
                                    <a href="#" class="spf-notice-close notice-dis spf-review-notice">
                                        <?php _e("Dismiss", "gravityforms"); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="spf_upgrade_btn">
                            <a href="<?php echo spffgfp_fs()->get_upgrade_url(); ?>">
                                <?php _e('Upgrade Now!', 'gravityforms'); ?>
                            </a>
                        </div>
                    </div>
                    <style>
                        .notice_container {
                            display: flex;
                            align-items: center;
                            padding: 10px 0;
                            gap: 15px;
                            justify-content: space-between;
                        }

                        img.spf_logo {
                            max-width: 90px;
                        }

                        .notice-heading {
                            font-size: 16px;
                            font-weight: 500;
                            margin-bottom: 5px;
                        }

                        .spf-review-notice-container {
                            margin-top: 11px;
                            display: flex;
                            align-items: center;
                        }

                        .spf-notice-close {
                            padding-left: 5px;
                        }

                        span.dashicons.dashicons-smiley {
                            padding-left: 15px;
                        }

                        .notice_wrap {
                            display: flex;
                            align-items: center;
                            gap: 15px;
                        }

                        .spf_upgrade_btn a {
                            text-decoration: none;
                            font-size: 15px;
                            background: #7BBD02;
                            color: #fff;
                            display: inline-block;
                            padding: 10px 20px;
                            border-radius: 3px;
                            transition: 0.3s;
                        }

                        .spf_upgrade_btn a:hover {
                            background: #69a103;
                        }
                    </style>
                </div>

        <?php
            }
        }
    }

    function spf_ajax_fn_dismiss_notice() {
        $notice_id = (isset($_POST['notice_id']) ? sanitize_key($_POST['notice_id']) : '');
        $repeat_notice_after = 60 * 60 * 24 * 7;
        if (!empty($notice_id)) {
            if (!empty($repeat_notice_after)) {
                set_transient($notice_id, true, $repeat_notice_after);
                wp_send_json_success();
            }
        }
    }

    function spf_footer_script() {
        ?>

        <script type="text/javascript">
            var $ = jQuery;
            var admin_url_zzd = '<?php echo  admin_url("admin-ajax.php"); ?>';
            jQuery(document).on("click", '#spf-notice .notice-dis', function() {
                $(this).parents('#spf-notice').find('.notice-dismiss').click();
            });
            jQuery(document).on("click", '#spf-notice .notice-dismiss', function() {

                var notice_id = $(this).parents('#spf-notice').attr('id') || '';
                jQuery.ajax({
                    url: admin_url_zzd,
                    type: 'POST',
                    data: {
                        action: 'spf-notice-dismiss',
                        notice_id: notice_id,
                    },
                });
            });
        </script>

<?php
    }

    public function admin_footer($text) {
        global $current_screen;

        if (! empty($current_screen->id) && strpos($current_screen->id, 'smart-phone-field-for-gravity-forms-pro') !== false) {
            $url  = 'https://wordpress.org/support/plugin/smart-phone-field-for-gravity-forms/reviews/?filter=5#new-post';
            $text = sprintf(
                wp_kses(
                    /* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
                    __('Thank you for using %1$s. Please rate us <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to boost our motivation.', 'gravityforms'),
                    array(
                        'a' => array(
                            'href'   => array(),
                            'target' => array(),
                            'rel'    => array(),
                        ),
                    )
                ),
                '<strong>Smart Phone Field For Gravity Forms</strong>',
                $url,
                $url
            );
        }

        return $text;
    }
}

new GF_smart_phone_field_menu();
