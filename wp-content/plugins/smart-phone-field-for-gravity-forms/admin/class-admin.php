<?php
if (!defined('ABSPATH')) {
    exit;
}

class SPF_Admin_Menu {
    public function __construct() {
        add_filter('admin_footer_text', [$this, 'admin_footer'], 1, 2);
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

        add_action('admin_notices', [$this, 'review_request']);
        add_action('wp_ajax_plc_review_dismiss', [$this, 'review_dismiss']);
        add_action('wp_ajax_pcafe_offer_notice_dismiss', [$this, 'pcafe_offer_notice_dismiss']);
    }

    public function admin_scripts() {
        $current_screen = get_current_screen();
        if (strpos($current_screen->base, 'smart-phone-field-for-gravity-forms') === false) {
            return;
        }

        wp_enqueue_style('spf_admin_menu', GF_SMART_PHONE_FIELD_URL . 'assets/css/spf_admin.css', array(), GF_SMART_PHONE_FIELD_VERSION_NUM);
        wp_enqueue_script('spf_admin_menu', GF_SMART_PHONE_FIELD_URL . 'assets/js/spf_admin_script.js', array('jquery'), GF_SMART_PHONE_FIELD_VERSION_NUM, true);
    }

    public function add_menu() {
        add_submenu_page(
            'options-general.php',
            'Smart Phone Field Gravity Forms',
            'Smart Phone Field',
            'administrator',
            'smart-phone-field-for-gravity-forms-pro',
            [$this, 'spf_admin_page']
        );
    }

    public function spf_admin_page() {
        echo '<div class="pcafe_spf_dashboard">';
        include_once __DIR__ . '/template/header.php';

        echo '<div id="pcafe_tab_box" class="pcafe_container">';
        include_once __DIR__ . '/template/introduction.php';
        include_once __DIR__ . '/template/usage.php';
        include_once __DIR__ . '/template/help.php';
        include_once __DIR__ . '/template/pro.php';
        include_once __DIR__ . '/template/other-plugins.php';
        echo '</div>';
        echo '</div>';
    }

    public function admin_footer($text) {
        global $current_screen;

        if (! empty($current_screen->id) && strpos($current_screen->id, 'smart-phone-field-for-gravity-forms') !== false) {
            $url  = 'https://wordpress.org/support/plugin/smart-phone-field-for-gravity-forms/reviews/#new-post';
            $text = sprintf(
                wp_kses(
                    /* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
                    __('Thank you for using %1$s. Please rate us <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to boost our motivation.', 'smart-phone-field-for-gravity-forms'),
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

    public function review_request() {
        if (! is_super_admin()) {
            return;
        }

        $time = time();
        $load = false;

        $review = get_option('pcafe_spf_review_status');

        if (! $review) {
            $review_time = strtotime("+15 days", time());
            update_option('pcafe_spf_review_status', $review_time);
        } else {
            if (! empty($review) && $time > $review) {
                $load = true;
            }
        }
        if (! $load) {
            return;
        }

        $this->review();
    }

    public function review() {
        $current_user = wp_get_current_user();
        $nonce = wp_create_nonce('smart_phone_field_review_dismiss_nonce');
?>
        <div class="notice notice-info is-dismissible pcafe_gfspf_review_notice" data-nonce="<?php echo esc_attr($nonce); ?>">
            <p>
                <?php
                echo sprintf(
                    /* translators: 1: User display name, 2: Plugin name */
                    esc_html__(
                        'Hey %1$s 👋, I noticed you are using %2$s for a few days — that\'s Awesome! If you feel %2$s is helping your business to grow in any way, could you please do us a BIG favor and give it a 5-star rating on WordPress to boost our motivation?',
                        'smart-phone-field-for-gravity-forms'
                    ),
                    esc_html($current_user->display_name),
                    '<strong>Smart Phone Field For Gravity Forms</strong>'
                );
                ?>
            </p>

            <ul style="margin-bottom: 5px">
                <li style="display: inline-block">
                    <a style="padding: 5px 5px 5px 0; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/smart-phone-field-for-gravity-forms/reviews/#new-post') ?>">
                        <span class="dashicons dashicons-external"></span><?php esc_html_e(' Ok, you deserve it!', 'smart-phone-field-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="already_done" data-status="already">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('I already did', 'smart-phone-field-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="later" data-status="later">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Maybe Later', 'smart-phone-field-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" target="_blank" href="<?php echo esc_url('https://pluginscafe.com/support/') ?>">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e('I need help', 'smart-phone-field-for-gravity-forms') ?>
                    </a>
                </li>
                <li style="display: inline-block">
                    <a style="padding: 5px; text-decoration: none;" href="#" class="never" data-status="never">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Never show again', 'smart-phone-field-for-gravity-forms') ?>
                    </a>
                </li>
            </ul>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $(document).on('click', '.already_done, .later, .never, .notice-dismiss', function(event) {
                    event.preventDefault();

                    var $this = $(this);
                    var status = $this.attr('data-status');
                    var nonce = '';

                    if (status == 'already' || status == 'later' || status == 'never') {
                        nonce = $this.parent().parent().parent().attr('data-nonce');
                    }

                    var data = {
                        action: 'plc_review_dismiss',
                        status: status,
                        nonce: nonce
                    };

                    console.log(data);
                    $.ajax({
                        url: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
                        type: 'POST',
                        data: data,
                        success: function(data) {
                            console.log(data);
                            $('.pcafe_gfspf_review_notice').remove();
                        },
                        error: function(data) {}
                    });
                });
            });
        </script>
<?php
    }

    public function review_dismiss() {
        check_ajax_referer('smart_phone_field_review_dismiss_nonce', 'nonce');

        $status = '';
        if (isset($_POST['status'])) {
            $status = sanitize_text_field(wp_unslash($_POST['status']));
        }

        if ($status == 'already' || $status == 'never') {
            $next_try     = strtotime("+30 days", time());
            update_option('pcafe_spf_review_status', $next_try);
        } else if ($status == 'later') {
            $next_try     = strtotime("+10 days", time());
            update_option('pcafe_spf_review_status', $next_try);
        }
        wp_die();
    }
}


new SPF_Admin_Menu();
