<?php // phpcs:ignore
/**
 * Footer for all Snaspshot pages.
 *
 * @package snapshot
 */

$this->render( 'modals/apply-config-region-change' );
$this->render( 'modals/configs/apply' );

$footer_nav_links = array(
	array(
		'href' => 'https://wpmudev.com/hub2/',
		'name' => __( 'The Hub', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/projects/category/plugins/',
		'name' => __( 'Plugins', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/roadmap/',
		'name' => __( 'Roadmap', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/hub2/support',
		'name' => __( 'Support', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/docs/',
		'name' => __( 'Docs', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/hub2/community/',
		'name' => __( 'Community', 'snapshot' ),
	),
	array(
		'href' => 'https://wpmudev.com/terms-of-service/',
		'name' => __( 'Terms of Service', 'snapshot' ),
	),
	array(
		'href' => 'https://incsub.com/privacy-policy/',
		'name' => __( 'Privacy Policy', 'snapshot' ),
	),
);

/* translators: %s - icon */
$footer_text = sprintf( __( 'Made with %s by WPMU DEV', 'snapshot' ), ' <span class="sui-icon-heart"></span>' );
$hide_footer = false;
$footer_text = apply_filters( 'wpmudev_branding_footer_text', $footer_text );
$hide_footer = apply_filters( 'wpmudev_branding_change_footer', $hide_footer );
?>
<div class="sui-footer"><?php echo wp_kses_post( $footer_text ); ?></div>

<?php if ( ! $hide_footer ) : ?>
	<ul class="sui-footer-nav">
		<?php foreach ( $footer_nav_links as $footer_nav_link ) : ?>
			<li><a href="<?php echo esc_url( $footer_nav_link['href'] ); ?>" target="_blank"><?php echo esc_html( $footer_nav_link['name'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<ul class="sui-footer-social">
		<li>
			<a href="https://www.facebook.com/wpmudev" target="_blank">
				<span class="sui-icon-social-facebook" aria-hidden="true"></span>
				<span class="sui-screen-reader-text">Facebook</span>
			</a>
		</li>
		<li>
			<a href="https://twitter.com/wpmudev" target="_blank">
				<span class="sui-icon-social-twitter" aria-hidden="true"></span>
				<span class="sui-screen-reader-text">Twitter</span>
			</a>
		</li>
		<li>
			<a href="https://www.instagram.com/wpmu_dev/" target="_blank">
				<span class="sui-icon-instagram" aria-hidden="true"></span>
				<span class="sui-screen-reader-text">Instagram</span>
			</a>
		</li>
	</ul>
<?php endif; ?>