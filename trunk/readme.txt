=== SlimFAQ ===
Contributors: consti
Tags: intercom, intercom.io, crm, messaging, contact form, support, email, feedback, customer relationship management, users, slimfaq, faq, sidebar
Requires at least: 3.8
Tested up to: 4.5
Stable tag: trunk
License: GPL license http://www.opensource.org/licenses/gpl-license.php

Easy integration of the SlimFAQ sidebar with optional Intercom integration.

== Description ==

[SlimFAQ](https://slimfaq.com) provides a simple FAQ platform and great integration with Intercom and as a sidebar on WordPress.

This plugin generates the Javascript install code to integrate all of this functionality into your WordPress-powered web app.

To use the plugin you must have a [SlimFAQ](https://slimfaq.com) account. You can [sign up for free](https://slimfaq.com) and add a great FAQ to your WordPress web app.

This plugin is fully compatible with the [Official Intercom WordPress plugin](https://wordpress.org/plugins/intercom).

== Frequently Asked Questions ==

= How do I get started? =

This plugin only works with a SlimFAQ account. You can get started for free at [slimfaq.com](https://slimfaq.com).

= Where can I find my FAQ ID? =

After signing up for a SlimFAQ account, you can find your FAQ ID in your [FAQ's settings page](https://slimfaq.com/account/settings).

= Can I display the FAQ to users who have not logged in? =

Just enable the "show for logged out users" flag on the settings page.

= How do I integrate SlimFAQ with Intercom? =

Install and setup the [Official Intercom WordPress Plugin](https://wordpress.org/plugins/intercom) - then install the SlimFAQ plugin. In the SlimFAQ settings, enable "Integrate with Intercom".

= Can I completely disable the snippet on certain pages? =

Sure, just use the `ll_slimfaq_output_snippet` filter. Here's an example:

`
add_filter( 'll_slimfaq_output_snippet', 'no_slimfaq_on_page_10' );

function no_slimfaq_on_page_10( $show ) {

	if ( is_page( 10 ) )
		return false;

	return true;

}
`

= Does this plugin work on older versions of WordPress or PHP? =

Possibly, but I've not tried. I can only provide support if you're using the latest version of this plugin together with the latest version of WordPress and PHP 5.2.4 or newer.

== Screenshots ==

1. The SlimFAQ plugin integrates smoothly as a sidebar in your WordPress-powered web app.

== Installation ==

1. Upload the slimfaq-for-wordpress folder to your wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to the settings page.
4. Enter your FAQ ID.
6. Choose if you like to show the sidebar for logged out users and whether to integrate with Intercom.
7. Highly recommended: if you are using Intercom, get the [Official Intercom WordPress Plugin](https://wordpress.org/plugins/intercom).

== Changelog ==

= 1.1.1 (18th June 2016) =
* Cleanup naming and use HTTPS for all links to the plugin.

= 1.1 (15th June 2016) =
* Remove user role setting; mention the Official Intercom WordPress plugin. Change name to SlimFAQ.

= 1.0 (14th June 2016) =
* Initial release, based on [Intercom for WordPress Plugin](https://wordpress.org/plugins/intercom-for-wordpress)
