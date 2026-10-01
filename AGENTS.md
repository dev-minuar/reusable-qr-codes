# Reusable QR Codes - Project Plan

## Project Overview

**Name:** Reusable QR Codes
**Type:** WordPress Plugin (Free + Premium)
**Purpose:** Create reusable QR codes with changeable destinations
**Target:** Museums, retail, events, real estate, education, tourism

### The Problem We Solve

Physical QR codes become obsolete when destinations change. Users must reprint and replace them - expensive, wasteful, and time-consuming.

### Our Solution

Create permanent QR codes that can be updated anytime without reprinting. The QR code points to a WordPress URL that redirects to any destination you choose.

---

## Architecture & Philosophy

### Core Principles

1. **Lean & Performant** - Minimal database impact, conditional loading
2. **No Dependencies** - Fully self-contained, no external services
3. **Privacy First** - No tracking in free version
4. **WordPress Standards** - Clean, documented, compliant code
5. **Freemium Model** - Solid free version, valuable premium features

### Technology Stack

- **Backend:** PHP 7.4+ (WordPress native)
- **Frontend:** JavaScript (jQuery), CSS
- **QR Generation:** QR Code Styling library
- **Storage:** WordPress custom post types & meta
- **No External APIs** - Everything runs locally

---

## Development Roadmap

### ✅ Phase 1: FREE CORE (v1.0.0) - COMPLETED

**Goal:** WordPress.org ready plugin with core functionality

#### Features Delivered

**Custom Post Type**
- Post type: `rqrc_item`
- Slug: `/rqrc/` (changed from `/qr/` to avoid conflicts)
- Supports: title only (Gutenberg disabled, no featured image)
- Public, queryable
- Classic editor interface (no block editor bloat)

**Meta Fields (Native WordPress)**
- Destination URL (required) - `_rqrc_destination_url` - High priority, appears right after title
- Notes field (optional) - `_rqrc_notes` - Internal notes for tracking (not visible to visitors)
- Simple native WordPress inputs with validation
- No ACF or external dependencies

**QR Code Generation**
- Client-side generation via QR Code Styling library
- Preview in admin sidebar (canvas/PNG rendering for proper sizing)
- Beautiful placeholder for unpublished posts (hourglass icon with striped background)
- Download formats: PNG (1024x1024), SVG (1024x1024)
- Clean, modern download buttons (PNG primary dark button, SVG secondary)
- Customizable via global settings

**Redirect System**
- Template redirect hook
- 302 temporary (default) or 301 permanent
- Fallback template if no destination set
- Admin notification in fallback template

**Settings Page**
- Location: QR Codes → Settings (submenu under QR Codes, not under WordPress Settings)
- QR Code Color (WordPress native color picker)
- Background Color (WordPress native color picker)
- Dot Style (5 options: square, dots, rounded, classy, classy-rounded)
- Display Size (128-512px)
- Redirect Type (302 temporary / 301 permanent)

**Security & Standards**
- Nonces on all forms
- Capability checks (`edit_post`, `manage_options`)
- Input sanitization (`esc_url_raw`, `sanitize_hex_color`)
- Output escaping (`esc_url`, `esc_html`, `esc_attr`)
- WordPress Coding Standards compliant

**Internationalization**
- Text domain: `reusable-qr-codes`
- All strings wrapped in translation functions (`__()`, `_e()`, `esc_html__()`)
- POT template file included (`reusable-qr-codes.pot`)
- Pre-translated to 3 major languages:
  - Spanish (es_ES) - 500M+ speakers, Latin America market
  - German (de_DE) - DACH region, high WordPress adoption
  - French (fr_FR) - France + French-speaking Africa
- Language folder: `/languages/`
- Ready for community translations on translate.wordpress.org

