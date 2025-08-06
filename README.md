# Tarot Yes/No Reading Plugin

A WordPress plugin that provides an interactive tarot yes/no reading system with card selection and interpretations.

## Features

- Interactive tarot card reading interface
- Customizable number of cards to select
- Card interpretations for different categories (general, love, money, health, career)
- Upright and reversed card orientations
- Admin interface for managing settings and interpretations
- Responsive design

## Database Structure

The plugin now uses custom database tables instead of WordPress options for better data management:

### Tables Created

1. **`wp_tynr_settings`** - Stores plugin settings
   - `id` (mediumint) - Primary key
   - `setting_key` (varchar) - Setting name
   - `setting_value` (longtext) - Setting value (JSON for arrays)
   - `created_at` (datetime) - Creation timestamp
   - `updated_at` (datetime) - Last update timestamp

2. **`wp_tynr_interpretations`** - Stores card interpretations
   - `id` (mediumint) - Primary key
   - `card_id` (int) - Card ID
   - `orientation` (enum) - 'upright' or 'reversed'
   - `category` (enum) - 'general', 'love', 'money', 'health', 'career'
   - `interpretation` (text) - Interpretation text
   - `created_at` (datetime) - Creation timestamp
   - `updated_at` (datetime) - Last update timestamp

## Installation

1. Upload the plugin files to `/wp-content/plugins/tarot-yes-no-reading/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically create the required database tables
4. If you have existing data in wp_options, a migration notice will appear in the admin

## Migration from wp_options

If you're upgrading from a previous version that used wp_options, the plugin will automatically detect this and show a migration notice. Click "Migrate Now" to transfer your data to the new database structure.

### Migration Process

The migration will:
1. Transfer settings from `wp_tynr_settings` option to the settings table
2. Transfer back image settings from `tynr_back_image_settings` option to the settings table
3. Transfer interpretations from `tynr_interpretations` option to the interpretations table
4. Clean up old options after successful migration

## Usage

### Shortcode

Use the shortcode `[tarot_yes_no_reading]` on any post or page to display the reading interface.

### Admin Interface

Access the admin interface at **Tarot Yes/No** in the WordPress admin menu.

#### Settings Tab
- Configure number of cards to display
- Enable/disable animations
- Show/hide card names

#### Back Card Images Tab
- Upload and manage back card images
- Set number of cards to show
- Configure image names and display order

#### Card Interpretations Tab
- Set interpretations for each tarot card
- Configure upright and reversed meanings
- Manage interpretations for different categories

## API Methods

### Database Helper Class

The plugin includes a `Tarot_Yes_No_DB` class with methods for database operations:

```php
// Get a setting
$value = Tarot_Yes_No_DB::get_setting('num_cards', 3);

// Set a setting
Tarot_Yes_No_DB::set_setting('num_cards', 5);

// Get all settings
$settings = Tarot_Yes_No_DB::get_all_settings();

// Get back images
$images = Tarot_Yes_No_DB::get_back_images();

// Get interpretations for a card
$interpretations = Tarot_Yes_No_DB::get_interpretations($card_id, 'upright');

// Save interpretations
Tarot_Yes_No_DB::save_interpretations($card_id, $interpretations);
```

## File Structure

```
tarot-yes-no-reading/
├── admin/
│   └── admin-page.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── includes/
│   ├── class-tarot-yes-no.php
│   ├── class-tarot-yes-no-activator.php
│   ├── class-tarot-yes-no-db.php
│   ├── class-tarot-yes-no-migration.php
│   └── tarot-card-data.php
├── templates/
│   └── reading-interface.php
├── tarot-yes-no-reading.php
└── README.md
```

## Changelog

### Version 1.1.0
- **Major Update**: Migrated from wp_options to custom database tables
- Added `Tarot_Yes_No_DB` class for database operations
- Added `Tarot_Yes_No_Migration` class for data migration
- Improved data management and performance
- Better separation of concerns

### Version 1.0.0
- Initial release
- Basic tarot reading functionality
- Admin interface for settings and interpretations

## Support

For support or feature requests, please contact the plugin developer.

## License

This plugin is licensed under the GPL v2 or later. 