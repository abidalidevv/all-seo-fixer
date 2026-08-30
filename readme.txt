=== All-in-One SEO Fixer & Auditor ===
Contributors: abidalidev
Tags: seo, seo audit, pagespeed, broken links, redirect manager, on-page seo, core web vitals, orphan media, schema, sitemap
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free, open-source 360° SEO Diagnostic & Auto-Fixer. PageSpeed Insights, On-Page Checker, Broken Link Cleaner, 301 Redirects, IndexNow. No upsells.

== Description ==

**All-in-One SEO Fixer & Auditor** is a completely free, open-source WordPress SEO plugin built for developers and site owners who want a real, comprehensive SEO toolkit — without monthly fees, artificial limitations, or premium upsells.

= 🛡️ 360° SEO Dashboard =
One-click full audit covering 24+ SEO factors: missing titles, meta descriptions, H1 headings, alt texts, duplicate content, schema markup, open graph tags, robots.txt, sitemap, broken links, and orphan media — with a clear action plan for every issue.

= ⚡ Google PageSpeed Insights & Core Web Vitals =
Integrated with the free Google PageSpeed Insights API v5. Test any URL for Performance, Accessibility, Best Practices, and SEO scores (0–100) plus LCP, TBT, CLS, FCP, and Speed Index.

= 🔧 On-Page SEO Checker =
Scans every published post and page for:
* Title tag (missing, too short, too long)
* Meta description (missing, too short, too long)
* Missing H1 heading / multiple H1 detection
* Missing H2 on long-form content
* Image alt text (per image)
* Thin content detection (< 300 words)
* Schema JSON-LD presence
* NOINDEX flag warnings
* Internal link count

= 🔗 Broken Link & Typo Cleaner =
Detects and auto-fixes double-domain URL typos (e.g. `domain.comhttps://domain.com/`) in Elementor, Gutenberg, and Classic Editor content and postmeta.

= 🖼️ Orphan Media Scanner =
Cross-references every image against post content, Elementor JSON, featured images, and site logos. Safely trash unused images in one click (fully reversible).

= 🔀 301 Redirect Manager =
Manage permanent 301 redirects for old URLs, renamed slugs, or Google Search Console 404 errors — no .htaccess editing required.

= 🚀 IndexNow & Sitemap Pinger =
Instantly alert Google (sitemap ping), Bing, Yandex, and Naver (IndexNow) to re-crawl your most recently updated pages and purge cached sitemaps.

= 🛡️ Always-On Protections =
* RSS/Atom feed pages: `X-Robots-Tag: noindex, follow`
* Elementor, Divi, Oxygen, Beaver Builder template CPTs automatically excluded from XML Sitemap index

= Free APIs Used =
* Google PageSpeed Insights v5 (25,000 free requests/day)
* IndexNow API (Bing, Yandex, Seznam, Naver)
* Google Sitemap Ping
* Chart.js via jsDelivr CDN (score visualization)

= Works With =
* Rank Math SEO
* Yoast SEO
* AIOSEO
* Elementor
* Divi Builder
* Oxygen Builder
* Beaver Builder
* Brizy

== Installation ==

1. Upload the `all-seo-fixer` folder to the `/wp-content/plugins/` directory, or upload `all-seo-fixer.zip` via **Plugins → Add New → Upload Plugin**
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **All SEO Fixer** in the WordPress admin sidebar
4. (Optional) Add your free Google PageSpeed Insights API key in **⚙️ Settings**

== Frequently Asked Questions ==

= Is this plugin truly free? =
Yes. 100% free, open-source under GPL-2.0. No premium version, no upsells, no feature locks. Source code on GitHub.

= Does it work with Rank Math and Yoast? =
Yes. The plugin reads Rank Math and Yoast meta fields (title, description, OG tags) and integrates with their sitemap filters to exclude builder CPTs.

= Do I need the PageSpeed API key? =
No — all other features work without it. The API key only unlocks the PageSpeed Insights & Core Web Vitals tab. The key is free from Google Cloud Console.

= Will running the audit slow down my site? =
No. All scans run exclusively in the WordPress admin via AJAX. No frontend impact whatsoever.

= Is it safe to use the Broken Link Auto-Fixer? =
Yes. It only modifies `postmeta` rows containing the specific `comhttps` typo pattern. A detailed before/after report is shown after each run.

= Is it safe to trash orphan images? =
Yes. Images are moved to WordPress Trash, not permanently deleted. You can restore them anytime from **Media → Trash**.

== Screenshots ==

1. 360° SEO Dashboard — full site health overview with action plan
2. Google PageSpeed Insights with Chart.js score rings and Core Web Vitals
3. On-Page SEO Checker — per-page issue table with direct edit links
4. 301 Redirect Manager — manage rules without editing .htaccess
5. Settings page with robots.txt guide and sitemap submission helper

== Changelog ==

= 2.1.0 =
* Complete plugin restructure: proper MVC separation with includes/ and admin/views/
* Dedicated assets/css/admin.css — premium design system
* Dedicated assets/js/admin.js — all JavaScript centralized with jQuery + Chart.js
* Added duplicate title & duplicate meta detection to 360° audit
* Added H2 heading check and content length (thin content) detection to On-Page Checker
* Added internal link count check
* Added NOINDEX flag detection per page
* Premium UI overhaul: animated stat cards, score bars, tooltips, score rings via Chart.js
* Author: Abid Ali Dev (https://abidalidev.com / @abidalidevv)

= 2.0.0 =
* Generalized from Orbix-specific plugin to universal WordPress plugin
* Added 360° audit dashboard
* Added IndexNow pinger
* Added Media orphan scanner
* Added 301 Redirect Manager

== Upgrade Notice ==

= 2.1.0 =
Major restructure to proper plugin architecture. Recommend fresh install by deactivating 2.0.0 first, then activating 2.1.0.
