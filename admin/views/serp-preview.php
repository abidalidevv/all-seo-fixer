<?php
/**
 * View: Live SERP & Social Snippet Simulator
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
		<h1>👁️ Live SERP & Social Card Simulator</h1>
		<p>Preview exactly how your website appears on Google Search (Mobile & Desktop) and Facebook/Twitter social sharing cards before search engines crawl your site.</p>
		<div class="asf-hero-meta">
			<a href="https://abidalidev.com" target="_blank">🌐 abidalidev.com</a>
			<a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">⭐ @abidalidevv</a>
		</div>
	</div>

	<div class="asf-card">
		<h2>⚙️ Select Page or Customize Snippet</h2>
		<div style="margin-bottom:16px;">
			<label style="font-weight:600;display:block;margin-bottom:6px;">Load From Existing Page:</label>
			<select id="asf-serp-post-select" class="asf-input asf-select" style="max-width:450px;">
				<option value="">-- Choose a page/post --</option>
				<?php foreach ( $posts as $p ) : ?>
					<?php
					$t = get_post_meta( $p->ID, 'rank_math_title', true ) ?: get_post_meta( $p->ID, '_yoast_wpseo_title', true ) ?: get_the_title( $p->ID );
					$d = get_post_meta( $p->ID, 'rank_math_description', true ) ?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) ?: '';
					$u = get_permalink( $p->ID );
					?>
					<option value="<?php echo esc_attr( $p->ID ); ?>"
						data-title="<?php echo esc_attr( $t ); ?>"
						data-desc="<?php echo esc_attr( $d ); ?>"
						data-url="<?php echo esc_attr( $u ); ?>">
						<?php echo esc_html( get_the_title( $p->ID ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
			<div>
				<label style="font-weight:600;display:block;margin-bottom:4px;">SEO Title Tag:</label>
				<input type="text" id="asf-serp-title-input" class="asf-input" value="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' ) ); ?>" placeholder="Page Title (aim for 50–60 characters)" />
				<div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-top:4px;">
					<span id="asf-title-char-count">0 / 60 chars</span>
					<span id="asf-title-pixel-count">0 / 600 px</span>
				</div>
				<div class="asf-score-bar-wrap" style="margin-top:4px;"><div id="asf-title-bar" class="asf-score-bar green" style="width:0%;"></div></div>
			</div>

			<div>
				<label style="font-weight:600;display:block;margin-bottom:4px;">Meta Description:</label>
				<input type="text" id="asf-serp-desc-input" class="asf-input" value="Welcome to our official website. Explore our services, products, and insights." placeholder="Meta Description (aim for 120–155 characters)" />
				<div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-top:4px;">
					<span id="asf-desc-char-count">0 / 155 chars</span>
					<span id="asf-desc-pixel-count">0 / 960 px</span>
				</div>
				<div class="asf-score-bar-wrap" style="margin-top:4px;"><div id="asf-desc-bar" class="asf-score-bar green" style="width:0%;"></div></div>
			</div>
		</div>
	</div>

	<!-- GOOGLE SERP PREVIEW BOX -->
	<div class="asf-card">
		<h2>🔍 Google Search Live Preview</h2>
		<div style="background:#fff;border:1px solid #dfe1e5;border-radius:10px;padding:20px;max-width:650px;font-family:arial,sans-serif;">
			<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
				<div style="width:26px;height:26px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:13px;">🌐</div>
				<div>
					<div style="font-size:14px;color:#202124;line-height:1.2;font-weight:400;"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></div>
					<div id="asf-serp-url-prev" style="font-size:12px;color:#4d5156;line-height:1.2;"><?php echo esc_html( home_url( '/' ) ); ?></div>
				</div>
			</div>
			<div id="asf-serp-title-prev" style="font-size:20px;color:#1a0dab;font-weight:400;line-height:1.3;margin-bottom:4px;cursor:pointer;">
				Page Title Preview
			</div>
			<div id="asf-serp-desc-prev" style="font-size:14px;color:#4d5156;line-height:1.58;word-wrap:break-word;">
				Meta description preview goes here...
			</div>
		</div>
	</div>

	<!-- SOCIAL CARD PREVIEW BOX -->
	<div class="asf-card">
		<h2>📱 Social Share Card Preview (Facebook / Open Graph)</h2>
		<div style="background:#fff;border:1px solid #dadde1;border-radius:8px;max-width:500px;overflow:hidden;font-family:system-ui,-apple-system,BlinkMacSystemFont,sans-serif;">
			<div style="background:#f0f2f5;height:220px;display:flex;align-items:center;justify-content:center;color:#65676b;font-size:14px;font-weight:600;border-bottom:1px solid #dadde1;">
				🖼️ Featured Image / Open Graph Image Preview
			</div>
			<div style="padding:12px 16px;background:#f2f3f5;">
				<div style="font-size:12px;color:#606770;text-transform:uppercase;margin-bottom:2px;"><?php echo esc_html( strtoupper( parse_url( home_url(), PHP_URL_HOST ) ) ); ?></div>
				<div id="asf-og-title-prev" style="font-size:16px;font-weight:600;color:#1d2129;margin-bottom:4px;line-height:1.3;">Page Title Preview</div>
				<div id="asf-og-desc-prev" style="font-size:14px;color:#606770;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Meta description preview...</div>
			</div>
		</div>
	</div>

	<div class="asf-footer">
		Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a> · <a href="https://github.com/abidalidevv/all-seo-fixer" target="_blank">GitHub @abidalidevv</a>
	</div>
</div>
