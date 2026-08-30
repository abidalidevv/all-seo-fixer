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

- **🛡️ 360° SEO Audit Dashboard**: One-click site diagnostic covering 24+ critical SEO parameters with an actionable fix list.
- **⚡ Google PageSpeed Insights & Core Web Vitals**: Integrated with Google's free PageSpeed v5 API. Reports scores (0–100) and Core Web Vitals (LCP, TBT, CLS, FCP, Speed Index).
- **🧱 Page Builder & Elementor Overhead Optimizer**: Detects active page builders, analyzes database JSON payload (`_elementor_data` MB size), identifies heavy DOM pages, and dequeues unused font/icon scripts.
- **🚀 Speed & Database Optimizer**: Enforces native `loading="lazy"` on all content images & iframes. 1-click DB cleanup for revisions, auto-drafts, spam comments, and expired transients.
- **🔧 On-Page SEO Checker**: Per-page audit for title length, meta descriptions, H1/H2 tags, missing image alt text, thin content (<300 words), Schema markup, internal links count, and NOINDEX flags.
- **🔤 Keyword Density & Over-Optimization Analyzer**: N-gram word frequency analyzer with keyword stuffing alerts (>2.5% density).
- **📈 Domain Rating (DR/DA) & Link Equity Estimator**: Calculates an On-Page Domain Rating (0–100) based on link graph connectivity and content depth.
- **👁️ Live SERP & Social Card Simulator**: Real-time Google Search (Desktop & Mobile) and Facebook share card preview widget with character & pixel-width meters.
- **🔒 Domain Security & Spam Blacklist Audit**: Audits SSL, Security Headers (`HSTS`, `X-Frame-Options`, `CSP`), CDN detection, and live DNSBL blacklist checks (Spamhaus, SpamCop, Barracuda).
- **🔗 Broken Link & Double-Domain Typo Cleaner**: Auto-detects and repairs malformed URL typos (`domain.comhttps://domain.com/`) in post content and Elementor metadata.
- **🖼️ Media Scanner & 1-Click Auto Alt-Text Generator**: Finds unused orphan media files and auto-generates clean Alt text from filenames.
- **🔀 301 Redirect Manager**: Manages permanent canonical 301 redirects without editing `.htaccess`.
- **🚀 IndexNow & Sitemap Pinger**: Instant re-indexing alerts to Google, Bing, Yandex, and Naver.
- **📄 Downloadable / Printable PDF Reports**: Generate agency-ready printable PDF audit reports in one click.

---

## 📥 Installation

1. Download `all-seo-fixer.zip` from the [Releases](https://github.com/abidalidevv/all-seo-fixer/releases) page.
2. In your WordPress Admin Dashboard, go to **Plugins → Add New → Upload Plugin**.
3. Select `all-seo-fixer.zip` and click **Install Now**.
4. Click **Activate Plugin**.
5. Navigate to **All SEO Fixer** in your WordPress sidebar.
6. (Optional) Add your free Google PageSpeed Insights API key in **⚙️ Settings**.

---

## 📂 Project Structure

```
all-seo-fixer/
├── all-seo-fixer.php           # Main bootstrap file
├── readme.txt                  # WordPress.org plugin directory readme
├── README.md                   # GitHub readme
├── DOCUMENTATION.md            # Technical manual & developer guide
├── assets/
│   ├── css/admin.css           # Premium admin stylesheet
│   └── js/admin.js             # Central admin JavaScript & AJAX controller
├── includes/
│   ├── class-asf-core.php      # Always-on protection hooks
│   ├── class-asf-audit.php     # 360° SEO audit handler
│   ├── class-asf-pagespeed.php # Google PageSpeed Insights API handler
│   ├── class-asf-security.php  # Security headers, CDN & DNSBL handler
│   ├── class-asf-performance.php # Native lazyloading & DB optimizer
│   ├── class-asf-authority.php # Domain Rating & Keyword Density handler
│   ├── class-asf-builder.php   # Page Builder & Elementor optimizer
│   └── class-asf-handlers.php  # OnPage, LinkCleaner, Media & Pinger handlers
└── admin/
    ├── class-asf-admin.php     # Menu & asset enqueue controller
    └── views/                  # UI View templates
```

---

## 🌐 Free APIs Used

| API / Service | Cost | Function |
|---------------|------|----------|
| **Google PageSpeed Insights v5** | Free (25,000 req/day) | Core Web Vitals & Lighthouse Scores |
| **IndexNow API** | Free | Instant Bing, Yandex & Naver re-indexing |
| **Google Sitemap Ping** | Free | Direct Google sitemap update notification |
| **Chart.js** (via jsDelivr) | Free | Score ring & metric visualization |

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!  
Feel free to check the [issues page](https://github.com/abidalidevv/all-seo-fixer/issues).

---

## 📜 License

Distributed under the **GPL-2.0-or-later** License. See `LICENSE` for more information.

---

## 👨‍💻 Author

**Abid Ali Dev**  
- Website: [abidalidev.com](https://abidalidev.com)  
- GitHub: [@abidalidevv](https://github.com/abidalidevv)
