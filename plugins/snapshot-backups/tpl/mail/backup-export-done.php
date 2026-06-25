<?php // phpcs:ignore
/**
 * Backup export mail template.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();

$restore_guide_url = 'https://wpmudev.com/docs/hub-2-0/backup/?utm_source=snapshot&utm_medium=email&utm_campaign=snapshot-documentation#restore-website-snapshot';

$is_branding_hidden = \WPMUDEV\Snapshot4\Helper\Settings::get_branding_hide_doc_link();
//phpcs:disable
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <title><?php echo esc_html( $subject ); ?></title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
    #outlook a {
        padding: 0;
    }

    body {
        margin: 0;
        padding: 0;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }

    table,
    td {
        border-collapse: collapse;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
    }

    img {
        border: 0;
        height: auto;
        line-height: 100%;
        outline: none;
        text-decoration: none;
        -ms-interpolation-mode: bicubic;
    }

    p {
        display: block;
        margin: 13px 0;
    }
    </style>
    <!--[if mso]>
		<xml>
		<o:OfficeDocumentSettings>
			<o:AllowPNG/>
			<o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
		</xml>
		<![endif]-->
    <!--[if lte mso 11]>
		<style type="text/css">
			.mj-outlook-group-fix { width:100% !important; }
		</style>
		<![endif]-->
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap" rel="stylesheet"
        type="text/css">
    <style type="text/css">
    @import url(https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap);
    </style>
    <!--<![endif]-->
    <style type="text/css">
    @media only screen and (min-width:480px) {
        .mj-column-per-100 {
            width: 100% !important;
            max-width: 100%;
        }
    }
    </style>
    <style type="text/css">
    @media only screen and (max-width:480px) {
        table.mj-full-width-mobile {
            width: 100% !important;
        }

        td.mj-full-width-mobile {
            width: auto !important;
        }
    }
    </style>
    <style type="text/css">
    * {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .p-30 {
        margin-bottom: 30px !important;
    }

    h1 {
        font-size: 25px;
        line-height: 35px;
    }

    h2 {
        font-size: 20px;
        line-height: 30px;
    }

    ol li {
        font-weight: bold;
    }

    p,
    li {
        font-size: 14px;
        line-height: 30px;
        font-weight: normal;
    }

    a {
        text-decoration: none !important;
        font-weight: 700 !important;
        color: #0059FF !important;
    }

    .hidden-img img {
        display: none !important;
    }

    .button a,
    a.button,
    a.button-cta {
        font-family: Roboto, arial, sans-serif;
        font-size: 13px !important;
        line-height: 24px;
        font-weight: bold;
        background: #0059FF;
        text-decoration: none !important;
        padding: 8px 21px;
        color: #ffffff !important;
        border-radius: 6px;
        display: inline-block;
        margin: 20px auto;
        text-transform: unset !important;
        min-width: unset !important;
    }

    small {
        font-size: 10px;
        line-height: 24px;
    }

    .main-content img {
        max-width: 100% !important;
    }

    @media (min-width: 600px) {

        p,
        li {
            font-size: 16px;
        }
    }
    </style>
</head>
<?php //phpcs:enable ?>

<body style="word-spacing:normal;background-color:#F6F6F6;">
	<div style="background-color:#F6F6F6;">
		<!-- Header image -->
		<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
		<div style="margin:0px auto;max-width:600px;">
			<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
					<tr>
						<td style="direction:ltr;font-size:0px;padding:25px 0 0;text-align:center;">
							<?php if ( ! $is_branding_hidden ) { ?>
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
							<div class="mj-column-per-100 mj-outlook-group-fix"
								style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
									<tbody>
										<tr>
											<td
												style="background-color:#35104C;border-radius:15px 15px 0 0;vertical-align:top;padding:35px 0;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation"
													style="" width="100%">
													<tbody>
														<tr>
															<td align="center"
																style="font-size:0px;padding:2px 25px;word-break:break-word;">
																<table border="0" cellpadding="0" cellspacing="0"
																	role="presentation"
																	style="border-collapse:collapse;border-spacing:0px;">
																	<tbody>
																		<tr>
																			<td style="width:159px;">
																				<img height="auto"
																					src="<?php echo esc_attr( $assets->get_asset( 'img/mail-snapshot-backup-logo.png' ) ); ?>"
																					style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;"
																					width="159" />
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
								<?php
							}//end if
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!--[if mso | IE]></td></tr></table><![endif]-->
		<!-- END Header image -->
		<!-- Main content -->
		<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="main-content-outlook" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
		<div class="main-content" style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;
	<?php
	if ( $is_branding_hidden ) {
		?>
		border-radius:15px;<?php } ?>">
			<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;
		<?php
		if ( $is_branding_hidden ) {
			?>
			border-radius:15px;<?php } ?>">
				<tbody>
					<tr>
						<td style="direction:ltr;font-size:0px;padding:30px 25px 15px;text-align:center;">
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:550px;" ><![endif]-->
							<div class="mj-column-per-100 mj-outlook-group-fix"
								style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation"
									style="vertical-align:top;" width="100%">
									<tbody>
										<tr>
											<td align="left" style="font-size:0px;padding:0;word-break:break-word;">
												<div
													style="font-family:Roboto, Arial, sans-serif;font-size:18px;letter-spacing:-.25px;line-height:30px;text-align:left;color:#1A1A1A;">
													<?php /* translators: %1$s - Site URL, %2$s - Site domain */ ?>
													<h1><?php echo wp_kses_post( sprintf( __( 'Backup of <a href="%1$s" target="_blank">%2$s</a> is ready for download.', 'snapshot' ), $site_url, $site ) ); ?>
													</h1>
													<?php /* translators: %s - User name */ ?>
													<p><?php echo esc_html( sprintf( __( 'Hi %s,', 'snapshot' ), $name ) ); ?>
													</p>
													<?php /* translators: %s - Backup URL */ ?>
													<p><?php echo wp_kses_post( sprintf( __( 'Your exported <a href="%s" target="_blank">backup</a> can be downloaded using the link below. This link will expire in 7 days, but don\'t worry, your backup will be kept for 50 days from the date it was created.', 'snapshot' ), $backup_url ) ); ?>
													</p>

													<?php if ( ! empty( $snapshot_name ) && ! empty( $export_date ) ) : ?>
													<table role="presentation" cellpadding="0" cellspacing="0"
														style="border: 1px solid #f2f2f2; width: 100%; font-size: 15px; margin: 23px 0;">
														<thead>
															<tr
																style="height: 28px; background: #f2f2f2; border-radius: 4px 4px 0px 0px; padding: 7px 20px;">
																<th
																	style="width: 60%; padding: 3px 20px; font-weight: 500;">
																	<?php echo esc_html( __( 'Backup title', 'snapshot' ) ); ?>
																</th>
																<th
																	style="width: 40%; padding: 3px 20px; font-weight: 500;">
																	<?php echo esc_html( __( 'Date Created', 'snapshot' ) ); ?>
																</th>
															</tr>
														</thead>
														<tbody>
															<tr style="height: 56px;">
																<td style="padding: 18px 0 20px 20px;"><strong
																		style="font-weight: 500"><?php echo esc_html( $snapshot_name ); ?></strong>
																</td>
																<td style="padding: 18px 0 20px 20px;">
																	<?php echo esc_html( $export_date ); ?></td>
															</tr>
														</tbody>
													</table>
													<?php endif; ?>

													<center><a class="button"
															href="<?php echo esc_attr( $export_link ); ?>"><?php esc_html_e( 'Download Backup', 'snapshot' ); ?></a>
													</center>

													<p><?php echo wp_kses_post( __( 'You can also use the link below to download the backup:', 'snapshot' ) ); ?>
													</p>
													<a
														href="<?php echo esc_attr( $export_link ); ?>"><?php echo esc_html( $export_link ); ?></a>
													<h1><?php esc_html_e( 'How to restore your site:', 'snapshot' ); ?>
													</h1>
													<ol>
														<li>
															<p><?php esc_html_e( 'Download the backup .zip file using the link above.', 'snapshot' ); ?>
															</p>
														</li>
														<li>
															<p>
																<?php
																/* translators: %s - Snapshot Installer URL */
																echo wp_kses_post( sprintf( __( 'Download the <a href="%s" target="_blank">snapshot-installer.php</a> file.', 'snapshot' ), esc_attr( $snapshot_installer_url ) ) );
																?>
															</p>
														</li>
														<li>
															<p><?php echo wp_kses_post( __( 'Upload both the backup <strong>.zip</strong> file and the <strong>snapshot-installer.php</strong> file to the root directory of the site to which you’d like to restore the backup.', 'snapshot' ) ); ?>
															</p>
														</li>
														<?php $snapshot_installer_path = $site_url . '/snapshot-installer.php'; ?>
														<li>
															<p>
																<?php
																/* translators: %1$s - Snapshot Installer path, %2$s - Snapshot Installer path */
																echo wp_kses_post( sprintf( __( 'Navigate to the <strong>snapshot-installer.php</strong> file in your web browser (<a href="%1$s" target="_blank">%1$s</a>) and follow the on-screen steps to complete the restore process.', 'snapshot' ), esc_attr( $snapshot_installer_path ), $snapshot_installer_path ) );
																?>
															</p>
														</li>
													</ol>

													<?php if ( ! $is_branding_hidden ) { ?>
														<p class="p-30">
															<?php
															/* translators: %s - Restore guide URL */
															echo wp_kses_post( sprintf( __( 'For more detailed instructions, check out our <a href="%s" target="_blank">restore guide</a>.', 'snapshot' ), esc_attr( $restore_guide_url ) ) );
															?>
														</p>
													<?php } ?>
													<p><?php esc_html_e( 'Stay protected,', 'snapshot' ); ?></p>
													<?php if ( 'WPMU DEV' === $plugin_custom_name ) { ?>
														<p><?php esc_html_e( 'Snapshot', 'snapshot' ); ?></p>
													<?php } ?>
													<p>
														<?php
														/* translators: %s - Plugin custom name */
														echo esc_html( sprintf( __( '%s Backup Hero', 'snapshot' ), $plugin_custom_name ) );
														?>
													</p>
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!--[if mso | IE]></td></tr></table><![endif]-->
		<!-- END Main content -->
		<?php if ( ! $is_branding_hidden ) { ?>
		<!-- Footer -->
		<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
		<div
			style="background:#E7F1FB;background-color:#E7F1FB;margin:0px auto;border-radius:0 0 15px 15px;max-width:600px;">
			<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
				style="background:#E7F1FB;background-color:#E7F1FB;width:100%;border-radius:0 0 15px 15px;">
				<tbody>
					<tr>
						<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
							<div class="mj-column-per-100 mj-outlook-group-fix"
								style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation"
									style="vertical-align:top;" width="100%">
									<tbody>
										<tr>
											<td align="center"
												style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation"
													style="border-collapse:collapse;border-spacing:0px;">
													<tbody>
														<tr>
															<td style="width:168px;">
																<img height="auto"
																	src="<?php echo esc_attr( $assets->get_asset( 'img/mail-wpmudev-logo-text.png' ) ); ?>"
																	style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;"
																	width="168" />
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
		<div style="margin:0px auto;max-width:600px;">
			<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
					<tr>
						<td style="direction:ltr;font-size:0px;padding:25px 20px 15px;text-align:center;">
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:560px;" ><![endif]-->
							<div class="mj-column-per-100 mj-outlook-group-fix"
								style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation"
									style="vertical-align:top;" width="100%">
									<tbody>
										<tr>
											<td align="center" style="font-size:0px;padding:0;word-break:break-word;">
												<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]-->
												<table align="center" border="0" cellpadding="0" cellspacing="0"
													role="presentation" style="float:none;display:inline-table;">
													<tr class="hidden-img">
														<td style="padding:1px;vertical-align:middle;">
															<table border="0" cellpadding="0" cellspacing="0"
																role="presentation"
																style="background:transparent;border-radius:3px;width:0;">
																<tr>
																	<td
																		style="font-size:0;height:0;vertical-align:middle;width:0;">
																		<img height="0"
																			style="border-radius:3px;display:block;"
																			width="0" />
																	</td>
																</tr>
															</table>
														</td>
														<td style="vertical-align:middle;">
															<span
																style="color:#333333;font-size:13px;font-weight:700;font-family:Roboto, Arial, sans-serif;line-height:25px;text-decoration:none;"><?php esc_html_e( 'Follow us', 'snapshot' ); ?></span>
														</td>
													</tr>
												</table>
												<!--[if mso | IE]></td><td><![endif]-->
												<table align="center" border="0" cellpadding="0" cellspacing="0"
													role="presentation" style="float:none;display:inline-table;">
													<tr>
														<td style="padding:1px;vertical-align:middle;">
															<table border="0" cellpadding="0" cellspacing="0"
																role="presentation"
																style="background:transparent;border-radius:3px;width:25px;">
																<tr>
																	<td
																		style="font-size:0;height:25px;vertical-align:middle;width:25px;">
																		<a href="https://www.facebook.com/wpmudev"
																			target="_blank">
																			<img height="25"
																				src="<?php echo esc_attr( $assets->get_asset( 'img/mail-button-logo-facebook.png' ) ); ?>"
																				style="border-radius:3px;display:block;"
																				width="25" />
																		</a>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<!--[if mso | IE]></td><td><![endif]-->
												<table align="center" border="0" cellpadding="0" cellspacing="0"
													role="presentation" style="float:none;display:inline-table;">
													<tr>
														<td style="padding:1px;vertical-align:middle;">
															<table border="0" cellpadding="0" cellspacing="0"
																role="presentation"
																style="background:transparent;border-radius:3px;width:25px;">
																<tr>
																	<td
																		style="font-size:0;height:25px;vertical-align:middle;width:25px;">
																		<a href="https://www.instagram.com/wpmu_dev/"
																			target="_blank">
																			<img height="25"
																				src="<?php echo esc_attr( $assets->get_asset( 'img/mail-button-logo-instagram.png' ) ); ?>"
																				style="border-radius:3px;display:block;"
																				width="25" />
																		</a>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<!--[if mso | IE]></td><td><![endif]-->
												<table align="center" border="0" cellpadding="0" cellspacing="0"
													role="presentation" style="float:none;display:inline-table;">
													<tr>
														<td style="padding:1px;vertical-align:middle;">
															<table border="0" cellpadding="0" cellspacing="0"
																role="presentation"
																style="background:transparent;border-radius:3px;width:25px;">
																<tr>
																	<td
																		style="font-size:0;height:25px;vertical-align:middle;width:25px;">
																		<a href="https://twitter.com/wpmudev"
																			target="_blank">
																			<img height="25"
																				src="<?php echo esc_attr( $assets->get_asset( 'img/mail-button-logo-twitter.png' ) ); ?>"
																				style="border-radius:3px;display:block;"
																				width="25" />
																		</a>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
												<!--[if mso | IE]></td></tr></table><![endif]-->
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
		<div style="margin:0px auto;max-width:600px;">
			<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
				<tbody>
					<tr>
						<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
							<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
							<div class="mj-column-per-100 mj-outlook-group-fix"
								style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation"
									style="vertical-align:top;" width="100%">
									<tbody>
										<tr>
											<td align="center"
												style="font-size:0px;padding:0 0 15px;word-break:break-word;">
												<div
													style="font-family:Roboto, Arial, sans-serif;font-size:9px;letter-spacing:-.25px;line-height:30px;text-align:center;color:#505050;">
													INCSUB PO BOX 163, ALBERT PARK, VICTORIA.3206 AUSTRALIA</div>
											</td>
										</tr>
										<tr>
											<td align="center"
												style="font-size:0px;padding:0 0 25px;word-break:break-word;">
												<div
													style="font-family:Roboto, Arial, sans-serif;font-size:10px;letter-spacing:-.25px;line-height:30px;text-align:center;color:#1A1A1A;">
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<!--[if mso | IE]></td></tr></table><![endif]-->
		<!-- END footer -->
			<?php
		}//end if
		?>
	</div>
</body>

</html>