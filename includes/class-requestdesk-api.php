<?php

/**
 * RequestDesk API Class
 *
 * Handles all REST API endpoints for RequestDesk WordPress Connector
 */
class RequestDesk_API {

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Test connection endpoint
        register_rest_route('requestdesk/v1', '/test-connection', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'verify_api_key')
        ));

        // Backward compatibility: old test endpoint
        register_rest_route('requestdesk/v1', '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'verify_api_key')
        ));

        // Pull posts endpoint
        register_rest_route('requestdesk/v1', '/pull-posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'pull_posts_for_knowledge'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 100
                ),
                'offset' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                    'minimum' => 0
                ),
                'modified_since' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'ISO date to get posts modified since (for incremental sync)'
                ),
                'include_content' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Include full post content'
                )
            )
        ));

        // Pull pages endpoint (NEW for v1.3.0)
        register_rest_route('requestdesk/v1', '/pull-pages', array(
            'methods' => 'GET',
            'callback' => array($this, 'pull_pages_for_knowledge'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50,
                    'minimum' => 1,
                    'maximum' => 100
                ),
                'offset' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                    'minimum' => 0
                ),
                'modified_since' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'ISO date to get pages modified since (for incremental sync)'
                ),
                'include_content' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Include full page content'
                )
            )
        ));

        // Publish content endpoint
        register_rest_route('requestdesk/v1', '/publish', array(
            'methods' => 'POST',
            'callback' => array($this, 'publish_content'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'content' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'status' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'draft',
                    'enum' => array('draft', 'publish', 'private')
                ),
                'ticket_id' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'agent_id' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'featured_image' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'URL of the featured image to set for the post'
                ),
                'excerpt' => array(
                    'required' => false,
                    'type' => 'string'
                ),
                'categories' => array(
                    'required' => false,
                    'type' => 'array'
                ),
                'tags' => array(
                    'required' => false,
                    'type' => 'array'
                )
            )
        ));
    }

    /**
     * Test connection endpoint
     */
    public function test_connection($request) {
        $settings = get_option('requestdesk_settings', array());

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Connection successful',
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => REQUESTDESK_VERSION,
            'site_url' => home_url(),
            'capabilities' => array(
                'posts' => true,
                'pages' => true,  // NEW in v1.3.0
                'publish' => true
            ),
            'site_info' => array(
                'name' => get_bloginfo('name'),
                'url' => home_url(),
                'version' => get_bloginfo('version'),
                'plugin_version' => REQUESTDESK_VERSION,
                'capabilities' => array(
                    'posts' => true,
                    'pages' => true,  // NEW in v1.3.0
                    'publish' => true
                )
            ),
            'settings' => array(
                'debug_mode' => $settings['debug_mode'] ?? false,
                'allowed_post_types' => $settings['allowed_post_types'] ?? array('post'),
                'default_post_status' => $settings['default_post_status'] ?? 'draft'
            )
        ), 200);
    }

    /**
     * Pull posts for RequestDesk knowledge chunks
     */
    public function pull_posts_for_knowledge($request) {
        try {
            $per_page = $request->get_param('per_page') ?: 50;
            $offset = $request->get_param('offset') ?: 0;
            $modified_since = $request->get_param('modified_since');
            $include_content = $request->get_param('include_content') === true || $request->get_param('include_content') === 'true';

            // Build WP_Query arguments
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $per_page,
                'offset' => $offset,
                'orderby' => 'modified',
                'order' => 'DESC',
                'no_found_rows' => false // We need total count
            );

            // Add date filter if specified
            if (!empty($modified_since)) {
                $args['date_query'] = array(
                    array(
                        'column' => 'post_modified',
                        'after' => $modified_since,
                        'inclusive' => true
                    )
                );
            }

            $query = new WP_Query($args);
            $posts = array();

            foreach ($query->posts as $post) {
                $post_data = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'slug' => $post->post_name,
                    'url' => get_permalink($post->ID),
                    'excerpt' => get_the_excerpt($post),
                    'date' => $post->post_date,
                    'modified' => $post->post_modified,
                    'author' => get_the_author_meta('display_name', $post->post_author),
                    'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
                    'tags' => wp_get_post_tags($post->ID, array('fields' => 'names'))
                );

                // Add content if requested
                if ($include_content) {
                    $post_data['content'] = apply_filters('the_content', $post->post_content);
                }

                $posts[] = $post_data;
            }

            // Get site info
            $site_info = array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
                'version' => get_bloginfo('version'),
                'language' => get_locale()
            );

            $total_posts = $query->found_posts;

            // Log the sync
            $this->log_sync('posts', count($posts), 'success');

            return new WP_REST_Response(array(
                'success' => true,
                'posts' => $posts,
                'site_info' => $site_info,
                'pagination' => array(
                    'per_page' => (int) $per_page,
                    'offset' => (int) $offset,
                    'total' => $total_posts,
                    'has_more' => ($offset + $per_page) < $total_posts
                )
            ), 200);

        } catch (Exception $e) {
            $this->log_sync('posts', 0, 'error', $e->getMessage());

            return new WP_Error(
                'pull_posts_error',
                'Failed to pull posts: ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Pull pages for RequestDesk knowledge chunks (NEW in v1.3.0)
     */
    public function pull_pages_for_knowledge($request) {
        try {
            $per_page = $request->get_param('per_page') ?: 50;
            $offset = $request->get_param('offset') ?: 0;
            $modified_since = $request->get_param('modified_since');
            $include_content = $request->get_param('include_content') === true || $request->get_param('include_content') === 'true';

            // Build WP_Query arguments for pages
            $args = array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'posts_per_page' => $per_page,
                'offset' => $offset,
                'orderby' => 'modified',
                'order' => 'DESC',
                'no_found_rows' => false // We need total count
            );

            // Add date filter if specified
            if (!empty($modified_since)) {
                $args['date_query'] = array(
                    array(
                        'column' => 'post_modified',
                        'after' => $modified_since,
                        'inclusive' => true
                    )
                );
            }

            $query = new WP_Query($args);
            $pages = array();

            foreach ($query->posts as $page) {
                $page_data = array(
                    'id' => $page->ID,
                    'title' => $page->post_title,
                    'slug' => $page->post_name,
                    'url' => get_permalink($page->ID),
                    'excerpt' => get_the_excerpt($page),
                    'date' => $page->post_date,
                    'modified' => $page->post_modified,
                    'author' => get_the_author_meta('display_name', $page->post_author),
                    'parent' => $page->post_parent,
                    'menu_order' => $page->menu_order
                );

                // Add content if requested
                if ($include_content) {
                    $page_data['content'] = apply_filters('the_content', $page->post_content);
                }

                $pages[] = $page_data;
            }

            // Get site info
            $site_info = array(
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'url' => home_url(),
                'version' => get_bloginfo('version'),
                'language' => get_locale()
            );

            $total_pages = $query->found_posts;

            // Log the sync
            $this->log_sync('pages', count($pages), 'success');

            return new WP_REST_Response(array(
                'success' => true,
                'pages' => $pages,
                'site_info' => $site_info,
                'pagination' => array(
                    'per_page' => (int) $per_page,
                    'offset' => (int) $offset,
                    'total' => $total_pages,
                    'has_more' => ($offset + $per_page) < $total_pages
                )
            ), 200);

        } catch (Exception $e) {
            $this->log_sync('pages', 0, 'error', $e->getMessage());

            return new WP_Error(
                'pull_pages_error',
                'Failed to pull pages: ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Publish content to WordPress
     */
    public function publish_content($request) {
        try {
            $title = sanitize_text_field($request->get_param('title'));
            $content = wp_kses_post($request->get_param('content'));
            $status = sanitize_text_field($request->get_param('status')) ?: 'draft';
            $ticket_id = sanitize_text_field($request->get_param('ticket_id'));
            $agent_id = sanitize_text_field($request->get_param('agent_id'));
            $featured_image = esc_url_raw($request->get_param('featured_image'));
            $excerpt = sanitize_textarea_field($request->get_param('excerpt'));
            $categories = $request->get_param('categories') ?: array();
            $tags = $request->get_param('tags') ?: array();

            // Create post
            $post_data = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $status,
                'post_type' => 'post'
            );

            // Add excerpt if provided
            if (!empty($excerpt)) {
                $post_data['post_excerpt'] = $excerpt;
            }

            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                throw new Exception('Failed to create post: ' . $post_id->get_error_message());
            }

            // Handle featured image
            if (!empty($featured_image)) {
                $this->set_featured_image_from_url($post_id, $featured_image);
            }

            // Handle categories
            if (!empty($categories) && is_array($categories)) {
                $category_ids = array();
                foreach ($categories as $category_name) {
                    $category = get_category_by_slug(sanitize_title($category_name));
                    if (!$category) {
                        // Create category if it doesn't exist
                        $category_id = wp_create_category(sanitize_text_field($category_name));
                        if (!is_wp_error($category_id)) {
                            $category_ids[] = $category_id;
                        }
                    } else {
                        $category_ids[] = $category->term_id;
                    }
                }
                if (!empty($category_ids)) {
                    wp_set_post_categories($post_id, $category_ids);
                }
            }

            // Handle tags
            if (!empty($tags) && is_array($tags)) {
                $tag_names = array_map('sanitize_text_field', $tags);
                wp_set_post_tags($post_id, $tag_names);
            }

            // Add metadata for tracking
            if ($ticket_id) {
                update_post_meta($post_id, '_requestdesk_ticket_id', $ticket_id);
            }
            if ($agent_id) {
                update_post_meta($post_id, '_requestdesk_agent_id', $agent_id);
            }

            // Log successful publish
            $this->log_sync('publish', 1, 'success', '', $ticket_id, $post_id, $agent_id);

            return new WP_REST_Response(array(
                'success' => true,
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id),
                'edit_url' => get_edit_post_link($post_id, 'raw'),
                'featured_image_set' => !empty($featured_image),
                'categories_set' => count($category_ids ?? []),
                'tags_set' => count($tag_names ?? [])
            ), 201);

        } catch (Exception $e) {
            $this->log_sync('publish', 0, 'error', $e->getMessage(), $ticket_id, null, $agent_id);

            return new WP_Error(
                'publish_error',
                'Failed to publish content: ' . $e->getMessage(),
                array('status' => 500)
            );
        }
    }

    /**
     * Set featured image from URL
     */
    private function set_featured_image_from_url($post_id, $image_url) {
        if (empty($image_url)) {
            return false;
        }

        // Include WordPress media functions
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        try {
            // Download image
            $temp_file = download_url($image_url);

            if (is_wp_error($temp_file)) {
                error_log('RequestDesk: Failed to download featured image: ' . $temp_file->get_error_message());
                return false;
            }

            // Prepare file array
            $file_array = array(
                'name' => basename($image_url),
                'tmp_name' => $temp_file
            );

            // Upload to media library
            $attachment_id = media_handle_sideload($file_array, $post_id);

            // Clean up temp file
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }

            if (is_wp_error($attachment_id)) {
                error_log('RequestDesk: Failed to create attachment: ' . $attachment_id->get_error_message());
                return false;
            }

            // Set as featured image
            set_post_thumbnail($post_id, $attachment_id);

            return $attachment_id;

        } catch (Exception $e) {
            error_log('RequestDesk: Exception setting featured image: ' . $e->getMessage());

            // Clean up temp file if it exists
            if (isset($temp_file) && file_exists($temp_file)) {
                unlink($temp_file);
            }

            return false;
        }
    }

    /**
     * Verify API key for authentication
     */
    public function verify_api_key($request) {
        $settings = get_option('requestdesk_settings', array());
        $api_key = $settings['api_key'] ?? '';

        if (empty($api_key)) {
            return new WP_Error(
                'no_api_key',
                'RequestDesk API key not configured',
                array('status' => 401)
            );
        }

        $provided_key = $request->get_header('X-RequestDesk-API-Key');
        if (empty($provided_key)) {
            $provided_key = $request->get_param('api_key');
        }

        if (empty($provided_key) || $provided_key !== $api_key) {
            return new WP_Error(
                'invalid_api_key',
                'Invalid API key',
                array('status' => 401)
            );
        }

        return true;
    }

    /**
     * Log sync activity
     */
    private function log_sync($operation, $count, $status, $error_message = '', $ticket_id = '', $post_id = null, $agent_id = '') {
        global $wpdb;

        $table_name = $wpdb->prefix . 'requestdesk_sync_log';

        $wpdb->insert(
            $table_name,
            array(
                'ticket_id' => $ticket_id ?: 'N/A',
                'post_id' => $post_id ?: 0,
                'agent_id' => $agent_id ?: 'N/A',
                'sync_status' => $status,
                'sync_date' => current_time('mysql'),
                'error_message' => $error_message
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s')
        );

        // Also log to WordPress error log if debug mode is enabled
        $settings = get_option('requestdesk_settings', array());
        if ($settings['debug_mode'] ?? false) {
            error_log("RequestDesk Sync - $operation: $count items, status: $status" .
                     ($error_message ? ", error: $error_message" : ""));
        }
    }
}