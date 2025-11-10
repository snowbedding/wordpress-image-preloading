# Image Preloading (WordPress Plugin)

Modern image preloading/prefetching plugin for WordPress to improve page loading performance.

## ✨ Features

- **Multiple Preloading Methods**: JavaScript preloading, Link preload headers, or both
- **Modern JavaScript**: Promise-based loading with proper error handling and concurrency control
- **Performance Optimized**: Uses `requestIdleCallback` when available for optimal timing
- **Conditional Loading**: Load on specific page types, exclude specific pages by ID
- **Admin Interface**: Clean, modern settings page integrated with WordPress admin
- **Security Focused**: Proper input sanitization and output escaping
- **Localization Ready**: Translation-ready with proper text domains
- **Backward Compatible**: Automatic migration from old plugin versions

## 📋 Requirements

- **WordPress**: 5.0+
- **PHP**: 7.2+
- **MySQL**: 5.6+

## 🚀 Installation

1. Download and extract the plugin files
2. Upload the `image-preloading` folder to `wp-content/plugins/`
3. Activate the plugin through WordPress admin → **Plugins**
4. Go to **Settings → Image Preloading** to configure options
5. Add image URLs to preload in the settings page

## 📖 Usage

### Basic Setup

1. After activation, go to **Settings → Image Preloading**
2. Choose your preloading method (JavaScript, Link Preload, or Both)
3. Add image URLs (one per line) in the text area
4. Configure additional options as needed
5. Save settings

### Preloading Methods

#### JavaScript Method (Recommended)
- Uses modern JavaScript with Promise-based image loading
- Compatible with all browsers
- Includes error handling and performance optimizations
- Respects browser concurrency limits

#### Link Preload Method
- Uses HTML `<link rel="preload">` tags in the document `<head>`
- Better performance in modern browsers
- Falls back gracefully in older browsers
- Check page source to verify `<link rel="preload">` tags are present

#### Both Methods
- Combines both approaches for maximum compatibility and performance
- Recommended for most sites

### Configuration Options

#### Basic Settings
- **Enable Image Preloading**: Toggle the entire functionality on/off
- **Preloading Method**: Choose JavaScript, Link Preload, or Both
- **Image URLs**: Enter all URLs you want to preload (one per line)

#### Conditional Loading
- **Load on**: Choose where to load preloading scripts
  - All pages (default)
  - Front page (static page or posts page)
  - Blog posts page (when using static front page)
  - Single posts only
  - Pages only
  - Archive pages only

#### Page Exclusion
- **Exclude Pages**: Comma-separated list of page/post IDs to exclude
- Useful for excluding heavy pages or specific landing pages

## ⚙️ Configuration

### Settings Page

Located in **Settings → Image Preloading**:

The settings page includes quick access buttons to the WordPress.org plugin page and GitHub repository for support and updates.

1. **Enable/Disable**: Global toggle for the plugin
2. **Preloading Method**: Choose JavaScript, Link Preload, or Both
3. **Image URLs**: Add one URL per line (supports external domains)
4. **Conditional Loading**: Choose page types to load on
5. **Page Exclusion**: Exclude specific page/post IDs

### Advanced Configuration

#### Image URL Format
```
https://example.com/image1.jpg
https://example.com/image2.png
https://cdn.example.com/hero-banner.webp
```

#### Page ID Exclusion
```
1, 5, 12, 25
```

## 🐛 Troubleshooting

### Images Not Preloading

1. **Check URLs**: Ensure all URLs are valid and accessible
2. **Browser Console**: Check for JavaScript errors in browser developer tools
3. **Network Tab**: Verify preload requests are being made
4. **CORS Issues**: External domains may need CORS headers
5. **Link Preload Method**: View page source (Ctrl+U) to verify `<link rel="preload">` tags are present in `<head>` section
6. **Method Selection**: Ensure correct preloading method is selected in settings

### Performance Issues

1. **Use Conditional Loading**: Only load on specific pages where needed
2. **Check Image Sizes**: Large images impact performance
3. **Browser Limits**: Modern browsers limit concurrent requests (plugin optimizes automatically)
4. **Monitor Console**: Check browser console for loading status and any errors

### Console Debugging

The plugin provides console logging for troubleshooting:

1. **Check Browser Console**: Open browser developer tools (F12)
2. **Look for "[Image Preloading]" messages**: These show preload status and any errors
3. **Verify Method**: Console will show which preloading method is being used
4. **Monitor Progress**: See successful/failed image loads with URLs

### Settings Not Saving

1. **Permissions**: Ensure you have proper admin permissions
2. **Cache**: Clear any caching plugins
3. **JavaScript**: Ensure JavaScript is enabled in your browser

### Migration Issues

If upgrading from version 1.x:
1. Settings should migrate automatically
2. Check admin notices for migration confirmation
3. Old settings format will be cleaned up automatically

## 🏗️ Development

### Architecture

Clean object-oriented architecture with proper separation of concerns:

```
image-preloading.php          # Main plugin file with class definitions
├── Image_Preloading_Plugin   # Main plugin class (singleton pattern)
│   ├── __construct()         # Hook registration and initialization
│   ├── load_options()        # Settings loading with migration
│   ├── add_admin_menu()      # Admin menu registration
│   ├── register_settings()   # Settings API integration
│   ├── sanitize_options()    # Input validation and sanitization
│   ├── enqueue_scripts()     # Frontend asset loading
│   └── should_load_scripts() # Conditional loading logic
├── assets/
│   └── js/
│       └── image-preloading.js # Modern JavaScript preloader
├── languages/                # Translation files
└── README.md                 # This documentation
```

### Key Classes

- `Image_Preloading_Plugin` - Main plugin controller with singleton pattern
- Modern JavaScript preloader with Promise-based loading
- Settings API integration with proper validation

### Internationalization

- Text domain: `image-preloading`
- Translation files: `languages/` directory
- Fully translatable admin interface and frontend output

## 📄 License

Licensed under the GNU General Public License v2.0 or later.

```
Copyright (C) 2025 snowbedding

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Made with ❤️ for the WordPress community**
