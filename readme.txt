=== VPN leaks test ===
Contributors: macvk
Tags: security,admin
Requires at least: 4.7
Tested up to: 5.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The plugin contains the leaks test package: 
- DNS leak test
- WEBrtc IP leak test
- Email IP leak test

The plugin integrates with https://bash.ws, a 3rd party service, to have it do the VPN testing.
The plugin essentially makes a remote request (POST) to a service URL, passing an user IP address as a parameter.
The service is free to use and does not require an API key and/or registration, see terms of use https://bash.ws/terms.

== Installation ==

1. Install and activate the plugin through the 'Plugins' menu in WordPress.
2. Go to Settings > VPN leaks test.
3. Perform the necessary settings and press the Save button.
4. Use shortcode [vltp id=TEST_ID] to integrate the test into frontend of the website.

== Changelog ==

= 1.0.0 =
Initial version

