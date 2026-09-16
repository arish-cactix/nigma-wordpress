<?php
/**
 * Plugin Name: Imdaad Leadership Sync
 * Description: Syncs the /group/leadership/ page (bios + headshots) from the
 *              Imdaad Group Contentful space, which is the source of truth
 *              used by imdaad.ae/group/people/leadership. Read-only Contentful
 *              access (Content Delivery API only). A "Revalidate from Imdaad"
 *              button on the page editor re-fetches and rebuilds the page on
 *              demand; nothing runs automatically.
 * Version:     1.0
 *
 * @package Nigma
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------------

if ( ! defined( 'NIGMA_LEADERSHIP_WP_PAGE_ID' ) ) {
	define( 'NIGMA_LEADERSHIP_WP_PAGE_ID', 509 ); // /group/leadership/
}
if ( ! defined( 'NIGMA_LEADERSHIP_CF_ENTRY_ID' ) ) {
	// Same Contentful "Leadership Page" entry used for imdaad.ae and homepro.ae —
	// this is a group-wide entry, not a nigma-specific one.
	define( 'NIGMA_LEADERSHIP_CF_ENTRY_ID', '3UnytZ6wA9RBT8ytpQsvOr' );
}

/**
 * Returns the Contentful CDA credentials.
 *
 * Prefers wp-config.php constants (IMDAAD_CONTENTFUL_SPACE_ID /
 * IMDAAD_CONTENTFUL_CDA_TOKEN / IMDAAD_CONTENTFUL_ENV), falling back to WP
 * options set via `wp option update imdaad_contentful_space_id ...` /
 * `imdaad_contentful_cda_token`. Never hardcoded here — this file is
 * version-controlled.
 *
 * @return array{space_id:string,token:string,environment:string}|WP_Error
 */
function nigma_leadership_cf_credentials() {
	$space = defined( 'IMDAAD_CONTENTFUL_SPACE_ID' ) ? IMDAAD_CONTENTFUL_SPACE_ID : get_option( 'imdaad_contentful_space_id' );
	$token = defined( 'IMDAAD_CONTENTFUL_CDA_TOKEN' ) ? IMDAAD_CONTENTFUL_CDA_TOKEN : get_option( 'imdaad_contentful_cda_token' );
	$env   = defined( 'IMDAAD_CONTENTFUL_ENV' ) ? IMDAAD_CONTENTFUL_ENV : get_option( 'imdaad_contentful_environment', 'master' );

	if ( empty( $space ) || empty( $token ) ) {
		return new WP_Error(
			'nigma_leadership_missing_credentials',
			'Contentful credentials are not configured. Set the imdaad_contentful_space_id and imdaad_contentful_cda_token options (or the equivalent wp-config.php constants).'
		);
	}

	return array(
		'space_id'    => $space,
		'token'       => $token,
		'environment' => $env ? $env : 'master',
	);
}

// ---------------------------------------------------------------------------
// Contentful API access (read-only)
// ---------------------------------------------------------------------------

/**
 * Performs a GET request against the Contentful Content Delivery API.
 *
 * @param string $path   API path, e.g. "/entries".
 * @param array  $params Query params.
 * @return array|WP_Error Decoded JSON body, or WP_Error on failure.
 */
