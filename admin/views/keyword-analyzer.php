<?php
/**
 * View: Keyword Density & Over-Optimization Analyzer
 *
 * @package All_SEO_Fixer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'manage_options' ) ) return;

$posts = get_posts( array(
	'post_type'      => array( 'post', 'page' ),
	'post_status'    => 'publish',
	'posts_per_page' => 100,
) );
?>
<div class="wrap asf-wrap">

	<div class="asf-hero">
		<h1>🔤 Keyword Density & Over-Optimization Analyzer</h1>
		<p>Analyze N-gram word frequencies (1-word & 2-word phrases) and keyword density percentages. Detect and prevent Google keyword stuffing penalties (> 2.5% density).</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>🔍 Select Page to Analyze</h2>
		<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
			<select id="asf-kw-post-select" class="asf-input asf-select" style="max-width:450px;">
				<option value="">-- Choose a published page/post --</option>
				<?php foreach ( $posts as $p ) : ?>
					<option value="<?php echo esc_attr( $p->ID ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="asf-btn-primary" id="asf-kw-run-btn">🔤 Analyze Keyword Density</button>
		</div>
		<div id="asf-kw-status" style="margin-top:16px;"></div>
	</div>

	<div id="asf-kw-results"></div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
