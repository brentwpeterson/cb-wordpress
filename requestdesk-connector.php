<?php
/**
 * Plugin Name: RequestDesk Connector
 * Plugin URI: https://requestdesk.ai
 * Description: Connects RequestDesk.ai to WordPress for publishing content with secure API key authentication and AEO/AIO/GEO optimization
 * Version: 2.1.1
 * Author: RequestDesk Team
 * License: GPL v2 or later
 * Text Domain: requestdesk-connector
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REQUESTDESK_VERSION', '2.1.1');
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
require_once REQUESTDESK_PLUGIN_DIR . 'includes/class-requestdesk-claude-integration.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/aeo-settings-page.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/aeo-meta-boxes.php';
require_once REQUESTDESK_PLUGIN_DIR . 'admin/aeo-bulk-optimizer.php';

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
    // Main menu page - AEO Analytics Dashboard
    add_menu_page(
        'RequestDesk AEO Dashboard',
        'RequestDesk',
        'manage_options',
        'requestdesk-aeo-analytics',
        'requestdesk_aeo_analytics_page',
        'dashicons-chart-area',
        30
    );

    // Combined Settings page (RequestDesk + AEO settings)
    add_submenu_page(
        'requestdesk-aeo-analytics',
        'RequestDesk Settings',
        'Settings',
        'manage_options',
        'requestdesk-settings',
        'requestdesk_combined_settings_page'
    );
}

/**
 * Combined Settings Page (RequestDesk + AEO Settings)
 */
function requestdesk_combined_settings_page() {
    ?>
    <div class="wrap">
        <h1>RequestDesk Settings</h1>

        <nav class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active" onclick="openTab(event, 'general')">General Settings</a>
            <a href="#aeo" class="nav-tab" onclick="openTab(event, 'aeo')">AEO Settings</a>
        </nav>

        <!-- General RequestDesk Settings Tab -->
        <div id="general" class="tab-content" style="display: block;">
            <?php
            // Include the original RequestDesk settings content
            ob_start();
            requestdesk_settings_page();
            $settings_content = ob_get_clean();

            // Remove the outer wrap div and h1 to avoid duplication
            $settings_content = preg_replace('/<div class="wrap">.*?<h1>.*?<\/h1>/s', '', $settings_content);
            $settings_content = preg_replace('/<\/div>\s*$/', '', $settings_content);

            echo $settings_content;
            ?>
        </div>

        <!-- AEO Settings Tab -->
        <div id="aeo" class="tab-content" style="display: none;">
            <?php
            // Include the AEO settings content
            ob_start();
            requestdesk_aeo_settings_page();
            $aeo_content = ob_get_clean();

            // Remove the outer wrap div and h1 to avoid duplication
            $aeo_content = preg_replace('/<div class="wrap">.*?<h1>.*?<\/h1>/s', '', $aeo_content);
            $aeo_content = preg_replace('/<\/div>\s*$/', '', $aeo_content);

            echo $aeo_content;
            ?>
        </div>
    </div>

    <style>
    .nav-tab-wrapper {
        margin-bottom: 20px;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    </style>

    <script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("nav-tab");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("nav-tab-active");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("nav-tab-active");
    }
    </script>
    <?php
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

/**
 * AJAX handler for testing Claude API connection
 */
add_action('wp_ajax_test_claude_connection', 'requestdesk_test_claude_connection');

function requestdesk_test_claude_connection() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'test_claude_connection')) {
        wp_die('Invalid nonce');
    }

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    $api_key = sanitize_text_field($_POST['api_key']);

    if (empty($api_key)) {
        wp_send_json_error(array('message' => 'API key is required'));
        return;
    }

    // Temporarily store the API key for testing
    $original_settings = get_option('requestdesk_settings', array());
    $test_settings = $original_settings;
    $test_settings['claude_api_key'] = $api_key;
    update_option('requestdesk_settings', $test_settings);

    // Test the connection
    $claude = new RequestDesk_Claude_Integration();
    $result = $claude->test_connection();

    // Restore original settings
    update_option('requestdesk_settings', $original_settings);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    } else {
        wp_send_json_success(array(
            'message' => 'Connection successful! Model: ' . $result['model'],
            'response' => $result['response']
        ));
    }
}