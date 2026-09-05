# 🛡️ All-in-One SEO Fixer & Auditor

**Free, open-source 360° SEO Diagnostic, Console Error Doctor, Speed, Security & AI Copilot Suite for ANY WordPress website.**

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://php.net)
[![Author](https://img.shields.io/badge/Author-Abid%20Ali%20Dev-indigo)](https://abidalidev.com)
[![Version](https://img.shields.io/badge/Version-2.3.0-green)](https://github.com/abidalidevv/all-seo-fixer)

---

## 🚀 Overview

**All-in-One SEO Fixer & Auditor** is a complete, professional-grade WordPress SEO and technical diagnostic toolkit built for site owners, developers, and SEO agencies. Unlike traditional plugins that lock essential diagnostic features behind expensive subscriptions, this plugin is **100% free and open-source forever under GPL-2.0**.

---

## 🌟 20+ Pro Modules & Key Features

1. **🛡️ 360° SEO Audit Dashboard & Executive 4-Page PDF Reports**: One-click site diagnostic covering 24+ critical SEO parameters with a comprehensive 4-page print-ready client audit report.
2. **🤖 Dedicated AI SEO Assistant & Copilot**: Ultra-fast **Groq LLaMA-3.3-70B AI** conversational assistant with live site audit snapshot context and 1-click Master AI Prompt JSON export & import.
3. **💬 Global Floating AI Assistant Bubble**: Ambient pulsing AI assistant widget embedded across all plugin admin screens with slide-out chat drawer and instant prompt chips.
4. **⚙️ Settings Hub with 7 Horizontal Tabs**: Clean WordPress native tabs for General, AI Engine, Local/GEO SEO, Robots/LLMs, System Diagnostics, Pro License, and Developer.
5. **🩺 System Diagnostics & Email Report Dispatcher**: 16-point server environment health scanner, debug log viewer, and 1-click email report sender to developer support.
6. **👑 Pro Licensing & Feature Tiers**: Dummy activation system ready for premium monetization while keeping all core SEO tools free.
7. **📍 Schema JSON-LD Studio**: Visual builder for Organization, LocalBusiness, FAQ, Product, Article, and BreadcrumbList structured data.
8. **🌐 GEO & AI Search Hub**: Generative Engine Optimization for SearchGPT, Perplexity AI & Gemini with live `/llms.txt` publisher.
9. **🛠️ Swiss-Knife Multi-Tools (19 Live Tools)**: Complete diagnostic suite including DNS, WHOIS, SSL, Ping, Traceroute, Port Scanner, and IP Geolocation.
10. **🩺 Console & Error Doctor**: Real-time front-end JavaScript runtime error monitor, Mixed Content auto-fixer, jQuery wrapper, and debug log solver.
11. **🖼️ Media Scanner & Auto-Alt Generator**: Smart 120–155 character alt-text generator with visual gallery and safe orphan media trashing.
12. **🔧 On-Page SEO Checker**: Per-page audit for title length, meta description quality, Elementor headings, and duplicate metadata resolution.
13. **⚡ Google PageSpeed Insights & Core Web Vitals**: Google PageSpeed API v5 integration with desktop & mobile scores (LCP, TBT, CLS).
14. **🖼️ Lazy Load Images & Media Speed Optimizer**: Native HTML5 `loading="lazy"` enforcement on all `<img>` and `<iframe>` tags with LCP Hero Guard.
15. **🎯 Live GSC Keyword Rank Tracker**: Fetch top 50 keywords directly from Google Search Console API with rank, clicks, impressions, and CTR.
16. **🚀 Google Indexing API v3 Integration**: Direct RS256 signed JWT OAuth 2.0 URL submission for instant indexing in Google Search Console.
17. **🧱 Page Builder & Elementor Overhead Optimizer**: Analyzes database JSON payload (`_elementor_data` MB size) and strips unused scripts.
18. **🧹 Speed & Database Optimizer**: 50-item batched cleanup for revisions, auto-drafts, trashed posts, and spam comments without PHP timeouts.
19. **🔒 Security Headers & HSTS Auto-Fix**: 1-click auto-fixer for missing `Strict-Transport-Security`, `X-Frame-Options`, and `Referrer-Policy`.
20. **🔗 Broken Link & Typo Cleaner**: Repairs malformed URL typos (`domain.comhttps://domain.com/`) in post content and Elementor metadata.
21. **🔀 301 Redirects & 404 Monitor**: Manage permanent 301 redirects and monitor 404 hits in real-time.

---

## 📥 Installation

1. Download or clone the plugin directory to `wp-content/plugins/all-seo-fixer`.
2. In your WordPress Admin Dashboard, go to **Plugins → Installed Plugins**.
3. Locate **All-in-One SEO Fixer & Auditor** and click **Activate**.
4. Navigate to **All SEO Fixer** in your WordPress admin sidebar.
5. (Optional) Add your free Google PageSpeed Insights API key, Google Search Console Service Account JSON, or Groq/Gemini API keys in **⚙️ Settings**.

---

## 📂 Project Architecture

```
all-seo-fixer/
├── all-seo-fixer.php              # Main bootstrap file, activation hook & table creation
├── readme.txt                     # WordPress.org plugin directory readme
├── README.md                      # Single Master Documentation (User + Developer Guide)
├── documentation.html             # Full offline HTML User & Developer Manual
├── assets/
│   ├── css/admin.css              # Premium WordPress Admin Native stylesheet
│   └── js/admin.js                # Central AJAX handlers, modal engine & UI bindings
├── includes/
│   ├── class-asf-core.php         # Canonical tags, Discover robots meta, 404 logger & Schema/OG injector
│   ├── class-asf-audit.php        # 360° SEO audit, E-E-A-T & Accessibility inspector
│   ├── class-asf-error-doctor.php # Console error monitor, HTTPS rewriter, jQuery wrapper & debug log parser
│   ├── class-asf-pagespeed.php    # Google PageSpeed Insights API handler
│   ├── class-asf-security.php     # Security headers, HSTS auto-fixer, CDN & DNSBL handler
│   ├── class-asf-performance.php  # 50-item batched DB optimizer & speed utilities
│   ├── class-asf-authority.php    # On-Page Health Score & Keyword Density handler
│   ├── class-asf-builder.php      # Page Builder & Elementor bloat optimizer
│   ├── class-asf-gsc.php          # Google OAuth JWT bearer token & Indexing API / Rank Tracker
│   ├── class-asf-w3c.php          # W3C Nu HTML Checker API validator
│   └── class-asf-handlers.php     # OnPage, LinkCleaner, Media, AI Chatbot, Pinger & AutoFixers
└── admin/
    ├── class-asf-admin.php        # 17-Menu registration & asset enqueue controller
    └── views/                     # 17 UI View templates (dashboard, error-doctor, onpage, etc.)
```

---

## 💻 Developer Guide & Security Standards

### 🛡️ Security Implementation
- **Capability Check**: Every AJAX endpoint verifies `manage_options` capability via `asf_cap_check()`.
- **Nonce Verification**: All requests check CSRF nonces via `asf_check_nonce()`.
- **SQL Injection Prevention**: All custom database queries use `$wpdb->prepare()` with explicit parameter placeholders (`%s`, `%d`).
- **Input Sanitization**: User inputs are sanitized using `sanitize_text_field()`, `esc_url_raw()`, and `trim()`.

### 🔄 DB Optimizer Batching
Database optimization runs in batches of 50 records per request via `wp_delete_post()` and `wp_delete_comment()` to prevent PHP `max_execution_time` timeouts on large databases. A maximum guard limit of 50 batches (2,500 records) protects against infinite loops.

### 🔑 Google OAuth JWT Caching
Google Access Tokens generated via RS256 signed JWT assertions are cached in a site-scoped transient (`asf_gsc_token_...`) for 50 minutes to avoid hitting Google OAuth rate limits.

---

## 🌐 Free APIs Integrated

| API / Service | Cost | Function |
|---------------|------|----------|
| **Google Search Console API** | Free | Instant Indexing API v3 & 30-Day Keyword Rank Tracking |
| **Google PageSpeed Insights v5** | Free (25,000 req/day) | Core Web Vitals & Lighthouse Scores |
| **Groq AI / Gemini / OpenRouter** | Free Tiers | AI SEO Assistant, Prompt Exporter & Error Doctor |
| **W3C Nu HTML Checker API** | Free | Real-time HTML syntax validation |
| **IndexNow API** | Free | Instant Bing, Yandex, Naver & Seznam re-indexing |

---

## 📝 Changelog

### Version 2.2.0
- **Console & Error Doctor Module (`asf-error-doctor`)**: Added dedicated browser console monitor, 1-click Mixed Content HTTPS rewriter, safe jQuery compatibility wrapper, and PHP debug.log inspector with AI diagnosis.
- **Canonical URL Engine**: Added automated `<link rel="canonical">` tag generation for singular posts, pages, categories, taxonomies, archives, and homepages.
- **Google Discover Robots Meta**: Added `<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">` to unlock Google Discover feed eligibility.
- **BreadcrumbList Schema**: Automated hierarchical JSON-LD breadcrumb markup (Home → Category → Page).
- **Article E-E-A-T Schema**: Enriched Article schema with `author` (Person), `datePublished`, `dateModified`, and `mainEntityOfPage`.
- **LocalBusiness Schema Enrichment**: Added GeoCoordinates, Opening Hours, Price Range, and Social `sameAs` support.
- **IndexNow Verification Key Auto-Creation**: Automatically generates site root `.txt` key verification file for IndexNow submissions.
- **Accurate Schema Detection**: Refactored audit logic to evaluate postmeta rich snippet settings and auto-injected schema rather than raw post_content.
- **Keyword Density Fix**: Resolved `$_GET` vs `$_REQUEST` parameter handling for seamless AJAX execution.

### Version 2.1.0 (Product Hunt Release)
- **Lazy Load Images & Media Speed Optimizer**: Enforces native HTML5 `loading="lazy"` on all content images (`<img>`) and video embeds (`<iframe>`) with LCP Hero Guard.
- **1-Click Executive Audit PDF Reports**: Print-ready executive client PDF audit report generator with before & after optimization scores.
- **AI SEO Assistant & Copilot**: Integrated Groq AI (`openai/gpt-oss-120b`) & Google Gemini AI chatbot providing instant custom SEO recommendations.
- **Native WordPress Dashicons Design System**: Crisp vector icons across all module cards & tools.
- **Media Scanner Upgrades**: 48x48 thumbnail image lightbox preview & intelligent fallback Alt text generator for numeric filenames (`83746874365.png`).
- **Double-Domain Link Typo Repair**: Scans both `wp_posts.post_content` and `wp_postmeta` across `.com`, `.org`, `.net`, `.ae` TLDs.
- **Title Synchronization**: Core `wp_update_post` title tag updates synchronized alongside Rank Math & Yoast SEO postmeta.

---

## 📜 License

Distributed under the **GPL-2.0-or-later** License. See `LICENSE` for details.

---

## 👨‍💻 Author

Built with ❤️ by **Abid Ali Dev**  
- Website: [https://abidalidev.com](https://abidalidev.com)  
- GitHub: [@abidalidevv](https://github.com/abidalidevv)
