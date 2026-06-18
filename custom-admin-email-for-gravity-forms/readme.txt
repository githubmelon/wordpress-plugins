=== Custom Admin Email for Gravity Forms ===
Contributors: watermelons
Tags: gravity forms, email, admin email, notifications, override
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a new settings tab to Gravity Forms to globally override the {admin_email} merge tag destination.

== Description ==

By default, Gravity Forms uses the global WordPress site admin email whenever the `{admin_email}` merge tag is used in form notifications. While this is fine for basic setups, it often means form submissions get mixed up with core WordPress administrative alerts, update notices, and security warnings.

**Custom Admin Email for Gravity Forms** fixes this by giving Gravity Forms its own dedicated "Admin Email" setting. 

This lightweight add-on integrates seamlessly into the native Gravity Forms Settings menu. It allows you to specify a custom email address that will automatically override the `{admin_email}` merge tag across all of your form notifications (To, From, Reply-To, and BCC fields) without altering your site-wide WordPress core settings.

### Features
* Native UI integration in the Gravity Forms Settings menu.
* Globally overrides the `{admin_email}` merge tag for all forms.
* Sanitizes inputs to ensure valid email addresses are used.
* Falls back to the default WordPress admin email if left blank.
* Built using the official Gravity Forms Add-On Framework for maximum compatibility and security.

== Installation ==

1. Ensure you have the **Gravity Forms** plugin installed and activated.
2. Upload the `gf-custom-admin-email` folder to the `/wp-content/plugins/` directory, or install the ZIP file via the WordPress plugins page.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to **Forms > Settings > Admin Email** in your WordPress dashboard.
5. Enter your desired custom email address and save.

== Frequently Asked Questions ==

= What happens if I leave the custom email field blank? =
If you leave the field blank, or enter an invalid email address, the plugin will gracefully fall back to using the standard WordPress admin email. It will not break your notifications.

= Do I need to update my existing form notifications? =
No! As long as your existing forms are using the `{admin_email}` merge tag, this plugin will intercept and replace the destination automatically. You do not need to manually edit your individual forms.

= Does this change my main WordPress admin email? =
No, this plugin only affects Gravity Forms notifications. Your WordPress core emails (like new user registrations, password resets, and core updates) will still go to the original WordPress admin email.

= Is Gravity Forms required? =
Yes. This is an add-on for Gravity Forms and requires Gravity Forms 2.5 or higher to function.

== Changelog ==

= 1.0.1 =
* Updated plugin/readme metadata for WordPress.org compatibility checks.
* Renamed plugin so the title does not begin with a restricted trademarked term.

= 1.0.0 =
* Initial release. Added global override for {admin_email} merge tag via native GF Settings tab.
