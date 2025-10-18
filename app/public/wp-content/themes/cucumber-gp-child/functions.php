<?php
/**
 * Cucumber GP Child Theme Functions
 *
 * Content Cucumber's custom functionality and styles for GeneratePress.
 * This file contains all version-controlled customizations.
 *
 * @package CucumberGPChild
 * @version 1.0.0
 * @author Content Cucumber Team
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed.');
}

/**
 * Enqueue parent theme, child theme, and custom styles
 *
 * Loads styles in the correct order to ensure proper CSS cascade:
 * 1. Parent theme (GeneratePress)
 * 2. Child theme base styles
 * 3. Custom component styles (hero-styles.css)
 */
function cucumber_gp_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style(
        'generatepress-parent',
        get_template_directory_uri() . '/style.css'
    );

    // Enqueue child theme base styles
    wp_enqueue_style(
        'cucumber-gp-child',
        get_stylesheet_directory_uri() . '/style.css',
        array('generatepress-parent'),
        wp_get_theme()->get('Version')
    );

    // Enqueue hero styles with proper dependencies to override GenerateBlocks
    wp_enqueue_style(
        'cucumber-hero-styles',
        get_stylesheet_directory_uri() . '/hero-styles.css',
        array('generateblocks', 'cucumber-gp-child'),
        '1.0.1',
        'all'
    );
}
add_action('wp_enqueue_scripts', 'cucumber_gp_child_enqueue_styles', 999);

/**
 * Content Cucumber Custom Functionality
 *
 * Add any custom functions, hooks, or modifications below this line.
 */