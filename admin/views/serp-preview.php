<?php
/**
 * View: SERP & Social Card Simulator
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
			<h1>Live SERP & Social Card Simulator</h1>
			<p class="asf-header-desc">Real-time interactive Google Search preview (Desktop & Mobile) and Facebook Open Graph card simulator with character count and pixel-width meters.</p>
		</div>
	</div>

	<!-- SERP SIMULATOR CONTROLS & PREVIEW -->
	<div class="asf-grid-2">
		<!-- INPUT FORM -->
		<div class="asf-card">
			<h2>Snippet Settings</h2>

			<div style="margin-bottom:14px;">
				<label for="asf-serp-post-select"><strong>Select Published Page/Post to Edit</strong></label>
				<select id="asf-serp-post-select" class="asf-input" style="width:100%;margin-top:4px;">
					<option value="0">-- Custom / Homepage Preview --</option>
					<?php
					$all_pages = get_posts( array(
						'post_type'      => array( 'post', 'page' ),
						'post_status'    => 'publish',
						'posts_per_page' => 100,
					) );
					foreach ( $all_pages as $p ) {
						$m_title = get_post_meta( $p->ID, '_asf_seo_title', true ) ?: ( get_post_meta( $p->ID, 'rank_math_title', true ) ?: ( get_post_meta( $p->ID, '_yoast_wpseo_title', true ) ?: $p->post_title ) );
						$m_desc  = get_post_meta( $p->ID, '_asf_meta_description', true ) ?: ( get_post_meta( $p->ID, 'rank_math_description', true ) ?: get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) );
						echo '<option value="' . esc_attr( $p->ID ) . '" data-url="' . esc_url( get_permalink( $p->ID ) ) . '" data-title="' . esc_attr( $m_title ) . '" data-desc="' . esc_attr( $m_desc ) . '">' . esc_html( $p->post_title ) . ' (#' . $p->ID . ')</option>';
					}
					?>
				</select>
			</div>

			<div style="margin-bottom:14px;">
				<label for="asf-serp-input-title"><strong>SEO Title Tag</strong></label>
				<input id="asf-serp-input-title" type="text" class="asf-input" style="width:100%;margin-top:4px;" value="<?php echo esc_attr( get_bloginfo('name') . ' — ' . ( get_bloginfo('description') ?: 'Official Website' ) ); ?>" />
				<div style="font-size:12px;color:var(--asf-text-muted);margin-top:4px;">
					Length: <strong id="asf-serp-title-len">0</strong> chars (Recommended: 50–60 chars / ~600px max)
				</div>
			</div>

			<div style="margin-bottom:14px;">
				<label for="asf-serp-input-url"><strong>Target URL</strong></label>
				<input id="asf-serp-input-url" type="text" class="asf-input" style="width:100%;margin-top:4px;" value="<?php echo esc_attr( home_url('/') ); ?>" />
			</div>

			<div style="margin-bottom:14px;">
				<label for="asf-serp-input-desc"><strong>Meta Description</strong></label>
				<textarea id="asf-serp-input-desc" class="asf-input" style="width:100%;height:90px;margin-top:4px;"><?php echo esc_textarea( get_bloginfo('description') ?: 'Discover top-rated features, insights, and resources on our official WordPress site.' ); ?></textarea>
				<div style="font-size:12px;color:var(--asf-text-muted);margin-top:4px;">
					Length: <strong id="asf-serp-desc-len">0</strong> chars (Recommended: 120–160 chars / ~960px max)
				</div>
			</div>

			<div style="margin-bottom:18px;">
				<label for="asf-serp-input-img"><strong>Social Share Image URL (og:image)</strong></label>
				<?php
				$logo_id  = (int) get_theme_mod( 'custom_logo' );
				$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
				?>
				<input id="asf-serp-input-img" type="url" class="asf-input" style="width:100%;margin-top:4px;" value="<?php echo esc_attr( $logo_url ); ?>" placeholder="https://domain.com/wp-content/uploads/og-image.jpg" />
			</div>

			<div>
				<button type="button" class="button button-primary" id="asf-serp-save-btn" style="width:100%;">💾 Save Meta Title & Description to Page</button>
				<div id="asf-serp-save-status" style="margin-top:8px;"></div>
			</div>
		</div>

		<!-- LIVE PREVIEW CARDS -->
		<div>
			<!-- GOOGLE DESKTOP PREVIEW -->
			<div class="asf-card">
				<h2>Google Search Preview (Desktop)</h2>
				<div style="font-family:arial,sans-serif;margin-top:10px;">
					<div style="font-size:12px;color:#202124;margin-bottom:2px;display:flex;align-items:center;gap:6px;">
						<span style="display:inline-block;width:18px;height:18px;background:#f1f3f4;border-radius:50%;text-align:center;line-height:18px;font-size:10px;color:#5f6368;">🌐</span>
						<span id="asf-serp-preview-domain" style="color:#202124;font-size:14px;"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
					</div>
					<div id="asf-serp-preview-url" style="font-size:12px;color:#4d5156;margin-bottom:4px;"><?php echo esc_html( home_url('/') ); ?></div>
					<div id="asf-serp-preview-title" style="font-size:20px;color:#1a0dab;line-height:1.3;margin-bottom:4px;cursor:pointer;">
						<?php echo esc_html( get_bloginfo('name') ); ?>
					</div>
					<div id="asf-serp-preview-desc" style="font-size:14px;color:#4d5156;line-height:1.58;">
						<?php echo esc_html( get_bloginfo('description') ); ?>
					</div>
				</div>
			</div>

			<!-- FACEBOOK SOCIAL CARD PREVIEW -->
			<div class="asf-card">
				<h2>Facebook Social Share Preview</h2>
				<div style="border:1px solid #dadde1;border-radius:6px;overflow:hidden;background:#f2f3f5;margin-top:10px;">
					<div id="asf-social-img-box" style="height:180px;background:#e9ebee;display:flex;align-items:center;justify-content:center;background-size:cover;background-position:center;">
						<span id="asf-social-img-fallback" style="color:#8d949e;font-size:13px;">No OG Image Provided</span>
					</div>
					<div style="padding:10px 12px;background:#f2f3f5;border-top:1px solid #dadde1;">
						<div id="asf-social-preview-domain" style="font-size:11px;color:#606770;text-transform:uppercase;margin-bottom:2px;">
							<?php echo esc_html( strtoupper( parse_url( home_url(), PHP_URL_HOST ) ) ); ?>
						</div>
						<div id="asf-social-preview-title" style="font-size:14px;font-weight:600;color:#1d2129;margin-bottom:4px;">
							<?php echo esc_html( get_bloginfo('name') ); ?>
						</div>
						<div id="asf-social-preview-desc" style="font-size:12px;color:#606770;line-height:1.4;max-height:34px;overflow:hidden;">
							<?php echo esc_html( get_bloginfo('description') ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="asf-footer">
		All-in-One SEO Fixer v<?php echo esc_html( ASF_VERSION ); ?> · Built by <a href="https://abidalidev.com" target="_blank">Abid Ali Dev</a>
	</div>
</div>
