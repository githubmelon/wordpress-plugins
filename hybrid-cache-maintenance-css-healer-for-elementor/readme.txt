=== Hybrid Cache Maintenance & CSS Healer for Elementor ===
Contributors: watermelons
Tags: elementor, cache, litespeed, wp-rocket
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 2.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prevent broken layouts and missing styles on Elementor sites caused by aggressive page caching, CDNs, or background cache flushes.

== Description ==

Every Elementor administrator eventually faces the dreaded "broken layout" syndrome[cite: 2]. This happens because Elementor routinely flushes and regenerates its static CSS files (during updates, template saves, or internal cleanups)[cite: 3]. If your site uses aggressive page caching or an edge CDN (like Cloudflare), those platforms continue serving cached HTML that references the old, deleted CSS files, causing the page to display completely unstyled[cite: 4].

**Hybrid Cache Maintenance & CSS Healer for Elementor** solves this permanently using a robust, two-layer hybrid strategy[cite: 5]:

1. **The Proactive Maintenance Engine (Every 12 Hours):** A background WP-Cron script automatically triggers every 12 hours to safely clear Elementor's file cache and systematically flushes major caching environments (LiteSpeed Cache, WP Rocket, W3 Total Cache, and WP Super Cache)[cite: 5]. It then immediately fires an asynchronous background loopback request to pre-warm the primary homepage layout[cite: 6].
2. **The Reactive Self-Healing Safety Net (On-Demand):** If a visitor, web browser, or CDN requests an Elementor CSS file that has vanished from the server within that 12-hour window, our interceptor catches the `404 Not Found` error[cite: 7]. It instantly orders Elementor to rebuild that specific post or template asset in the background, serving the clean style file inline as a successful `200 OK` response before the visitor's browser notices a thing[cite: 8].

### Supported Caching Ecosystems
* **LiteSpeed Cache (LSCWP)** (Including upstream Cloudflare integrations) [cite: 9]
* **WP Rocket** [cite: 9]
* **W3 Total Cache** [cite: 9]
* **WP Super Cache** [cite: 9]

### Credits & Open-Source Acknowledgments
The reactive 404 interceptor logic implemented within this extension is adapted and optimized from the excellent standalone open-source repository "Elementor CSS Regenerator" authored by Robert Went[cite: 9].

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugins dashboard[cite: 10].
2. Activate the plugin through the **Plugins** screen in WordPress[cite: 11].
3. The plugin will immediately verify your Elementor installation and establish the automatic 12-hour cron cycle. No extra configurations or settings pages are required! [cite: 12, 13]

== Frequently Asked Questions ==

= Does this plugin have a configuration or settings panel? =
No. It is engineered as a zero-overhead, "set-and-forget" utility. It automatically runs silently in the background and respects your existing caching rules[cite: 14].

= How can I force-test the maintenance cycle immediately? =
You can trigger the 12-hour maintenance callback using WP-CLI via your server terminal[cite: 15]:
`wp cron event run wm_ech_elementor_clear_cache` [cite: 15]
Alternatively, you can utilize developer utility plugins like **WP-Crontrol** to find and click "Run Now" on the `wm_ech_elementor_clear_cache` hook[cite: 15].

= Does this clear my Cloudflare Edge Cache? =
If you are utilizing the LiteSpeed Cache plugin linked up to your Cloudflare API token, our maintenance execution will automatically push the global purge request straight up to Cloudflare's network edge seamlessly[cite: 16].

= Can I check if the background healer is actively working? =
Yes[cite: 17]. If you add `define( 'WM_ECH_DEBUG', true );` to your `wp-config.php` file, the plugin will write detailed event timestamps directly to your standard server error log logs whenever it detects a missing asset or fires a background warmer loopback[cite: 18].

== Angelic Care & Debugging ==

If you ever encounter performance bottlenecks or notice your server environments dropping concurrent requests during loopbacks, you can safely define `WM_ECH_DEBUG` as true to audit local execution times[cite: 19].

== Changelog ==

= 2.1.1 =
* Fixed Plugin URI to point to the correct, public landing page URL.
* Refactored root directory and primary file naming structure to align perfectly with the assigned WordPress.org slug.
* Synchronized Text Domain and internal internationalization strings for strict directory compliance.

= 2.1.0 =
* Initial public directory release packaging[cite: 20].
* Added multi-cache ecosystem integration (WP Rocket, W3TC, WP Super Cache)[cite: 21].
* Implemented fail-safe admin notice dependency structural check gates for Elementor Core[cite: 22].
* Fully refactored syntax blocks to conform precisely to official WordPress Core Coding Standards (WPCS)[cite: 23].

= 2.0.0 =
* Internal revision update. Integrated Robert Went's on-demand 404 interceptor array framework alongside our core cron scheduler module to create a unified hybrid solution[cite: 24].
* Changed standard cycle cadence intervals from 15 minutes to 12 hours to preserve edge CDN cache efficiencies[cite: 25].

= 1.0.0 =
* Legacy baseline build used internally by Watermelon Web Works for automated 15-minute file system flushes[cite: 26].