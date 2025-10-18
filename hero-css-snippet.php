<?php
/**
 * Code Snippet to enqueue hero page CSS
 * Add this to Code Snippets plugin
 */

function enqueue_hero_styles() {
    wp_enqueue_style(
        'hero-styles',
        get_template_directory_uri() . '/hero-styles.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_hero_styles');