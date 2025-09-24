<?php
/**
 * RequestDesk Push Handler - Syncs WordPress posts to RequestDesk RAG system
 */

class RequestDesk_Push {
    
    private $api_endpoint;
    private $api_key;
    
    public function __construct() {
        $settings = get_option('requestdesk_settings', array());
        $this->api_key = $settings['api_key'] ?? '';
        $this->api_endpoint = $settings['requestdesk_endpoint'] ?? '';
        
        // Hook into post publish/update events
        add_action('publish_post', array($this, 'sync_post_on_publish'), 10, 2);
        add_action('publish_to_publish', array($this, 'sync_post_on_update'), 10, 1);
        
        // Add admin actions
        add_action('admin_post_requestdesk_bulk_sync', array($this, 'handle_bulk_sync'));
        add_action('admin_post_requestdesk_sync_single', array($this, 'handle_single_sync'));
        
        // Add meta box for single post sync
        add_action('add_meta_boxes', array($this, 'add_sync_meta_box'));
    }
    
    /**
     * Push a single post to RequestDesk
     */
    public function push_to_requestdesk($post_id, $force = false) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_status !== 'publish') {
            return false;
        }
        
        // Check if already synced (unless forcing)
        if (!$force) {
            $last_sync = get_post_meta($post_id, '_requestdesk_last_push', true);
            $post_modified = strtotime($post->post_modified);
            
            if ($last_sync && $last_sync >= $post_modified) {
                return array('status' => 'skipped', 'message' => 'Post already synced');
            }
        }
        
        // Prepare post data for RequestDesk RAG
        $post_data = array(
            'source_id' => 'wp_post_' . $post_id,
            'source_type' => 'wordpress_post',
            'title' => $post->post_title,
            'content' => $this->prepare_content($post),
            'excerpt' => $post->post_excerpt ?: wp_trim_words($post->post_content, 55),
            'url' => get_permalink($post_id),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'published_date' => $post->post_date,
            'modified_date' => $post->post_modified,
            'categories' => $this->get_post_categories($post_id),
            'tags' => $this->get_post_tags($post_id),
            'metadata' => array(
                'post_type' => $post->post_type,
                'post_format' => get_post_format($post_id) ?: 'standard',
                'featured_image' => get_the_post_thumbnail_url($post_id, 'full'),
                'word_count' => str_word_count(strip_tags($post->post_content))
            )
        );
        
        // Send to RequestDesk
        $response = $this->send_to_requestdesk($post_data);
        
        if ($response['success']) {
            // Update sync metadata
            update_post_meta($post_id, '_requestdesk_last_push', time());
            update_post_meta($post_id, '_requestdesk_push_status', 'success');
            
            // Log the sync
            $this->log_push($post_id, 'success', $response['message']);
        } else {
            update_post_meta($post_id, '_requestdesk_push_status', 'failed');
            $this->log_push($post_id, 'failed', $response['error']);
        }
        
        return $response;
    }
    
    /**
     * Send data to RequestDesk API
     */
    private function send_to_requestdesk($data) {
        if (empty($this->api_endpoint) || empty($this->api_key)) {
            return array(
                'success' => false,
                'error' => 'RequestDesk endpoint or API key not configured'
            );
        }
        
        $response = wp_remote_post($this->api_endpoint . '/rag/documents', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
                'X-Source' => 'WordPress'
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code >= 200 && $response_code < 300) {
            return array(
                'success' => true,
                'message' => $response_body['message'] ?? 'Document added to RAG system',
                'document_id' => $response_body['document_id'] ?? null
            );
        } else {
            return array(
                'success' => false,
                'error' => $response_body['error'] ?? 'Failed to push to RequestDesk'
            );
        }
    }
    
    /**
     * Prepare post content for RAG system
     */
    private function prepare_content($post) {
        $content = $post->post_content;
        
        // Apply content filters to get rendered content
        $content = apply_filters('the_content', $content);
        
        // Strip shortcodes but keep their content
        $content = strip_shortcodes($content);
        
        // Convert to plain text while preserving structure
        $content = wp_strip_all_tags($content, true);
        
        // Clean up whitespace
        $content = preg_replace('/\n\s*\n/', "\n\n", $content);
        
        return trim($content);
    }
    
    /**
     * Get post categories
     */
    private function get_post_categories($post_id) {
        $categories = wp_get_post_categories($post_id, array('fields' => 'names'));
        return $categories ?: array();
    }
    
    /**
     * Get post tags
     */
    private function get_post_tags($post_id) {
        $tags = wp_get_post_tags($post_id, array('fields' => 'names'));
        return $tags ?: array();
    }
    
    /**
     * Sync post when published
     */
    public function sync_post_on_publish($post_id, $post) {
        // Skip auto-drafts and revisions
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Check if auto-sync is enabled
        $settings = get_option('requestdesk_settings', array());
        if (empty($settings['auto_sync_on_publish'])) {
            return;
        }
        
        $this->push_to_requestdesk($post_id);
    }
    
    /**
     * Sync post when updated
     */
    public function sync_post_on_update($post) {
        if (!is_object($post)) {
            $post = get_post($post);
        }
        
        // Check if auto-sync updates is enabled
        $settings = get_option('requestdesk_settings', array());
        if (empty($settings['auto_sync_on_update'])) {
            return;
        }
        
        $this->push_to_requestdesk($post->ID);
    }
    
    /**
     * Handle bulk sync request
     */
    public function handle_bulk_sync() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('requestdesk_bulk_sync');
        
        // Get all published posts
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        $post_ids = get_posts($args);
        $total = count($post_ids);
        $success = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($post_ids as $post_id) {
            $result = $this->push_to_requestdesk($post_id);
            
            if ($result['success']) {
                $success++;
            } elseif ($result['status'] === 'skipped') {
                $skipped++;
            } else {
                $failed++;
            }
        }
        
        // Store results in transient for display
        set_transient('requestdesk_bulk_sync_results', array(
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'timestamp' => current_time('mysql')
        ), 300);
        
        wp_redirect(admin_url('admin.php?page=requestdesk-settings&sync=complete'));
        exit;
    }
    
    /**
     * Handle single post sync
     */
    public function handle_single_sync() {
        if (!current_user_can('edit_posts')) {
            wp_die('Unauthorized');
        }
        
        $post_id = intval($_GET['post_id'] ?? 0);
        $nonce = $_GET['_wpnonce'] ?? '';
        
        if (!wp_verify_nonce($nonce, 'requestdesk_sync_post_' . $post_id)) {
            wp_die('Invalid nonce');
        }
        
        $result = $this->push_to_requestdesk($post_id, true);
        
        if ($result['success']) {
            $redirect_url = add_query_arg(array(
                'post' => $post_id,
                'action' => 'edit',
                'requestdesk_sync' => 'success'
            ), admin_url('post.php'));
        } else {
            $redirect_url = add_query_arg(array(
                'post' => $post_id,
                'action' => 'edit',
                'requestdesk_sync' => 'failed',
                'requestdesk_error' => urlencode($result['error'])
            ), admin_url('post.php'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Add sync meta box to post editor
     */
    public function add_sync_meta_box() {
        add_meta_box(
            'requestdesk_sync',
            'RequestDesk Sync',
            array($this, 'render_sync_meta_box'),
            'post',
            'side',
            'default'
        );
    }
    
    /**
     * Render sync meta box
     */
    public function render_sync_meta_box($post) {
        $last_push = get_post_meta($post->ID, '_requestdesk_last_push', true);
        $push_status = get_post_meta($post->ID, '_requestdesk_push_status', true);
        
        echo '<div class="requestdesk-sync-status">';
        
        if ($last_push) {
            $time_diff = human_time_diff($last_push, current_time('timestamp'));
            echo '<p><strong>Last synced:</strong> ' . $time_diff . ' ago</p>';
            echo '<p><strong>Status:</strong> <span class="' . ($push_status === 'success' ? 'success' : 'error') . '">' . ucfirst($push_status) . '</span></p>';
        } else {
            echo '<p>Not yet synced to RequestDesk</p>';
        }
        
        if ($post->post_status === 'publish') {
            $sync_url = wp_nonce_url(
                admin_url('admin-post.php?action=requestdesk_sync_single&post_id=' . $post->ID),
                'requestdesk_sync_post_' . $post->ID
            );
            echo '<p><a href="' . esc_url($sync_url) . '" class="button">Sync to RequestDesk</a></p>';
        } else {
            echo '<p><em>Publish this post to enable syncing</em></p>';
        }
        
        echo '</div>';
        
        echo '<style>
            .requestdesk-sync-status .success { color: #46b450; }
            .requestdesk-sync-status .error { color: #dc3232; }
        </style>';
    }
    
    /**
     * Log push operation
     */
    private function log_push($post_id, $status, $message = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'requestdesk_push_log';
        
        // Create table if it doesn't exist
        $wpdb->query("CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            push_status varchar(50) DEFAULT 'success',
            push_date datetime DEFAULT CURRENT_TIMESTAMP,
            message text,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        )");
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'push_status' => $status,
                'message' => $message
            ),
            array('%d', '%s', '%s')
        );
    }
}