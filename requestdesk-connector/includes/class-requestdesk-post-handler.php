<?php
/**
 * RequestDesk Post Handler
 * Handles post creation and updates from RequestDesk
 */

class RequestDesk_Post_Handler {
    
    /**
     * Create or update a post from RequestDesk data
     */
    public static function handle_post_data($data) {
        // Check if post already exists for this ticket
        $existing_post = self::get_post_by_ticket_id($data['ticket_id']);
        
        if ($existing_post) {
            return self::update_post($existing_post->ID, $data);
        } else {
            return self::create_post($data);
        }
    }
    
    /**
     * Get post by RequestDesk ticket ID
     */
    public static function get_post_by_ticket_id($ticket_id) {
        $args = array(
            'meta_key' => '_requestdesk_ticket_id',
            'meta_value' => $ticket_id,
            'post_type' => 'any',
            'posts_per_page' => 1
        );
        
        $posts = get_posts($args);
        return !empty($posts) ? $posts[0] : null;
    }
    
    /**
     * Create a new post
     */
    private static function create_post($data) {
        $post_data = array(
            'post_title'   => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['content']),
            'post_excerpt' => sanitize_textarea_field($data['excerpt'] ?? ''),
            'post_status'  => $data['post_status'] ?? 'draft',
            'post_type'    => $data['post_type'] ?? 'post',
            'meta_input'   => array(
                '_requestdesk_ticket_id' => $data['ticket_id'],
                '_requestdesk_agent_id'  => $data['agent_id'],
                '_requestdesk_synced_at' => current_time('mysql')
            )
        );
        
        // Set author if provided
        if (!empty($data['author_id'])) {
            $post_data['post_author'] = intval($data['author_id']);
        }
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Handle taxonomies
        self::handle_taxonomies($post_id, $data);
        
        return $post_id;
    }
    
    /**
     * Update an existing post
     */
    private static function update_post($post_id, $data) {
        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field($data['title']),
            'post_content' => wp_kses_post($data['content']),
            'post_excerpt' => sanitize_textarea_field($data['excerpt'] ?? '')
        );
        
        // Only update status if explicitly provided
        if (!empty($data['post_status'])) {
            $post_data['post_status'] = $data['post_status'];
        }
        
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Update meta
        update_post_meta($post_id, '_requestdesk_last_synced', current_time('mysql'));
        update_post_meta($post_id, '_requestdesk_agent_id', $data['agent_id']);
        
        // Handle taxonomies
        self::handle_taxonomies($post_id, $data);
        
        return $post_id;
    }
    
    /**
     * Handle categories and tags
     */
    private static function handle_taxonomies($post_id, $data) {
        // Handle categories
        if (!empty($data['categories'])) {
            $category_ids = array();
            
            foreach ($data['categories'] as $category_name) {
                $category_name = sanitize_text_field($category_name);
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
        if (!empty($data['tags'])) {
            $tags = array_map('sanitize_text_field', $data['tags']);
            wp_set_post_tags($post_id, $tags);
        }
    }
    
    /**
     * Get RequestDesk metadata for a post
     */
    public static function get_requestdesk_meta($post_id) {
        return array(
            'ticket_id' => get_post_meta($post_id, '_requestdesk_ticket_id', true),
            'agent_id' => get_post_meta($post_id, '_requestdesk_agent_id', true),
            'synced_at' => get_post_meta($post_id, '_requestdesk_synced_at', true),
            'last_synced' => get_post_meta($post_id, '_requestdesk_last_synced', true)
        );
    }
}