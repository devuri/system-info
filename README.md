# System Info Plugin

## Description

The System Info plugin generates `composer.json` and `system` info based on the active plugins and theme in your WordPress installation. 

## Features

- Scans the current WordPress setup for active plugins and the active theme.
- Generates a `composer.json` info with the necessary requirements.
- Outputs the generated `composer.json` content in the admin interface.

## Installation

1. Download the plugin files.
2. Upload the `system-info` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.


## Example Output

```json
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
```

## Frequently Asked Questions

**Q:** What does this plugin do?  
**A:** This plugin scans the current WordPress setup for active plugins and the active theme and generates `composer.json` info with the necessary requirements.

**Q:** Where can I find the generated `composer.json` file?  
**A:** The generated content is displayed in the admin interface under `Tools -> System Info`. You can copy the content from there.

**Q:** Does this plugin write any files to the server?  
**A:** No, the plugin does not write any files to the server. It only displays the generated `composer.json` content for you to copy.

## License

This plugin is licensed under the GPL-2.0+ license.
