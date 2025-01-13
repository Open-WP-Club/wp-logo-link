# WP Logo Link

A lightweight WordPress plugin that enhances your site logo's functionality by adding customizable click behaviors. Left-click directs to your homepage, while right-click leads to your specified assets page.

## Features

- 🏠 Left-click on logo leads to homepage
- 🎨 Right-click redirects to your assets page
- 🔄 Falls back to Media Library if no assets URL is set

## Installation

1. Clone this repository or download the ZIP file

```bash
git clone https://github.com/Open-WP-Club/wp-logo-link.git
```

2. Upload to your WordPress site:
   - Upload the `wp-logo-link` folder to `/wp-content/plugins/`
   - Or compress the folder and upload via WordPress Admin → Plugins → Add New → Upload Plugin

3. Activate the plugin through WordPress Admin → Plugins

4. Configure the assets page URL in Settings → General

## Configuration

The plugin adds a simple setting in WordPress Admin:

1. Go to Settings → General
2. Scroll down to "WP Logo Link Settings"
3. Enter your desired assets page URL
4. Save changes

## Requirements

- WordPress 5.0 or higher
- Any theme that uses standard WordPress logo classes

## Support

If you encounter any issues or need assistance:

1. Open an issue in this repository
2. Provide details about your WordPress version and theme
3. Include steps to reproduce the issue

## License

This project is licensed under the GPL v2 License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Feel free to:

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/awesome-feature`
3. Commit your changes: `git commit -m 'Add awesome feature'`
4. Push to the branch: `git push origin feature/awesome-feature`
5. Open a Pull Request
