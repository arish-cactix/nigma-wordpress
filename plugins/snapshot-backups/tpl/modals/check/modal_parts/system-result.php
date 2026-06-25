<?php // phpcs:ignore
/**
 * Snapshot preflight system check result template
 *
 * @package snapshot
 */

$parsed_url    = wp_parse_url( get_site_url() );
$host_name     = $parsed_url['host'];
$max_execution = ini_get( 'max_execution_time' );

if ( in_array( 'memory_limit', $args, true ) ) :
	?>
	<div class="sui-accordion accordion-memory-limit">
		<div class="sui-accordion-item">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title sui-accordion-col-4"><span aria-hidden="true" class="sui-icon-warning-alert sui-warning"></span> <?php esc_html_e( 'Low Server Memory', 'snapshot' ); ?></div>
				<div class="sui-accordion-col-4">
					<button class="sui-button-icon sui-accordion-open-indicator" aria-label=<?php esc_attr__( 'Open item', 'snapshot' ); ?>>
						<span class="sui-icon-chevron-down" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<div class="seperator"></div>
				<div class="sui-box" tabindex="0">
					<div class="sui-box-body">
						<div class="sub-heading overview">
							<?php esc_html_e( 'Overview', 'snapshot' ); ?>
						</div>
						<p class="mb-30"><?php esc_html_e( 'Low server memory can cause your website to perform poorly and disrupt backup processes. Ensuring adequate memory is key to maintaining site reliability and speed.', 'snapshot' ); ?></p>
						<div class="snapshot-pf-status mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'Status', 'snapshot' ); ?>
							</div>
							<div class="sui-notice sui-notice-yellow sui-active" style="display: block;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
										<p>
										<?php
										/* translators: %s - site name */
										printf( esc_html__( 'The memory allocated to the %s server is less than the recommended 256MB limit, which may affect the performance of the backup processes.', 'snapshot' ), '<strong>' . esc_html( $host_name ) . '</strong>' );
										?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div class="snapshot-pf-fix mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'How to Fix', 'snapshot' ); ?>
							</div>
							<p>
								<?php
									/* translators: %s - wp_memory_limit */
									printf( esc_html__( 'You can set the %s of your site to any value above 128M by using any of the following methods:', 'snapshot' ), '<strong>wp_memory_limit</strong>' );
								?>
							</p>
							<ol>
								<li>
									<p>
										<?php
											esc_html_e( 'Go to cPanel > Files section > File manager menu or connect to your site via FTP :', 'snapshot' );
											echo '<br>';
											/* translators: %s - file name */
											printf( esc_html__( 'In the WordPress installation directory, select %s file > Edit', 'snapshot' ), '<strong>wp-config.php</strong>' );
											echo '<br>';
											esc_html_e( "Add the following code right before the /* That's all, stop editing! Happy blogging. */' line:", 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">define( 'WP_MEMORY_LIMIT', '256M' );</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'If you have access to the php.ini file, you can increase the memory limit by adding the following line of code or updating it (if it exists already) in your php.ini file.', 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">memory_limit = 256M</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'An alternative way is to add the following line to your .htaccess file. Make sure you backup your .htaccess file before you edit it.', 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">php_value memory_limit 256M</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'If none of the above works, you can ask your hosting support to increase the max execution time for you.', 'snapshot' );
										?>
									</p>
								</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
endif;
if ( in_array( 'max_execution_time', $args, true ) ) :
	?>
	<div class="sui-accordion accordion-execution-time">
		<div class="sui-accordion-item">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title sui-accordion-col-4"><span aria-hidden="true" class="sui-icon-warning-alert sui-warning"></span> <?php esc_html_e( 'Max Execution Time is low', 'snapshot' ); ?></div>
				<div class="sui-accordion-col-4">
					<div>
						<!-- number of large files or tables -->
					</div>
						<button class="sui-button-icon sui-accordion-open-indicator" aria-label=<?php esc_attr__( 'Open item', 'snapshot' ); ?>>
						<span class="sui-icon-chevron-down" aria-hidden="true"></span>
					</button>
				</div>
			</div>
			<div class="sui-accordion-item-body">
				<div class="seperator"></div>
				<div class="sui-box" tabindex="0">
					<div class="sui-box-body">
						<div class="sub-heading overview">
							<?php esc_html_e( 'Overview', 'snapshot' ); ?>
						</div>
						<p class="mb-30">
							<?php
							/* translators: %s - minimum max_execution_time */
								printf( esc_html__( 'Max execution time defines how long a PHP script can run before it returns an error. Snapshot will often require longer than the default setting, so we recommend increasing your Max Execution time to %s to ensure backups have the best chance of succeeding.', 'snapshot' ), '<strong>' . esc_html__( '60s or above', 'snapshot' ) . '</strong>' );
							?>
						</p>
						<div class="snapshot-pf-status mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'Status', 'snapshot' ); ?>
							</div>
							<div class="sui-notice sui-notice-yellow sui-active" style="display: block;">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
										<p>
										<?php
										/* translators: %1$s - host name, %2$s - actual limit */
										printf( esc_html__( 'Max execution time on %1$s is %2$s seconds.', 'snapshot' ), '<strong>' . esc_html( $host_name ) . '</strong>', esc_html( $max_execution ) );
										?>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div class="snapshot-pf-fix mb-30">
							<div class="sub-heading">
								<?php esc_html_e( 'How to Fix', 'snapshot' ); ?>
							</div>
							<p>
								<?php
									/* translators: %s - max_execution_time */
									printf( esc_html__( 'You can set the %s of your site to any value above 60s by using any of the following methods:', 'snapshot' ), '<strong>max_execution_time</strong>' );
								?>
							</p>
							<ol>
								<li>
									<p>
										<?php
											esc_html_e( ' Go to your cPanel > Select PHP Version, and click on the Switch to PHP Options link to see the default values of your PHP options. Update the value of max_execution_time to 60s, and click on Apply and then Save.', 'snapshot' );
										?>
									</p>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( ' Connect to your site via FTP, and add the following line to your .htaccess file. Make sure you backup your .htaccess file before you edit it.', 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">php_value max_execution_time 120</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'If you have access to the php.ini file, you can increase the execution time limit by adding the following line of code or updating it (if it exists already) in your php.ini file.', 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">max_execution_time = 120;</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'An alternative to editing the php.ini file is adding the following line of code in your wp-config.php file.', 'snapshot' );
										?>
									</p>
									<pre class="sui-code-snippet"><div class="fix-snippet">set_time_limit(120);</div>
									</pre>
								</li>
								<li>
									<p>
										<?php
											esc_html_e( 'If none of the above works, you can ask your hosting support to increase the max execution time for you.', 'snapshot' );
										?>
									</p>
								</li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
endif; ?>