# Respira for WordPress Lite - Build Notes

## Build Information

**Version:** 1.0.0
**Build Date:** 2024-11-22
**WordPress Compatibility:** 5.8 - 6.7
**PHP Compatibility:** 7.4+

## Project Structure

```
respira-for-wordpress-lite/
├── admin/
│   ├── class-respira-lite-admin.php
│   ├── css/
│   │   └── respira-lite-admin.css
│   ├── js/
│   │   └── respira-lite-admin.js
│   └── views/
│       ├── api-keys.php
│       ├── audit-log.php
│       ├── dashboard.php
│       └── settings.php
├── includes/
│   ├── class-respira-lite-activator.php
│   ├── class-respira-lite-api.php
│   ├── class-respira-lite-audit.php
│   ├── class-respira-lite-auth.php
│   ├── class-respira-lite-content-filter.php
│   ├── class-respira-lite-context.php
│   ├── class-respira-lite-core.php
│   ├── class-respira-lite-deactivator.php
│   ├── class-respira-lite-i18n.php
│   ├── class-respira-lite-loader.php
│   ├── class-respira-lite-security.php
│   ├── class-respira-lite-usage-limiter.php
│   └── gutenberg-intelligence/
│       ├── class-gutenberg-block-registry.php
│       ├── class-gutenberg-block-schema.php
│       ├── class-gutenberg-intelligence-loader.php
│       ├── class-gutenberg-validator.php
│       └── gutenberg-patterns.php
├── languages/
├── .wordpress-org/
├── LICENSE
├── readme.txt
├── respira-for-wordpress-lite.php
└── uninstall.php
```

## Features Implemented

### Core Features
✅ **30 Monthly Edits** - Obfuscated usage tracking with monthly reset
✅ **Gutenberg Support** - Full block editor intelligence
✅ **Secure REST API** - Key-based authentication
✅ **Page/Post Management** - CRUD operations
✅ **Media Upload** - File upload via API
✅ **Audit Logging** - 3-day activity retention
✅ **Content Security** - XSS and injection protection

### Admin Interface
✅ **Dashboard** - Usage stats and quick start guide
✅ **API Keys** - Generate and manage API keys
✅ **Settings** - Security configuration
✅ **Audit Log** - Activity tracking with filters

### Gates & Restrictions
✅ **Builder Gate** - Only allows Gutenberg (returns upgrade error for others)
✅ **Edit Limit** - Enforces 30 edits/month with proper error messages
✅ **Pro Feature Gates** - Analysis and plugin management return upgrade CTAs
✅ **No Duplicate-Before-Edit** - Edits live pages with warning message

### WordPress.org Compliance
✅ **GPL v2 License** - Proper open source licensing
✅ **No External API Calls** - All operations local
✅ **Security Files** - index.php in all directories
✅ **Proper Sanitization** - All inputs sanitized, outputs escaped
✅ **Database Cleanup** - uninstall.php removes all data
✅ **No License Checks** - Free version, no external validation

## Testing Checklist

### Pre-Submission Tests
- [x] PHP syntax validation (all files pass `php -l`)
- [ ] Plugin activation on fresh WordPress install
- [ ] API key generation and authentication
- [ ] Page/post CRUD operations
- [ ] Usage limit enforcement (create 31 edits)
- [ ] Builder gate (try Elementor/Divi endpoints)
- [ ] Audit log recording
- [ ] Settings save functionality
- [ ] Plugin deactivation and reactivation
- [ ] Complete uninstall (verify data removal)

### WordPress.org Submission
- [ ] Validate readme.txt: https://wordpress.org/plugins/developers/readme-validator/
- [ ] Create plugin assets (icons, banners, screenshots)
- [ ] Test on WordPress 5.8, 6.0, 6.4, 6.7
- [ ] Test on PHP 7.4, 8.0, 8.1, 8.2
- [ ] Review against plugin guidelines: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- [ ] Submit via WordPress.org plugin submission page

## Known Limitations (Lite vs Pro)

| Feature | Lite | Pro |
|---------|------|-----|
| Monthly Edits | 30 | Unlimited |
| Page Builders | Gutenberg only | All 10 builders |
| Duplicate-Before-Edit | ❌ No | ✅ Yes |
| SEO Analysis | ❌ No | ✅ Yes |
| Performance Analysis | ❌ No | ✅ Yes |
| AEO Analysis | ❌ No | ✅ Yes |
| Plugin Management | ❌ No | ✅ Yes |
| Audit Log Retention | 3 days | 90 days |
| Priority Support | ❌ No | ✅ Yes |

## Upgrade URLs

All upgrade links use proper UTM tracking:
- `utm_source=lite`
- `utm_medium={location}` - dashboard, api, plugins_page, settings
- `utm_campaign={action}` - upgrade_cta, limit_reached, builder_upgrade, feature_upgrade

## Database Tables

The plugin creates two tables:
1. `wp_respira_lite_api_keys` - Stores hashed API keys
2. `wp_respira_lite_audit_log` - Stores activity logs (3-day retention)

Both tables are dropped on uninstall.

## Security Considerations

1. **API Keys** - Hashed with `wp_hash_password()` (never stored in plain text)
2. **Nonce Verification** - All AJAX and form submissions verified
3. **Capability Checks** - All admin functions require `manage_options`
4. **Content Validation** - Optional XSS/injection scanning
5. **Input Sanitization** - All user input sanitized
6. **Output Escaping** - All output properly escaped
7. **Prepared Statements** - All database queries use `$wpdb->prepare()`

## Development Notes

### Obfuscated Usage Counter
The edit counter uses obfuscated option names to prevent easy tampering:
- Count: `respira_sys_{hash}` where hash = first 8 chars of MD5(AUTH_SALT + 'edit_usage_v1')
- Reset: `respira_cache_{hash}` where hash = first 8 chars of MD5(AUTH_SALT + 'reset_time_v1')

This makes it harder to find and reset the counter without access to wp-config.php constants.

### REST API Namespace
- Full version: `respira/v1`
- Lite version: `respira-lite/v1`

This allows both plugins to coexist (though not recommended).

## Future Enhancements (Potential v1.1+)

- Custom post type support
- Multisite compatibility
- WP-CLI commands
- REST API rate limiting
- API key expiration dates
- Team member API keys (read-only)
- Webhook integrations
- Export usage statistics

## Support & Documentation

- Documentation: https://respira.press/docs
- GitHub: https://github.com/webmyc/respira-for-wordpress-lite
- Support Forum: https://wordpress.org/support/plugin/respira-for-wordpress-lite/
- Pro Version: https://respira.press

## Credits

Built with ❤️ by the Respira team.

Based on WordPress Plugin Boilerplate structure.
Gutenberg intelligence adapted from main Respira plugin.
