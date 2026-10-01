=== Reusable QR Codes ===
Contributors: minuar
Tags: qr code, redirect, dynamic qr, qr manager, url shortener
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create reusable QR codes with changeable destinations. Perfect for museums, retail, events, and anywhere physical QR codes need to stay relevant.

== Description ==

**The Problem:** You print QR codes, hang them up, and when you need to change where they point, you have to reprint and replace them. Expensive, wasteful, and time-consuming.

**The Solution:** Reusable QR Codes creates permanent QR codes that you can update anytime without reprinting!

= How It Works =

1. Create a QR Code and set a destination URL
2. Download and print/share the QR code
3. Visitors scan the code and get redirected to your destination
4. **Update the destination anytime** without reprinting the QR code!

The QR code contains a permanent link to your WordPress site, which then redirects to wherever you want. Change the destination as many times as you need - the physical QR code never changes.

= Perfect For =

* **Museums & Galleries** - Update exhibit information without reprinting signs
* **Retail Stores** - Change product details, promotions, and seasonal content
* **Restaurants** - Update menus, daily specials, or seasonal offerings
* **Event Organizers** - Modify schedules, speaker info, or venue details
* **Real Estate** - Update property information and availability
* **Education** - Link to current classroom resources and materials
* **Tourism** - Keep landmark and trail information fresh

= Key Features =

* ✅ **Unlimited QR Codes** - Create as many as you need
* ✅ **Easy Destination Management** - Simple URL field, change anytime
* ✅ **High Quality Downloads** - PNG (1024x1024) and SVG formats
* ✅ **Customizable Appearance** - Colors, dot styles, and sizes
* ✅ **No Dependencies** - Works standalone, no external services
* ✅ **Optional Logo** - Site logo or a custom image in the centre, chosen per QR code
* ✅ **QR Code Block** - Show any QR code on a page, with caption and download button
* ✅ **Scan Counter** - See how often each code is scanned, without tracking people
* ✅ **Privacy Friendly** - No personal data, no cookies, no external calls
* ✅ **Translation Ready** - Fully internationalized, with German, Spanish and French included
* ✅ **Clean Code** - WordPress coding standards compliant

= Technical Details =

* Lightweight and performant - small database footprint
* Conditional asset loading - scripts only when needed
* Secure - nonces, capability checks, input sanitization
* Follows WordPress coding standards
* Uses native WordPress functions (no bloat!)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/reusable-qr-codes/` or install via WordPress plugin installer
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'QR Codes' in your admin menu to create your first QR code
4. Configure default settings under QR Codes → Settings (optional)

== Frequently Asked Questions ==

= Do I need any external services or API keys? =

No! This plugin is completely self-contained and works entirely within your WordPress installation. No external dependencies, no API keys, no recurring fees.

= Can I really change where the QR code points without reprinting it? =

Yes! That's the whole point. The QR code contains a permanent URL on your site (like `yoursite.com/rqrc/museum-exhibit-1/`). When someone scans it, they're instantly redirected to whatever destination URL you've set. Change that destination anytime in WordPress.

= How many QR codes can I create? =

Unlimited! Create as many as you need.

= What formats can I download? =

PNG (high resolution 1024x1024px) and SVG (vector, scales to any size). Both are perfect for printing.

= Will this slow down my site? =

No. The plugin is very lightweight and only loads assets when needed. The redirect stays fast. Each counted scan updates one counter in the database.

= Can I use my own logo in the QR code? =

Yes. Set a site logo under QR Codes → Settings, then choose per QR code: no logo, the site logo or a custom image. Codes with a logo use the highest error correction, so they still scan.

= Can I track how many times a QR code was scanned? =

Yes. Each QR code counts its scans and shows the count and the last scan date. Logged-in users, bots and link previews are not counted. No personal data is stored: no IP addresses, no cookies. If your host caches redirects at the edge, some scans may not reach WordPress and are not counted. With the 301 (permanent) redirect type, phones may remember the redirect, so repeat scans from the same phone are not counted; the default 302 counts every scan.

= Does this work with block themes? =

Yes! The plugin works with both classic and block themes.

= Can I display QR codes on the frontend? =

Yes. Add the QR Code block to any post or page, pick a QR code, and set its size. You can show a caption and a download button.

= What happens if I delete a QR code post? =

The QR code will stop working - visitors will see a 404 error. Only delete QR codes you're sure you don't need anymore.

= What happens if I uninstall the plugin? =

Deleting the plugin from the Plugins screen permanently removes all QR codes and settings, so every printed QR code stops working. Deactivating the plugin keeps your data.

= Can I export/import QR codes? =

Not currently.

== Screenshots ==

1. QR Code listing page - manage all your QR codes
2. Edit QR code - set destination URL and preview
3. QR code preview with download buttons
4. Settings page - customize default appearance
5. Frontend fallback view when no destination is set
6. QR code with a logo in the centre
7. The QR Code block in the editor
8. Scan counts in the QR code list

== Changelog ==

= 1.1.0 =
* New: Optional logo in the centre of a QR code: a site logo or a custom image, chosen per QR code
* New: QR Code block to show a QR code on any page, with size, caption and download button
* New: Scan counter per QR code, without personal data; bots and logged-in users are not counted

= 1.0.2 =
* Fix: QR code URLs no longer appear in site search results or the XML sitemap
* Fix: The "not configured" page now displays correctly on block themes
* Fix: Removed a duplicate robots meta tag on the "not configured" page
* Fix: Download filenames now handle accented letters, apostrophes and non-Latin titles
* Tweak: Tested up to WordPress 7.1
* Tweak: Removed references to a Premium version
* Tweak: Added an FAQ entry explaining that deleting the plugin removes all QR codes

= 1.0.1 =
* First release on WordPress.org
* New: Quick active/inactive toggle in the QR code list
* Security: Stricter input sanitization and a rate limit on the status toggle
* Tweak: Plugin Check fixes

= 1.0.0 =
* Initial release
* Create unlimited QR codes
* Set and update destination URLs
* Download PNG and SVG formats
* Customize colors and dot styles
* Automatic redirects (302/301)
* Translation ready
* Clean, WordPress-compliant code

== Upgrade Notice ==

= 1.1.0 =
Adds an optional logo, a QR Code block and a privacy-friendly scan counter. Existing QR codes are unchanged.

= 1.0.0 =
Initial release of Reusable QR Codes. Create reusable QR codes with changeable destinations!

== Privacy Policy ==

This plugin does not:
* Collect any personal data
* Use cookies
* Make external API calls
* Track users
* Store IP addresses

For each QR code it stores only a scan count and the date of the last scan.

== Support ==

For support, feature requests, or bug reports:
* [WordPress.org support forums](https://wordpress.org/support/plugin/reusable-qr-codes/)

== Credits ==

* QR Code generation powered by [QR Code Styling](https://github.com/kozakdenys/qr-code-styling)
* Developed by Minuar
