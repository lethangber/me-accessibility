# ME Accessibility

ME Accessibility is a high-fidelity accessibility toolkit for WordPress. Designed to assist you in optimizing your website in alignment with international accessibility standards—including **WCAG 2.1 AA, the European Accessibility Act (EAA), EN 301 549, and BITV 2.0**—this plugin provides an elegant floating accessibility toolbar, a manual WCAG compliance scanner, and a customizable accessibility statement generator.

---

## 🌟 Key Features (Free Version)

### ♿ Floating Accessibility Toolbar
- **User-Centric Presets**: 4 customized profiles (ADHD Friendly, Visual Impairment, Keyboard Navigation, and Cognitive Aid) to assist users with varying disabilities.
- **Reading & Visual Aids**: Features a relative text size scaling engine, readable fonts (including specialized OpenDyslexic integration), grayscale, high/negative contrasts, dark mode, highlighted links/headings, large cursor, reading line guide, and reading mask overlay.
- **Flawless Keyboard Control**: 100% compliant with standard tab structures and active focus traps. Hitting `Escape` closes the toolbar instantly and restores the focus position on the trigger button.
- **State Persistence**: Saved locally in the user's browser via `LocalStorage` for instantaneous application without layout shifts (CLS).

### 🔍 Manual WCAG Compliance Scanner
- **On-Demand Crawler**: Analyzes page structures (headings hierarchy, link validity, image alt attributes, ARIA landmarks) directly through an integrated DOM parser on demand.
- **CSV Export**: Export your manual scan results to CSV format for external analysis.

### 📄 Accessibility Statement Generator
- Generate and publish a custom accessibility statement for your site to document your compliance efforts and contact information.

---

## 🚀 Upgrade to Pro for Advanced Features

To take your website compliance to the next level, upgrade to **ME Accessibility Pro** at [me-toolkit.com](https://me-toolkit.com). Pro features include:
- 🔊 **Smart Text-to-Speech (TTS) Reader**: Native, 100% GDPR-compliant browser-based TTS voice synthesis reader.
- ⚙️ **One-Click Auto Fix**: Automate the repair of basic accessibility errors (like missing media alt texts or landmark roles) with a single click.
- 🛡️ **GDPR Cookie-Consent Banner**: Strict, keyboard-accessible focus-trapping cookie consent modal.
- 🖼️ **Bulk Alt-Text Media Editor**: Fast, inline media library editor to repair missing image descriptions in bulk.
- 🛠️ **White-Label & Custom Overrides**: Completely remove vendor branding and inject custom CSS/JS scripts.
- 📅 **Scheduled Background Scans**: Set cron-based weekly or monthly automated audits.

---

## 🚀 Installation & Setup

1. **Upload**: Upload the `/me-accessibility/` folder directly to the `/wp-content/plugins/` directory, or upload the zip archive via the WordPress dashboard (Plugins → Add New → Upload).
2. **Activate**: Activate the plugin through the WordPress "Plugins" menu.
3. **Initialize**: Navigate to the **ME Accessibility** tab in your admin sidebar to configure your settings and run your first manual scan.

---

## 📄 License & Credits

- Developed and maintained by **Me Toolkit** ([me-toolkit.com](https://me-toolkit.com)).
- Licensed under the **GPLv2 or later**.
- Powered by native browser APIs for maximum efficiency, security, and compliance.

---

## 📅 Changelog

### v3.1 (2026-06-02)
- **Settings & Dashboard Quick Links**: Added direct admin links under the plugin list page for faster navigation.
- **Go Pro Submenu**: Replaced local licensing controls with a premium Upgrade to Pro promotion page linking to me-toolkit.com.
- **Core Splitting**: Cleaned up the codebase to remove inactive Pro code blocks, ensuring full compliance with WordPress.org submission guidelines.
