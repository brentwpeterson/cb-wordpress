<?php
/**
 * Plugin Name: Cucumber Custom
 * Plugin URI: https://contentcucumber.com
 * Description: Content Cucumber's custom styles and functionality plugin
 * Version: 1.0.0
 * Author: Content Cucumber Team
 * Author URI: https://contentcucumber.com
 * Text Domain: cucumber-custom
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed.');
}

/**
 * Cucumber Custom Plugin Class
 *
 * Handles all custom styling and functionality for Content Cucumber site.
 * Alternative to child theme approach for easier plugin management.
 */
class CucumberCustom {

    /**
     * Plugin version
     */
    const VERSION = '1.0.0';

    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 999);
    }

    /**
     * Enqueue custom styles with proper dependencies
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'cucumber-custom-styles',
            plugin_dir_url(__FILE__) . 'assets/css/hero-styles.css',
            array('generateblocks'), // Load after GenerateBlocks
            self::VERSION,
            'all'
        );
    }
}

// Initialize the plugin
new CucumberCustom();