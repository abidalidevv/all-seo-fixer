# 🛡️ All-in-One SEO Fixer & Auditor

**Free, open-source 360° SEO Diagnostic, Speed & Security Engine for ANY WordPress website.**

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://php.net)
[![Author](https://img.shields.io/badge/Author-Abid%20Ali%20Dev-indigo)](https://abidalidev.com)

---

## 🚀 Overview

**All-in-One SEO Fixer & Auditor** is a complete, professional-grade WordPress SEO toolkit built for site owners, developers, and SEO agencies. Unlike traditional plugins that lock essential diagnostic features behind expensive subscriptions, this plugin is **100% free and open-source under GPL-2.0**.

---

## 🌟 Key Features

- **🛡️ 360° SEO Audit Dashboard & Executive PDF Reports**: One-click site diagnostic covering 24+ critical SEO parameters with 1-click **Download Executive Audit PDF Report** for client presentations.
- **📄 Download Audit PDF Reports**: Generates print-ready executive client PDF audit reports featuring before & after optimization scores.
- **🖼️ Lazy Load Images & Media Speed Optimizer**: Native HTML5 `loading="lazy"` enforcement on all `<img>` and `<iframe>` tags with LCP Hero Guard.
- **🤖 AI SEO Assistant & Copilot**: Integrated Groq AI & Google Gemini AI chatbot assistant providing instant custom SEO recommendations and CWV guides.
- **🎯 Live GSC Keyword Rank Tracker**: Fetch top 50 search keywords directly from Google Search Console API with real average position (Rank #1–100), clicks, impressions, and CTR.
- **🚀 Google Indexing API v3 Integration**: Direct RS256 signed JWT OAuth 2.0 URL submission for instant indexing in Search Console.
- **⚡ Google PageSpeed Insights & Core Web Vitals**: Integrated with Google's free PageSpeed v5 API. Reports scores (0–100) and Core Web Vitals (LCP, TBT, CLS, FCP, Speed Index).
- **📍 Local SEO & LocalBusiness Schema**: Built-in Name, Address, and Phone (NAP) settings with automatic `LocalBusiness` JSON-LD schema injection.
- **🎓 E-E-A-T Signals Inspector**: Detects Author Bio boxes, `Person` JSON-LD schema, and counts authoritative external citations (`.gov`, `.edu`, `wikipedia.org`, `webmd.com`).
- **♿ Accessibility & Intrusive Pop-up Inspector**: Detects missing ARIA labels on buttons/inputs and intrusive fixed overlay containers (`z-index: 9999+`) that trigger mobile penalties.
- **🚨 Real-Time 404 URL Monitor**: Dedicated custom database table (`wp_asf_404_logs`) tracking missing page hits with a 1-click **Create 301 Redirect** button.
- **🧱 Page Builder & Elementor Overhead Optimizer**: Detects active page builders, analyzes database JSON payload (`_elementor_data` MB size), identifies heavy DOM pages, and dequeues unused font/icon scripts.
- **🧹 Speed & Database Optimizer**: Batch-processed (50 items/batch) cleanup for revisions, auto-drafts, trashed posts, spam comments, and expired transients without PHP timeouts.
- **🔒 Security Headers & HSTS Auto-Fix**: 1-click auto-fixer for missing `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, and `Referrer-Policy` headers.
- **🔤 Keyword Density & Over-Optimization Analyzer**: N-gram word frequency analyzer with keyword stuffing alerts (>2.5% density).
- **📈 On-Page SEO Health Score & Link Equity Graph**: On-Page health score (0–100) based on link graph connectivity and content depth.
- **👁️ Live SERP & Social Card Simulator**: Real-time Google Search (Desktop & Mobile) and Facebook share card preview widget with character & pixel-width meters.
- **🔗 Broken Link & Double-Domain Typo Cleaner**: Auto-detects and repairs malformed URL typos (`domain.comhttps://domain.com/`) in post content and Elementor metadata.
- **🖼️ Media Scanner & Auto Alt-Text Generator**: Finds unused orphan media files and auto-generates clean Alt text from filenames with thumbnail lightbox preview.
- **🔀 301 Canonical Redirect Manager**: Manages permanent canonical 301 redirects without editing `.htaccess`.
- **🚀 IndexNow & Sitemap Pinger**: Instant re-indexing alerts to Google, Bing, Yandex, and Naver.

---

## 📥 Installation

1. Download or clone the plugin directory to `wp-content/plugins/all-seo-fixer`.
2. In your WordPress Admin Dashboard, go to **Plugins → Installed Plugins**.
3. Locate **All-in-One SEO Fixer & Auditor** and click **Activate**.
4. Navigate to **All SEO Fixer** in your WordPress admin sidebar.
5. Configure your free Google PageSpeed Insights API key and Google Search Console Service Account JSON in **⚙️ Settings**.

---

## 📂 Project Architecture

```
all-seo-fixer/
├── all-seo-fixer.php           # Main bootstrap file, activation hook & table creation
├── readme.txt                  # WordPress.org plugin directory readme
├── README.md                   # Single Master Documentation (User + Developer Guide)
├── assets/
│   ├── css/admin.css           # Premium responsive admin stylesheet
│   └── js/admin.js             # Central admin JavaScript, modal engine & AJAX handlers
├── includes/
│   ├── class-asf-core.php      # 404 logger, SEO duplication guard & Schema/OG injector
│   ├── class-asf-audit.php     # 360° SEO audit, E-E-A-T & Accessibility inspector
│   ├── class-asf-pagespeed.php # Google PageSpeed Insights API handler
│   ├── class-asf-security.php  # Security headers, HSTS auto-fixer, CDN & DNSBL handler
│   ├── class-asf-performance.php # 50-item batched DB optimizer & lazyloading engine
│   ├── class-asf-authority.php # On-Page Health Score & Keyword Density handler
│   ├── class-asf-builder.php   # Page Builder & Elementor bloat optimizer
│   ├── class-asf-gsc.php       # Google OAuth JWT bearer token & Indexing API / Rank Tracker
│   ├── class-asf-w3c.php       # W3C Nu HTML Checker API validator
│   └── class-asf-handlers.php  # OnPage, LinkCleaner, Media, AutoFixers & Pinger handlers
└── admin/
    ├── class-asf-admin.php     # Admin menu registration & asset enqueue controller
    └── views/                  # UI View templates (dashboard, pagespeed, redirects, etc.)
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
| **W3C Nu HTML Checker API** | Free | Real-time HTML syntax validation |
| **IndexNow API** | Free | Instant Bing, Yandex & Naver re-indexing |
| **Google Sitemap Ping** | Free | Direct Google sitemap update notification |

---

## 📝 Changelog

### Version 2.1.0 (Product Hunt Release)
- **Lazy Load Images & Media Speed Optimizer**: Enforces native HTML5 `loading="lazy"` on all content images (`<img>`) and video embeds (`<iframe>`) with LCP Hero Guard.
- **1-Click Executive Audit PDF Reports**: Print-ready executive client PDF audit report generator with before & after optimization scores.
- **AI SEO Assistant & Copilot**: Integrated Groq AI (`openai/gpt-oss-120b`) & Google Gemini AI chatbot providing instant custom SEO recommendations.
- **Native WordPress Dashicons Design System**: Crisp vector icons across all 13+ module cards & tools.
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