function nigma_leadership_cf_get( $path, $params = array() ) {
	$creds = nigma_leadership_cf_credentials();
	if ( is_wp_error( $creds ) ) {
		return $creds;
	}

	$url = sprintf(
		'https://cdn.contentful.com/spaces/%s/environments/%s%s',
		rawurlencode( $creds['space_id'] ),
		rawurlencode( $creds['environment'] ),
		$path
	);
	if ( ! empty( $params ) ) {
		$url = add_query_arg( $params, $url );
	}

	$response = wp_remote_get(
		$url,
		array(
			'headers' => array( 'Authorization' => 'Bearer ' . $creds['token'] ),
			'timeout' => 25,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error(
			'nigma_leadership_cf_http_error',
			sprintf( 'Contentful API returned HTTP %d for %s', $code, $path )
		);
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( null === $body ) {
		return new WP_Error( 'nigma_leadership_cf_bad_json', 'Contentful API returned invalid JSON.' );
	}

	return $body;
}

// ---------------------------------------------------------------------------
// Content transformation
// ---------------------------------------------------------------------------

/**
 * Normalizes em-dashes and en-dashes to a plain hyphen, matching the same
 * editorial rule already applied to the homepro leadership sync.
 *
 * @param string $text Raw text from Contentful.
 * @return string Text with — and – replaced by -.
 */
function nigma_leadership_normalize_dashes( $text ) {
	return str_replace( array( '—', '–' ), '-', $text );
}

/**
 * Converts a Contentful RichText field into an array of paragraph strings.
 * No wording is changed, invented, or re-split — paragraph breaks are taken
 * as-is from Contentful, matching the current page's own paragraph structure.
 *
 * @param array $richtext Contentful RichText document.
 * @return string[] Paragraph strings (may contain <strong>/<em>).
 */
function nigma_leadership_richtext_to_paragraphs( $richtext ) {
	$paragraphs = array();
	if ( empty( $richtext['content'] ) || ! is_array( $richtext['content'] ) ) {
		return $paragraphs;
	}

	foreach ( $richtext['content'] as $node ) {
		if ( 'paragraph' !== ( $node['nodeType'] ?? '' ) ) {
			continue;
		}
		$text = '';
		foreach ( $node['content'] ?? array() as $child ) {
			if ( 'text' !== ( $child['nodeType'] ?? '' ) ) {
				continue;
			}
			$value = $child['value'] ?? '';
			$marks = wp_list_pluck( $child['marks'] ?? array(), 'type' );
			if ( in_array( 'bold', $marks, true ) ) {
				$value = '<strong>' . $value . '</strong>';
			}
			if ( in_array( 'italic', $marks, true ) ) {
				$value = '<em>' . $value . '</em>';
			}
			$text .= $value;
		}
		$text = nigma_leadership_normalize_dashes( trim( $text ) );
		if ( '' !== $text ) {
			$paragraphs[] = $text;
		}
	}

	return $paragraphs;
}

/**
 * Sideloads a Contentful image asset into the WP Media Library, reusing an
 * existing attachment (matched by Contentful asset ID) if one was already
 * imported, so re-syncing does not re-download unchanged images.
 *
 * @param array $asset Contentful Asset object (resolved from includes.Asset).
 * @return int|WP_Error Attachment ID, or WP_Error on failure.
 */
function nigma_leadership_sideload_image( $asset ) {
	$asset_id = $asset['sys']['id'] ?? '';
	if ( '' === $asset_id ) {
		return new WP_Error( 'nigma_leadership_bad_asset', 'Asset has no id.' );
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_imdaad_cf_asset_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $asset_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$file_info = $asset['fields']['file'] ?? array();
	$url       = $file_info['url'] ?? '';
	if ( '' === $url ) {
		return new WP_Error( 'nigma_leadership_bad_asset', 'Asset has no file URL.' );
	}
	if ( '//' === substr( $url, 0, 2 ) ) {
		$url = 'https:' . $url;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$title = $asset['fields']['title'] ?? $asset_id;
	$tmp   = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	$ext        = pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
	$file_array = array(
		'name'     => sanitize_file_name( $title . ( $ext ? '.' . $ext : '' ) ),
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, $title );
	if ( is_wp_error( $attachment_id ) ) {
		wp_delete_file( $tmp );
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_imdaad_cf_asset_id', $asset_id );

	return $attachment_id;
}

/**
 * Builds Fusion Builder (Avada) page content matching the existing leadership
 * page's layout: a centered section heading followed by an image/bio row per
 * person (headshot in a 1/3 column, title + name + bio in a 2/3 column, a
 * full-width divider), from resolved grouping/person data. This mirrors the
 * exact shortcode skeleton currently saved for page 509, read directly from
 * the page editor before writing this function.
 *
 * @param array $groupings Array of ['heading' => string, 'people' => array].
 * @return string Fusion Builder shortcode markup.
 */
function nigma_leadership_build_fusion_content( $groupings ) {
	$common_container_attrs = 'type="flex" hundred_percent="no" hundred_percent_height="no" hundred_percent_height_scroll="no" hundred_percent_height_center_content="yes" equal_height_columns="no" hide_on_mobile="small-visibility,medium-visibility,large-visibility" status="published" border_style="solid"';

	$out = '';

	foreach ( $groupings as $group ) {
		$heading = esc_html( strtoupper( $group['heading'] ) );
		$out    .= sprintf(
			'[fusion_builder_container %1$s][fusion_builder_row][fusion_builder_column type="1_1" layout="1_1" hide_on_mobile="small-visibility,medium-visibility,large-visibility" border_style="solid"][fusion_title title_type="text" rotation_effect="bounceIn" loop_animation="off" style_type="none" fusion_font_family_title_font="Play" fusion_font_variant_title_font="700" font_size="40px" text_color="#c7a877" content_align="center" size="1"]%2$s[/fusion_title][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
			$common_container_attrs,
			'<h1 class="vc_custom_heading" style="text-align: center;"><strong>' . $heading . '</strong></h1>'
		);

		foreach ( $group['people'] as $person ) {
			$image_col = $person['image_id']
				? sprintf(
					'[fusion_imageframe image_id="%1$d|full" style_type="none" hover_type="none" align="center" alt="%2$s"]%3$s[/fusion_imageframe]',
					(int) $person['image_id'],
					esc_attr( $person['name'] ),
					esc_url( wp_get_attachment_url( $person['image_id'] ) )
				)
				: '';

			$title_span = sprintf( '<span class="element" style="margin-bottom: 0;">%s</span>', esc_html( $person['title'] ) );
			$name_span  = sprintf( '<span class="element" style="margin-bottom: 0;">%s</span>', esc_html( $person['name'] ) );
			$bio        = implode( "\n\n", array_map( 'wp_kses_post', $person['paragraphs'] ) );

			$out .= sprintf(
				'[fusion_builder_container %1$s padding_top="20px"][fusion_builder_row][fusion_builder_column type="1_3" layout="1_3" hide_on_mobile="small-visibility,medium-visibility,large-visibility" border_style="solid"]%2$s[/fusion_builder_column][fusion_builder_column type="2_3" layout="2_3" hide_on_mobile="small-visibility,medium-visibility,large-visibility" border_style="solid"][fusion_text]

%3$s
[/fusion_text][fusion_text]

%4$s
[/fusion_text][fusion_text]

%5$s
[/fusion_text][/fusion_builder_column][fusion_builder_column type="1_1" layout="1_1" hide_on_mobile="small-visibility,medium-visibility,large-visibility" border_style="solid"][fusion_separator style_type="single solid" alignment="center" /][/fusion_builder_column][/fusion_builder_row][/fusion_builder_container]',
				$common_container_attrs,
				$image_col,
				$title_span,
				$name_span,
				$bio
			);
		}
	}

	return $out;
}

// ---------------------------------------------------------------------------
// Sync orchestration
// ---------------------------------------------------------------------------

/**
 * Fetches the leadership page from Contentful and rebuilds the WordPress
 * leadership page to match. Never leaves the page in a broken state: if
 * anything fails before the final content is fully built, the existing page
 * is left untouched. The previous content is always backed up to postmeta
 * before being overwritten.
 *
 * @return true|WP_Error
 */
function nigma_sync_leadership_from_contentful() {
	$res = nigma_leadership_cf_get(
		'/entries',
		array(
			'sys.id'  => NIGMA_LEADERSHIP_CF_ENTRY_ID,
			'include' => 5,
		)
	);
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	if ( empty( $res['items'][0] ) ) {
		return new WP_Error( 'nigma_leadership_not_found', 'Leadership page entry not found in Contentful.' );
	}

	$entries_by_id = array();
	foreach ( $res['includes']['Entry'] ?? array() as $e ) {
		$entries_by_id[ $e['sys']['id'] ] = $e;
	}
	$assets_by_id = array();
	foreach ( $res['includes']['Asset'] ?? array() as $a ) {
		$assets_by_id[ $a['sys']['id'] ] = $a;
	}

	$page_entry   = $res['items'][0];
	$section_refs = $page_entry['fields']['sections'] ?? array();

	$groupings    = array();
	$person_count = 0;

	foreach ( $section_refs as $ref ) {
		$sid     = $ref['sys']['id'] ?? '';
		$section = $entries_by_id[ $sid ] ?? null;
		if ( ! $section ) {
			continue;
		}
		$component_refs = $section['fields']['components'] ?? array();
		if ( empty( $component_refs ) ) {
			continue; // Skip non-people sections (e.g. a "Get Started" CTA block).
		}

		$heading = nigma_leadership_normalize_dashes( $section['fields']['heading'] ?? '' );
		$people  = array();

		foreach ( $component_refs as $cref ) {
			$cid       = $cref['sys']['id'] ?? '';
			$component = $entries_by_id[ $cid ] ?? null;
			if ( ! $component ) {
				continue;
			}
			$fields     = $component['fields'];
			$name       = nigma_leadership_normalize_dashes( trim( $fields['heading'] ?? '' ) );
			$title      = nigma_leadership_normalize_dashes( trim( $fields['subHeading'] ?? '' ) );
			$paragraphs = nigma_leadership_richtext_to_paragraphs( $fields['description'] ?? array() );

			if ( '' === $name || empty( $paragraphs ) ) {
				continue; // Skip incomplete entries rather than publish blanks.
			}

			$image_id      = $fields['image']['sys']['id'] ?? null;
			$attachment_id = null;
			if ( $image_id && isset( $assets_by_id[ $image_id ] ) ) {
				$att = nigma_leadership_sideload_image( $assets_by_id[ $image_id ] );
				if ( ! is_wp_error( $att ) ) {
					$attachment_id = $att;
				} else {
					// Don't fail the whole sync over one missing headshot, but
					// don't let it fail silently either.
					error_log( sprintf( 'nigma_leadership_sync: image sideload failed for %s: %s', $name, $att->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}

			$people[] = array(
				'name'       => $name,
				'title'      => $title,
				'paragraphs' => $paragraphs,
				'image_id'   => $attachment_id,
			);
			++$person_count;
		}

		if ( ! empty( $people ) ) {
			$groupings[] = array(
				'heading' => $heading,
				'people'  => $people,
			);
		}
	}

	if ( empty( $groupings ) ) {
		return new WP_Error(
			'nigma_leadership_no_people',
			'No leadership entries resolved from Contentful; aborting to avoid publishing an empty page.'
		);
	}

	$content = nigma_leadership_build_fusion_content( $groupings );

	// Always keep a backup of what was there before this sync overwrote it.
	$current = get_post_field( 'post_content', NIGMA_LEADERSHIP_WP_PAGE_ID );
	update_post_meta( NIGMA_LEADERSHIP_WP_PAGE_ID, '_imdaad_leadership_pre_sync_backup', $current );

	// The <span style="..."> markup used for each person's title/name is
	// stripped by WordPress's content_save_pre KSES filtering unless the
	// saving user has the 'unfiltered_html' capability. WP-CLI has no logged-in
	// user by default, so a CLI-run sync would silently lose that styling. The
	// page-editor "Revalidate" button already runs as a real logged-in admin
	// and is unaffected; this just makes CLI syncs behave the same way.
	$original_user_id = get_current_user_id();
	$borrowed_admin    = false;
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'fields'  => 'ID',
				'orderby' => 'ID',
			)
		);
		if ( ! empty( $admins ) ) {
			wp_set_current_user( (int) $admins[0] );
			$borrowed_admin = true;
		}
	}

	wp_update_post(
		array(
			'ID'           => NIGMA_LEADERSHIP_WP_PAGE_ID,
			'post_content' => $content,
		)
	);
	clean_post_cache( NIGMA_LEADERSHIP_WP_PAGE_ID );

	if ( $borrowed_admin ) {
		wp_set_current_user( $original_user_id );
	}

	// Purge any cached copy of the page so the rebuilt content is visible
	// immediately rather than waiting for cache expiry.
	do_action( 'litespeed_purge_post', NIGMA_LEADERSHIP_WP_PAGE_ID );

	update_option( 'imdaad_leadership_last_synced', current_time( 'mysql' ) );
	update_option( 'imdaad_leadership_last_sync_count', $person_count );

	return true;
}

// ---------------------------------------------------------------------------
// Admin UI: meta box + AJAX revalidate button
// ---------------------------------------------------------------------------

add_action( 'add_meta_boxes', 'nigma_leadership_add_meta_box' );
/**
 * Registers the "Imdaad Leadership Sync" meta box on the leadership page's
 * editor screen only.
 *
 * @return void
 */
function nigma_leadership_add_meta_box() {
	global $post;
	if ( ! $post || (int) $post->ID !== NIGMA_LEADERSHIP_WP_PAGE_ID ) {
		return;
	}
	add_meta_box(
		'nigma-leadership-sync',
		'Imdaad Leadership Sync',
		'nigma_leadership_meta_box_render',
		'page',
		'side',
		'high'
	);
}

/**
 * Renders the meta box: last-synced status + Revalidate button.
 *
 * @return void
 */
function nigma_leadership_meta_box_render() {
	$last_synced = get_option( 'imdaad_leadership_last_synced' );
	$count       = get_option( 'imdaad_leadership_last_sync_count' );
	?>
	<div id="nigma-leadership-sync-box">
		<p id="nigma-leadership-sync-status">
			<?php if ( $last_synced ) : ?>
				Last synced: <strong><?php echo esc_html( $last_synced ); ?></strong> (site time)<br>
				<?php echo esc_html( (string) $count ); ?> leadership entries.
			<?php else : ?>
				Never synced from Contentful yet.
			<?php endif; ?>
		</p>
		<button type="button" id="nigma-leadership-revalidate" class="button button-primary">
			Revalidate from Imdaad
		</button>
		<p class="description">Fetches the latest bios and photos from imdaad.ae&#8217;s Contentful source and rebuilds this page. Reload the editor after syncing to see the refreshed content.</p>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'nigma_leadership_admin_assets' );
/**
 * Enqueues the small inline script that wires the Revalidate button to the
 * AJAX handler, only on the leadership page's editor screen.
 *
 * @param string $hook Current admin page hook.
 * @return void
 */
function nigma_leadership_admin_assets( $hook ) {
	if ( 'post.php' !== $hook ) {
		return;
	}
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id !== NIGMA_LEADERSHIP_WP_PAGE_ID ) {
		return;
	}

	wp_add_inline_script(
		'jquery',
		'jQuery(function($){
			$("#nigma-leadership-revalidate").on("click", function(){
				var $btn = $(this), $status = $("#nigma-leadership-sync-status");
				$btn.prop("disabled", true).text("Syncing...");
				$.post(ajaxurl, {
					action: "nigma_leadership_revalidate",
					nonce: "' . esc_js( wp_create_nonce( 'nigma_leadership_revalidate' ) ) . '"
				}).done(function(res){
					if (res && res.success) {
						$status.html("<strong>Synced:</strong> " + res.data.message);
					} else {
						$status.html("<strong style=\"color:#b32d2e\">Error:</strong> " + (res && res.data ? res.data.message : "Unknown error"));
					}
				}).fail(function(){
					$status.html("<strong style=\"color:#b32d2e\">Error:</strong> Request failed.");
				}).always(function(){
					$btn.prop("disabled", false).text("Revalidate from Imdaad");
				});
			});
		});'
	);
}

add_action( 'wp_ajax_nigma_leadership_revalidate', 'nigma_leadership_ajax_revalidate' );
/**
 * AJAX handler for the Revalidate button.
 *
 * @return void
 */
function nigma_leadership_ajax_revalidate() {
	check_ajax_referer( 'nigma_leadership_revalidate', 'nonce' );

	if ( ! current_user_can( 'edit_post', NIGMA_LEADERSHIP_WP_PAGE_ID ) ) {
		wp_send_json_error( array( 'message' => 'You do not have permission to do this.' ), 403 );
	}

	$result = nigma_sync_leadership_from_contentful();

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
	}

	$count = get_option( 'imdaad_leadership_last_sync_count' );
	wp_send_json_success(
		array(
			'message' => sprintf( '%d leadership entries updated from Contentful.', (int) $count ),
		)
	);
}

// ---------------------------------------------------------------------------
// WP-CLI: `wp imdaad-leadership sync`
// ---------------------------------------------------------------------------

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Syncs the /group/leadership/ page from Contentful.
	 */
	class Nigma_Leadership_CLI_Command {
		/**
		 * Runs the sync.
		 *
		 * @return void
		 */
		public function sync() {
			$upload_dir = wp_upload_dir();
			if ( empty( $upload_dir['error'] ) && ! is_writable( $upload_dir['basedir'] ) ) {
				WP_CLI::warning(
					'wp-content/uploads is not writable by the current user -- headshots will likely fail to ' .
					'download. Run this as the web server user instead, e.g.: sudo -u www-data wp imdaad-leadership sync'
				);
			}

			$result = nigma_sync_leadership_from_contentful();
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			WP_CLI::success( 'Leadership page synced from Contentful (' . (int) get_option( 'imdaad_leadership_last_sync_count' ) . ' entries).' );
		}
	}
	WP_CLI::add_command( 'imdaad-leadership', 'Nigma_Leadership_CLI_Command' );
}
