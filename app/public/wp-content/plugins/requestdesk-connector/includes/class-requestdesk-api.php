<?php
/**
 * RequestDesk REST API Handler
 */

class RequestDesk_API {
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        $namespace = 'requestdesk/v1';
        
        // Endpoint to receive posts from RequestDesk
        register_rest_route($namespace, '/posts', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_post'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'title' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'content' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'excerpt' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'ticket_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'agent_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'post_status' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'draft',
                    'enum' => array('draft', 'pending', 'private', 'publish')
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
        
        // Endpoint to test connection
        register_rest_route($namespace, '/test', array(
            'methods' => 'GET',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'verify_api_key')
        ));
        
        // Endpoint to get sync status
        register_rest_route($namespace, '/sync-status/(?P<ticket_id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sync_status'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'ticket_id' => array(
                    'required' => true,
                    'type' => 'string'
                )
            )
        ));
        
        // NEW: Endpoint to pull posts for RequestDesk knowledge chunks
        register_rest_route($namespace, '/pull-posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'pull_posts_for_knowledge'),
            'permission_callback' => array($this, 'verify_api_key'),
            'args' => array(
                'per_page' => array(
                    'default' => 50,
                    'sanitize_callback' => 'absint',
                ),
                'offset' => array(
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ),
                'modified_since' => array(
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'include_content' => array(
                    'default' => 'true',
                    'sanitize_callback' => 'sanitize_text_field',
                )
            )
        ));
    }
    
    /**
     * Verify API key from RequestDesk agent
     */
    public function verify_api_key($request) {
        $api_key = $request->get_header('X-RequestDesk-API-Key');
        
        if (empty($api_key)) {
            return new WP_Error(
                'missing_api_key',
                'API key is required',
                array('status' => 401)
            );
        }
        
        $settings = get_option('requestdesk_settings', array());
        
        // If debug mode is on, accept any key (SECURITY RISK - FOR TESTING ONLY)
        if (!empty($settings['debug_mode'])) {
            error_log('RequestDesk: DEBUG MODE - Accepting any API key (SECURITY RISK)');
            return true;
        }
        
        // Get the configured API key from settings
        $configured_key = $settings['api_key'] ?? '';
        
        if (empty($configured_key)) {
            return new WP_Error(
                'no_api_key_configured',
                'No API key configured in WordPress settings. Please configure an API key in RequestDesk → Settings.',
                array('status' => 401)
            );
        }
        
        // Exact match validation for security
        if (!hash_equals($configured_key, $api_key)) {
            error_log('RequestDesk: API key validation failed - Key mismatch');
            return new WP_Error(
                'invalid_api_key',
                'Invalid API key',
                array('status' => 401)
            );
        }
        
        // API key is valid
        return true;
    }
    
    /**
     * Create a WordPress post from RequestDesk ticket
     */
    public function create_post($request) {
        $params = $request->get_params();
        
        try {
            // Prepare post data
            $post_data = array(
                'post_title'   => $params['title'],
                'post_content' => $params['content'],
                'post_excerpt' => $params['excerpt'] ?? '',
                'post_status'  => $params['post_status'] ?? 'draft',
                'post_type'    => 'post',
                'meta_input'   => array(
                    '_requestdesk_ticket_id' => $params['ticket_id'],
                    '_requestdesk_agent_id'  => $params['agent_id'],
                    '_requestdesk_synced_at' => current_time('mysql')
                )
            );
            
            // Create the post
            $post_id = wp_insert_post($post_data, true);
            
            if (is_wp_error($post_id)) {
                $this->log_sync(
                    $params['ticket_id'],
                    0,
                    $params['agent_id'],
                    'failed',
                    $post_id->get_error_message()
                );
                
                return new WP_Error(
                    'post_creation_failed',
                    $post_id->get_error_message(),
                    array('status' => 500)
                );
            }
            
            // Handle categories
            if (!empty($params['categories'])) {
                $category_ids = array();
                foreach ($params['categories'] as $category_name) {
                    $term = term_exists($category_name, 'category');
                    if (!$term) {
                        $term = wp_insert_term($category_name, 'category');
                    }
                    if (!is_wp_error($term)) {
                        $category_ids[] = is_array($term) ? $term['term_id'] : $term;
                    }
                }
                if (!empty($category_ids)) {
                    wp_set_post_categories($post_id, $category_ids);
                }
            }
            
            // Handle tags
            if (!empty($params['tags'])) {
                wp_set_post_tags($post_id, $params['tags']);
            }
            
            // Log successful sync
            $this->log_sync(
                $params['ticket_id'],
                $post_id,
                $params['agent_id'],
                'success'
            );
            
            // Get the post URL
            $post_url = get_permalink($post_id);
            $edit_url = get_edit_post_link($post_id, 'raw');
            
            return rest_ensure_response(array(
                'success' => true,
                'post_id' => $post_id,
                'post_url' => $post_url,
                'edit_url' => $edit_url,
                'message' => 'Post created successfully'
            ));
            
        } catch (Exception $e) {
            $this->log_sync(
                $params['ticket_id'],
                0,
                $params['agent_id'],
                'failed',
                $e->getMessage()
            );
            
            return new WP_Error(
                'server_error',
                $e->getMessage(),
                array('status' => 500)
            );
        }
    }
    
    /**
     * Test connection endpoint
     */
    public function test_connection($request) {
        global $wp_version;
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Connection successful',
            'wordpress_version' => $wp_version,
            'plugin_version' => REQUESTDESK_VERSION,
            'site_url' => get_site_url(),
            'rest_url' => get_rest_url(),
            'capabilities' => array(
                'posts' => true,
                'categories' => true,
                'tags' => true,
                'media' => current_user_can('upload_files')
            )
        ));
    }
    
    /**
     * Get sync status for a ticket
     */
    public function get_sync_status($request) {
        global $wpdb;
        
        $ticket_id = $request->get_param('ticket_id');
        $table_name = $wpdb->prefix . 'requestdesk_sync_log';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE ticket_id = %s ORDER BY sync_date DESC",
            $ticket_id
        ));
        
        if (empty($results)) {
            return rest_ensure_response(array(
                'success' => true,
                'synced' => false,
                'message' => 'No sync history found for this ticket'
            ));
        }
        
        $latest = $results[0];
        $post_url = $latest->post_id ? get_permalink($latest->post_id) : null;
        
        return rest_ensure_response(array(
            'success' => true,
            'synced' => true,
            'post_id' => $latest->post_id,
            'post_url' => $post_url,
            'sync_status' => $latest->sync_status,
            'sync_date' => $latest->sync_date,
            'sync_history' => $results
        ));
    }
    
    /**
     * Pull posts for RequestDesk knowledge chunks
     */
    public function pull_posts_for_knowledge($request) {
        $per_page = $request->get_param('per_page') ?: 50;
        $offset = $request->get_param('offset') ?: 0;
        $modified_since = $request->get_param('modified_since');
        $include_content = $request->get_param('include_content') === 'true';
        
        // Build query args
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'offset' => $offset,
            'orderby' => 'modified',
            'order' => 'DESC'
        );
        
        // Add date filter if provided
        if ($modified_since) {
            $args['date_query'] = array(
                array(
                    'column' => 'post_modified',
                    'after' => $modified_since,
                    'inclusive' => true
                )
            );
        }
        
        // Get posts
        $posts = get_posts($args);
        $formatted_posts = array();
        
        foreach ($posts as $post) {
            $post_data = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'published_date' => $post->post_date,
                'modified_date' => $post->post_modified,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'excerpt' => wp_trim_words($post->post_excerpt ?: $post->post_content, 55),
                'word_count' => str_word_count(strip_tags($post->post_content))
            );
            
            // Include full content if requested
            if ($include_content) {
                // Strip shortcodes and clean content
                $content = $post->post_content;
                $content = strip_shortcodes($content);
                $content = wp_strip_all_tags($content);
                $content = trim(preg_replace('/\s+/', ' ', $content));
                $post_data['content'] = $content;
            }
            
            // Get categories
            $categories = wp_get_post_categories($post->ID, array('fields' => 'names'));
            $post_data['categories'] = $categories ?: array();
            
            // Get tags
            $tags = wp_get_post_tags($post->ID, array('fields' => 'names'));
            $post_data['tags'] = $tags ?: array();
            
            $formatted_posts[] = $post_data;
        }
        
        // Get total count for pagination
        $count_args = $args;
        $count_args['posts_per_page'] = -1;
        $count_args['fields'] = 'ids';
        unset($count_args['offset']);
        $total_posts = count(get_posts($count_args));
        
        // Get site info
        $site_info = array(
            'name' => get_bloginfo('name'),
            'url' => get_site_url(),
            'description' => get_bloginfo('description'),
            'language' => get_bloginfo('language')
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'posts' => $formatted_posts,
            'site_info' => $site_info,
            'pagination' => array(
                'total' => $total_posts,
                'per_page' => $per_page,
                'offset' => $offset,
                'has_more' => ($offset + $per_page) < $total_posts
            )
        ), 200);
    }
    
    /**
     * Log sync operation
     */
    private function log_sync($ticket_id, $post_id, $agent_id, $status = 'success', $error_message = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'requestdesk_sync_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'ticket_id' => $ticket_id,
                'post_id' => $post_id,
                'agent_id' => $agent_id,
                'sync_status' => $status,
                'error_message' => $error_message
            ),
            array('%s', '%d', '%s', '%s', '%s')
        );
    }
}