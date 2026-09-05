# 🛡️ All-in-One SEO Fixer & Auditor — Master Technical Documentation

> **Version**: 2.3.0  
> **Author**: [Abid Ali](https://abidalidev.com)  
> **License**: GPL-2.0-or-later  
> **Repository**: [github.com/abidalidevv/all-seo-fixer](https://github.com/abidalidevv/all-seo-fixer)  
> **Support**: [support@abidalidev.com](mailto:support@abidalidev.com)

---

## 📑 Table of Contents

1. [Architectural Overview](#1-architectural-overview)
2. [Complete 20-Module System Index](#2-complete-20-module-system-index)
3. [🤖 AI SEO Assistant & Copilot Page](#3--ai-seo-assistant--copilot-page)
4. [💬 Global Floating AI Assistant Bubble](#4--global-floating-ai-assistant-bubble)
5. [⚙️ Settings Hub with Horizontal Tabs](#5-️-settings-hub-with-horizontal-tabs)
6. [🩺 System Diagnostics & Email Dispatcher](#6--system-diagnostics--email-dispatcher)
7. [👑 Pro Licensing & Monetization Matrix](#7--pro-licensing--monetization-matrix)
8. [📊 360° SEO Audit Engine & 4-Page PDF Reports](#8--360-seo-audit-engine--4-page-pdf-reports)
9. [📍 Schema JSON-LD Studio & GEO Hub](#9--schema-json-ld-studio--geo-hub)
10. [🛠️ Swiss-Knife 19-in-1 Diagnostic Suite](#10-️-swiss-knife-19-in-1-diagnostic-suite)
11. [💻 Developer API, AJAX Endpoints & Hooks](#11--developer-api-ajax-endpoints--hooks)
12. [🔒 Security, Nonces & Capability Enforcement](#12--security-nonces--capability-enforcement)

---

## 1. Architectural Overview

**All-in-One SEO Fixer** is engineered from the ground up as a native WordPress plugin without external bloated React or Vue builds. It leverages the clean, responsive **WordPress Admin Native Design System** (`.wrap`, `.asf-wrap`, `.asf-card`, `.nav-tab-wrapper`, `.form-table`, `.button-primary`), ensuring 100% theme compatibility, sub-50ms rendering speeds, and minimal memory overhead.

### Directory Structure

```
all-seo-fixer/
├── all-seo-fixer.php              # Main bootstrap, options registry & class loader
├── readme.txt                     # WordPress.org plugin repository directory specifications
├── README.md                      # Quick-start documentation
├── DOCUMENTATION.md               # Master technical architectural manual
├── documentation.html             # Offline graphical documentation browser
├── assets/
│   ├── css/admin.css              # Universal design system, responsive grids & floating AI widgets
│   └── js/admin.js                # Central AJAX request dispatcher, modal manager & tab switchers
├── includes/
│   ├── class-asf-core.php         # Canonical tags, robots meta, 404 logger, virtual /robots.txt & /llms.txt
│   ├── class-asf-audit.php        # 360° SEO crawler, E-E-A-T evaluator & accessibility auditor
│   ├── class-asf-handlers.php     # OnPage, Media, LinkCleaner, AI Chatbot, Diagnostics & Licensing
│   ├── class-asf-pagespeed.php    # Google PageSpeed Insights API v5 & Core Web Vitals handler
│   ├── class-asf-security.php     # HTTP Security Headers, HSTS, DNSBL & SSL inspectors
│   ├── class-asf-performance.php  # Batched (50/batch) database optimizer & transient cleaner
│   ├── class-asf-authority.php    # Content depth, readability, and authority analyzer
│   ├── class-asf-builder.php      # Page builder & Elementor JSON payload bloat optimizer
│   ├── class-asf-gsc.php          # Google OAuth 2.0 JWT assertion & Instant Indexing API v3
│   ├── class-asf-w3c.php          # W3C Nu HTML Checker API integration
│   ├── class-asf-error-doctor.php # Live browser JS error monitor & debug.log analyzer
│   ├── class-asf-schema.php       # Schema JSON-LD Studio (Organization, LocalBusiness, FAQ, Product...)
│   └── class-asf-geo.php          # GEO Search Engine Optimization & /llms.txt generator
└── admin/
    ├── class-asf-admin.php        # Submenu registrations, view dispatcher & floating AI footer hook
    └── views/                     # 20 view templates:
        ├── dashboard.php          # 360° Audit Dashboard & SEO Tools Command Center
        ├── ai-assistant.php       # Dedicated AI Assistant & Copilot with Master JSON Importer
        ├── settings.php           # 7 Horizontal Tabs (General, AI, GEO, Robots, Diagnostics, License, Dev)
        ├── onpage.php             # Per-page title/meta audit & Elementor clean-up
        ├── media-scanner.php      # Alt-text generator & orphan media remover
        ├── pagespeed.php          # Live CWV (LCP, CLS, TBT) & Lighthouse scoring
        ├── schema-studio.php      # Visual JSON-LD schema builder & validator
        ├── geo-hub.php            # Generative Engine Optimization for SearchGPT & Perplexity
        ├── error-doctor.php       # Front-end JavaScript console monitor & debug log solver
        ├── swiss-tools.php        # 19-in-1 Network, DNS, WHOIS & HTTP diagnostic hub
        ├── redirects.php          # 301 Redirect Rules & 404 live monitor
        ├── link-cleaner.php       # Broken link & malformed double-domain typo fixer
        ├── lazy-load.php          # Native HTML5 lazy loading with LCP Hero Guard
        ├── performance.php        # 50-batch DB table optimizer & revision pruner
        ├── builder-analyzer.php   # Elementor JSON MB footprint & bloat stripper
        ├── gsc-inspector.php      # Google Search Console keyword rank tracker
        ├── w3c-validator.php      # Nu HTML validator with line-by-line syntax feedback
        ├── keyword-analyzer.php   # N-gram keyword density & stuffing detector
        ├── authority.php          # Readability score, heading structure & E-E-A-T signals
        └── serp-preview.php       # Desktop/Mobile Google SERP simulator & Facebook card preview
```

---

## 2. Complete 20-Module System Index

| # | Page Slug | Menu Title | Core Engine |
|---|-----------|------------|-------------|
| 1 | `asf-panel` | 360° SEO Audit | `ASF_Audit` (`class-asf-audit.php`) |
| 2 | `asf-onpage` | On-Page Checker | `ASF_OnPage` (`class-asf-handlers.php`) |
| 3 | `asf-pagespeed` | PageSpeed Insights | `ASF_PageSpeed` (`class-asf-pagespeed.php`) |
| 4 | `asf-lazy-load` | Lazy Load Images | `ASF_LazyLoad` (`class-asf-handlers.php`) |
| 5 | `asf-media` | Media Scanner | `ASF_Media` (`class-asf-handlers.php`) |
| 6 | `asf-performance` | Speed & DB Optimizer | `ASF_Performance` (`class-asf-performance.php`) |
| 7 | `asf-builder-analyzer` | Page Builder Optimizer | `ASF_Builder` (`class-asf-builder.php`) |
| 8 | `asf-gsc-inspector` | Google Search Console | `ASF_GSC` (`class-asf-gsc.php`) |
| 9 | `asf-w3c-validator` | W3C HTML Validator | `ASF_W3C` (`class-asf-w3c.php`) |
| 10 | `asf-keyword-analyzer` | Keyword Density | `ASF_KeywordDensity` (`class-asf-handlers.php`) |
| 11 | `asf-authority` | On-Page SEO Health | `ASF_Authority` (`class-asf-authority.php`) |
| 12 | `asf-serp-preview` | SERP Simulator | SERP Simulator UI (`serp-preview.php`) |
| 13 | `asf-security` | Security & Headers | `ASF_Security` (`class-asf-security.php`) |
| 14 | `asf-error-doctor` | Console & Error Doctor | `ASF_ErrorDoctor` (`class-asf-error-doctor.php`) |
| 15 | `asf-link-cleaner` | Broken Link Cleaner | `ASF_LinkCleaner` (`class-asf-handlers.php`) |
| 16 | `asf-redirects` | 301 Redirects | `ASF_Core` (`class-asf-core.php`) |
| 17 | `asf-schema` | Schema JSON-LD Studio | `ASF_Schema` (`class-asf-schema.php`) |
| 18 | `asf-geo` | GEO & AI Search Hub | `ASF_GEO` (`class-asf-geo.php`) |
| 19 | `asf-ai-assistant` | 🤖 AI Assistant | `ASF_AIChatbot` (`class-asf-handlers.php`) |
| 20 | `asf-swiss-tools` | 🛠️ Swiss-Knife Tools | `ASF_SwissTools` (`class-asf-handlers.php`) |
| 21 | `asf-settings` | Settings | `ASF_Diagnostics` (`class-asf-handlers.php`) |

---

## 3. 🤖 AI SEO Assistant & Copilot Page

Located at **All SEO Fixer → 🤖 AI Assistant** (`admin.php?page=asf-ai-assistant`):

### Core Architecture
- **Primary AI Provider**: **Groq AI** using model `llama-3.3-70b-versatile` with automatic fallback to `llama-3.1-8b-instant`.
- **Failover Routing**: If Groq is unavailable, requests seamlessly fail over to **Google Gemini 1.5 Flash** → **OpenRouter LLaMA-3.3** → **Built-in Offline SEO Heuristic Engine**.
- **Context Injection**: Every prompt is automatically pre-pended with a deep, structured JSON snapshot of the WordPress site:
  - Technical audit scores & health grade
  - Missing titles, bad meta descriptions & missing alt text counts
  - Public posts/pages inventory with titles, permalinks, word counts, and headings
  - Active schema markup & Open Graph tags
  - Core Web Vitals status

### Master AI Prompt & External LLM Workflow
1. **Download Master AI Prompt (JSON)**:
   - Endpoint: `asf_export_ai_prompt`
   - Exports complete site profile, Business NAP, technical SEO flags, full page directory, and explicit JSON output instructions.
2. **External Model Optimization**:
   - Provide the file to ChatGPT Plus (GPT-4o), Claude 3.5 Sonnet, or Google Gemini 1.5 Pro.
3. **Import & Apply External AI JSON Config**:
   - Endpoint: `asf_import_ai_config`
   - Paste returned JSON into the console and click **⚡ Apply AI JSON Settings to Site**.
   - Mass-updates titles (`_asf_seo_title`), descriptions (`_asf_meta_description`), HSTS, and lazy loading in under 2 seconds.

---

## 4. 💬 Global Floating AI Assistant Bubble

Rendered in the WordPress admin footer across **all plugin pages** (`page=asf-*`):

### Visual & Interactive Behavior
- **Trigger Button** (`#asf-floating-ai-trigger`):
  - Fixed at `bottom: 24px; right: 24px; z-index: 999990;`
  - Vibrant gradient (`#2563eb` to `#7c3aed`) with an active green pulsing status badge (`.asf-ai-badge-dot`).
  - Subtle breathing animation (`@keyframes asfFloatPulse`).
- **Chat Drawer** (`#asf-floating-ai-drawer`):
  - Fixed at `bottom: 88px; right: 24px; width: 390px; height: 540px;`
  - Header displays active model status (`🟢 Groq AI LLaMA 3.3`), external link to open full-page copilot, and minimize button.
  - Horizontal chip carousel for instant 1-click questions (Audit Fixes, Meta Descs, Speed & CWV, Local Schema).
  - Clean conversational speech bubbles with user right-alignment and bot left-alignment.
  - Enter key listener and Send button with loading spinner.

---

## 5. ⚙️ Settings Hub with Horizontal Tabs

Located at **All SEO Fixer → Settings** (`admin.php?page=asf-settings`):

The settings page features WordPress native `.nav-tab-wrapper` horizontal tabs for clean organization:

1. **⚙️ General & APIs**:
   - Homepage SEO Meta Description (120–155 chars)
   - Google PageSpeed Insights API Key (25,000 req/day quota)
   - Google Search Console Integration (OAuth 2.0 Web Client ID/Secret & Service Account JSON)
2. **🤖 AI Engine & Keys**:
   - Groq AI API Key (Default Primary Provider, pre-configured out of the box)
   - Google Gemini Flash API Key (Failover Provider)
   - OpenRouter API Key (Alternative Failover)
3. **📍 Local & GEO SEO**:
   - Live Geotargeting toggle (`geo.position`, `geo.placename`, `geo.region`, `ICBM`)
   - Schema.org `LocalBusiness` entity selector (13 business types)
   - GPS coordinate inputs with **📍 Auto-Detect via Browser GPS** button
   - Google Maps profile URL, Opening hours, and `sameAs` directory profiles
4. **🤖 Robots & LLMs.txt**:
   - Virtual `robots.txt` live editor with dynamic crawler serving
   - 2026 AI Search Crawler `/llms.txt` publisher (SearchGPT, Perplexity AI, ClaudeBot, Google-Extended)
5. **🩺 System Diagnostics & Logs**:
   - Complete server health inspector, debug log viewer, and report exporter (see Section 6)
6. **👑 Pro License & Plans**:
   - Product key input, activation/deactivation handler, and Free vs Pro comparison matrix (see Section 7)
7. **👨‍💻 About Developer**:
   - Creator profile for Abid Ali (abidalidev.com), support channels, and portfolio links

> [!NOTE]
> Settings submissions automatically preserve the active tab using `#asf_active_tab` and URL hash sync (`#tab-diagnostics`), preventing unwanted tab resets on save.

---

## 6. 🩺 System Diagnostics & Email Dispatcher

Built into the **System Diagnostics & Logs** tab on the Settings page:

### Diagnostic Checklist
The engine executes 16+ real-time checks across 6 critical categories:
- **Environment**: PHP Version (>= 8.0 recommended), PHP Memory Limit (>= 256M recommended), Max Execution Time (>= 60s).
- **PHP Extensions**: `curl` (API communication), `openssl` (SSL), `simplexml` (Sitemaps), `dom` (HTML parsing), `mbstring` (UTF-8 manipulation), `json` (Schema & API).
- **WordPress Core**: WP Version, HTTPS/SSL active, Search-engine friendly permalinks, Multisite status.
- **Database**: MySQL/MariaDB version, table prefix, collation.
- **Filesystem**: `wp-content/uploads` write permissions, site root write status.
- **Plugin Health**: Last audit cached data, Groq API status, PageSpeed key status, virtual robots/llms status, active license.
- **Recent Error Logs**: Extracts and displays the last 25 lines from `wp-content/debug.log` (if enabled).

### Report Actions
- **🔍 Run Live Diagnostics Scan**: Dispatches AJAX action `asf_run_diagnostics`, computes 0–100% health score, and colors the status ring.
- **📋 Copy System Report**: Copies complete Markdown/ASCII diagnostic log to the clipboard for support tickets.
- **📥 Download System Log (.txt)**: Generates timestamped `.txt` download (`asf-system-diagnostics-YYYY-MM-DD.txt`).
- **✉️ Send Diagnostic Report to Developer**: Dispatches AJAX action `asf_send_diagnostic_report` via `wp_mail()` to `support@abidalidev.com` or custom admin email with user-supplied notes.

---

## 7. 👑 Pro Licensing & Monetization Matrix

Managed in the **Pro License & Plans** tab:

### Activation Mechanism
- Users can enter a license key (`ASF-PRO-XXXX-XXXX-XXXX-XXXX`).
- AJAX endpoint `asf_save_license` validates and updates `asf_license_status` to `'active'` and sets `asf_license_type` to `'PRO Lifetime Unlimited'`.
- Instant deactivation resets the site to `'free'` edition with zero data loss.

### Feature Comparison Matrix

| Feature | Free Standard Edition | PRO Enterprise Edition ⚡ |
|---------|-----------------------|---------------------------|
| **Full 360° SEO Site Audit** | Unlimited Manual Audits | Unlimited + Automated Daily Cron |
| **Groq LLaMA-3.3 AI Copilot** | Included (Llama 3.3) | Unlimited + GPT-4o & Claude 3.5 |
| **On-Page Meta Autofixer** | Up to 250 Posts/Pages | Unlimited Posts, Pages & CPTs |
| **Executive PDF Audit Reports** | Standard ASF Branding | 100% White-Label (Custom Logo & Colors) |
| **Google Instant Indexing API** | Manual URL Submission | Auto-Ping on Post Publish |
| **Schema JSON-LD Studio** | All 7 Schema Types | Multi-Location & Deep WooCommerce Schema |
| **Console Error Doctor** | Included | Real-Time Slack & Webhook Alerts |
| **Developer Support** | Community Support | Priority 24/7 Direct Ticket Support |

---

## 8. 📊 360° SEO Audit Engine & 4-Page PDF Reports

Located at **All SEO Fixer → 360° SEO Audit** (`admin.php?page=asf-panel`):

### Audit Engine
- Evaluates 24+ technical SEO parameters across content, headings, meta tags, indexability, security, and schema.
- Accurately parses Elementor headings (`_elementor_data` JSON `"tag":"h1"`) to prevent false-positive "Missing H1" alerts.
- Checks both physical and virtual `robots.txt` and `sitemap.xml`.

### Executive 4-Page PDF Audit Report
Overhauled to generate a print-ready, publication-grade executive audit report with `@page { size: A4 portrait; }` and `.page-break`:
- **Page 1**: Executive Overview, Overall Health Score, KPI Snapshot (Posts, Pages, Media, Redirects), and Core Web Vitals.
- **Page 2**: On-Page Metadata Integrity, Title Lengths, Meta Description Compliance, and Elementor Heading Hierarchy.
- **Page 3**: Media Optimization, Alt-Text Coverage, Orphan Files, and Schema JSON-LD Structured Data Coverage.
- **Page 4**: Technical Crawlability, Security Headers, and Prioritized Remediation Roadmap (High, Medium, Low actions).

---

## 9. 📍 Schema JSON-LD Studio & GEO Hub

### Schema JSON-LD Studio (`admin.php?page=asf-schema`)
Allows visual configuration and automatic `<head>` injection of valid Schema.org entities:
- **Organization**: Name, Logo, URL, Social Profiles (`sameAs`).
- **LocalBusiness**: GeoCoordinates (Lat/Lng), Address, OpeningHours, Telephone, PriceRange, Map Link.
- **WebSite & Sitelinks Searchbox**: Generates `SearchAction` for Google Sitelinks Search.
- **Article & BlogPosting**: Author Person schema, dates published/modified, and featured image publisher.
- **FAQPage**: Dynamic question and answer pairs.
- **Product**: Price, currency, availability, and aggregate rating.
- **BreadcrumbList**: Hierarchical navigational taxonomy.

### GEO & AI Search Hub (`admin.php?page=asf-geo`)
Built for Generative Engine Optimization (SearchGPT, Perplexity AI, Google Gemini, Claude):
- Serves virtual `/llms.txt` directly to AI web crawlers.
- Includes 1-Click Auto-Regenerator (`asf_regenerate_llms_txt`) that crawls the site and formats Markdown documentation with site summary, URLs, and AI permissions.

---

## 10. 🛠️ Swiss-Knife 19-in-1 Diagnostic Suite

Located at **All SEO Fixer → 🛠️ Swiss-Knife Tools** (`admin.php?page=asf-swiss-tools`):

19 live diagnostic tools with zero third-party dependencies:
1. **DNS Records Lookup**: A, AAAA, MX, TXT, CNAME, NS via `dns_get_record()`.
2. **WHOIS Domain Lookup**: Live port 43 socket connection to IANA and TLD registries.
3. **HTTP Status & Header Inspector**: Full response headers, status code, redirects, and protocol.
4. **SSL / TLS Certificate Inspector**: Issuer, validity dates, cipher suite, and expiry countdown.
5. **Reverse IP & Shared Hosting Checker**: Discovers co-hosted domains on the server IP.
6. **Ping & Server Latency Tester**: ICMP ping simulation with packet loss and millisecond latency.
7. **Traceroute & Network Hop Map**: Step-by-step route visualization to the target host.
8. **Port Scanner**: Tests common ports (21, 22, 25, 80, 443, 3306, 8080) for open/closed states.
9. **IP Geolocation & ISP Finder**: Country, city, region, ISP, and coordinates.
10. **robots.txt Live Validator**: Fetches and parses target domain `robots.txt` directives.
11. **XML Sitemap Sanity Inspector**: Fetches XML sitemap, verifies validity, and counts URLs.
12. **Meta Tag & Open Graph Scraper**: Scrapes title, description, canonical, OG, and Twitter tags.
13. **Security Headers Checker**: Audits HSTS, CSP, X-Frame-Options, and X-Content-Type-Options.
14. **Email SPF, DKIM & DMARC Checker**: Inspects DNS TXT records for email authentication.
15. **PageSpeed Free Diagnostic**: Google PageSpeed v5 API integration without dashboard restrictions.
16. **Keyword Suggestion Generator**: Scrapes Google Suggest autocomplete for keyword clusters.
17. **SERP Simulator**: Previews search snippet appearance with character counters.
18. **Password & Hash Generator**: Generates MD5, SHA-256, crypt, and secure random passwords.
19. **Base64 & URL Encoder / Decoder**: Real-time two-way encoding and decoding utility.

---

## 11. 💻 Developer API, AJAX Endpoints & Hooks

All AJAX requests route through WordPress core `admin-ajax.php` and require valid capability and nonces.

### AJAX Endpoints Reference

| Endpoint Action | Class Handler | Description |
|-----------------|---------------|-------------|
| `asf_run_audit` | `ASF_Audit` | Executes 360° technical SEO audit crawl |
| `asf_ai_chat` | `ASF_AIChatbot` | Dispatches Groq LLaMA-3.3 query with site context |
| `asf_export_ai_prompt` | `ASF_AIChatbot` | Exports comprehensive Master AI Prompt JSON |
| `asf_import_ai_config` | `ASF_AIChatbot` | Applies external AI JSON metadata to site |
| `asf_run_diagnostics` | `ASF_Diagnostics` | Runs 16-point server and WP diagnostic check |
| `asf_send_diagnostic_report` | `ASF_Diagnostics` | Emails diagnostic report to developer or admin |
| `asf_save_license` | `ASF_Diagnostics` | Activates or deactivates Pro license key |
| `asf_onpage_scan` | `ASF_OnPage` | Audits published posts/pages for metadata and titles |
| `asf_generate_single_meta_desc`| `ASF_AutoFixer` | Generates 120–155 char description from content |
| `asf_media_scan` | `ASF_Media` | Scans media library for missing alt text and orphans |
| `asf_media_trash` | `ASF_Media` | Safely sends orphan media files to WordPress trash |
| `asf_fetch_psi` | `ASF_PageSpeed` | Queries Google PageSpeed API v5 for desktop/mobile |
| `asf_regenerate_llms_txt` | `ASF_GEO` | Builds structured Markdown `/llms.txt` file |
| `asf_save_schema_studio` | `ASF_Schema` | Saves visual schema definitions to database |
| `asf_tool_*` | `ASF_SwissTools` | 19 separate network diagnostic endpoints |

---

## 12. 🔒 Security, Nonces & Capability Enforcement

### Nonce Verification
All AJAX endpoints verify the session token via:
```php
function asf_check_nonce() {
    $nonce = $_REQUEST['nonce'] ?? $_SERVER['HTTP_X_WP_NONCE'] ?? '';
    if ( ! wp_verify_nonce( $nonce, 'asf_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed. Please refresh.' ), 403 );
    }
}
```

### Capability Guard
Administrative access is strictly restricted to authorized administrators:
```php
function asf_cap_check() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized user.' ), 403 );
    }
}
```

### Safe Media Deletion
Orphan media files are never permanently unlinked or deleted from disk without rollback. The plugin exclusively invokes:
```php
wp_trash_post( $attachment_id );
```
This moves the file to the native WordPress Media Trash, allowing full restoration if needed.

---

## 13. Credits & Support

All-in-One SEO Fixer is proudly developed and maintained by **Abid Ali**.

- **Website**: [https://abidalidev.com](https://abidalidev.com)
- **Email Support**: [support@abidalidev.com](mailto:support@abidalidev.com)
- **License**: GNU General Public License v2.0 or later
