# CLAUDE.md

## 🔄 Session Context (Current)
- **Current Branch**: `feature/requestdesk-aeo-extension`
- **Working Directory**: `/Users/brent/LocalSites/contentcucumber`
- **Session Status**: ✅ RequestDesk Plugin v2.0.0 AEO Extension Complete
- **Next Action**: Create pull request at https://github.com/brentwpeterson/cb-wordpress/pull/new/feature/requestdesk-aeo-extension
- **Context Saved**: Ready for session restart

This file contains important configuration and guidance for Claude when working with this WordPress site.

## Project Information
- **Project**: Content Cucumber WordPress Site
- **Working Directory**: `/Users/brent/LocalSites/contentcucumber/app/public`
- **Development Environment**: LocalWP (Local by Flywheel)

## Development Environment
- **Platform**: LocalWP (Local by Flywheel)
- **Site Path**: `/Users/brent/LocalSites/contentcucumber/`
- **Public Root**: `/Users/brent/LocalSites/contentcucumber/app/public`
- **Local Site URL**: Check LocalWP app for the local domain
- **Database**: Managed through LocalWP interface
- **Database Table Prefix**: `wp_83rxila95v_`
- **PHP Version**: Configured in LocalWP settings
- **Web Server**: Configured in LocalWP (nginx or Apache)

## Common Commands
- **CRITICAL WARNING**: DO NOT use WP-CLI commands in this LocalWP environment
- WP-CLI causes PHP extension conflicts and database corruption in this setup
- Use direct SQL commands through LocalWP's database interface instead
- LocalWP manages PHP/MySQL versions - external CLI tools interfere with this

## Code Conventions
- Follow WordPress coding standards
- Use spaces for indentation, not tabs
- Keep file structure organized according to WordPress best practices

## Important Files
- WordPress configuration: `wp-config.php`
- Theme files: Located in `wp-content/themes/[theme-name]`
- Plugin files: Located in `wp-content/plugins`

## Notes
- This is a local development environment

## Important Search Hints
- **Bug hunting**: Issues will NOT be in files ignored by git (WordPress core, default themes, etc.)
- Focus searches on tracked files only: custom themes, plugins, and configuration files
- Use git-tracked files as the primary search scope for troubleshooting

## Module Development and Testing

### RequestDesk Plugin Development
- **Plugin Path**: `app/public/wp-content/plugins/requestdesk-connector/`
- **Main Plugin File**: `requestdesk-connector.php`
- **API Class**: `includes/class-requestdesk-api.php`
- **Admin Settings**: `admin/settings-page.php`

### Development Workflow
1. **Making Changes**:
   - Edit plugin files in `app/public/wp-content/plugins/requestdesk-connector/`
   - Changes are immediately reflected in LocalWP environment
   - No build process required for PHP changes

2. **Testing Changes in LocalWP**:
   - Access WordPress admin: `https://contentcucumber.local/wp-admin`
   - Plugin settings: `https://contentcucumber.local/wp-admin/admin.php?page=requestdesk-settings`
   - Test API connections through plugin interface
   - Check WordPress debug log for errors

3. **Activating/Deactivating Plugin**:
   - Navigate to: `https://contentcucumber.local/wp-admin/plugins.php`
   - Find "RequestDesk Connector" plugin
   - Use Activate/Deactivate links to test plugin lifecycle
   - **Note**: Deactivation/reactivation helps test initialization code

4. **Database Changes**:
   - Access database through LocalWP interface (right-click site → Database → Adminer)
   - Plugin tables use prefix: `wp_83rxila95v_`
   - Check plugin options in `wp_83rxila95v_options` table

### Local Testing Best Practices
- **Enable WordPress Debug Mode**: Add to `wp-config.php`:
  ```php
  define('WP_DEBUG', true);
  define('WP_DEBUG_LOG', true);
  define('WP_DEBUG_DISPLAY', false);
  ```
- **Debug Log Location**: `app/public/wp-content/debug.log`
- **Clear Cache**: If using caching plugins, clear cache after changes
- **Test Different User Roles**: Test plugin functionality with different WordPress user permissions

### Plugin Update Workflow
1. **Update Plugin Files**: Make changes to plugin files in the repository
2. **Version Bump**: Update version number in main plugin file header
3. **Test Locally**: Verify changes work in LocalWP environment
4. **Commit Changes**: Use git to commit and push changes
5. **Deploy**: Upload changes to production environment

### Common Development Tasks
- **Add New API Endpoints**: Extend `class-requestdesk-api.php`
- **Update Settings Page**: Modify `admin/settings-page.php`
- **Add Database Tables**: Use WordPress `dbDelta()` function in activation hook
- **Debug API Calls**: Check WordPress debug log and browser network tab

## GP Premium Elements Management
- **Admin Interface**: https://contentcucumber.local/wp-admin/edit.php?post_type=gp_elements
- GP Premium Elements are stored as WordPress posts with `post_type = 'gp_elements'`
- Template issues (like "Post title" instead of `{{post_title}}`) are in these elements
- Common problematic elements:
  - Single post (controls post title display)
  - Right sidebar (controls author display)
- Edit these through WP Admin interface, not database when possible