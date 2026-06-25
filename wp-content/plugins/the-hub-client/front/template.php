<?php
$wpmudev_hub_assets        = WPMUDEV_HUB_Plugin_Front::get_assets();
$wpmudev_hub_customization = array(
	'app_logo'                        => esc_url( WPMUDEV_HUB_Plugin::get_customization_app_logo() ),
	'app_logo_urls'                   => WPMUDEV_HUB_Plugin::get_customization_app_logo( 'all' ),
	'app_name'                        => esc_html( WPMUDEV_HUB_Plugin::get_customization_app_name() ),
	'navigation_background_color'     => esc_attr( WPMUDEV_HUB_Plugin::get_customization( 'navigation_background_color', WPMUDEV_HUB_Plugin::DEFAULT_NAVIGATION_BACKGROUND_COLOR ) ),
	'navigation_text_color'           => esc_attr( WPMUDEV_HUB_Plugin::get_customization( 'navigation_text_color', WPMUDEV_HUB_Plugin::DEFAULT_NAVIGATION_TEXT_COLOR ) ),
	'navigation_selected_hover_color' => esc_attr( WPMUDEV_HUB_Plugin::get_customization( 'navigation_selected_hover_color', WPMUDEV_HUB_Plugin::DEFAULT_SELECTED_HOVER_COLOR ) ),
	'content_hyperlink_color'         => esc_attr( WPMUDEV_HUB_Plugin::get_customization( 'content_hyperlink_color', '' ) ),
	'navigation_menus'                => WPMUDEV_HUB_Plugin::get_extra_navigation_items(),
	'tos_url'                         => esc_url( WPMUDEV_HUB_Plugin::get_tos_url() ),
	'privacy_url'                     => esc_url( WPMUDEV_HUB_Plugin::get_privacy_url() ),
	'help_url'                        => esc_url( WPMUDEV_HUB_Plugin::get_customization( 'help_url', '' ) ),
	'custom_home_url'                 => esc_url( WPMUDEV_HUB_Plugin::get_customization( 'custom_home_url', '' ) ),
	'custom_home_title'               => esc_html( WPMUDEV_HUB_Plugin::get_customization( 'custom_home_title', '' ) ),
	'custom_home_url_is_new_tab'      => WPMUDEV_HUB_Plugin::get_customization( 'custom_home_url_is_new_tab', false ),
	'live_chats'                      => WPMUDEV_HUB_Plugin::get_customization( 'live_chats', array() ),
	'locale'                          => get_locale(),
	'default_language'                => WPMUDEV_HUB_Plugin::get_default_language(),
	'version'                         => WPMUDEV_HUB_Plugin::VERSION,
);

$wpmudev_hub_site_api_url  = WPMUDEV_HUB_Plugin::get_rest_url( WPMUDEV_HUB_Plugin::REST_API_SLUG_BASE );
$wpmudev_hub_site_api_urls = WPMUDEV_HUB_Plugin::get_rest_url(
	array(
		WPMUDEV_HUB_Plugin::REST_API_SLUG_BASE,
		WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_HOSTING_SETTINGS,
		WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_SETTINGS,
		WPMUDEV_HUB_Plugin::REST_API_SLUG_PUBLIC_RESELLER_DOMAIN_LOOKUP,
	)
);
?>
<!DOCTYPE html>
<html lang="">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( WPMUDEV_HUB_Plugin::get_customization_app_name( 'The Hub' ) ); ?></title>
	<script type="text/javascript">
		<?php // spacing stuff, phpcs is having trouble on mixed content php+js such as this ?>
		<?php // phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed ?>
		<?php // phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
		<?php // phpcs:disable Universal.WhiteSpace.PrecisionAlignment.Found ?>
        window.wpmudev_hub_public_path     =
          '<?php echo esc_url( WPMUDEV_HUB_Plugin::get_base_static_server() ); ?>hub2/build/'
        window.wpmudev_hub_public_src_path =
          '<?php echo esc_url( WPMUDEV_HUB_Plugin::get_base_static_server() ); ?>hub2/src/'
        window.wpmudev_hub_api_server      =
          '<?php echo esc_url( untrailingslashit( WPMUDEV_HUB_API_Request::get_instance()->get_base_api_server() ) ); ?>'
        window.wpmudev_hub_api_team_id     =
          '<?php echo esc_js( WPMUDEV_HUB_Plugin::get_team_id() ); ?>'
        window.wpmudev_hub_auth_method     = 'jwt'
        window.wpmudev_hub_router          = 'hash'
        window.wpmudev_hub_embed_url       =
          '<?php echo esc_js( WPMUDEV_HUB_Plugin_Front::get_embed_url() ); ?>'
        window.wpmudev_hub_site_url        =
          '<?php echo esc_js( site_url() ); ?>'
        window.wpmudev_hub_home_url        =
          '<?php echo esc_js( home_url() ); ?>'
        window.wpmudev_hub_is_embed        = true
        window.wpmudev_hub_site_name       =
          '<?php echo esc_js( WPMUDEV_HUB_Plugin_Front::get_site_name() ); ?>'
        window.wpmudev_hub_embed_site_id   = <?php echo esc_js( WPMUDEV_HUB_Plugin::get_hub_site_id() ); ?>;
        window.wpmudev_hub_customization   = <?php echo wp_json_encode( $wpmudev_hub_customization ); ?>;
        window.wpmudev_hub_site_api_url    = '<?php echo esc_url( $wpmudev_hub_site_api_url ); ?>'
        window.wpmudev_hub_site_api_urls   = <?php echo wp_json_encode( $wpmudev_hub_site_api_urls ); ?>;
		<?php // phpcs:enable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed ?>
		<?php // phpcs:enable WordPress.WhiteSpace.PrecisionAlignment.Found ?>
		<?php // phpcs:enable Universal.WhiteSpace.PrecisionAlignment.Found ?>
	</script>
	<?php // CSS here ?>
	<?php foreach ( $wpmudev_hub_assets['css'] as $wpmudev_hub_asset_id => $wpmudev_hub_asset_url ) : ?>
		<?php // intended to replace the whole WordPress page, skip wp-script/style framework. ?>
		<link href="<?php echo esc_url( $wpmudev_hub_asset_url ); ?>" rel="stylesheet"/><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<?php endforeach; ?>
	<?php wp_site_icon(); ?>
	<?php
	/**
	 * Fires before </head> close tag on HTML of Hub Client front page
	 *
	 * @since 1.0.0
	 */
	do_action( 'wpmudev_hub_template_head' );
	?>
</head>
<body>
<div id="wpmud-hub-wrapper" class="Hub"></div>
<?php // JS here ?>
<?php foreach ( $wpmudev_hub_assets['js'] as $wpmudev_hub_asset_id => $wpmudev_hub_asset_url ) : ?>
	<?php // intended to replace the whole WordPress page, skip wp-script/style framework. ?>
	<script type="text/javascript" src="<?php echo esc_url( $wpmudev_hub_asset_url ); ?>"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<?php endforeach; ?>
<?php
/**
 * Fires before </body> close tag on HTML of Hub Client front page
 *
 * @since 1.0.0
 */
do_action( 'wpmudev_hub_template_footer' );
?>
</body>
</html>