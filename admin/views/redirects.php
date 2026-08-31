<?php
/**
 * View: 301 Redirect Manager & 404 Monitor
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;

// Handle save
if ( isset( $_POST['asf_save_redirects'] ) && check_admin_referer( 'asf_save_redirects', 'asf_redirects_nonce' ) ) {
	$sources = array_map( 'sanitize_text_field', $_POST['asf_source'] ?? array() );
	$targets = array_map( 'esc_url_raw',          $_POST['asf_target'] ?? array() );
	$new     = array();
	for ( $i = 0; $i < count( $sources ); $i++ ) {
		$s = trim( $sources[ $i ] );
		$t = trim( $targets[ $i ] );
		if ( $s && $t ) $new[ $s ] = $t;
	}
	update_option( ASF_OPT_REDIRECTS, $new );
	echo '<div class="asf-notice asf-notice-success"><strong>' . count($new) . ' redirect rule(s) saved successfully.</strong></div>';
}

$redirects = get_option( ASF_OPT_REDIRECTS, array() );
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>301 Canonical Redirect Manager</h1>
			<p class="asf-header-desc">Manage permanent 301 redirects for legacy URLs, renamed slugs, and 404 errors without modifying .htaccess files.</p>
		</div>
		<div class="asf-header-actions">
			<button type="button" class="button button-secondary" id="asf-add-row">+ Add Redirect Rule</button>
		</div>
	</div>

	<form method="post">
		<?php wp_nonce_field( 'asf_save_redirects', 'asf_redirects_nonce' ); ?>
		<div class="asf-card">
			<h2>Active Redirect Rules <span class="asf-count-pill blue"><?php echo count($redirects); ?></span></h2>
			<table class="widefat striped" id="asf-redir-table" style="margin-bottom:14px;">
				<thead>
					<tr>
						<th>Source Path (Legacy URL, e.g. /old-slug/)</th>
						<th>Target URL (Destination, e.g. https://domain.com/new-slug/)</th>
						<th style="width:70px;text-align:center;">Action</th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $redirects ) : foreach ( $redirects as $src => $tgt ) : ?>
					<tr>
						<td><input type="text" name="asf_source[]" value="<?php echo esc_attr($src); ?>" class="asf-input" style="width:100%;" placeholder="/legacy-path/" /></td>
						<td><input type="text" name="asf_target[]" value="<?php echo esc_attr($tgt); ?>" class="asf-input" style="width:100%;" placeholder="https://domain.com/new-url/" /></td>
						<td style="text-align:center;"><button type="button" class="asf-btn-danger asf-btn-xs asf-remove-row">✕</button></td>
					</tr>
				<?php endforeach; else : ?>
					<tr>
						<td><input type="text" name="asf_source[]" class="asf-input" style="width:100%;" placeholder="/old-blog-post-slug/" /></td>
						<td><input type="text" name="asf_target[]" class="asf-input" style="width:100%;" placeholder="https://yourdomain.com/new-slug/" /></td>
						<td style="text-align:center;"><button type="button" class="asf-btn-danger asf-btn-xs asf-remove-row">✕</button></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
			<div class="asf-action-bar">
				<button type="button" class="button button-secondary" id="asf-add-row-bottom">+ Add Rule</button>
				<input type="submit" name="asf_save_redirects" class="button button-primary" value="Save All Redirect Rules" />
			</div>
		</div>
	</form>

	<!-- REAL-TIME 404 URL MONITOR -->
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . 'asf_404_logs';
	$logs404 = array();
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
		$logs404 = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY hits DESC LIMIT 50" );
	}
	if ( ! empty( $logs404 ) ) :
	?>
	<div class="asf-card">
		<h2>Real-Time 404 Error Log <span class="asf-count-pill red"><?php echo count($logs404); ?></span></h2>
		<p>Visitors and search crawlers hit 404 errors on these URLs. Click <strong>Create 301 Redirect</strong> to map them to an active page:</p>
		<div style="max-height:360px;overflow-y:auto;">
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Missing 404 Path</th>
						<th>Hits</th>
						<th>Last Seen</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $logs404 as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html($row->url); ?></code></td>
						<td><strong><?php echo esc_html($row->hits); ?></strong></td>
						<td><small><?php echo esc_html( date( 'Y-m-d H:i', $row->last_seen ) ); ?></small></td>
						<td>
							<button type="button" class="button button-primary asf-btn-xs asf-add-404-redir-btn"
								data-src="<?php echo esc_attr($row->url); ?>">⚡ Create 301 Redirect</button>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php endif; ?>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
