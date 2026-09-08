# 🛡️ All-in-One SEO Fixer & Auditor

**Free, open-source 360° SEO Diagnostic, AI Copilot, Schema Studio, Smart Silo Linking, GEO SEO & Full Technical Suite for ANY WordPress website.**

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%20to%206.8-blue)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://php.net)
[![Author](https://img.shields.io/badge/Author-Abid%20Ali%20Dev-indigo)](https://abidalidev.com)
[![Version](https://img.shields.io/badge/Version-3.0.0-green)](https://github.com/abidalidevv/all-seo-fixer)

---

## 🚀 Overview

**All-in-One SEO Fixer & Auditor** is a complete, professional-grade WordPress SEO and technical diagnostic toolkit built for site owners, developers, and SEO agencies. Unlike traditional plugins that lock essential features behind expensive recurring subscriptions, artificial site limits, or aggressive upselling, this plugin is **100% free and open-source forever under GPL-2.0**.

---

## 🌟 Feature Modules

### 🧙 Setup Wizard (New in v3.0)
Guided 5-step onboarding wizard on first activation:
- **Step 1** — Brand Profile & Industry Niche
- **Step 2** — Business & Local SEO (phone, address, city, GPS coordinates)
- **Step 3** — Social & OpenGraph Branding (logo, OG image, Facebook, Instagram, Twitter, LinkedIn, YouTube, GMB)
- **Step 4** — AI Engine Keys & Automation (Groq, Gemini, auto-schema, OG, robots)
- **Step 5** — Completion with redirect to dashboard

### 🛡️ 360° SEO Audit Dashboard
One-click site audit covering **24+ critical SEO parameters** with weighted scoring:
- Missing/duplicate title tags & meta descriptions
- Missing H1/H2 headings (Elementor-aware)
- Image alt texts
- Schema JSON-LD & Open Graph presence
- Robots.txt & Sitemap validation (live HTTP check)
- E-E-A-T signals (author bio, authoritative citations)
- Accessibility & ARIA attributes
- Orphan media detection
- Double-domain URL typos (`domain.comhttps://...`)
- Intrusive popup detection
- Downloadable **Executive 4-page PDF Audit Report**

### 🤖 AI SEO Copilot
- **Groq LLaMA-3.3-70B** primary engine (ultra-fast sub-second generation)
- **Google Gemini Flash** secondary engine
- **OpenRouter** failover provider
- Global **Floating AI Assistant Bubble** on every admin screen
- Master AI Prompt JSON export/import for ChatGPT / Claude / Gemini
- AI-powered 1-Click title, meta description & focus keyword generation

### 🔧 On-Page SEO Checker
Per-page deep analysis for all published posts & pages:
- Title tag: missing, too short (<30), too long (>65), **duplicate pair highlighting** (shows both original & duplicate)
- Meta description: missing, short (<70), long (>160), duplicate
- H1: missing, multiple, Elementor JSON detection
- H2 structure for long content
- Image alt text per image
- Thin content (<300 words for posts)
- Internal link count
- Schema JSON-LD presence
- Bulk AI Auto-Fix, single-page AI fix, manual edit modal

### 🔗 Smart Internal Link Silo Engine
Auto-injects contextual internal links from body text to:
- WooCommerce product categories
- Blog categories
- High-value pillar service pages (via focus keywords)
- Max 3 links per page, longest keyword matched first
- Skips: headings, existing `<a>` tags, scripts, buttons, and self-links

### 📍 Schema JSON-LD Studio
Visual builder for:
- Organization / LocalBusiness (with NAP, GeoCoordinates & Opening Hours)
- Article / BlogPosting (with E-E-A-T author, datePublished, dateModified)
- FAQ / HowTo
- Product (WooCommerce)
- BreadcrumbList (auto-generated)
- WebPage / WebSite

### 🌐 GEO & AI Search Hub
- Geo meta tags: `geo.position`, `ICBM`, `geo.placename`, `geo.region`
- OpenGraph place meta tags
- Browser GPS auto-fill for coordinates
- `/llms.txt` & `/llms-full.txt` live publisher for AI search engines (ChatGPT, Perplexity, Claude, Gemini)

### 🎯 Google Search Console & Indexing API
- Live GSC Keyword Rank Tracker (top 50 keywords, clicks, impressions, CTR)
- Google Indexing API v3 (RS256 JWT OAuth 2.0 signed requests)
- IndexNow instant ping for Bing, Yandex, Seznam & Naver

### ⚡ Google PageSpeed Insights & Core Web Vitals
- API v5 integration (desktop & mobile)
- LCP, TBT, CLS, FCP, Speed Index metrics
- Side-by-side benchmark comparison

### 🩺 Console & Error Doctor
- Front-end JavaScript error monitor
- 1-click Mixed Content HTTPS rewriter
- Safe jQuery compatibility layer
- PHP debug.log parser with AI diagnosis

### 🖼️ Lazy Load & Media Optimizer
- Native HTML5 `loading="lazy"` on `<img>` & `<iframe>` tags
- LCP Hero Guard (first above-the-fold image excluded to maintain optimal LCP)

### 🖼️ Media Scanner & Auto-Alt Generator
- Orphan image detection (cross-references content, Elementor JSON, featured images, logos)
- Auto-generates 120–155 char alt text
- Safe trash (moves to WordPress trash, never irreversible deletion)

### 🛠️ Swiss-Knife Diagnostic Tools (19 Live Tools)
DNS Lookup, WHOIS, SSL Check, IP Lookup, Reverse IP, Redirect Chain, HTTP Headers, Broken Link Scanner, Email Extractor, Page Source Viewer, Class-C IP, Blacklist Checker, Keyword Suggester, Page Size Analyzer.

### 🔒 Security Module
- Security Headers audit (HSTS, X-Frame-Options, Referrer-Policy, CSP)
- 1-click auto-inject via `.htaccess`

### 🔗 Broken Link & Typo Cleaner
- Repairs malformed `domain.comhttps://` typos in `wp_posts` & `wp_postmeta`
- `.com`, `.org`, `.net`, `.ae`, and custom TLD support

### 🔀 301 Redirect Manager & 404 Monitor
- DB-driven redirect engine (no `.htaccess` required)
- Real-time 404 hit logger with dedicated indexed DB table

### 🌐 W3C HTML Validator
- Live W3C HTML Nu validation for any URL directly from the plugin dashboard

### 🧹 Speed & Database Optimizer
- Batched (50-item batches) cleanup for revisions, auto-drafts, trashed posts, and spam comments

---

## 🤝 Works With & Compatibility

All-in-One SEO Fixer is engineered to coexist peacefully with existing plugins without collision:
- **SEO Plugins**: Rank Math SEO, Yoast SEO, All in One SEO (AIOSEO), SEOPress (built-in conflict guards prevent duplicate tags)
- **Page Builders**: Elementor & Elementor Pro (JSON-aware heading detection), Divi, Oxygen, Beaver Builder, Gutenberg Block Editor
- **E-Commerce**: WooCommerce (product schema, category silo linking)
- **WordPress**: 5.8 through 6.8+
- **PHP**: 7.4, 8.0, 8.1, 8.2, 8.3+

---

## 📥 Installation

1. Download or clone this repository to `/wp-content/plugins/all-seo-fixer`
2. Go to **Plugins → Installed Plugins** and click **Activate**
3. The **Setup Wizard** launches automatically — complete 5 steps to configure your brand & SEO defaults
4. Navigate to **All SEO Fixer** in the admin sidebar
5. (Optional) Add your free Groq or Gemini API keys in **Settings → AI Engine**

---

## 🔑 Free APIs Used

| Service | Purpose | Get Key |
|---------|---------|---------|
| Groq | AI title/meta generation (LLaMA 3.3-70B) | [console.groq.com](https://console.groq.com/keys) |
| Google Gemini | AI fallback generation | [aistudio.google.com](https://aistudio.google.com) |
| Google PageSpeed Insights | Core Web Vitals testing | [developers.google.com/speed](https://developers.google.com/speed/docs/insights/v5/get-started) |
| Google Search Console | Live rank tracking & top queries | [search.google.com/search-console](https://search.google.com/search-console) |
| Google Indexing API v3 | Instant URL submission | [console.cloud.google.com](https://console.cloud.google.com) |
| IndexNow | Instant ping to Bing, Yandex, Seznam | Built-in auto key generation |
| W3C Validator | Live HTML standards validation | Free public service |

---

## ❓ Frequently Asked Questions

### Is this plugin truly free?
Yes. 100% free and open-source under GPL-2.0. There are no "Pro" locks, no artificial limits, no ads, and no recurring fees.

### Does it work with Rank Math or Yoast already installed?
Yes. The plugin includes automatic conflict detection. When Rank Math or Yoast is active, All-in-One SEO Fixer automatically defers duplicate meta tags while still providing full access to the 360° Audit, Broken Link Cleaner, Console Error Doctor, Media Scanner, Swiss-Knife Tools, and PageSpeed suite.

### Do I need an API key to use the plugin?
No! All core diagnostics, on-page audits, schema tools, redirect managers, broken link cleaners, security headers, and lazy load features function completely without API keys. API keys are strictly optional if you want to enable AI generation (Groq/Gemini), PageSpeed benchmarking, and live GSC rank tracking.

### Will the audit slow down my live website?
No. All scans execute within the WordPress admin dashboard via asynchronous AJAX requests. There is zero impact on your front-end visitors.

### Is the Broken Link Auto-Fixer safe?
Yes. It only repairs verified double-domain syntax errors (e.g. `yourdomain.comhttps://external.com/`) and displays a before/after log of changes.

### Is it safe to trash orphan images?
Yes. Images flagged as unreferenced are moved to the standard WordPress Media Trash rather than permanently deleted, allowing for 1-click restoration if needed.

---

## 📁 Plugin Structure

```
all-seo-fixer/
├── all-seo-fixer.php              ← Bootstrap, activation, constants
├── README.md                      ← Master documentation & quick-start
├── DOCUMENTATION.md               ← Full technical architecture guide
├── documentation.html             ← Standalone interactive HTML docs
├── assets/
│   ├── css/admin.css              ← Premium admin stylesheet
│   └── js/admin.js                ← All admin JavaScript & AJAX
├── includes/
│   ├── class-asf-core.php         ← Always-on hooks (canonical, OG, schema, redirects)
│   ├── class-asf-audit.php        ← 360° SEO audit engine
│   ├── class-asf-handlers.php     ← All AJAX handlers (OnPage, Links, Media, AI, Wizard...)
│   ├── class-asf-pagespeed.php    ← Google PageSpeed Insights API
│   ├── class-asf-schema.php       ← Schema Studio
│   ├── class-asf-geo.php          ← GEO SEO Hub
│   ├── class-asf-gsc.php          ← Google Search Console
│   ├── class-asf-security.php     ← Security Headers
│   ├── class-asf-performance.php  ← DB Optimizer
│   ├── class-asf-swiss-tools.php  ← Swiss-Knife 19 Tools
│   ├── class-asf-w3c.php          ← W3C Validator
│   ├── class-asf-error-doctor.php ← Console Error Doctor
│   ├── class-asf-authority.php    ← Authority & Backlinks
│   └── class-asf-builder.php      ← Page Builder Optimizer
└── admin/
    ├── class-asf-admin.php        ← Menu registration + asset enqueue
    └── views/                     ← Admin page templates (20+ views)
```

---

## 🔄 Changelog

### v3.0.0 — September 2026
- ✅ **NEW:** One-Time Setup Wizard (5-step onboarding)
- ✅ **NEW:** Smart Internal Link Silo Engine (auto-injects contextual links to categories & services)
- ✅ **NEW:** W3C HTML Validator tab
- ✅ **NEW:** Authority & Backlink Hub
- ✅ **NEW:** GEO & AI Search Hub (`/llms.txt` publisher)
- ✅ **NEW:** Google Indexing API v3 (RS256 JWT)
- ✅ **NEW:** Duplicate title modal shows both pages of dup pair with visual badges
- 🐛 **FIX:** `dbDelta()` added to activation hook — 404 log table now properly created
- 🐛 **FIX:** Robots.txt blocking check `stripos === false` comparison
- 🐛 **FIX:** Batch save AJAX invalidates all caches on save
- 🐛 **FIX:** Saved title/meta fields no longer overwritten by AI auto-generate
- ⚡ **IMPROVED:** On-Page scanner detects Elementor `_elementor_data` h1/h2 JSON

### v2.2.0
- Console & Error Doctor, Canonical URL Engine, BreadcrumbList Schema, E-E-A-T Article Schema, IndexNow key auto-creation

### v2.1.0
- Lazy Load, PDF Report, AI Copilot, Media Scanner upgrades, Broken Link Cleaner improvements

---

## 👨‍💻 Author

**Abid Ali Dev** — [abidalidev.com](https://abidalidev.com) | [@abidalidevv](https://github.com/abidalidevv)

## 📄 License

GPL-2.0-or-later — [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html)
