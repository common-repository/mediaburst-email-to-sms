=== Clockwork SMS Notfications ===
Author: Clockwork
Website:  http://www.clockworksms.com/platforms/wordpress/?utm_source=wordpress&utm_medium=plugin&utm_campaign=smsnotifications
Contributors: mediaburst, martinsteel
Tags: SMS, Clockwork, Clockwork SMS, Mediaburst, SMS Notifications, Text Message, Free SMS, Free SMS Notifications
Text Domain: wp_sms
Requires at least: 3.0.0
Tested up to: 4.9.1
Stable tag: 3.0.4

Send your subscribers SMS messages when you publish a new post.

== Description ==

*Free:* Send your subscribers SMS messages when you publish a new post via free email to SMS gateways. Many mobile providers throughout the world provide free email to SMS gateways, which this plugin uses to send the SMS notifications. Because the gateways are free, some mobile providers do not prioritise their maintenance or publicise their existence, this plugin uses a central list of known working gateways which is provided by SMS service provider [Clockwork](http://www.clockworksms.com/ "Clockwork SMS") as a service to the community.

*Paid:* Send your subscribers SMS messages when you publish a new post through Clockwork SMS. Clockwork is a paid SMS gateway. We charge 5p per SMS in the UK, and deliver to over 900 networks in over 200 countries worldwide. It's free to upgrade the plugin - you only pay for the SMS messages you send to your subscribers. This also simplifies the widget so that instead of having to choose their country and network, your visitors just have to enter their phone number - and we do the rest.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or install through your WordPress admin area.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the "Clockwork SMS" widget to one of your sidebars.

== Frequently Asked Questions ==

= Nobody is receiving notifications! =

Check that the email service from your WordPress site is working. One way to do this is to log out then try using the "Lost your password?" link; if you don't receive the email, then you may have a problem with the ability for WordPress to send email.

The most common email sending problem with WordPress is that the default "from" email address is "wordpress@[your domain here]", which is an address that often doesn't exist. Here are a few things which you could try to fix the problem:

* Can you create the email address "wordpress@[your domain here]"? In that case, create it.
* Change the "from" address to one which you can create, or which already exists, using a plugin. This plugin might help: [WP Mail SMTP](http://wordpress.org/extend/plugins/wp-mail-smtp/) (this plugin does more than allow you to change the "from" address, but first just restrict yourself to that).
* Also using the [WP Mail SMTP](http://wordpress.org/extend/plugins/wp-mail-smtp/), you could try changing your email **sending** service to the one which comes with your email host (contact them for SMTP details).

If all the above fail, and your WordPress site is not sending **any** email, you could try [hitting the forums](http://wordpress.org/support/forum/how-to-and-troubleshooting) where there's a bunch of super helpful WordPress people, just like you. Make sure you explain your problem clearly and detail what you've tried (without including any login information).

If your WordPress site is sending the forgotten password email, but your SMS notifications aren't arriving, please [email us](mailto:hello@clockworksms.com).

= How do I add an email to SMS gateway? =

If you know of a gateway we haven't listed, please [email us](mailto:hello@clockworksms.com).

== Changelog ==

= 3.0.4 =
* Remove old branding

= 3.0.3 =
* Security Hardening

= 3.0.0 =

* Fix XSS vulnerability
* Fix bug that deleted subscribers when plugin is deactivated or upgraded

= 2.0.3 =

* Add unsubscribe function in WordPress admin
* Fix table naming in upgrade code when upgrading from 1.x releases
* Remove mysql_ functions as WordPress 3.9 uses mysqli instead.

= 2.0.2 =

* PHP 5.2 compatability fix
* Fix default styling of sidebar widget
* Switched to MIT license from ISC as it's better known

= 2.0.1 =

* Bugfix release, couple of incorrect paths within the plugin

= 2.0.0 =

* Full rewrite
* Resolves many issues with theme and plugin incompatibility
* Provides an upgrade route and compatibility with other Clockwork SMS plugins

= 1.0.8 =

* Fixed an issue with HTML entities not being decoded when SMS is sent.
* Fixed an error whereby {post_url} was being returned to the SMS as blank.

= 1.0.7 =

* Update to base plugin class to resolve some theme conflicts.

= 1.0.5 =

* Cope with WordPress being loaded slightly unconventionally by themes and plugins for AJAX and similar purposes.

= 1.0.4 =

* Adds support for `post_excerpt` in messages, as per a user request.

= 1.0.3 =

* Fixed a bug relating to some tokens, e.g. `{post_title}`, not being replaced in SMS messages.

= 1.0.2 =

* Fixes for admin menu issues
* Adds an optional Mediaburst link to the widget

= 1.0.1 =

* Minor tidying

= 1.0 =

* First stable release
