<?php
/**
 * View: Keyword Density & Over-Optimization Analyzer
 * WordPress Admin Native Design System
 *
 * @package All_SEO_Fixer
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! current_user_can('manage_options') ) return;
?>
<div class="wrap asf-wrap">

	<!-- PAGE HEADER -->
	<div class="asf-header">
		<div class="asf-header-title">
			<h1>Keyword Density & Over-Optimization Analyzer</h1>
			<p class="asf-header-desc">Inspect word frequency across single words, 2-word, and 3-word phrases to detect potential search engine keyword stuffing penalties.</p>
		</div>
	</div>

	<!-- PRE-SCAN INFORMATION CARD -->
	<div class="asf-card asf-card-info">
		<h2>About Keyword Density Diagnostics</h2>
		<p>Search engines like Google penalize pages that repeat keywords unnatural amounts of times (keyword stuffing). This tool extracts post content, strips HTML tags, and calculates N-gram frequency distribution.</p>
		<table class="widefat striped" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Analysis Parameter</th>
					<th>Standard Target Range</th>
					<th>Over-Optimization Warning Threshold</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>Single Keywords (1-gram)</strong></td>
					<td>1.0% – 2.0% density</td>
					<td><span class="asf-badge asf-badge-red">&gt; 2.5% Density</span></td>
				</tr>
				<tr>
					<td><strong>2-Word Keyphrases (2-gram)</strong></td>
					<td>0.5% – 1.5% density</td>
					<td><span class="asf-badge asf-badge-yellow">&gt; 2.0% Density</span></td>
				</tr>
				<tr>
					<td><strong>3-Word Keyphrases (3-gram)</strong></td>
					<td>0.3% – 1.0% density</td>
					<td><span class="asf-badge asf-badge-yellow">&gt; 1.5% Density</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- ANALYSIS CONTROLS -->
	<div class="asf-card">
		<h2>Select Page or Post to Analyze</h2>
		<div class="asf-action-bar" style="margin-top:0;">
			<?php
			$posts = get_posts( array( 'post_type' => array('post','page'), 'post_status' => 'publish', 'posts_per_page' => 100 ) );
			?>
			<select id="asf-kw-post-select" class="asf-input" style="max-width:450px;">
				<?php foreach ( $posts as $p ) : ?>
					<option value="<?php echo esc_attr( $p->ID ); ?>"><?php echo esc_html( $p->post_title ); ?> (ID: <?php echo esc_html( $p->ID ); ?>)</option>
				<?php endforeach; ?>
			</select>
			<button class="button button-primary" id="asf-kw-run-btn">Run Keyword Density Scan</button>
		</div>
		<div id="asf-kw-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-kw-results"></div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
