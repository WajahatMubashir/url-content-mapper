=== URL Content Mapper ===
Contributors: wajahatmubashir
Donate link: https://wajahatmubashir.netlify.app/
Tags: ga4, analytics, google tag manager, content groups
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to dynamically add content groups in GA4 and inject code before GA4/GTM scripts.

== Description ==
URL Content Mapper dynamically adds content groups in Google Analytics 4 and injects code before GA4 or Google Tag Manager scripts in the `<head>` tag of your WordPress site. This allows you to better organize and track user activity, ensuring that your analytics data is both accurate and comprehensive.

== Installation ==
1. **Upload the plugin files** to the `/wp-content/plugins/wp-url-content-mapper/` directory, or install the plugin through the WordPress plugins screen directly by uploading the zip file.
2. **Activate the plugin** through the 'Plugins' screen in WordPress.
3. **Navigate to Settings** → **URL Content Mapper** to configure the plugin.
4. Once configured, the plugin automatically injects your custom code before GA4/GTM scripts on every page load.

== Frequently Asked Questions ==
= Does this plugin work with both GA4 and GTM? =
Yes. It injects a script responsible for creating content groups before GA4 or GTM scripts, allowing you to set content groups more effectively.

= Will it slow down my site? =
This plugin adds a minimal amount of code, so the impact on performance should be negligible. However, always test thoroughly on your environment.

= Where can I find the plugin settings? =
After activation, go to **Settings** → **URL Content Mapper** in your WordPress Admin panel.

== Changelog ==
= 1.0 =
* Initial release: Adds the capability to inject code before GA4/GTM scripts and configure content groups.

== Upgrade Notice ==
= 1.0 =
Initial version. No upgrade needed.
