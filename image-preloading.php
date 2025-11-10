<?php
/**
 * Image Preloading Plugin
 *
 * @package ImagePreloading
 * @version 2.0.0
 * @author snowbedding
 * @license GPL-2.0+
 *
 * Plugin Name: Image Preloading
 * Plugin URI: https://github.com/snowbedding/image-preloading
 * Description: Modern image preloading/prefetching plugin for WordPress to improve page loading performance by preloading images in the background using multiple methods.
 * Version: 2.0.0
 * Author: snowbedding
 * Author URI: https://github.com/snowbedding
 * Text Domain: image-preloading
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('IMAGE_PRELOADING_VERSION', '2.0.0');
define('IMAGE_PRELOADING_PLUGIN_FILE', __FILE__);
define('IMAGE_PRELOADING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IMAGE_PRELOADING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IMAGE_PRELOADING_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Image_Preloading_Plugin {

    /**
     * Single instance of the plugin
     *
     * @var Image_Preloading_Plugin|null
     */
    private static $instance = null;

    /**
     * Plugin options
     *
     * @var array
     */
    private $options = array();

    /**
     * Get single instance of the plugin
     *
     * @return Image_Preloading_Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize the plugin
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_options();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));

        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_filter('plugin_action_links_' . IMAGE_PRELOADING_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        }

        // Frontend hooks
        if (!is_admin() && !wp_is_json_request()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        }
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'image-preloading',
            false,
            dirname(IMAGE_PRELOADING_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Plugin initialization code
    }

    /**
     * Load plugin options
     */
    private function load_options() {
        $this->options = get_option('image_preloading_options', array(
            'image_urls' => '',
            'preload_method' => 'javascript',
            'enable_preload' => '1',
            'conditional_loading' => 'all',
            'exclude_pages' => ''
        ));

        // Migrate from old option format if needed
        $this->migrate_old_options();
    }

    /**
     * Migrate old plugin options to new format
     */
    private function migrate_old_options() {
        $old_option = get_option('image_preloading_option_name');
        if (!empty($old_option) && is_array($old_option) && isset($old_option['image_urls_0'])) {
            // Migrate old data
            $new_options = array(
                'image_urls' => $old_option['image_urls_0'],
                'preload_method' => 'javascript', // Default for migrated data
                'enable_preload' => '1',
                'conditional_loading' => 'all',
                'exclude_pages' => ''
            );

            update_option('image_preloading_options', $new_options);
            $this->options = $new_options;

            // Remove old option
            delete_option('image_preloading_option_name');

            // Add admin notice
            add_action('admin_notices', array($this, 'migration_notice'));
        }
    }

    /**
     * Show migration notice
     */
    public function migration_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Image Preloading: Settings have been successfully migrated to the new format!', 'image-preloading'); ?></p>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_image-preloading' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'image-preloading-admin',
            IMAGE_PRELOADING_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            IMAGE_PRELOADING_VERSION
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Image Preloading Settings', 'image-preloading'),
            __('Image Preloading', 'image-preloading'),
            'manage_options',
            'image-preloading',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'image_preloading_settings',
            'image_preloading_options',
            array($this, 'sanitize_options')
        );

        add_settings_section(
            'image_preloading_main',
            __('Main Settings', 'image-preloading'),
            array($this, 'render_settings_section'),
            'image_preloading_settings'
        );

        add_settings_field(
            'enable_preload',
            __('Enable Image Preloading', 'image-preloading'),
            array($this, 'render_enable_field'),
            'image_preloading_settings',
            'image_preloading_main'
        );

        add_settings_field(
            'preload_method',
            __('Preloading Method', 'image-preloading'),
            array($this, 'render_method_field'),
            'image_preloading_settings',
            'image_preloading_main'
        );

        add_settings_field(
            'image_urls',
            __('Image URLs', 'image-preloading'),
            array($this, 'render_urls_field'),
            'image_preloading_settings',
            'image_preloading_main'
        );


        add_settings_field(
            'conditional_loading',
            __('Load on', 'image-preloading'),
            array($this, 'render_conditional_field'),
            'image_preloading_settings',
            'image_preloading_main'
        );

        add_settings_field(
            'exclude_pages',
            __('Exclude Pages', 'image-preloading'),
            array($this, 'render_exclude_field'),
            'image_preloading_settings',
            'image_preloading_main'
        );
    }

    /**
     * Sanitize options
     *
     * @param array $input Input options
     * @return array Sanitized options
     */
    public function sanitize_options($input) {
        $sanitized = array();

        // Enable preload
        $sanitized['enable_preload'] = isset($input['enable_preload']) ? '1' : '0';

        // Preload method
        $allowed_methods = array('javascript', 'link_preload', 'both');
        $sanitized['preload_method'] = in_array($input['preload_method'], $allowed_methods, true)
            ? $input['preload_method']
            : 'javascript';

        // Image URLs - sanitize each URL
        $urls = isset($input['image_urls']) ? trim($input['image_urls']) : '';
        if (!empty($urls)) {
            $url_array = array_filter(array_map('trim', explode("\n", $urls)));
            $sanitized_urls = array();

            foreach ($url_array as $url) {
                $clean_url = esc_url_raw($url);
                if (!empty($clean_url)) {
                    $sanitized_urls[] = $clean_url;
                }
            }

            $sanitized['image_urls'] = implode("\n", $sanitized_urls);
        } else {
            $sanitized['image_urls'] = '';
        }


        // Conditional loading
        $allowed_conditions = array('all', 'front_page', 'posts_page', 'single', 'page', 'archive');
        $sanitized['conditional_loading'] = in_array($input['conditional_loading'], $allowed_conditions, true)
            ? $input['conditional_loading']
            : 'all';

        // Exclude pages - comma-separated list of page IDs
        $exclude_pages = isset($input['exclude_pages']) ? trim($input['exclude_pages']) : '';
        if (!empty($exclude_pages)) {
            $page_ids = array_filter(array_map('intval', array_map('trim', explode(',', $exclude_pages))));
            $sanitized['exclude_pages'] = implode(',', $page_ids);
        } else {
            $sanitized['exclude_pages'] = '';
        }

        return $sanitized;
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        ?>
        <div class="wrap">
            <div class="image-preloading-header">
                <h1><?php _e('Image Preloading Settings', 'image-preloading'); ?></h1>
                <div class="image-preloading-links">
                    <a href="https://wordpress.org/plugins/image-preloading/" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                        <?php _e('WordPress.org Plugin Page', 'image-preloading'); ?>
                    </a>
                    <a href="https://github.com/snowbedding/image-preloading" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                        <?php _e('GitHub Repository', 'image-preloading'); ?>
                    </a>
                </div>
            </div>

            <p><?php _e('Configure image preloading to improve your site\'s performance by loading images in the background.', 'image-preloading'); ?></p>

            <form method="post" action="options.php">
                <?php
                settings_fields('image_preloading_settings');
                do_settings_sections('image_preloading_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render settings section
     */
    public function render_settings_section() {
        echo '<p>' . __('Configure how images should be preloaded on your site.', 'image-preloading') . '</p>';
    }

    /**
     * Render enable field
     */
    public function render_enable_field() {
        $checked = checked('1', $this->options['enable_preload'], false);
        echo '<label><input type="checkbox" name="image_preloading_options[enable_preload]" value="1"' . $checked . ' /> ';
        _e('Enable image preloading functionality', 'image-preloading');
        echo '</label>';
    }

    /**
     * Render method field
     */
    public function render_method_field() {
        $methods = array(
            'javascript' => __('JavaScript (compatible with all browsers)', 'image-preloading'),
            'link_preload' => __('Link Preload (modern browsers only)', 'image-preloading'),
            'both' => __('Both methods (recommended)', 'image-preloading')
        );

        echo '<select name="image_preloading_options[preload_method]" id="preload_method">';
        foreach ($methods as $value => $label) {
            $selected = selected($value, $this->options['preload_method'], false);
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Choose how images should be preloaded.', 'image-preloading') . '</p>';
    }

    /**
     * Render URLs field
     */
    public function render_urls_field() {
        echo '<textarea name="image_preloading_options[image_urls]" rows="8" cols="50" class="large-text code" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.png">';
        echo esc_textarea($this->options['image_urls']);
        echo '</textarea>';
        echo '<p class="description">' . __('Enter one image URL per line. Only enter URLs of images you want to preload.', 'image-preloading') . '</p>';
    }


    /**
     * Render conditional loading field
     */
    public function render_conditional_field() {
        $conditions = array(
            'all' => __('All pages', 'image-preloading'),
            'front_page' => __('Front page (static page or posts page)', 'image-preloading'),
            'posts_page' => __('Blog posts page (when using static front page)', 'image-preloading'),
            'single' => __('Single posts only', 'image-preloading'),
            'page' => __('Pages only', 'image-preloading'),
            'archive' => __('Archive pages only', 'image-preloading')
        );

        echo '<select name="image_preloading_options[conditional_loading]" id="conditional_loading">';
        foreach ($conditions as $value => $label) {
            $selected = selected($value, $this->options['conditional_loading'], false);
            echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Choose where to load the preloading scripts. Front page includes your homepage (static page or blog posts).', 'image-preloading') . '</p>';
    }

    /**
     * Render exclude pages field
     */
    public function render_exclude_field() {
        echo '<input type="text" name="image_preloading_options[exclude_pages]" value="' . esc_attr($this->options['exclude_pages']) . '" class="regular-text" placeholder="1, 5, 12" />';
        echo '<p class="description">' . __('Comma-separated list of page/post IDs to exclude from image preloading.', 'image-preloading') . '</p>';
    }

    /**
     * Add settings link to plugin actions
     *
     * @param array $links Plugin action links
     * @return array Modified links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=image-preloading') . '">' . __('Settings', 'image-preloading') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        if (empty($this->options['enable_preload']) || $this->options['enable_preload'] !== '1') {
            return;
        }

        // Check conditional loading
        if (!$this->should_load_scripts()) {
            return;
        }

        $urls = $this->get_image_urls();
        if (empty($urls)) {
            return;
        }

        $preload_method = $this->options['preload_method'];

        // Enqueue JavaScript preloader
        if (in_array($preload_method, array('javascript', 'both'))) {
            wp_enqueue_script(
                'image-preloading-js',
                IMAGE_PRELOADING_PLUGIN_URL . 'assets/js/image-preloading.js',
                array(),
                IMAGE_PRELOADING_VERSION,
                true
            );

            wp_localize_script('image-preloading-js', 'imagePreloadingData', array(
                'images' => $urls,
                'maxConcurrent' => min(10, count($urls)), // Allow higher concurrency, but cap at 10 for browser compatibility
                'method' => $preload_method
            ));

            // Add debug info in development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                wp_localize_script('image-preloading-js', 'imagePreloadingDebug', array(
                    'method' => $preload_method,
                    'imageCount' => count($urls),
                    'maxConcurrent' => min(10, count($urls))
                ));
            }
        }

        // Add link preload headers
        if (in_array($preload_method, array('link_preload', 'both'))) {
            // Store URLs in a global variable that wp_head hook can access
            global $image_preloading_urls;
            $image_preloading_urls = $urls;

            // Try multiple approaches to ensure link preload headers are added
            add_action('wp_head', array($this, 'output_link_preload_headers'), 1);
            add_action('wp_print_styles', array($this, 'output_link_preload_headers'), 1); // Alternative hook
        }
    }

    /**
     * Check if scripts should be loaded based on conditional settings
     *
     * @return bool Whether to load scripts
     */
    private function should_load_scripts() {
        $condition = $this->options['conditional_loading'];

        switch ($condition) {
            case 'front_page':
                return is_front_page();
            case 'posts_page':
                return is_home() && !is_front_page(); // Blog posts page when different from front page
            case 'single':
                return is_single();
            case 'page':
                return is_page();
            case 'archive':
                return is_archive();
            case 'all':
            default:
                // Check excluded pages
                if (!empty($this->options['exclude_pages'])) {
                    $excluded_ids = array_filter(array_map('intval', explode(',', $this->options['exclude_pages'])));
                    if (in_array(get_the_ID(), $excluded_ids)) {
                        return false;
                    }
                }
                return true;
        }
    }

    /**
     * Output link preload headers (called by wp_head hook)
     */
    public function output_link_preload_headers() {
        static $output_done = false;

        // Prevent duplicate output
        if ($output_done) {
            return;
        }

        global $image_preloading_urls;

        if (!empty($image_preloading_urls)) {
            // Add debug comment in development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo "<!-- Image Preloading: Link preload headers for " . count($image_preloading_urls) . " images -->\n";
            }

            foreach ($image_preloading_urls as $url) {
                echo '<link rel="preload" href="' . esc_url($url) . '" as="image" crossorigin="anonymous">' . "\n";
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo "<!-- End Image Preloading link preload headers -->\n";
            }

            $output_done = true; // Mark as done
        }
    }

    /**
     * Add link preload headers (legacy method for backward compatibility)
     *
     * @param array $urls Array of image URLs to preload (optional, uses stored URLs if not provided)
     */
    public function add_link_preload_headers($urls = null) {
        if ($urls === null) {
            $urls = $this->get_image_urls();
        }

        if (!empty($urls)) {
            // Add debug comment in development
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo "<!-- Image Preloading: Link preload headers for " . count($urls) . " images -->\n";
            }

            foreach ($urls as $url) {
                echo '<link rel="preload" href="' . esc_url($url) . '" as="image" crossorigin="anonymous">' . "\n";
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo "<!-- End Image Preloading link preload headers -->\n";
            }
        }
    }

    /**
     * Get sanitized image URLs
     *
     * @return array Array of image URLs
     */
    private function get_image_urls() {
        if (empty($this->options['image_urls'])) {
            return array();
        }

        $urls = array_filter(array_map('trim', explode("\n", $this->options['image_urls'])));
        return array_map('esc_url', $urls);
    }
}

/**
 * Initialize the plugin
 *
 * @return Image_Preloading_Plugin
 */
function image_preloading_plugin() {
    return Image_Preloading_Plugin::get_instance();
}

// Start the plugin
image_preloading_plugin();
