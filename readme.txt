=== All-in-One SEO Fixer & Auditor ===
Contributors: abidalidev
Tags: seo, seo audit, pagespeed, console errors, broken links, redirect manager, on-page seo, core web vitals, orphan media, schema, sitemap, lazy load, pdf audit report, ai assistant, diagnostics
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 2.3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Free, open-source 360° SEO Diagnostic & Auto-Fixer. Console Error Doctor, PageSpeed Insights, On-Page Checker, Broken Link Cleaner, 301 Redirects, Lazy Loading, PDF Reports, Groq AI Copilot, Floating AI Widget, System Diagnostics & 19 Swiss-Knife Tools.

== Description ==

**All-in-One SEO Fixer & Auditor** is a completely free, open-source WordPress SEO & technical performance plugin built for developers and site owners who want a real, comprehensive SEO toolkit — without monthly fees, artificial limitations, or premium upsells.

= 🤖 Dedicated AI SEO Assistant & Global Floating Copilot =
Real-time conversational SEO intelligence powered primarily by ultra-fast **Groq LLaMA-3.3-70B AI** (with Google Gemini Flash & OpenRouter failovers). Features a dedicated AI Assistant console with Master AI Prompt JSON export/import and a global **Floating AI Assistant Bubble Widget** on every plugin admin screen for instant assistance.

= ⚙️ Settings Hub with Horizontal Tabs & System Diagnostics =
Complete settings reorganization into 7 native horizontal tabs: General & APIs, AI Engine & Keys, Local & GEO SEO, Robots & LLMs.txt, System Diagnostics & Logs, Pro License & Plans, and About Developer. Includes a 16-point server environment health scanner with 1-click **Email Diagnostic Report to Developer Support**.

= 🛡️ 360° SEO Dashboard & Executive PDF Reports =
One-click full audit covering 24+ SEO factors: missing titles, meta descriptions, H1 headings, alt texts, duplicate content, schema markup, open graph tags, robots.txt, sitemap, broken links, and orphan media — with 1-click **Download Executive Audit PDF Report** for clients.

= 🩺 Console & Error Doctor (Script & Debug Log Fixer) =
Real-time front-end JavaScript runtime error monitor, 1-click **Mixed Content (HTTP on HTTPS) auto-fixer**, safe **jQuery Compatibility Layer** (`$ is not a function` fixer), `wp-content/debug.log` parser, and **AI Error Doctor** with instant root-cause analysis and code fixes.

= 🖼️ Lazy Load Images & Media Speed Optimizer =
Enforces native HTML5 `loading="lazy"` on all content images (`<img>`) and video embeds (`<iframe>`), with LCP Hero Guard to preserve above-the-fold PageSpeed scores.

= 🤖 AI SEO Assistant & Copilot =
Multi-provider AI engine (Groq LLaMA 3.3, Google Gemini Flash, OpenRouter) providing instant custom SEO recommendations, JSON schema generators, and Core Web Vitals diagnostic guides.

= ⚡ Google PageSpeed Insights & Core Web Vitals =
Integrated with the free Google PageSpeed Insights API v5. Test any URL for Performance, Accessibility, Best Practices, and SEO scores (0–100) plus LCP, TBT, CLS, FCP, and Speed Index.

= 🔧 On-Page SEO Checker =
Scans every published post and page for:
* Title tag (missing, too short, too long, duplicate deduplication)
* Meta description (missing, too short, too long)
* Missing H1 heading / multiple H1 detection
* Image alt text (per image with intelligent thumbnail lightbox preview)
* Thin content detection (< 300 words)
* Schema JSON-LD presence
* NOINDEX flag warnings
* Internal link count

= 🔗 Broken Link & Typo Cleaner =
Detects and auto-fixes double-domain URL typos (e.g. `domain.comhttps://domain.com/`) in Elementor, Gutenberg, and Classic Editor content and postmeta across `.com`, `.org`, `.net`, and `.ae` domains.

= 🖼️ Media Scanner & Auto-Alt Generator =
Cross-references every image against post content, Elementor JSON, featured images, and site logos. Auto-generates clean Alt text for numeric filenames and safely trashes unused images.

= 🔀 301 Redirect Manager =
Manage permanent 301 redirects for old URLs, renamed slugs, or Google Search Console 404 errors — no .htaccess editing required.

= 🚀 IndexNow & Sitemap Pinger =
Instantly alert Bing, Yandex, Naver & Seznam (IndexNow) to re-crawl your most recently updated pages and purge cached sitemaps.

= 🛡️ Always-On Protections =
* Front-end `<link rel="canonical">` generation across all page types
* `<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">` for Google Discover eligibility
* Automatic `BreadcrumbList` JSON-LD schema
* `LocalBusiness` schema with NAP, Geo Coordinates & Opening Hours
* RSS/Atom feed pages: `X-Robots-Tag: noindex, follow`
* Elementor, Divi, Oxygen, Beaver Builder template CPTs automatically excluded from XML Sitemap index

= Free APIs Used =
* Google PageSpeed Insights v5 (25,000 free requests/day)
* Google Search Console Indexing API v3
* IndexNow API (Bing, Yandex, Seznam, Naver)
* Groq AI / Google Gemini / OpenRouter
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

== Changelog ==

= 2.2.0 =
* Added Console & Error Doctor Module (`asf-error-doctor`): front-end JavaScript console error monitor, 1-click Mixed Content HTTPS rewriter, safe jQuery compatibility wrapper, and PHP debug.log inspector with AI diagnosis.
* Added Canonical URL Engine for all page types.
* Added Google Discover Robots Meta (`max-image-preview:large`).
* Added BreadcrumbList JSON-LD Schema.
* Added Article E-E-A-T Schema with author (Person), dates, and mainEntityOfPage.
* Enriched LocalBusiness Schema with GeoCoordinates, Opening Hours, Price Range, and sameAs.
* Added auto-creation of IndexNow verification key file in site root.
* Refactored Schema detection to evaluate postmeta rich snippet settings and auto-injected schema.

= 2.1.0 =
* Added Lazy Load Images & Media Speed Optimizer module with LCP Hero Guard
* Added 1-Click Executive Audit PDF Report Generator for client presentations
* Added AI SEO Assistant & Copilot powered by Groq AI and Google Gemini AI
* Added Native WordPress Dashicons Design System across all module cards
* Upgraded Media Scanner with 48x48 thumbnail lightbox preview & intelligent fallback Alt text generator
* Upgraded Double-Domain Link Typo Cleaner across .com, .org, .net, and .ae TLDs
* Fixed core wp_update_post title tag synchronization alongside Rank Math and Yoast
