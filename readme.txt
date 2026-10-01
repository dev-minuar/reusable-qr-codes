=== Reusable QR Codes ===
Contributors: minuar
Tags: qr code, dynamic qr code, qr code generator, redirect, url shortener
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Print a QR code once and change where it points at any time. Optional logo, a QR Code block and a privacy-friendly scan counter.

== Description ==

A printed QR code usually points to one fixed address. When that address changes, the sign, menu or label has to be reprinted.

Reusable QR Codes points each code at a permanent address on your own site, which then redirects to any destination you choose. Change the destination in WordPress and every printed copy follows, with no reprint. Museums use it for exhibit labels, restaurants for menus, shops for promotions, and event organisers for schedules.

= Features =

* **Change the destination at any time**, as often as you like
* **Optional logo** in the centre: a site logo or a custom image, chosen per QR code
* **QR Code block** to show a code on any page, with an optional caption and download button
* **Scan counter** per code, with the date of the last scan
* **Print-ready downloads** as PNG (1024×1024) or SVG
* **Your own look**: colours and dot styles
* **Turn a code off** without deleting it; it then sends visitors to your home page
* **Translated** into German, Spanish and French

Everything runs on your own site: no account, no API key, no external service and no fees.

== Installation ==

1. Install the plugin from Plugins → Add New, or upload it to `/wp-content/plugins/`, then activate it.
2. Open **QR Codes** in the admin menu and add your first code.
3. Optional: set colours, dot style and a site logo under **QR Codes → Settings**.

== Frequently Asked Questions ==

= How does the code keep working when I change the destination? =

The code contains an address on your site, such as `yoursite.com/rqrc/museum-entrance/`. That address never changes. When someone scans it, WordPress redirects them to the destination you set.

= How many QR codes can I create? =

As many as you need.

= Can I add my logo? =

Yes. Set a site logo under QR Codes → Settings, then choose per code: no logo, the site logo or a custom image. Codes with a logo use the highest error correction, so they still scan reliably.

= How does the scan counter work? =

Each visit to a code's address adds one to its count, and the edit screen shows the last scan date. Logged-in users, bots and link previews are not counted.

Two cases can lower the count: a host that caches redirects before WordPress runs, and the 301 redirect type, because phones remember a permanent redirect. The default 302 counts every scan.

= Can I show a QR code on my site? =

Yes. Add the QR Code block to any post or page, choose a code and set its size.

= Does it work with my theme? =

Yes, with both block themes and classic themes.

= Will it slow down my site? =

No. Scripts load only on the screens and pages that need them, and a scan costs one redirect and one small database update.

= What happens if I delete a QR code, or the plugin? =

A deleted code stops working: anyone who scans it gets a "not found" page. Deleting the plugin from the Plugins screen removes all codes and settings, so every printed code stops working. Deactivating the plugin keeps your data.

== Screenshots ==

1. The QR code list
2. Editing a QR code: destination, preview and downloads
3. The preview with PNG and SVG downloads
4. Settings: colours, dot style and redirect type
5. The page visitors see when a code has no destination yet
6. A QR code with a logo in the centre
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

== Privacy ==

The plugin stores no personal data: no IP addresses, no cookies and no user agents. For each QR code it stores only a scan count and the date of the last scan. It makes no external requests.

QR codes are drawn with the open-source [QR Code Styling](https://github.com/kozakdenys/qr-code-styling) library, bundled with the plugin.
