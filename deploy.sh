#!/bin/bash

# RequestDesk WordPress Plugin Deployment Script
# Deploys plugin via symlink for development testing

set -e

# Configuration
PLUGIN_SOURCE_DIR="/Users/brent/scripts/CB-Workspace/cb-wordpress"
WORDPRESS_PLUGINS_DIR="/Users/brent/LocalSites/contentcucumber/app/public/wp-content/plugins"
PLUGIN_TARGET_DIR="$WORDPRESS_PLUGINS_DIR/requestdesk-connector"

echo "🚀 RequestDesk Plugin Deployment"
echo "=================================="

# Check if source directory exists
if [ ! -d "$PLUGIN_SOURCE_DIR" ]; then
    echo "❌ Source directory not found: $PLUGIN_SOURCE_DIR"
    exit 1
fi

# Check if WordPress plugins directory exists
if [ ! -d "$WORDPRESS_PLUGINS_DIR" ]; then
    echo "❌ WordPress plugins directory not found: $WORDPRESS_PLUGINS_DIR"
    echo "💡 Is your Local site running?"
    exit 1
fi

# Backup existing plugin if it exists and isn't a symlink
if [ -d "$PLUGIN_TARGET_DIR" ] && [ ! -L "$PLUGIN_TARGET_DIR" ]; then
    echo "📦 Backing up existing plugin..."
    mv "$PLUGIN_TARGET_DIR" "$PLUGIN_TARGET_DIR.backup.$(date +%Y%m%d-%H%M%S)"
    echo "✅ Backup created"
fi

# Remove existing symlink if present
if [ -L "$PLUGIN_TARGET_DIR" ]; then
    echo "🔗 Removing existing symlink..."
    rm "$PLUGIN_TARGET_DIR"
fi

# Create symlink
echo "🔗 Creating symlink..."
ln -s "$PLUGIN_SOURCE_DIR" "$PLUGIN_TARGET_DIR"

# Verify symlink
if [ -L "$PLUGIN_TARGET_DIR" ]; then
    echo "✅ Symlink created successfully!"
    echo "📂 Plugin files are now linked to development directory"
    echo ""
    echo "🎯 Benefits:"
    echo "  • Real-time code changes without copying files"
    echo "  • No need to manually update plugin after changes"
    echo "  • Instant testing of development code"
    echo ""
    echo "📝 To test your changes:"
    echo "  1. Make changes to files in: $PLUGIN_SOURCE_DIR"
    echo "  2. Refresh WordPress admin or frontend"
    echo "  3. Changes are immediately active!"
    echo ""
    echo "🚨 Remember: This is for development only!"
    echo "   Use the zip file for production deployment"
else
    echo "❌ Failed to create symlink"
    exit 1
fi

# Check plugin status in WordPress
echo "🔍 Plugin Status:"
ls -la "$PLUGIN_TARGET_DIR" | head -5
echo ""

echo "🎉 Deployment Complete!"
echo "🌐 Test at: https://contentcucumber.local/wp-admin/plugins.php"