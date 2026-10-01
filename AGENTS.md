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

## Known follow-ups (parked 2026-10-01, none blocks a release)

1. Scan counter: the bot pattern in `includes/class-scans.php` matches "Cubot" phones, so their scans are not counted. An invalid pattern from the `rqrc_bot_user_agent_pattern` filter counts the scan and raises a PHP warning; it should fall back to the default.
2. Translations: the plugin never calls `load_plugin_textdomain`, and `wp_set_script_translations` gets no path. On WordPress versions before 7.x the bundled de/es/fr files may not load; pass the `languages/` path.
3. Spanish uses both "Logo" and "logotipo" for the logo strings.
4. Block: `localize_editor()` loads every QR code on each editor load; cap or cache it for sites with thousands of codes.
5. Logo: switching a code away from "Custom image" forgets the chosen image.
6. Screenshot 2 on WordPress.org shows a `localhost` permalink.
7. Publishing workflows (`dev-minuar/wporg-release`): `actions/checkout` v4.4.0 logs a Node 20 deprecation warning; bump the pin when v5 is current.

## Releasing

IF you are releasing or updating the WordPress.org page → read `../AGENTS.md` (the SVN folder guide). In short: bump the version in 3 places, push `main`, then push a `vX.Y.Z` tag. GitHub Actions (`dev-minuar/wporg-release@v1`) runs the checks and waits for approval in the `wordpress-org` environment before it publishes.
