=== Image Preloading ===
Contributors: snowbedding
Tags: image, preload, prefetch, performance, speed
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.2

Modern image preloading/prefetching plugin for WordPress to improve page loading performance.

== Description ==

Image Preloading is a powerful WordPress plugin that helps improve your website's performance by preloading images in the background. This results in faster page loading experiences, especially beneficial for photo galleries, image-heavy sites, and e-commerce stores.

### Features

* **Multiple Preloading Methods**: Choose between JavaScript preloading, modern Link preload headers, or both
* **Unlimited Image URLs**: Add as many images as you need
* **Modern JavaScript**: Uses Promise-based loading with proper error handling
* **Performance Optimized**: Only loads when needed and uses browser idle time when available
* **Security Focused**: Proper input sanitization and validation
* **Admin Interface**: Clean, modern settings page integrated with WordPress admin
* **Localization Ready**: Translation-ready with proper text domains

### Preloading Methods

1. **JavaScript Method**: Uses modern JavaScript with Promise-based image loading. Compatible with all browsers.
2. **Link Preload**: Uses HTML `<link rel="preload">` tags for modern browsers with native preloading support.
3. **Both Methods**: Combines both approaches for maximum compatibility and performance.

### Use Cases

* Photo galleries and portfolios
* E-commerce product images
* Hero banners and sliders
* Background images
* Any image-heavy content

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "Image Preloading"
4. Click **Install Now**
5. Activate the plugin

### Manual Installation

1. Download the plugin ZIP file
2. Upload the plugin files to `/wp-content/plugins/image-preloading/`
3. Activate the plugin through the **Plugins** menu in WordPress

== Frequently Asked Questions ==

= How does image preloading work? =

Image preloading loads images in the background before they are needed, so when users navigate to pages containing these images, they appear instantly instead of loading progressively.

= Which preloading method should I choose? =

* **JavaScript**: Best for compatibility with all browsers
* **Link Preload**: Best performance for modern browsers
* **Both**: Recommended for maximum compatibility and performance

= Can I preload images from external domains? =

Yes, but ensure the external server allows cross-origin requests. The plugin properly handles CORS headers.

= How many images can I preload? =

There's no strict limit, but keep in mind that preloading too many large images can impact initial page load performance. Use judiciously.

= Does this work with caching plugins? =

Yes, the plugin works well with caching plugins and can complement them by ensuring images are cached even before they're viewed.

== Screenshots ==

1. **Settings Page** 
2. **HTML code of preloading** 
3. **Preloading waterfall**

== Changelog ==

= 2.0.0 =
* Complete rewrite with modern WordPress standards
* Added multiple preloading methods (JavaScript, Link preload, Both)
* Modern admin interface with improved UX
* Security enhancements and proper input sanitization
* Performance optimizations with requestIdleCallback support
* Promise-based JavaScript with proper error handling
* Better localization support
* PHP 7.2+ requirement for better performance
* WordPress 5.0+ compatibility

= 1.0.0 =
* Initial release
* Basic JavaScript image preloading functionality

== Upgrade Notice ==

= 2.0.0 =
This version includes major improvements and requires WordPress 5.0+. All settings will be preserved during upgrade.

== Support ==

For support, bug reports, or feature requests, please visit: [Github](https://github.com/snowbedding/image-preloading)

== Contributing ==

Contributions are welcome! Please feel free to submit pull requests or open issues on GitHub.

== License ==

This plugin is licensed under the GPLv2 or later.
License URI: https://www.gnu.org/licenses/gpl-2.0.html