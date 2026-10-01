=== Reusable QR Codes ===
Contributors: minuar
Tags: qr code, redirect, dynamic qr, qr manager, url shortener
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
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
* ✅ **Privacy Friendly** - No tracking, no external calls
* ✅ **Translation Ready** - Fully internationalized
* ✅ **Clean Code** - WordPress coding standards compliant

= Technical Details =

* Lightweight and performant - minimal database impact
* Conditional asset loading - scripts only when needed
* Secure - nonces, capability checks, input sanitization
* Follows WordPress coding standards
* Uses native WordPress functions (no bloat!)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/reusable-qr-codes/` or install via WordPress plugin installer
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'QR Codes' in your admin menu to create your first QR code
4. Configure default settings under Settings → QR Codes (optional)

== Frequently Asked Questions ==

= Do I need any external services or API keys? =

No! This plugin is completely self-contained and works entirely within your WordPress installation. No external dependencies, no API keys, no recurring fees.

= Can I really change where the QR code points without reprinting it? =

Yes! That's the whole point. The QR code contains a permanent URL on your site (like `yoursite.com/qr/museum-exhibit-1/`). When someone scans it, they're instantly redirected to whatever destination URL you've set. Change that destination anytime in WordPress.

= How many QR codes can I create? =

Unlimited! Create as many as you need.

= What formats can I download? =

PNG (high resolution 1024x1024px) and SVG (vector, scales to any size). Both are perfect for printing.

= Will this slow down my site? =

No. The plugin is very lightweight and only loads assets when needed. The redirect happens instantly with minimal database queries.

= Can I use my own logo in the QR code? =

No. The QR codes support custom colors and dot styles, but not logos.

= Can I track how many times a QR code was scanned? =

No. The plugin does not track scans, which keeps it privacy-friendly.

= Does this work with block themes? =

Yes! The plugin works with both classic and block themes.

= Can I display QR codes on the frontend? =

No. The plugin focuses on backend management and downloads. Download the PNG or SVG and add it to any page as an image.

= What happens if I delete a QR code post? =

The QR code will stop working - visitors will see a 404 error. Only delete QR codes you're sure you don't need anymore.

= What happens if I uninstall the plugin? =

Deleting the plugin from the Plugins screen permanently removes all QR codes and settings, so every printed QR code stops working. Deactivating the plugin keeps your data.

= Can I export/import QR codes? =

Not currently. This is planned for a future version.

== Screenshots ==

1. QR Code listing page - manage all your QR codes
2. Edit QR code - set destination URL and preview
3. QR code preview with download buttons
4. Settings page - customize default appearance
5. Frontend fallback view when no destination is set

== Changelog ==

= 1.0.2 =
* Fix: QR code URLs no longer appear in site search results or the XML sitemap
* Fix: The "not configured" page now displays correctly on block themes
* Fix: Removed a duplicate robots meta tag on the "not configured" page
* Fix: Download filenames now handle accented letters, apostrophes and non-Latin titles
* Tweak: Tested up to WordPress 7.1
* Tweak: Removed references to a Premium version
* Tweak: Added an FAQ entry explaining that deleting the plugin removes all QR codes

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

= 1.0.0 =
Initial release of Reusable QR Codes. Create reusable QR codes with changeable destinations!

== Privacy Policy ==

This plugin does not:
* Collect any personal data
* Use cookies
* Make external API calls
* Track users
* Store IP addresses

== Support ==

For support, feature requests, or bug reports:
* [WordPress.org support forums](https://wordpress.org/support/plugin/reusable-qr-codes/)

== Credits ==

* QR Code generation powered by [QR Code Styling](https://github.com/kozakdenys/qr-code-styling)
* Developed by Minuar