**User Interface (shadcn-inspired Design)**
- Clean, modern admin interface with shadcn/ui aesthetic
- Smooth transitions and hover effects
- Focus states with blue glow (#3b82f6)
- Rounded corners (6-12px) and subtle shadows
- Gray color palette (#18181b, #e4e4e7, #71717a, #fafafa)
- Responsive design with mobile optimizations
- Custom success messages (no confusing "View Post" links)
- Professional download buttons (dark primary, light secondary)

**Required Files**
- ✅ `readme.txt` - WordPress.org format
- ✅ `LICENSE` - GPL v2 or later
- ✅ `uninstall.php` - Clean deletion
- ✅ `CLAUDE.md` - Project documentation
- ✅ Proper plugin headers

**File Structure**
```
reusable-qr-codes/
├── reusable-qr-codes.php          # Bootstrap
├── LICENSE                        # GPL v2
├── readme.txt                     # WordPress.org
├── uninstall.php                  # Cleanup
├── CLAUDE.md                      # This file
├── includes/
│   ├── class-plugin.php           # Main class
│   ├── class-post-type.php        # CPT registration
│   ├── class-meta-boxes.php       # Meta boxes
│   ├── class-redirects.php        # Redirect logic
│   └── class-settings.php         # Settings page
├── admin/
│   ├── css/admin.css              # Admin styles
│   └── js/qr-generator.js         # QR generation
├── assets/
│   ├── vendor/QrCodeStyling.min.js
│   └── images/default_logo.svg
├── templates/
│   └── single-rqrc_item.php       # Fallback template
└── languages/
    └── reusable-qr-codes.pot      # Translation template (to be generated)
```

#### Testing Checklist

- [ ] Plugin activates without errors
- [ ] CPT appears in admin menu
- [ ] Can create new QR code
- [ ] Can set destination URL
- [ ] QR code displays in sidebar
- [ ] QR code downloads (PNG & SVG)
- [ ] Redirect works (visitor → destination)
- [ ] Fallback template shows when no destination
- [ ] Settings page loads and saves
- [ ] Color pickers work
- [ ] No PHP errors in debug mode
- [ ] No JavaScript console errors
- [ ] Works with block themes
- [ ] Works with classic themes

---

### 🚧 Phase 2: PREMIUM FOUNDATION (v1.1.0) - PLANNED

**Goal:** Licensing system and premium feature infrastructure

#### Tasks

**Licensing System**
- Integrate Freemius SDK or Easy Digital Downloads
- License activation UI
- License validation on premium features
- Auto-updates for premium version
- Deactivation/transfer handling

**Premium Plugin Structure**
- Separate premium plugin or addon approach?
- Feature gating system (`if ( rqrc_is_premium() )`)
- Graceful degradation if license expires

**Upgrade Prompts (Non-intrusive)**
- Settings page: "🔒 Premium Feature" badges
- Meta box: "Upgrade for logos" notice
- Dashboard widget with premium features
- Limit to 2-3 prompts max

**Pricing Tiers**
- Personal: $49/year - 1 site
- Business: $99/year - 5 sites
- Agency: $199/year - Unlimited sites

#### Timeline
2 weeks after v1.0.0 launch

---

### 🎯 Phase 3: PREMIUM FEATURES - ANALYTICS (v1.2.0) - PLANNED

**Goal:** Lightweight scan tracking and analytics

#### Features

**Database Schema**
```sql
CREATE TABLE wp_rqrc_scans (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    qr_id BIGINT NOT NULL,
    scan_date DATETIME NOT NULL,
    INDEX (qr_id),
    INDEX (scan_date)
);
```

**Scan Tracking**
- Increment counter on redirect
- Store: QR ID, timestamp only
- No IP addresses, no user agents (lean!)
- Option to disable tracking per QR

**Analytics Dashboard**
- Location: QR Codes → Analytics
- Simple table view:
  - QR Code name
  - Total scans
  - Last scanned date
- Filter by date range
- Sort by scans, date

**Export**
- CSV export of analytics data
- Columns: QR Name, URL, Destination, Total Scans, Last Scan
- Date range filter

**Performance**
- Async tracking (doesn't slow redirect)
- Indexed database queries
- Optional: purge old data (> 1 year)

#### Timeline
1 month after premium launch

---

### 🎨 Phase 4: PREMIUM FEATURES - CUSTOMIZATION (v1.3.0) - PLANNED

**Goal:** Advanced QR code styling options

#### Features

**Per-QR Logo Upload**
- Meta box: "QR Code Logo" (WordPress Media Library)
- Preview with logo
- Size/position options (center, size %)
- Falls back to global logo if not set

**Gradient Colors**
- Two-color picker in meta box
- Gradient types: linear, radial
- Falls back to solid color if not set

**Eye Customization**
- Corner "eye" style (square, rounded, dot)
- Separate eye color picker
- Preview updates in real-time

**Dot Styles (Extended)**
- All 5 existing styles
- Premium: extra-rounded, classy-rounded variations
- Custom dot size

**Templates System**
- NOT full template library (bloat!)
- Just 3-5 preset combinations:
  - Classic (black & white)
  - Modern (gradient, rounded)
  - Minimal (thin dots)
- One-click apply

#### Performance Note
All rendering is client-side via QR Code Styling library - no server processing!

#### Timeline
2 weeks after analytics launch

---

### 📦 Phase 5: PREMIUM FEATURES - BULK & ADVANCED (v1.4.0) - PLANNED

**Goal:** Professional workflow features

#### Features

**Bulk Download**
- Select multiple QR codes (checkbox)
- Download all as ZIP file
- Background processing for large batches
- Filename format: `{qr-title}-qr-code.png`

**PDF Generation**
- Print-ready PDF with multiple QR codes
- Layout options: 1, 4, 6, 9 per page
- Include title and destination URL
- Uses TCPDF or similar library

**Shortcodes**
- `[rqrc_display id="123"]` - Display QR on frontend
- `[rqrc_display id="123" size="512"]` - Custom size
- `[rqrc_download id="123"]` - Download button
- Cached SVG output for performance

**Scheduled Destinations**
- Meta box: "Schedule Destination Change"
- Date picker + new URL
- Uses WordPress cron
- Email notification when changed
- Max 5 scheduled changes per QR

**Expiration Dates**
- Meta box: "Expiration Date"
- After expiration: redirect to custom page or 404
- Email reminder 7 days before
- Option to extend

**Password Protection**
- Meta box: "Password Protect"
- Simple password field
- Cookie-based (24h session)
- Redirect to password page before destination

#### Timeline
1 month after customization features

---

### 📝 Phase 6: POLISH & OPTIMIZATION (v1.5.0) - PLANNED

**Goal:** Refinement and user experience improvements

#### Features

**Multi-site Support**
- Network activation
- License works across network
- Separate settings per site or global

**Import/Export**
- Export QR codes to JSON
- Import from JSON (bulk creation)
- Useful for migrations

**Shortcode Builder**
- Visual shortcode generator in admin
- Copy to clipboard
- Preview before inserting

**QR Code Collections**
- Custom taxonomy: `rqrc_collection`
- Group QR codes by project/event
- Filter by collection in admin

**Performance Optimizations**
- Lazy load admin scripts
- Cache QR code SVG output
- Database query optimization
- Asset minification

**Documentation**
- Inline help tooltips
- Getting started wizard
- Video tutorials
- Knowledge base

#### Timeline
2 months after bulk features

---

## Premium Features Summary

### ❌ EXCLUDED (Bloat Prevention)

These features will NOT be added:
- ❌ Geolocation tracking (external APIs, privacy issues)
- ❌ Background images (file size bloat)
- ❌ Template library (too many options = confusion)
- ❌ Brand kits (unnecessary storage)
- ❌ White-label (support complexity)
- ❌ A/B testing (over-engineering)
- ❌ Device/location routing (complexity)
- ❌ Webhooks (external calls)

### ✅ INCLUDED (High Value, Low Impact)

| Feature | Value | Performance Impact | Complexity |
|---------|-------|-------------------|------------|
| Scan tracking | High | Low (async) | Low |
| Analytics dashboard | High | Low (indexed) | Medium |
| CSV export | Medium | Low | Low |
| Logo upload | High | None (client-side) | Low |
| Gradient colors | Medium | None (client-side) | Low |
| Eye customization | Medium | None (client-side) | Low |
| Bulk download | High | Medium (cron) | Medium |
| PDF generation | High | Medium (library) | Medium |
| Shortcodes | High | Low (cached) | Medium |
| Scheduled destinations | High | Low (WP cron) | Medium |
| Expiration dates | Medium | Low (meta check) | Low |
| Password protection | Medium | Low (session) | Low |
| Multi-site | Medium | None | Low |

---

## Marketing & Launch Strategy

### Free Version Launch (WordPress.org)

**Pre-Launch**
- [ ] Final testing on fresh WP install
- [ ] Test with popular themes (Astra, GeneratePress, Twenty Twenty-Four)
- [ ] Test with block editor & classic editor
- [ ] Take 5 screenshots for readme
- [ ] Create demo video (2 min)
- [ ] Set up support forum monitoring

**Launch Day**
- [ ] Submit to WordPress.org
- [ ] Announce on social media (Twitter, LinkedIn)
- [ ] Post on Reddit (r/WordPress, r/webdev)
- [ ] Product Hunt submission
- [ ] Email existing contacts

**Post-Launch (First Month)**
- [ ] Respond to all support requests within 24h
- [ ] Collect feedback and feature requests
- [ ] Fix critical bugs immediately
- [ ] Monitor reviews and respond
- [ ] Write blog post: "Case Study: Museum QR Codes"

### Premium Version Launch

**Timing:** 2-3 months after free version launch

**Pre-Launch**
- [ ] Build landing page with pricing
- [ ] Create premium demo site
- [ ] Record feature walkthrough videos
- [ ] Set up payment processing (Stripe)
- [ ] Prepare email campaign for free users

**Launch Strategy**
- [ ] Announce to free plugin users (in-admin notice)
- [ ] Launch discount: 30% off first year
- [ ] Money-back guarantee (30 days)
- [ ] Affiliate program (20% commission)

**Marketing Channels**
- WordPress-focused blogs (guest posts)
- YouTube tutorials
- Facebook groups (museums, retail, events)
- LinkedIn (B2B targeting)

---

## Success Metrics

### Free Version (Year 1 Goals)

- **Active Installs:** 1,000+
- **Rating:** 4.5+ stars
- **Support Resolution:** <48h average
- **Reviews:** 50+ positive reviews

### Premium Version (Year 1 Goals)

- **Conversions:** 2% of free users
- **Revenue:** $10,000+ ARR
- **Churn Rate:** <10% annually
- **Customer Satisfaction:** 90%+ happy

### Long-term (Year 2-3)

- **Active Installs:** 10,000+
- **Premium Users:** 200+
- **Revenue:** $50,000+ ARR
- **Market Position:** Top 3 QR plugins on WordPress.org

---

## Technical Debt & Future Considerations

### Known Limitations (Acceptable)

1. **No mobile app** - Web-only management (acceptable for v1)
2. **No REST API endpoints** - Not needed for core features
3. **No Gutenberg blocks** - Shortcodes are sufficient
4. **No email notifications** - Add in premium v1.4

### Potential Improvements (Future)

1. **Gutenberg block** for displaying QR codes
2. **REST API** for external integrations
3. **WP-CLI commands** for bulk operations
4. **Dashboard widget** with quick stats
5. **Quick Edit** support in post list

---

## Support & Maintenance

### Free Version Support

- **Channel:** WordPress.org support forums
- **Response Time:** 48-72 hours
- **Scope:** Bug fixes, basic usage questions
- **No:** Custom development, extensive troubleshooting

### Premium Version Support

- **Channel:** Dedicated support system (email/ticket)
- **Response Time:** 24 hours (business days)
- **Scope:** All features, configuration help, compatibility
- **Includes:** Priority bug fixes, feature requests consideration

### Update Schedule

- **Security updates:** Immediate
- **Bug fixes:** Within 1 week
- **Minor features:** Monthly (if needed)
- **Major versions:** Quarterly

---

## Legal & Compliance

### License

- **Free Version:** GPL v2 or later
- **Premium Version:** GPL v2 + Commercial License
- **Libraries:** QR Code Styling (MIT License - compatible)

### Privacy & GDPR

**Free Version:**
- No data collection
- No cookies
- No external API calls
- No tracking

**Premium Version (Analytics):**
- Scan data: QR ID + timestamp only
- No personal data (no IP, no user agent)
- User can disable tracking per QR
- Data retention: 1 year (configurable)
- Export/delete on request

### WordPress.org Requirements

- ✅ GPL compatible
- ✅ No obfuscated code
- ✅ No external service calls (without permission)
- ✅ Sanitization & escaping
- ✅ Nonces & capability checks
- ✅ No upsells in code (only settings page)

---

## Team & Resources

### Development

- **Lead Developer:** Minuar
- **Code Review:** Community (GitHub?)
- **Testing:** Local testing + beta users

### Resources Needed

- **Development Time:** 40-60 hours (Phase 1 ✅)
- **Premium Development:** 80-100 hours (Phases 2-5)
- **Landing Page:** Design + copy
- **Marketing:** Content creation, social media

### Budget (Premium Launch)

- **Freemius SDK:** Free (5% transaction fee)
- **Landing Page:** $500 (design)
- **Paid Advertising:** $1,000/month (optional)
- **Video Production:** $300 (tutorial videos)

---

## Open Questions / Decisions Needed

1. **Plugin Name:** "Reusable QR Codes" or something catchier?
2. **Premium Delivery:** Separate plugin or license key unlock?
3. **Licensing Provider:** Freemius vs EDD vs custom?
4. **Support Platform:** Email, Zendesk, or built-in?
5. **Affiliate Program:** Yes/no? What commission?

---

## Version History & Changelog

| Version | Status | Date | Notes |
|---------|--------|------|-------|
| 1.0.0 | ✅ Complete | 2024-10-23 | Free core version |
| 1.1.0 | 📋 Planned | TBD | Premium foundation |
| 1.2.0 | 📋 Planned | TBD | Analytics |
| 1.3.0 | 📋 Planned | TBD | Customization |
| 1.4.0 | 📋 Planned | TBD | Bulk & Advanced |
| 1.5.0 | 📋 Planned | TBD | Polish |

### Detailed Changelog

#### v1.0.0 - 2024-10-23 (Initial Release)

**Core Features**
- Custom post type `rqrc_item` with unique slug `/rqrc/`
- Destination URL meta field with validation
- Notes meta field for internal tracking
- QR code generation with QR Code Styling library
- Canvas/PNG preview rendering (proper sizing)
- PNG & SVG download functionality
- 302/301 redirect system
- Fallback template for unconfigured QR codes
- Settings page with global defaults

**User Interface**
- shadcn/ui inspired design system
- Disabled Gutenberg (classic editor only)
- Removed featured image support (unnecessary)
- Clean meta box layout with proper hierarchy
- Beautiful placeholder for unpublished posts (hourglass + striped background)
- Modern download buttons (dark primary, light secondary)
- Custom WordPress success messages
- Smooth transitions and hover effects
- Focus states with blue glow
- Responsive design

**Technical**
- No external dependencies (ACF removed)
- WordPress Coding Standards compliant
- Full internationalization (i18n) support
- Security: nonces, capability checks, sanitization, escaping
- Performance: conditional asset loading
- Clean uninstall with data removal

**Files Created**
- Main plugin file with proper headers
- 5 class files (plugin, post-type, meta-boxes, redirects, settings)
- Admin CSS (shadcn-inspired)
- Admin JavaScript (QR generation)
- Fallback single template
- readme.txt (WordPress.org format)
- LICENSE (GPL v2)
- CLAUDE.md (project documentation)
- uninstall.php

**UX Improvements**
- Settings page moved to QR Codes submenu (better UX than WordPress Settings)
- Custom success messages (removed confusing "View Post" links)
- Download buttons without icons (clean minimalist design)
- Canvas/PNG preview instead of SVG (proper sizing)

**Translations**
- Pre-translated to Spanish, German, and French
- POT template for community translations
- Covers major non-English WordPress markets

---

## Contact & Links

- **Plugin URL:** https://wordpress.org/plugins/reusable-qr-codes/ (pending)
- **Premium Site:** https://minuar.com/reusable-qr-codes
- **Support:** https://wordpress.org/support/plugin/reusable-qr-codes/
- **GitHub:** https://github.com/minuar/reusable-qr-codes (to be created)

---

## Notes for Claude (AI Assistant)

This document serves as the master plan for the Reusable QR Codes plugin. When asked about the project:

1. **Always refer to this document first**
2. **Maintain the lean & performant philosophy**
3. **Reject features that add bloat**
4. **Stay focused on the freemium model**
5. **Keep WordPress standards at the forefront**

**Last Updated:** 2024-10-23 by Claude Code (Sprint 1 Complete + UI Polish)
