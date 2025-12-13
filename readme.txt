=== Respira for WordPress Lite ===
Contributors: respirapress
Donate link: https://respira.press
Tags: ai, gutenberg, cursor, claude, rest api, content editing, automation
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let AI coding assistants (Cursor, Claude Code, Windsurf) edit your WordPress site through a secure REST API. Gutenberg support with 30 edits/month.

== Description ==

**Respira for WordPress Lite** enables AI coding assistants like Cursor, Claude Code, Windsurf, Cline, and Continue.dev to edit your WordPress site through a secure REST API. No SSH required—works on any hosting, even shared hosting.

= How It Works =

1. Install this plugin on your WordPress site
2. Register for a free API key at [respira.press](https://respira.press)
3. Connect your AI coding assistant using the MCP server
4. Start editing your WordPress content with natural language commands

= Lite Version Features =

* **Gutenberg (Block Editor) support** – AI understands your blocks and can modify them intelligently
* **30 edits per month** – Enough to try the workflow and see if it fits
* **3-day audit log** – Track what changes were made
* **Security validation** – XSS and SQL injection protection on all content
* **REST API access** – Works with any MCP-compatible AI tool
* **Menu viewing** – AI can read your navigation menus (editing requires full version)
* **Taxonomy viewing** – AI can read categories, tags, and custom taxonomies (editing requires full version)

= What Can You Do? =

Tell your AI assistant things like:
* "Change the hero headline to 'Welcome to Our New Site'"
* "Update all buttons to say 'Get Started' instead of 'Contact Us'"
* "Add a new paragraph after the first section"
* "Change the page title to something more SEO-friendly"

= Supported AI Tools =

* Cursor
* Claude Code
* Claude Desktop
* Windsurf
* Cline
* Continue.dev
* Any MCP-compatible AI assistant

= Full Version Features =

The [full version of Respira for WordPress](https://respira.press) includes everything in Lite, plus:

* **All 10 page builders** – Divi (200+ modules), Elementor, Oxygen, Bricks, WPBakery, Beaver Builder, Thrive Architect, Brizy, Visual Composer
* **Duplicate-before-edit safety** – AI creates a safe copy before making changes, so your live pages are never touched until you approve
* **Unlimited edits** – No monthly limits
* **SEO Analysis** – Comprehensive SEO audit with actionable recommendations
* **Performance Analysis** – Core Web Vitals, page speed insights
* **AI Engine Optimization (AEO)** – Optimize content for AI search engines
* **Plugin management** – Install, activate, and update plugins via AI
* **90-day audit log** – Full history of all changes
* **Priority support** – Email support from humans, not bots

[Upgrade to Full Version →](https://respira.press)

= Why Lite Edits Live Pages =

The Lite version edits your live pages directly without creating safety duplicates. This is intentional to keep the free version simple. If you need the peace of mind that comes with duplicate-before-edit safety, [upgrade to the full version](https://respira.press).

= Documentation =

Full documentation, setup guides, and prompt examples are available at [respira.press/documentation](https://respira.press/documentation).

= Privacy =

Respira for WordPress Lite requires registration at respira.press to obtain an API key. The plugin communicates with your AI tool through the local MCP server on your machine—your content is not sent to Respira's servers.

The API key validates your identity but does not transmit your page content. Your WordPress data stays on your WordPress site.

== Installation ==

= Automatic Installation =

1. Go to Plugins → Add New in your WordPress admin
2. Search for "Respira for WordPress Lite"
3. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Activate the plugin

= After Installation =

1. Go to Respira Lite in your WordPress admin menu
2. Click "Create Account" to register at respira.press
3. Generate an API key from your respira.press dashboard
4. Download and configure the MCP server for your AI tool
5. Start editing your site with natural language!

== Frequently Asked Questions ==

= What is an MCP server? =

MCP (Model Context Protocol) is a standard that allows AI assistants to interact with external tools and services. The Respira MCP server is a small program that runs on your local machine and connects your AI assistant (like Cursor) to your WordPress site's REST API.

= Do I need coding knowledge? =

No! The whole point is to let you edit WordPress using natural language. Just tell your AI assistant what you want to change, and it handles the technical details.

= Is my content sent to external servers? =

No. The MCP server runs on your local machine. Your WordPress content travels directly between your AI tool and your WordPress site. Respira's servers only validate your API key—they never see your actual content.

= Why is there a 30 edit limit? =

The Lite version is free and designed to let you try the workflow. 30 edits per month is enough to see if Respira fits your needs. If you need more, [upgrade to the full version](https://respira.press) for unlimited edits.

= Why doesn't Lite have duplicate-before-edit safety? =

The full version's duplicate-before-edit feature creates a safe copy of each page before making changes, so you can review before anything goes live. This requires additional admin UI and workflow that we've reserved for the full version. If safety is important to you (it should be!), [consider upgrading](https://respira.press).

= Which page builders does Lite support? =

Lite supports Gutenberg (the WordPress Block Editor) only. For Divi, Elementor, Oxygen, Bricks, and 6 other builders, [upgrade to the full version](https://respira.press).

= Can I use this with multiple sites? =

Yes, you can install Respira Lite on multiple WordPress sites. Each site operates independently with its own monthly edit limit.

= What happens when I hit the edit limit? =

When you reach 30 edits in a month, the API will return an error until the next month begins. The limit resets automatically. You can also [upgrade to the full version](https://respira.press) for unlimited edits.

= How do I upgrade to the full version? =

Visit [respira.press](https://respira.press) to purchase a license. You'll get a different plugin file to install (the Lite version should be deactivated first). Your API key and account remain the same.

= Do you offer support for the Lite version? =

Support for the Lite version is provided through the WordPress.org support forums. For priority email support, [upgrade to the full version](https://respira.press).

== Screenshots ==

1. Dashboard showing monthly edit usage and quick start guide
2. API key management screen
3. Audit log showing recent AI edits
4. Example: Editing a page with Cursor AI
5. Settings and configuration options

== Changelog ==

= 1.0.2 =
* Added /usage endpoint for checking edit limits via API
* Added read-only menu endpoints (GET /menus, GET /menus/{id}, GET /menus/locations)
* Added read-only taxonomy endpoints (GET /taxonomies, GET /taxonomies/{tax}/terms)
* Added 3 API key limit enforcement with upgrade messaging
* Added automatic audit log cleanup (daily cron)
* Fixed version synchronization

= 1.0.1 =
* Minor bug fixes and improvements

= 1.0.0 =
* Initial release
* Gutenberg (Block Editor) support
* 30 edits per month limit
* 3-day audit log
* REST API with security validation
* Compatible with Cursor, Claude Code, Windsurf, and other MCP tools

== Upgrade Notice ==

= 1.0.2 =
New read-only menu and taxonomy endpoints help AI understand your site structure!

= 1.0.0 =
Initial release of Respira for WordPress Lite. Let AI coding assistants edit your WordPress site!
