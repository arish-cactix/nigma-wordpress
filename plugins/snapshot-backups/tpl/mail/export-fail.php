<?php // phpcs:ignore
/**
 * Failed backup mail template.
 *
 * @package snapshot
 */

$assets = new \WPMUDEV\Snapshot4\Helper\Assets();

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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"
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

    p.snapshot-log-error {
        margin-bottom: 0px;
        padding-left: 30px;
        background-image: url("<?php echo esc_attr( $assets->get_asset( 'img/mail-icon-error-6px@2x.png' ) ); ?>");
        background-repeat: no-repeat;
        background-size: 16px 22px;
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
																					src="<?php echo esc_attr( $assets->get_asset( 'img/mail-snapshot-report-logo.png' ) ); ?>"
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
													<h1><?php echo wp_kses_post( sprintf( __( 'Backup export failed for <a href="%1$s" target="_blank">%2$s</a>.', 'snapshot' ), $site_url, $site ) ); ?>
													</h1>
													<?php /* translators: %s - User name */ ?>
													<p><?php echo esc_html( sprintf( __( 'Hi %s,', 'snapshot' ), $name ) ); ?>
													</p>
													<p><?php echo wp_kses_post( $p1_html ); ?></p>
													<p class="snapshot-log-error">
														<strong><?php esc_html_e( 'Backup Error Log', 'snapshot' ); ?></strong>
													</p>
													<p style="font-size: 13px; margin-top: 0px; padding-left: 30px;">
														<?php echo esc_html( $error1 . ' ' . $error2 ); ?></p>
													<div
														style="border-bottom: 1px solid #E6E6E6; margin: 0 -25px 30px;">
													</div>
													<p><?php echo wp_kses_post( $p2_html ); ?></p>
													<center><a class="button"
															href="<?php echo esc_attr( $button_link ); ?>"><?php echo esc_html( $button_text ); ?></a>
													</center>
													<?php if ( ! empty( $bottom_link ) && ! empty( $bottom_link_text ) ) { ?>
													<center><a style="font-size: 16px;"
															href="<?php echo esc_attr( $bottom_link ); ?>"><?php echo esc_html( $bottom_link_text ); ?></a>
													</center>
													<?php } ?>
													<?php if ( ! $is_branding_hidden ) { ?>
													<p><?php esc_html_e( 'Stay protected,', 'snapshot' ); ?></p>
													<p><?php esc_html_e( 'Snapshot', 'snapshot' ); ?></p>
													<p>
														<?php
														/* translators: $s - plugin custom name */
														echo esc_html( sprintf( __( '%s Backup Hero', 'snapshot' ), $plugin_custom_name ) );
														?>
													</p>
													<?php } ?>
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