<?php
/**
 * RequestDesk Settings Page
 */

function requestdesk_settings_page() {
    // Save settings if form submitted
    if (isset($_POST['requestdesk_save_settings']) && wp_verify_nonce($_POST['requestdesk_nonce'], 'requestdesk_settings')) {
        $settings = array(
            'debug_mode' => isset($_POST['debug_mode']),
            'default_post_status' => sanitize_text_field($_POST['default_post_status']),
            'allowed_post_types' => array('post'), // For MVP, only posts
            'api_key' => sanitize_text_field($_POST['api_key']),
            'requestdesk_endpoint' => sanitize_text_field($_POST['requestdesk_endpoint']),
            'auto_sync_on_publish' => isset($_POST['auto_sync_on_publish']),
            'auto_sync_on_update' => isset($_POST['auto_sync_on_update'])
        );
        
        update_option('requestdesk_settings', $settings);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    // Check for bulk sync results
    if (isset($_GET['sync']) && $_GET['sync'] === 'complete') {
        $results = get_transient('requestdesk_bulk_sync_results');
        if ($results) {
            echo '<div class="notice notice-success"><p>';
            echo sprintf('Bulk sync completed: %d posts processed, %d successful, %d failed, %d skipped', 
                $results['total'], $results['success'], $results['failed'], $results['skipped']);
            echo '</p></div>';
            delete_transient('requestdesk_bulk_sync_results');
        }
    }
    
    $settings = get_option('requestdesk_settings', array(
        'debug_mode' => false,
        'default_post_status' => 'draft',
        'allowed_post_types' => array('post'),
        'api_key' => ''
    ));
    
    // Get sync logs
    global $wpdb;
    $table_name = $wpdb->prefix . 'requestdesk_sync_log';
    $recent_syncs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY sync_date DESC LIMIT 10");
    ?>
    
    <div class="wrap">
        <h1>RequestDesk Connector Settings</h1>
        
        <div class="card">
            <h2>Connection Information</h2>
            <table class="form-table">
                <tr>
                    <th>REST API Endpoint</th>
                    <td>
                        <code><?php echo get_rest_url(null, 'requestdesk/v1/posts'); ?></code>
                        <p class="description">Use this endpoint to send posts from RequestDesk</p>
                    </td>
                </tr>
                <tr>
                    <th>Test Endpoint</th>
                    <td>
                        <code><?php echo get_rest_url(null, 'requestdesk/v1/test'); ?></code>
                        <p class="description">Use this to test your connection</p>
                    </td>
                </tr>
                <tr>
                    <th>Authentication</th>
                    <td>
                        <p class="description">
                            Add header: <code>X-RequestDesk-API-Key: [Your Agent API Key]</code><br>
                            <strong>Security:</strong> Only requests with the exact API key configured above will be accepted.
                        </p>
                        <?php if (!empty($settings['api_key'])): ?>
                        <p class="description" style="color: green;">
                            ✅ <strong>Secure:</strong> API key is configured and connections are protected.
                        </p>
                        <?php else: ?>
                        <p class="description" style="color: red;">
                            ❌ <strong>Insecure:</strong> No API key configured! Configure one above to enable connections.
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('requestdesk_settings', 'requestdesk_nonce'); ?>
            
            <div class="card">
                <h2>📤 Push to RequestDesk RAG Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">RequestDesk Endpoint</th>
                        <td>
                            <input type="text" name="requestdesk_endpoint" value="<?php echo esc_attr($settings['requestdesk_endpoint'] ?? ''); ?>" class="regular-text" placeholder="https://api.requestdesk.ai">
                            <p class="description">
                                Your RequestDesk API endpoint for pushing content to the RAG system
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Auto Sync Options</th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_sync_on_publish" value="1" <?php checked($settings['auto_sync_on_publish'] ?? false, true); ?>>
                                Automatically sync new posts when published
                            </label><br>
                            <label>
                                <input type="checkbox" name="auto_sync_on_update" value="1" <?php checked($settings['auto_sync_on_update'] ?? false, true); ?>>
                                Automatically sync posts when updated
                            </label>
                            <p class="description">
                                Choose when posts should be automatically pushed to RequestDesk's RAG system
                            </p>
                        </td>
                    </tr>
                </table>
                
                <h3>Bulk Sync All Posts</h3>
                <p>Push all published posts to RequestDesk's RAG system at once.</p>
                <?php 
                $post_count = wp_count_posts('post');
                $published_count = $post_count->publish;
                ?>
                <p>You have <strong><?php echo $published_count; ?></strong> published posts ready to sync.</p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;">
                    <input type="hidden" name="action" value="requestdesk_bulk_sync">
                    <?php wp_nonce_field('requestdesk_bulk_sync'); ?>
                    <input type="submit" class="button button-primary" value="Sync All Published Posts" 
                           onclick="return confirm('This will sync all <?php echo $published_count; ?> published posts to RequestDesk. Continue?');">
                </form>
                <p class="description">
                    This will push all published posts to RequestDesk's RAG system. Posts that haven't changed since the last sync will be skipped.
                </p>
            </div>
            
            <div class="card">
                <h2>🔐 Security Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Allowed API Key</th>
                        <td>
                            <input type="password" name="api_key" value="<?php echo esc_attr($settings['api_key']); ?>" class="regular-text" placeholder="Enter your RequestDesk Agent API Key">
                            <p class="description">
                                <strong>Required:</strong> Enter your RequestDesk agent's API key to secure this connection.<br>
                                Only requests with this exact API key will be accepted.<br>
                                You can find your agent's API key in the RequestDesk dashboard under Agent Settings.
                            </p>
                            <?php if (empty($settings['api_key']) && !$settings['debug_mode']): ?>
                            <div class="notice notice-warning inline">
                                <p><strong>⚠️ Warning:</strong> No API key configured! Your WordPress site will reject all RequestDesk connections until you set an API key.</p>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="card">
                <h2>Plugin Settings</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Debug Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="debug_mode" value="1" <?php checked($settings['debug_mode'], true); ?>>
                                Enable debug mode (bypasses API key validation)
                            </label>
                            <p class="description">
                                <strong>⚠️ Security Risk:</strong> Debug mode disables API key validation and accepts ANY API key.<br>
                                Only enable this for testing. <strong>NEVER enable in production!</strong>
                            </p>
                            <?php if ($settings['debug_mode']): ?>
                            <div class="notice notice-error inline">
                                <p><strong>🚨 Security Warning:</strong> Debug mode is currently ENABLED! Your site accepts any API key. Disable this immediately for production use.</p>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Default Post Status</th>
                        <td>
                            <select name="default_post_status">
                                <option value="draft" <?php selected($settings['default_post_status'], 'draft'); ?>>Draft</option>
                                <option value="pending" <?php selected($settings['default_post_status'], 'pending'); ?>>Pending Review</option>
                                <option value="private" <?php selected($settings['default_post_status'], 'private'); ?>>Private</option>
                                <option value="publish" <?php selected($settings['default_post_status'], 'publish'); ?>>Published</option>
                            </select>
                            <p class="description">Default status for posts created from RequestDesk</p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" name="requestdesk_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
        
        <div class="card">
            <h2>Test Connection</h2>
            <p>Use this curl command to test your connection:</p>
            <pre style="background: #f0f0f0; padding: 10px; overflow-x: auto;">
curl -X GET \
  <?php echo get_rest_url(null, 'requestdesk/v1/test'); ?> \
  -H "X-RequestDesk-API-Key: YOUR_AGENT_API_KEY"</pre>
            
            <p>To send a test post:</p>
            <pre style="background: #f0f0f0; padding: 10px; overflow-x: auto;">
curl -X POST \
  <?php echo get_rest_url(null, 'requestdesk/v1/posts'); ?> \
  -H "Content-Type: application/json" \
  -H "X-RequestDesk-API-Key: YOUR_AGENT_API_KEY" \
  -d '{
    "title": "Test Post from RequestDesk",
    "content": "This is test content from RequestDesk integration.",
    "excerpt": "Test excerpt",
    "ticket_id": "test_ticket_123",
    "agent_id": "test_agent_456",
    "post_status": "draft",
    "categories": ["Test Category"],
    "tags": ["test", "requestdesk"]
  }'</pre>
        </div>
        
        <?php if (!empty($recent_syncs)): ?>
        <div class="card">
            <h2>Recent Sync Activity</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Post ID</th>
                        <th>Agent ID</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_syncs as $sync): ?>
                    <tr>
                        <td><?php echo esc_html($sync->ticket_id); ?></td>
                        <td>
                            <?php if ($sync->post_id): ?>
                                <a href="<?php echo get_edit_post_link($sync->post_id); ?>">
                                    #<?php echo $sync->post_id; ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($sync->agent_id); ?></td>
                        <td>
                            <span class="<?php echo $sync->sync_status === 'success' ? 'text-success' : 'text-error'; ?>">
                                <?php echo esc_html($sync->sync_status); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($sync->sync_date); ?></td>
                        <td><?php echo esc_html($sync->error_message ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Documentation</h2>
            <ul>
                <li><a href="https://requestdesk.ai/docs/wordpress-integration" target="_blank">Integration Guide</a></li>
                <li><a href="https://requestdesk.ai/support" target="_blank">Get Support</a></li>
            </ul>
        </div>
    </div>
    
    <style>
        .card {
            background: white;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .text-success { color: #46b450; }
        .text-error { color: #dc3232; }
    </style>
    <?php
}