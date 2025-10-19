<?php
/**
 * Plugin Name: RequestDesk Connector
 * Plugin URI: https://requestdesk.ai
 * Description: Connects RequestDesk.ai to WordPress for publishing content with secure API key authentication and AEO/AIO/GEO optimization
 * Version: 2.0.0
 * Author: RequestDesk Team
 * License: GPL v2 or later
 * Text Domain: requestdesk-connector
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REQUESTDESK_VERSION', '2.0.0');
define('REQUESTDESK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REQUESTDESK_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load plugin files
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-api.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-post-handler.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-push.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/settings-page.php';

// Load AEO/GEO extension files
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-aeo-core.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-content-analyzer.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-schema-generator.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-freshness-tracker.php';
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-citation-tracker.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/aeo-settings-page.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/aeo-meta-boxes.php';

// Initialize the plugin
add_action('init', 'requestdesk_init');

function requestdesk_init() {
    // Register REST API endpoints
    add_action('rest_api_init', 'requestdesk_register_api_endpoints');

    // Add admin menu
    add_action('admin_menu', 'requestdesk_add_admin_menu');

    // Initialize push functionality
    new RequestDesk_Push();

    // Initialize AEO functionality
    new RequestDesk_AEO_Core();
    new RequestDesk_Content_Analyzer();
    new RequestDesk_Schema_Generator();
    new RequestDesk_Freshness_Tracker();
    new RequestDesk_Citation_Tracker();

    // Initialize AEO admin functionality
    add_action('admin_menu', 'requestdesk_aeo_add_admin_menu');
    add_action('add_meta_boxes', 'requestdesk_aeo_add_meta_boxes');
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

    // Create AEO data table
    $aeo_table_name = $wpdb->prefix . 'requestdesk_aeo_data';

    $aeo_sql = "CREATE TABLE IF NOT EXISTS $aeo_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        content_type varchar(20) DEFAULT 'post',
        aeo_score tinyint(3) DEFAULT 0,
        last_analyzed datetime DEFAULT CURRENT_TIMESTAMP,
        ai_questions longtext,
        faq_data longtext,
        citation_stats longtext,
        optimization_status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY post_id (post_id),
        KEY content_type (content_type),
        KEY optimization_status (optimization_status),
        KEY aeo_score (aeo_score)
    ) $charset_collate;";

    dbDelta($aeo_sql);

    // Set default options
    add_option('requestdesk_settings', array(
        'debug_mode' => false,
        'allowed_post_types' => array('post'),
        'default_post_status' => 'draft',
        'api_key' => '' // Must be configured by user for security
    ));

    // Set default AEO options
    add_option('requestdesk_aeo_settings', array(
        'enabled' => true,
        'auto_optimize_on_publish' => true,
        'auto_optimize_on_update' => false,
        'generate_faq_schema' => true,
        'extract_qa_pairs' => true,
        'track_citations' => true,
        'monitor_freshness' => true,
        'min_content_length' => 300,
        'qa_extraction_confidence' => 0.7,
        'freshness_alert_days' => 90
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