# Respira for WordPress Lite

![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress Compatibility](https://img.shields.io/badge/wordpress-6.0%2B-green.svg)
![PHP Compatibility](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL%20v2-orange.svg)

Let AI coding assistants like **Cursor**, **Claude Code**, **Windsurf**, **Cline**, and **Continue.dev** edit your WordPress site through a secure REST API.

## 🚀 Features

- **30 Monthly Edits** - Free tier with 30 edits per month
- **Gutenberg Support** - Full Block Editor intelligence
- **Secure REST API** - Key-based authentication
- **Page/Post Management** - Complete CRUD operations
- **Media Upload** - File upload via API
- **3-Day Audit Log** - Track all changes
- **Content Security** - XSS and SQL injection protection

## 📦 Installation

### From WordPress.org (Recommended)

1. Go to **Plugins → Add New** in WordPress admin
2. Search for "Respira for WordPress Lite"
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the latest release from [Releases](https://github.com/webmyc/respira-for-wordpress-lite/releases)
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and activate

### From Source

```bash
# Clone the repository
git clone https://github.com/webmyc/respira-for-wordpress-lite.git

# Zip the plugin
cd respira-for-wordpress-lite
zip -r respira-for-wordpress-lite.zip . -x "*.git*" "README.md" "BUILD-NOTES.md" "GAP_ANALYSIS.md"

# Upload to WordPress via Plugins → Add New → Upload
```

## 🔧 Setup

1. **Install the Plugin** on your WordPress site
2. **Register** for a free account at [respira.press](https://respira.press)
3. **Generate an API Key** from your Respira dashboard
4. **Configure your AI tool** with the MCP server
5. **Start editing** your site with natural language!

## 📖 Documentation

- [Full Documentation](https://respira.press/documentation)
- [MCP Server Setup Guide](https://respira.press/docs/mcp-setup)
- [API Reference](https://respira.press/docs/api)
- [Prompt Examples](https://respira.press/docs/prompts)

## 🎯 Usage Examples

Tell your AI assistant:

- "Change the hero headline to 'Welcome to Our New Site'"
- "Update all buttons to say 'Get Started' instead of 'Contact Us'"
- "Add a new paragraph after the first section"
- "Change the page title to something more SEO-friendly"

## 🆚 Lite vs Full Version

| Feature | Lite | Full |
|---------|------|------|
| Monthly Edits | 30 | Unlimited |
| Page Builders | Gutenberg only | All 10 builders |
| Duplicate-Before-Edit | ❌ | ✅ |
| SEO Analysis | ❌ | ✅ |
| Performance Analysis | ❌ | ✅ |
| AEO Analysis | ❌ | ✅ |
| Plugin Management | ❌ | ✅ |
| Audit Log Retention | 3 days | 90 days |
| Support | Community | Priority |

[Upgrade to Full Version →](https://respira.press)

## 🛡️ Security

- API keys are hashed with `wp_hash_password()` (never stored in plain text)
- All admin functions require `manage_options` capability
- Nonce verification on all forms
- Content validation and XSS protection
- Prepared SQL statements for all queries
- No external API dependencies

## 🤝 Supported AI Tools

- [Cursor](https://cursor.sh)
- [Claude Code](https://claude.ai/code)
- [Claude Desktop](https://claude.ai/desktop)
- [Windsurf](https://codeium.com/windsurf)
- [Cline](https://github.com/cline/cline)
- [Continue.dev](https://continue.dev)
- Any MCP-compatible AI assistant

## 🐛 Support

- **Community Support**: [WordPress.org Forums](https://wordpress.org/support/plugin/respira-for-wordpress-lite/)
- **Bug Reports**: [GitHub Issues](https://github.com/webmyc/respira-for-wordpress-lite/issues)
- **Feature Requests**: [GitHub Discussions](https://github.com/webmyc/respira-for-wordpress-lite/discussions)

For priority support, [upgrade to the full version](https://respira.press).

## 📄 License

This plugin is licensed under the [GPL v2 or later](LICENSE).

```
Copyright (C) 2024 Respira

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🏗️ Development

Built with ❤️ by the Respira team.

- Based on WordPress Plugin Boilerplate structure
- Gutenberg intelligence adapted from main Respira plugin
- Follows WordPress Coding Standards

## 🔗 Links

- **Website**: [respira.press](https://respira.press)
- **Documentation**: [respira.press/documentation](https://respira.press/documentation)
- **Full Version**: [respira.press/pricing](https://respira.press/pricing)
- **GitHub**: [github.com/webmyc/respira-for-wordpress-lite](https://github.com/webmyc/respira-for-wordpress-lite)

---

Made with 🤖 for WordPress developers who love AI coding assistants.
