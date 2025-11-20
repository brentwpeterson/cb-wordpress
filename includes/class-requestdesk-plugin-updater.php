<?php
/**
 * RequestDesk Plugin Auto-Updater
 * Enables automatic updates from RequestDesk S3 server
 *
 * @package RequestDesk
 * @version 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RequestDesk_Plugin_Updater {

    /**
     * Update server configuration
     */
    private $update_server = 'https://requestdesk-plugin-updates.s3.amazonaws.com/api/';
    private $plugin_slug;
    private $plugin_file;
    private $plugin_version;
    private $cache_key;
    private $cache_allowed;

    /**
     * Initialize the updater
     *
     * @param string $plugin_file Path to the plugin file
     * @param array $config Update configuration
     */
    public function __construct($plugin_file, $config = array()) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);

        // Get plugin data
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $plugin_data = get_plugin_data($plugin_file, false);
        $this->plugin_version = $plugin_data['Version'];

        $this->cache_key = 'requestdesk_update_check_' . md5($this->plugin_slug);
        $this->cache_allowed = true;

        // Configuration
        $this->update_server = $config['server'] ?? $this->update_server;

        $this->init();
    }

    /**
     * Initialize hooks
     */
    private function init() {
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_api_call'), 10, 3);
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);

        // Admin notices for updates
        add_action('admin_notices', array($this, 'show_update_notice'));

        // WordPress auto-update integration (WordPress 5.5+)
        add_filter('plugin_auto_update_setting_html', array($this, 'plugin_auto_update_setting_html'), 10, 3);
        add_filter('auto_update_plugin', array($this, 'auto_update_plugin'), 10, 2);
    }

    /**
     * Check for plugin updates
     *
     * @param object $transient WordPress update transient
     * @return object Modified transient
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Check if our plugin is in the list
        if (!isset($transient->checked[$this->plugin_slug])) {
            return $transient;
        }

        // Get remote version info
        $remote_version = $this->get_remote_version();

        if (!$remote_version) {
            return $transient;
        }

        // Compare versions
        if (version_compare($this->plugin_version, $remote_version['new_version'], '<')) {
            $transient->response[$this->plugin_slug] = (object) array(
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version['new_version'],
                'url' => $remote_version['details_url'],
                'package' => $remote_version['download_url'],
                'tested' => $remote_version['tested'],
                'requires_php' => $remote_version['requires_php'],
                'compatibility' => array(),
            );

            // Add update details
            $transient->response[$this->plugin_slug]->update_message =
                'New version ' . $remote_version['new_version'] . ' available. <a href="' . $remote_version['details_url'] . '" target="_blank">View release notes</a>.';
        }

        return $transient;
    }

    /**
     * Get remote version information from update server
     *
     * @return array|false Remote version data or false on failure
     */
    private function get_remote_version() {
        // Check cache first
        if ($this->cache_allowed) {
            $cached_result = get_transient($this->cache_key);
            if ($cached_result !== false) {
                return $cached_result;
            }
        }

        // Make request to update server
        $request = wp_remote_get($this->update_server . 'check-version', array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'RequestDesk-Updater/' . $this->plugin_version . '; ' . home_url(),
            ),
            'body' => array(
                'plugin' => 'requestdesk-connector',
                'version' => $this->plugin_version,
                'site_url' => home_url(),
                'php_version' => phpversion(),
                'wp_version' => get_bloginfo('version'),
            ),
        ));

        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200) {
            // Cache negative result briefly
            if ($this->cache_allowed) {
                set_transient($this->cache_key, false, 300); // 5 minutes
            }
            return false;
        }

        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body, true);

        if (!$data || !isset($data['new_version'])) {
            return false;
        }

        $remote_version = array(
            'new_version' => $data['new_version'],
            'download_url' => $data['download_url'],
            'details_url' => $data['details_url'],
            'tested' => $data['tested'] ?? get_bloginfo('version'),
            'requires_php' => $data['requires_php'] ?? '7.4',
            'changelog' => $data['changelog'] ?? '',
        );

        // Cache result for 12 hours
        if ($this->cache_allowed) {
            set_transient($this->cache_key, $remote_version, 12 * HOUR_IN_SECONDS);
        }

        return $remote_version;
    }

    /**
     * Handle plugin API calls for update information
     *
     * @param mixed $result
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function plugin_api_call($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }

        $remote_version = $this->get_remote_version();

        if (!$remote_version) {
            return $result;
        }

        $result = new stdClass();
        $result->name = 'RequestDesk Connector';
        $result->slug = dirname($this->plugin_slug);
        $result->version = $remote_version['new_version'];
        $result->tested = $remote_version['tested'];
        $result->requires_php = $remote_version['requires_php'];
        $result->author = '<a href="https://requestdesk.ai">RequestDesk Team</a>';
        $result->author_profile = 'https://requestdesk.ai';
        $result->download_link = $remote_version['download_url'];
        $result->trunk = $remote_version['download_url'];
        $result->requires = '5.0';
        $result->last_updated = date('Y-m-d');
        $result->sections = array(
            'description' => 'Connects RequestDesk.ai to WordPress for publishing content with secure API key authentication and AEO/AIO/GEO optimization.',
            'changelog' => $remote_version['changelog'] ?: 'See the <a href="' . $remote_version['details_url'] . '" target="_blank">full changelog</a>.',
        );

        return $result;
    }

    /**
     * Show update notice in admin
     */
    public function show_update_notice() {
        if (!current_user_can('update_plugins')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'plugins') {
            return;
        }

        $remote_version = $this->get_remote_version();

        if (!$remote_version) {
            return;
        }

        if (version_compare($this->plugin_version, $remote_version['new_version'], '<')) {
            $plugin_name = get_plugin_data($this->plugin_file)['Name'];

            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>' . esc_html($plugin_name) . '</strong> ';
            echo 'version ' . esc_html($remote_version['new_version']) . ' is available. <a href="' . wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=' . urlencode($this->plugin_slug)), 'upgrade-plugin_' . $this->plugin_slug) . '">Update now</a> or <a href="' . esc_url($remote_version['details_url']) . '" target="_blank">view details</a>.';
            echo '</p></div>';
        }
    }

    /**
     * Clear update cache after successful update
     *
     * @param object $upgrader
     * @param array $options
     */
    public function after_update($upgrader, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && in_array($this->plugin_slug, $options['plugins'])) {
                delete_transient($this->cache_key);
            }
        }
    }

    /**
     * WordPress auto-update setting HTML (WordPress 5.5+)
     * This makes the "Enable auto-updates" link appear
     *
     * @param string $html
     * @param string $plugin_file
     * @param array $plugin_data
     * @return string
     */
    public function plugin_auto_update_setting_html($html, $plugin_file, $plugin_data) {
        if ($plugin_file === $this->plugin_slug) {
            $auto_updates_enabled = in_array($this->plugin_slug, (array) get_site_option('auto_update_plugins', array()));

            if ($auto_updates_enabled) {
                $html = '<a href="' . wp_nonce_url(admin_url('plugins.php?action=disable-auto-update-plugin&plugin=' . urlencode($this->plugin_slug)), 'disable-auto-update-plugin_' . $this->plugin_slug) . '" class="toggle-auto-update" data-wp-action="disable">Disable auto-updates</a>';
            } else {
                $html = '<a href="' . wp_nonce_url(admin_url('plugins.php?action=enable-auto-update-plugin&plugin=' . urlencode($this->plugin_slug)), 'enable-auto-update-plugin_' . $this->plugin_slug) . '" class="toggle-auto-update" data-wp-action="enable">Enable auto-updates</a>';
            }
        }
        return $html;
    }

    /**
     * Control whether our plugin should auto-update
     *
     * @param bool $update
     * @param object $item
     * @return bool
     */
    public function auto_update_plugin($update, $item) {
        if (isset($item->plugin) && $item->plugin === $this->plugin_slug) {
            $auto_updates_enabled = in_array($this->plugin_slug, (array) get_site_option('auto_update_plugins', array()));
            return $auto_updates_enabled;
        }
        return $update;
    }
}

// Auto-updater initialization moved to main plugin file for proper path handling