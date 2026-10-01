# Reusable QR Codes

A free WordPress plugin on WordPress.org (https://wordpress.org/plugins/reusable-qr-codes/). Each QR code points to a permanent address on the site (`/rqrc/<slug>/`), which redirects to a destination that can change at any time. There is no premium version and none is planned.

## Code map

- `reusable-qr-codes.php`: bootstrap, version constant `RQRC_VERSION` (CRLF line endings, keep them)
- `includes/class-post-type.php`: the `rqrc_item` post type and the admin list columns
- `includes/class-meta-boxes.php`: edit screen boxes (destination, notes, preview, logo, scans)
- `includes/class-redirects.php`: redirects, the "not configured" page, noindex, sitemap exclusion
- `includes/class-logo.php`: logo resolution (none / site logo / custom image)
- `includes/class-scans.php`: privacy-friendly scan counter (a count and a last-scan date per code)
- `includes/class-block.php` + `blocks/qr-code/`: the dynamic QR Code block
- `includes/class-settings.php`: settings page under QR Codes → Settings
- `assets/js/rqrc-qr.js`: shared browser helper that builds, draws and downloads every QR code
- `assets/vendor/QrCodeStyling.min.js`: bundled QR library (never edit)
- `templates/single-rqrc_item.php`: page shown when a code has no destination
- `languages/`: POT plus de_DE, es_ES, fr_FR (.po/.mo, and .json for the block editor)

## Rules

- Minimum WordPress 5.8 and PHP 7.4. Guard newer WordPress functions with `function_exists`.
- No build step and no new dependencies. The JavaScript uses plain `wp.*` globals and jQuery.
- Existing printed codes must keep working: never change a code's URL or its default look.
- Store no personal data.
- After changing strings, regenerate the POT and update the 3 translations.
- Docs and specs go in `docs/`, which never ships (`.distignore`). The old 2024 project plan is in `docs/_archive/`.

## Releasing

IF you are releasing or updating the WordPress.org page → read `../AGENTS.md` (the SVN folder guide). In short: bump the version in 3 places, push `main`, then push a `vX.Y.Z` tag. GitHub Actions (`dev-minuar/wporg-release@v1`) runs the checks and waits for approval in the `wordpress-org` environment before it publishes.
