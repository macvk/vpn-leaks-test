=== VPN leaks test ===
Contributors: macvk
Tags: security,admin
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 5.4
Stable tag: 1.0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The plugin contains the leaks test package: 
- DNS leak test
- WEBrtc IP leak test
- Email IP leak test
- Torrent IP leak test

The plugin integrates with https://bash.ws, a 3rd party service, to have it do the VPN testing.
The service is free to use and does not require an API key and/or registration, see terms of use https://bash.ws/terms.
The plugin essentially makes a remote request (POST) to a service URL, passing an user IP address as a parameter.
Depending on the test the following additional data are received by the service:
- DNS leak test: the plugin does several DNS-lookup requests to the service;
- WEBrtc IP leak test: the plugin receives additional IPs from the Internet browser;
- Email IP leak test: the plugin asks user to send an empty email to the service;
- Torrent IP leak test: the plugin asks user to download a torrent file from the service.

== Installation ==

1. Install and activate the plugin through the 'Plugins' menu in WordPress.
2. Go to Settings > VPN leaks test.
3. Perform the necessary settings and press the Save button.
4. Use shortcode [vltp id=TEST_ID] to integrate the test into frontend of the website.

== Changelog ==

= 1.0.0.1 =
Torrent leak test added.

= 1.0.0.0 =
Initial version

