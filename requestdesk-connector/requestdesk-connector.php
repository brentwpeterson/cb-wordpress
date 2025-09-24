<?php
/**
 * Plugin Name: RequestDesk Connector
 * Plugin URI: https://requestdesk.ai
 * Description: Connects RequestDesk.ai to WordPress for publishing content with secure API key authentication
 * Version: 1.2.0
 * Author: RequestDesk Team
 * License: GPL v2 or later
 * Text Domain: requestdesk-connector
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REQUESTDESK_VERSION', '1.2.0');
define('REQUESTDESK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REQUESTDESK_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load plugin files
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-api.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-post-handler.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-push.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/settings-page.php';

// Initialize the plugin
add_action('init', 'requestdesk_init');

function requestdesk_init() {
    // Register REST API endpoints
    add_action('rest_api_init', 'requestdesk_register_api_endpoints');
    
    // Add admin menu
    add_action('admin_menu', 'requestdesk_add_admin_menu');
    
    // Initialize push functionality
    new RequestDesk_Push();
}

/**
 * Register REST API endpoints
 */
function requestdesk_register_api_endpoints() {
    $api = new RequestDesk_API();
    $api->register_routes();
}

/**
 * Add admin menu
 */
function requestdesk_add_admin_menu() {
    add_menu_page(
        'RequestDesk Settings',
        'RequestDesk',
        'manage_options',
        'requestdesk-settings',
        'requestdesk_settings_page',
        'dashicons-edit-page',
        30
    );
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'requestdesk_activate');

function requestdesk_activate() {
    // Create database table for sync logs if needed
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'requestdesk_sync_log';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ticket_id varchar(255) NOT NULL,
        post_id bigint(20) NOT NULL,
        agent_id varchar(255) NOT NULL,
        sync_status varchar(50) DEFAULT 'success',
        sync_date datetime DEFAULT CURRENT_TIMESTAMP,
        error_message text,
        PRIMARY KEY (id),
        KEY ticket_id (ticket_id),
        KEY agent_id (agent_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Set default options
    add_option('requestdesk_settings', array(
        'debug_mode' => false,
        'allowed_post_types' => array('post'),
        'default_post_status' => 'draft',
        'api_key' => '' // Must be configured by user for security
    ));
    
    // Flush rewrite rules for REST API
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'requestdesk_deactivate');

function requestdesk_deactivate() {
    flush_rewrite_rules();
}