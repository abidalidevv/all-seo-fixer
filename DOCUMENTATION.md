# 🛡️ All-in-One SEO Fixer & Auditor — Technical Documentation & Manual

**Author:** Abid Ali Dev  
**Website:** [abidalidev.com](https://abidalidev.com)  
**GitHub Repository:** [github.com/abidalidevv/all-seo-fixer](https://github.com/abidalidevv/all-seo-fixer)  
**License:** GPL-2.0-or-later  
**Version:** 2.1.0  

---

## 📌 Table of Contents

1. [Overview & Architecture](#overview--architecture)
2. [Key Features & Capabilities](#key-features--capabilities)
3. [Directory & File Structure](#directory--file-structure)
4. [AJAX Endpoints & Security](#ajax-endpoints--security)
5. [Developer Hooks & Filters](#developer-hooks--filters)
6. [API Keys & Configuration](#api-keys--configuration)
7. [Troubleshooting & FAQ](#troubleshooting--faq)

---

## 1. Overview & Architecture

**All-in-One SEO Fixer & Auditor** is a lightweight, high-performance WordPress SEO & Speed Diagnostic plugin designed for site owners, developers, and SEO agencies.

### Core Architectural Principles:
- **Zero Frontend Overhead**: Scans and heavy calculations run exclusively in the WordPress admin panel via asynchronous AJAX requests (`admin-ajax.php`).
- **Strict OOP Modular Design**: Decoupled PHP classes organized into logical includes (`includes/`) and admin views (`admin/views/`).
- **Defensive Safety Guards**: Every PHP file contains `if ( ! defined( 'ABSPATH' ) ) exit;` guards. The main file includes a `PHP_VERSION` guard (PHP 7.4+) to prevent white screens on older servers.
- **WP Standards Compliant**: All DB interactions use `$wpdb->prepare()`, nonces are verified with `wp_verify_nonce()`, and permissions are restricted to users with `manage_options`.

---

## 2. Key Features & Capabilities

### 🛡️ 360° SEO Dashboard
- 1-click comprehensive site diagnostic scanning 24+ critical SEO parameters.
- Analyzes titles, meta descriptions, H1/H2 tags, missing alt text, duplicate content, Schema markup, Open Graph tags, robots.txt, sitemaps, double-domain URL typos, and orphan media.
- Provides a color-coded Action Plan with direct links to edit pages.

### ⚡ Google PageSpeed Insights & Core Web Vitals
- Connects to Google PageSpeed Insights v5 API.
- Evaluates 4 Lighthouse categories (Performance, Accessibility, Best Practices, SEO) with Chart.js visualization.
- Reports Core Web Vitals metrics: **LCP** (Largest Contentful Paint), **TBT** (Total Blocking Time), **CLS** (Cumulative Layout Shift), **FCP** (First Contentful Paint), and **Speed Index**.

### 🧱 Page Builder & Elementor Overhead Optimizer
- Detects active builders: Elementor, Divi, Oxygen, Beaver Builder, WPBakery, Brizy.
- Analyzes database payload size (`_elementor_data` MB) and top 5 heavy DOM pages.
- Provides 1-click options to dequeue unused Elementor icons (`eicons`), disable external Google Fonts, and clear Elementor CSS cache.

### 🚀 Speed & Database Optimizer
- Enforces native HTML5 `loading="lazy"` on all post/page content images and `<iframe>` embeds.
- 1-Click Database Cleanup: Removes post revisions, auto-drafts, trashed posts, spam comments, and expired transients.
- Provides ready-to-use `.htaccess` / Nginx snippets for Gzip, Brotli, and Expire Cache headers.

### 🔧 On-Page SEO Checker
- Per-page audit covering title length (30–60 chars), meta description length (120–155 chars), missing H1/H2 tags, image alt text, thin content (<300 words), Schema markup, internal links count, and `NOINDEX` flags.

### 🔤 Keyword Density & Over-Optimization Analyzer
- N-gram word frequency extractor (1-word and 2-word phrases).
- Calculates keyword density percentages and flags keyword stuffing (>2.5% density) to avoid search engine penalties.

### 📈 Domain Rating (DR/DA) & Link Equity
- On-Page Domain Rating Estimator (0–100) based on internal link graph connectivity, content depth, Schema coverage, and domain security signals.

### 👁️ Live SERP & Social Simulator
- Interactive preview widget simulating how pages will appear on Google Search (Mobile & Desktop) with character & pixel-width meters.
- Live Facebook / Open Graph social sharing card simulator.

### 🔒 Domain Security & Spam Blacklist Audit
- Evaluates HTTPS SSL enforcement, Security Headers (`HSTS`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `CSP`), CDN/WAF detection, and live DNSBL blacklist status (Spamhaus, SpamCop, Barracuda, SORBS).

### 🔗 Broken Link & Double-Domain Typo Cleaner
- Auto-detects and repairs malformed URL typos (e.g., `domain.comhttps://domain.com/`) in post content and Elementor JSON metadata.

### 🖼️ Orphan Media Scanner & Auto Alt-Text Generator
- Cross-references media attachments against post content, Elementor JSON, featured images, and theme logos to identify unused images.
- 1-Click Auto Alt-Text Generator that formats filenames into clean, human-readable Alt text.

### 🔀 301 Redirect Manager
- Manages permanent canonical redirects without modifying `.htaccess`.

---

## 3. Directory & File Structure

```
all-seo-fixer/
├── all-seo-fixer.php           # Main bootstrap file, version check & autoloader
├── readme.txt                  # WordPress.org plugin directory readme
├── README.md                   # GitHub repository readme
├── DOCUMENTATION.md            # Detailed technical manual
├── assets/
│   ├── css/
│   │   └── admin.css           # Premium admin stylesheet & design system
│   └── js/
│       └── admin.js            # Central admin JavaScript & AJAX controller
├── includes/
│   ├── class-asf-core.php      # Always-on protection hooks (RSS noindex, sitemap exclusions)
│   ├── class-asf-audit.php     # 360° SEO audit AJAX handler
│   ├── class-asf-pagespeed.php # Google PageSpeed Insights API handler
│   ├── class-asf-security.php  # Security headers, CDN & DNSBL blacklist handler
│   ├── class-asf-performance.php # Native lazyloading & Database optimizer
│   ├── class-asf-authority.php # Domain Rating (DA) & Keyword Density analyzer
│   ├── class-asf-builder.php   # Page Builder & Elementor overhead optimizer
│   └── class-asf-handlers.php  # OnPage, LinkCleaner, Media, and Pinger handlers
└── admin/
    ├── class-asf-admin.php     # Menu registration & asset enqueue controller
    └── views/                  # UI View Templates
        ├── dashboard.php       # 360° SEO Audit Dashboard
        ├── pagespeed.php       # PageSpeed Insights & Core Web Vitals
        ├── performance.php     # Speed & DB Optimizer
        ├── builder-analyzer.php # Page Builder & Elementor Optimizer
        ├── onpage.php          # On-Page SEO Checker
        ├── keyword-analyzer.php # Keyword Density Analyzer
        ├── authority.php        # Domain Rating (DA) & Link Equity
        ├── serp-preview.php    # Live SERP & Social Simulator
        ├── domain-security.php # Domain & Security Audit
        ├── link-cleaner.php    # Broken Link Cleaner
        ├── media-scanner.php   # Media & Orphan Scanner
        ├── redirects.php       # 301 Redirect Manager
        └── settings.php        # Settings & API Keys
```

---

## 4. AJAX Endpoints & Security

All AJAX actions require `manage_options` capability and a valid `asf_nonce`.

| Action Slug | Description | Class Handler |
|-------------|-------------|---------------|
| `asf_full_360_audit` | Runs site-wide 360° audit | `ASF_Audit` |
| `asf_pagespeed_test` | Queries Google PSI API v5 | `ASF_PageSpeed` |
| `asf_security_audit` | Audits SSL, security headers & DNSBL | `ASF_Security` |
| `asf_optimize_db` | Cleans revisions, drafts, spam comments, transients | `ASF_Performance` |
| `asf_authority_audit` | Calculates Domain Rating (DA/DR) score | `ASF_Authority` |
| `asf_keyword_density` | Analyzes page N-gram keyword density | `ASF_Authority` |
| `asf_builder_scan` | Audits Elementor & Page Builder overhead | `ASF_Builder` |
| `asf_builder_optimize` | Updates Elementor font/icon optimization options | `ASF_Builder` |
| `asf_onpage_scan` | Audits on-page SEO factors for all pages | `ASF_OnPage` |
| `asf_clean_broken_links` | Repairs `comhttps` double-domain typos in DB | `ASF_LinkCleaner` |
| `asf_scan_media` | Scans media library for orphan attachments | `ASF_Media` |
| `asf_trash_media` | Trashes an orphan attachment | `ASF_Media` |
| `asf_auto_alt_media` | Auto-generates missing alt text from filenames | `ASF_Media` |
| `asf_ping_search_engines` | Pings IndexNow API & Google Sitemap ping endpoint | `ASF_Pinger` |

---

## 5. Developer Hooks & Filters

### Built-in Filters Implemented:
- `rank_math/sitemap/exclude_post_type` & `wpseo_sitemap_exclude_post_type`: Excludes page builder CPTs (`elementor_library`, `tahefobu_header`, `tahefobu_footer`, `ct_builder`, `et_pb_layout`, `fl-builder-template`, `brizy-global`) from sitemap indexes.
- `the_content`: Enforces native `loading="lazy"` attribute on content images and iframes.
- `elementor/frontend/print_google_fonts`: Disables Elementor Google Fonts when optimization option is enabled.

---

## 6. API Keys & Configuration

- **Google PageSpeed Insights API Key**: Free API key from [Google Cloud Console](https://console.developers.google.com/apis/credentials). Configured in **All SEO Fixer → Settings**.
- **IndexNow API Key**: Automatically generated based on domain md5 hash (`md5(host)`).

---

## 7. Troubleshooting & FAQ

- **"Plugin file does not exist" error during zip upload**: Ensure the ZIP package contains a single root folder named `all-seo-fixer/` containing `all-seo-fixer.php`.
- **PHP Version Warning**: If the server runs PHP < 7.4, the plugin deactivates safely without throwing a fatal error.
- **Is media trashing safe?**: Yes, attachments are moved to WordPress Trash and can be restored anytime from **Media → Trash**.

---
*Maintained by Abid Ali Dev ([abidalidev.com](https://abidalidev.com))*
