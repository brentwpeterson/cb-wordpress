=== RequestDesk Connector ===
Contributors: requestdesk
Tags: content, api, publishing, automation
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect RequestDesk.ai to WordPress for seamless content publishing.

== Description ==

The RequestDesk Connector plugin allows you to publish content from RequestDesk.ai directly to your WordPress site. Content is sent via REST API and created as draft posts for your review.

= Features =

* Secure API key authentication
* Configurable API key management
* Receive content via REST API
* Create posts as drafts automatically
* Support for categories and tags
* Sync history tracking
* Debug mode for testing (disable in production)

== Installation ==

1. Upload the `requestdesk-connector` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to RequestDesk menu in your WordPress admin
4. **IMPORTANT:** Configure your RequestDesk agent API key in the Security Settings section
5. Configure other settings and note the API endpoint
6. Ensure debug mode is disabled for production use

== Frequently Asked Questions ==

= How do I get an API key? =

API keys are managed through your RequestDesk.ai agent settings. Each agent has its own API key that can be used for authentication.

= What post types are supported? =

Currently, the plugin supports standard WordPress posts. Pages and custom post types will be added in future versions.

= Can I customize the post status? =

Yes, you can set the default post status in the plugin settings. You can also specify the status when sending content from RequestDesk.

== Changelog ==

= 1.1.0 =
* **SECURITY:** Added configurable API key authentication
* **SECURITY:** Exact API key matching with hash_equals() for timing attack protection
* Added admin interface for API key configuration
* Enhanced security warnings for debug mode
* Added API key validation with clear error messages
* Updated settings page with security indicators
* **BREAKING CHANGE:** API keys must now be configured in WordPress admin

= 1.0.0 =
* Initial release
* Basic post creation via REST API
* Category and tag support
* Sync history tracking

== API Documentation ==

= Endpoints =

* POST /wp-json/requestdesk/v1/posts - Create a new post
* GET /wp-json/requestdesk/v1/test - Test connection
* GET /wp-json/requestdesk/v1/sync-status/{ticket_id} - Check sync status

= Authentication =

Include your agent API key in the header:
`X-RequestDesk-API-Key: YOUR_API_KEY`

= Example Request =

```
POST /wp-json/requestdesk/v1/posts
Content-Type: application/json
X-RequestDesk-API-Key: YOUR_API_KEY

{
  "title": "Your Post Title",
  "content": "Post content here",
  "excerpt": "Optional excerpt",
  "ticket_id": "unique_ticket_id",
  "agent_id": "agent_id",
  "post_status": "draft",
  "categories": ["Category 1"],
  "tags": ["tag1", "tag2"]
}
```