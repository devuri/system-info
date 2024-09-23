=== System Info ===
Contributors: icelayer
Tags: system, info, tools
Requires at least: 3.4
Tested up to: 6.6
Stable tag: 0.1.9
Requires PHP: 7.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The **System Info** plugin generates a `composer.json` file and provides system information based on the active plugins and theme in your WordPress installation. It's designed to help you easily manage your site's dependencies through Composer, a tool for dependency management in PHP.

== Features ==

* Scans your current WordPress setup to detect active plugins and the active theme.
* Automatically generates `composer.json` content with the necessary Composer requirements based on the detected plugins and theme.
* Displays the generated `composer.json` content in the WordPress admin interface for easy copying.

== Installation ==

1. Download the plugin files from WordPress.org or your preferred source.
2. Upload the `system-info` folder to the `/wp-content/plugins/` directory on your WordPress server.
3. Activate the plugin through the 'Plugins' menu in your WordPress admin dashboard.

== Usage ==

1. After activating the plugin, navigate to `Tools -> System Info` in the WordPress admin interface.
2. Review the automatically generated `composer.json` content.
3. Copy the displayed content and use it in your Composer setup as needed.

== Example Output ==

{
    "name": "yourname/wordpress-site",
    "description": "A WordPress site managed with Composer",
    "require": {
        "php": ">=7.4",
        "johnpbloch/wordpress": "*",
        "wpackagist-plugin/example-plugin": "*",
        "wpackagist-theme/example-theme": "*"
    }
}

== Frequently Asked Questions ==

= What does this plugin do? =
The plugin scans your current WordPress setup for active plugins and the active theme, then generates `composer.json` information containing the necessary requirements for Composer.

= Where can I find the generated `composer.json` file? =
The generated `composer.json` content is displayed in the WordPress admin interface under `Tools -> System Info`. You can easily copy the content from there.

= Does this plugin write any files to the server? =
No, the plugin does not write any files to the server. It simply generates and displays the `composer.json` content for you to copy.

= Do I need to have Composer installed to use this plugin? =
No, you don't need to have Composer installed to use this plugin. It simply generates the `composer.json` file content. Composer itself will need to be installed separately if you intend to manage dependencies with it.
