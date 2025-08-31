# CLAUDE.md

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

## GP Premium Elements Management
- **Admin Interface**: https://contentcucumber.local/wp-admin/edit.php?post_type=gp_elements
- GP Premium Elements are stored as WordPress posts with `post_type = 'gp_elements'`
- Template issues (like "Post title" instead of `{{post_title}}`) are in these elements
- Common problematic elements:
  - Single post (controls post title display)
  - Right sidebar (controls author display)
- Edit these through WP Admin interface, not database when possible