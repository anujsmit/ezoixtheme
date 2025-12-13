<?php

/**
 * Ezoix Tech Blog Theme Functions - Optimized Version 2.2
 * * @package Ezoix_Tech_Blog
 */

if (! defined('ABSPATH')) {
}

/**
 * Theme Setup
 */
function ezoix_theme_setup()
{
    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    add_theme_support('custom-background');

    add_theme_support('post-formats', array(
        'aside',
        'gallery',
        'link',
        'image',
        'quote',
        'status',
        'video',
        'audio',
        'chat'
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'ezoix'),
        'footer'  => __('Footer Menu', 'ezoix'),
    ));

    add_image_size('mobile-thumbnail', 400, 200, true);
    add_image_size('tablet-thumbnail', 600, 300, true);
    add_image_size('desktop-thumbnail', 800, 400, true);
    add_image_size('featured-image', 1200, 600, true);
    add_image_size('hero-image', 1920, 800, true);
    add_image_size('featured-image', 1200, 600, true);
    add_image_size('hero-image', 1920, 800, true);

    // NEW: 9:16 Portrait Aspect Ratio Sizes
    add_image_size('feed-landscape', 120, 67.5, true);
    add_image_size('grid-landscape', 280, 157.5, true);

    load_theme_textdomain('ezoix', get_template_directory() . '/languages');

    ezoix_register_mobile_device_cpt();
    ezoix_register_mobile_taxonomies();
    ezoix_register_laptop_device_cpt(); 
    ezoix_register_laptop_taxonomies(); 
}
add_action('after_setup_theme', 'ezoix_theme_setup');

/**
 * Optimized Enqueue Styles and Scripts
 */
function ezoix_theme_scripts()
{
    wp_enqueue_style('ezoix-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));

    wp_enqueue_style('ezoix-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap', array(), null);

    wp_enqueue_script('ezoix-script', get_template_directory_uri() . '/js/script.js', array(), '2.2', true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    $total_pages = 1;
    // Calculate total pages only on the home page for performance
    if (is_front_page() || is_home()) {
        $total_pages = ezoix_get_total_pages_for_merged_feed();
    }

    wp_localize_script('ezoix-script', 'ezoix_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ezoix_nonce'),
        'total_pages' => $total_pages,
        'loading_text' => __('Loading...', 'ezoix'),
        'no_more_posts' => __('No more posts to load', 'ezoix')
    ));
}
add_action('wp_enqueue_scripts', 'ezoix_theme_scripts');

/**
 * Defer non-critical CSS
 */
function ezoix_async_styles($html, $handle)
{
    if (is_admin()) return $html;

    $deferred_styles = ['ezoix-google-fonts'];
    if (in_array($handle, $deferred_styles)) {
        return str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
    }
    return $html;
}
add_filter('style_loader_tag', 'ezoix_async_styles', 10, 2);

/**
 * Enhanced Lazy Load images with fallback
 */
function ezoix_lazy_load_images($content)
{
    if (is_admin() || is_feed() || wp_is_json_request()) return $content;

    if (is_singular() && has_post_thumbnail()) {
        $content = preg_replace_callback('/<img([^>]*)class="([^"]*wp-post-image[^"]*)"([^>]*)>/i', function ($matches) {
            $img = $matches[1] . $matches[3];
            $classes = $matches[2] . ' ezoix-critical';
            return '<img' . $img . 'class="' . $classes . '" loading="eager">';
        }, $content);
    }

    $content = preg_replace_callback('/<img([^>]+)src=(["\'])(.*?)\2([^>]*)>/i', function ($matches) {
        $img_attrs = $matches[1] . $matches[4];
        $src = $matches[3];
        $quote = $matches[2];

        if (strpos($img_attrs, 'loading=') !== false) {
            return $matches[0];
        }

        if (strpos($img_attrs, 'ezoix-critical') !== false) {
            return $matches[0];
        }

        return '<img' . $img_attrs . ' data-src="' . esc_url($src) . '" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" loading="lazy" class="lazy">';
    }, $content);

    return $content;
}
add_filter('the_content', 'ezoix_lazy_load_images', 999);

/**
 * Responsive images with lazy loading
 */
function ezoix_responsive_images($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    if (is_admin()) return $html;

    $src = wp_get_attachment_image_src($post_thumbnail_id, $size);
    $srcset = wp_get_attachment_image_srcset($post_thumbnail_id, $size);
    $sizes = wp_get_attachment_image_sizes($post_thumbnail_id, $size);

    $loading = 'loading="lazy"';
    $lazy_class = 'class="lazy"';
    $img_src = 'src="' . esc_url($src[0]) . '"';
    $data_src = '';

    if (is_singular() && has_post_thumbnail() && get_post_thumbnail_id() == $post_thumbnail_id) {
        $loading = 'loading="eager"';
        $lazy_class = 'class="ezoix-critical"';
    } else {
        $data_src = 'data-src="' . esc_url($src[0]) . '" data-srcset="' . esc_attr($srcset) . '"';
        $img_src = 'src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"';
        $lazy_class = 'class="lazy"';
    }

    return sprintf(
        '<img %s %s sizes="%s" %s %s alt="%s">',
        $img_src,
        $data_src,
        esc_attr($sizes),
        $loading,
        $lazy_class,
        isset($attr['alt']) ? esc_attr($attr['alt']) : get_the_title($post_id)
    );
}
add_filter('post_thumbnail_html', 'ezoix_responsive_images', 10, 5);

/**
 * Register Widget Areas
 */
function ezoix_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Sidebar', 'ezoix'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'ezoix'),
        'before_widget' => '<div class="sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widgets', 'ezoix'),
        'id'            => 'footer-widgets',
        'description'   => __('Add widgets here to appear in your footer.', 'ezoix'),
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="footer-widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'ezoix_widgets_init');

/**
 * Cache database queries
 */
function ezoix_cache_featured_posts($number = 2)
{
    $cache_key = 'ezoix_featured_posts_' . $number;
    $posts = get_transient($cache_key);

    if (false === $posts) {
        // Query posts, mobile devices, AND LAPTOPS for featured section
        $posts = new WP_Query(array(
            'posts_per_page' => $number,
            'post_type' => array('post', 'mobile_device', 'laptop_device'), 
            'meta_query'     => array(
                array(
                    'key'     => 'featured_post',
                    'value'   => '1',
                    'compare' => '='
                )
            ),
            'no_found_rows'  => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));
        set_transient($cache_key, $posts, 12 * HOUR_IN_SECONDS);
    }
    return $posts;
}

/**
 * Cache recent posts
 */
function ezoix_cache_recent_posts($number = 5)
{
    $cache_key = 'ezoix_recent_posts_' . $number;
    $posts = get_transient($cache_key);

    if (false === $posts) {
        $posts = wp_get_recent_posts(array(
            'numberposts' => $number,
            'post_status' => 'publish',
            'post_type'   => 'post',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'suppress_filters' => true
        ));
        set_transient($cache_key, $posts, 6 * HOUR_IN_SECONDS);
    }
    return $posts;
}

/**
 * Custom Excerpt Length
 */
function ezoix_excerpt_length($length)
{
    return 25;
}
add_filter('excerpt_length', 'ezoix_excerpt_length');

/**
 * Custom Excerpt More
 */
function ezoix_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'ezoix_excerpt_more');

/**
 * Add Featured Post Meta Box
 */
function ezoix_add_featured_meta_box()
{
    add_meta_box(
        'ezoix_featured_meta',
        __('Featured Post', 'ezoix'),
        'ezoix_featured_meta_callback',
        array('post', 'mobile_device', 'laptop_device'), 
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'ezoix_add_featured_meta_box');

function ezoix_featured_meta_callback($post)
{
    wp_nonce_field('ezoix_featured_meta', 'ezoix_featured_meta_nonce');
    $featured = get_post_meta($post->ID, 'featured_post', true);
    echo '<label for="featured_post">';
    echo '<input type="checkbox" id="featured_post" name="featured_post" value="1" ' . checked($featured, 1, false) . ' />';
    echo ' ' . __('Mark as featured post', 'ezoix') . '</label>';
    echo '<p class="description">' . __('Featured items will appear in the "Trending Now" section.', 'ezoix') . '</p>';
}

function ezoix_save_featured_meta($post_id)
{
    if (! isset($_POST['ezoix_featured_meta_nonce'])) {
        return;
    }

    if (! wp_verify_nonce($_POST['ezoix_featured_meta_nonce'], 'ezoix_featured_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['post_type'])) {
        $post_type = $_POST['post_type'];
        if (! in_array($post_type, array('post', 'mobile_device', 'laptop_device'))) { 
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    $featured = isset($_POST['featured_post']) ? 1 : 0;
    update_post_meta($post_id, 'featured_post', $featured);

    delete_transient('ezoix_featured_posts_2');
}
add_action('save_post', 'ezoix_save_featured_meta');

/**
 * Optimize WordPress
 */
function ezoix_optimize_queries()
{
    if (! is_admin()) {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }
}
add_action('init', 'ezoix_optimize_queries');

/**
 * Customizer Options
 */
function ezoix_customize_register($wp_customize)
{
    $wp_customize->add_section('ezoix_hero_section', array(
        'title'    => __('Hero Section', 'ezoix'),
        'priority' => 30,
    ));

    $wp_customize->add_setting('ezoix_hero_title', array(
        'default'           => __('Latest Tech Insights & Reviews', 'ezoix'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('ezoix_hero_title', array(
        'label'   => __('Hero Title', 'ezoix'),
        'section' => 'ezoix_hero_section',
        'type'    => 'text',
    ));

    $wp_customize->add_setting('ezoix_hero_subtitle', array(
        'default'           => __('Stay updated with the newest technology trends, gadget reviews, and AI developments', 'ezoix'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('ezoix_hero_subtitle', array(
        'label'   => __('Hero Subtitle', 'ezoix'),
        'section' => 'ezoix_hero_section',
        'type'    => 'textarea',
    ));
}
add_action('customize_register', 'ezoix_customize_register');

/**
 * Custom Categories Widget
 */
class Ezoix_Categories_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'ezoix_categories_widget',
            __('Ezoix Categories', 'ezoix'),
            array('description' => __('A custom categories widget with counts', 'ezoix'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = ! empty($instance['title']) ? $instance['title'] : __('Categories', 'ezoix');

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        $categories = get_categories(array(
            'orderby' => 'name',
            'order'   => 'ASC'
        ));

        echo '<ul class="categories-list">';
        foreach ($categories as $category) {
            echo '<li><a href="' . get_category_link($category->term_id) . '">' . $category->name . ' <span class="category-count">' . $category->count . '</span></a></li>';
        }
        echo '</ul>';

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : __('Categories', 'ezoix');
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'ezoix'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

/**
 * Recent Posts Widget
 */
class Ezoix_Recent_Posts_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'ezoix_recent_posts_widget',
            __('Ezoix Recent Posts', 'ezoix'),
            array('description' => __('A custom recent posts widget', 'ezoix'))
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = ! empty($instance['title']) ? $instance['title'] : __('Recent Posts', 'ezoix');
        $number = ! empty($instance['number']) ? absint($instance['number']) : 5;

        echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];

        $recent_posts = ezoix_cache_recent_posts($number);

        echo '<ul class="recent-posts-list">';
        foreach ($recent_posts as $post) {
            echo '<li><a href="' . get_permalink($post['ID']) . '">' . $post['post_title'] . '</a></li>';
        }
        echo '</ul>';

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : __('Recent Posts', 'ezoix');
        $number = ! empty($instance['number']) ? absint($instance['number']) : 5;
    ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'ezoix'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts:', 'ezoix'); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
    <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (! empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (! empty($new_instance['number'])) ? absint($new_instance['number']) : 5;

        delete_transient('ezoix_recent_posts_' . $instance['number']);

        return $instance;
    }
}

/**
 * Register Custom Widgets
 */
function register_ezoix_widgets()
{
    register_widget('Ezoix_Categories_Widget');
    register_widget('Ezoix_Recent_Posts_Widget');
}
add_action('widgets_init', 'register_ezoix_widgets');

/**
 * Performance Monitoring
 */
function ezoix_performance_monitor()
{
    if (current_user_can('manage_options')) {
        echo '';
        echo '';
        echo '';
    }
}
add_action('wp_footer', 'ezoix_performance_monitor');

/**
 * Security: Disable XML-RPC
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove WordPress version number
 */
function ezoix_remove_version()
{
    return '';
}
add_filter('the_generator', 'ezoix_remove_version');

/**
 * Custom Login Logo
 */
function ezoix_custom_login_logo()
{
    if (has_custom_logo()) {
        $logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
    ?>
        <style type="text/css">
            #login h1 a,
            .login h1 a {
                background-image: url('<?php echo esc_url($logo[0]); ?>');
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                width: 100%;
                height: 60px;
            }
        </style>
    <?php
    }
}
add_action('login_enqueue_scripts', 'ezoix_custom_login_logo');

/**
 * Custom Login Logo URL
 */
function ezoix_login_logo_url()
{
    return home_url();
}
add_filter('login_headerurl', 'ezoix_login_logo_url');

/**
 * Custom Login Logo Title
 */
function ezoix_login_logo_url_title()
{
    return get_bloginfo('name');
}
add_filter('login_headertitle', 'ezoix_login_logo_url_title');

/**
 * Add body class for featured image
 */
function ezoix_body_class($classes)
{
    if (is_single() && has_post_thumbnail()) {
        $classes[] = 'has-featured-image';
    }

    if (wp_is_mobile()) {
        $classes[] = 'is-mobile';
    }

    if (is_category()) {
        $classes[] = 'category-page';
    }

    return $classes;
}
add_filter('body_class', 'ezoix_body_class');

/**
 * Clean up transients on post save
 */
function ezoix_clean_transients($post_id)
{
    if (wp_is_post_revision($post_id)) {
        return;
    }

    delete_transient('ezoix_featured_posts_2');
    delete_transient('ezoix_recent_posts_5');
}
add_action('save_post', 'ezoix_clean_transients');

/**
 * Preload critical resources
 */
function ezoix_preload_critical_resources()
{
    if (is_front_page()) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">' . "\n";

        $featured_posts = ezoix_cache_featured_posts(2);
        if ($featured_posts->have_posts()) {
            while ($featured_posts->have_posts()) {
                $featured_posts->the_post();
                if (has_post_thumbnail()) {
                    $thumb_url = wp_get_attachment_image_url(get_post_thumbnail_id(), 'desktop-thumbnail');
                    echo '<link rel="preload" href="' . esc_url($thumb_url) . '" as="image">' . "\n";
                }
            }
            wp_reset_postdata();
        }
    }
}
add_action('wp_head', 'ezoix_preload_critical_resources', 1);

/**
 * Fix category pagination
 */
function ezoix_fix_category_pagination($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_category()) {
        $query->set('posts_per_page', 12);
        $query->set('no_found_rows', false);
        $query->set('update_post_term_cache', true);
        $query->set('update_post_meta_cache', true);
    }
}
add_action('pre_get_posts', 'ezoix_fix_category_pagination');

/**
 * New function to include CPTs on the main home/index page query.
 */
function ezoix_include_cpt_on_home($query)
{
    // Check if it's the main query and the home page (or posts page)
    if (! is_admin() && $query->is_main_query() && ($query->is_home() || $query->is_front_page())) {
        $post_types_to_include = array('post', 'mobile_device', 'laptop_device'); 
        $current_post_types = (array) $query->get('post_type');

        $updated_post_types = array_unique(array_merge($current_post_types, $post_types_to_include));

        // Remove any empty/null values that might have been added
        $updated_post_types = array_filter($updated_post_types);

        $query->set('post_type', $updated_post_types);
    }
}
// Add to pre_get_posts hook
add_action('pre_get_posts', 'ezoix_include_cpt_on_home');


/**
 * Get total pages count for infinite scroll for the merged feed.
 */
function ezoix_get_total_pages_for_merged_feed()
{
    $posts_per_page = 10;

    // 1. Get featured posts IDs to exclude from the main feed count
    $featured_posts = ezoix_cache_featured_posts(2);
    $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

    // 2. Count ALL published posts (post, mobile_device, and laptop_device)
    $all_posts_query = new WP_Query(array(
        'post_type' => array('post', 'mobile_device', 'laptop_device'), 
        'post_status' => 'publish',
        'posts_per_page' => -1, // Get total count
        'fields' => 'ids',
        'no_found_rows' => false,
        'post__not_in' => $exclude_ids,
    ));

    $total_posts = $all_posts_query->found_posts;

    // 3. Calculate pages
    // The first 15 posts are loaded in the initial page load (index.php)
    $initial_load_count = 15;
    $remaining_posts = max(0, $total_posts - $initial_load_count);

    return ceil($remaining_posts / $posts_per_page) + 1; // +1 for the initial page
}

/**
 * Helper function to render a unified feed item card (article, mobile, or laptop device) in grid format.
 */
function ezoix_render_grid_card()
{
    $post_id = get_the_ID();
    $post_type = get_post_type();
    $author_name = get_the_author();
    $permalink = get_the_permalink();
    $title = get_the_title();

    // --- Device Specific Data ---
    $price = (function_exists('get_field') && in_array($post_type, ['mobile_device', 'laptop_device'])) ? get_field('device_price', $post_id) : null;
    $rating = (function_exists('get_field') && in_array($post_type, ['mobile_device', 'laptop_device'])) ? get_field('device_rating', $post_id) : null;

    // Calculate display rating (10-point scale to 5-point scale)
    $display_rating = $rating ? number_format(floatval($rating) / 2, 1) : null;

    // Set placeholder based on post type
    $device_type_label = 'ARTICLE';
    if ($post_type === 'mobile_device') {
        $device_type_label = 'DEVICE';
    } elseif ($post_type === 'laptop_device') { 
        $device_type_label = 'LAPTOP';
    } else {
        $categories = get_the_category();
        $device_type_label = !empty($categories) ? esc_html(strtoupper($categories[0]->name)) : 'ARTICLE';
    }

    // Calculate reading time or primary spec
    $meta_right_content = '';
    if (in_array($post_type, ['mobile_device', 'laptop_device'])) { 
        // Display Price/Rating
        if ($price) {
            $meta_right_content .= '💵 ' . esc_html($price);
        }
        if ($display_rating) {
            if ($meta_right_content) $meta_right_content .= ' &bull; ';
            $meta_right_content .= '⭐ ' . $display_rating . '/5';
        }
    } else {
        // Default blog post reading time
        $word_count = str_word_count(strip_tags(get_the_content()));
        $reading_time = ceil($word_count / 200);
        $meta_right_content = '⏱️ ' . $reading_time . ' min';
    }
    ?>
    <article class="category-post-card" data-type="<?php echo esc_attr($post_type); ?>">
        <div class="category-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <div class="thumbnail-aspect-ratio-box">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php
                        the_post_thumbnail('grid-landscape', array(
                            'loading' => 'lazy',
                            'alt' => get_the_title(),
                            'class' => 'category-post-thumbnail'
                        ));
                        ?>
                    <?php else : ?>
                        <div class="placeholder-content category-thumbnail-placeholder">
                            <div class="placeholder-title-text">
                                <?php echo esc_html(wp_trim_words($title, 20, '...')); // Limit words to prevent overflow 
                                ?>
                            </div>
                            <span class="category-placeholder-cat">
                                <?php echo $device_type_label; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <div class="category-post-content">
            <h3 class="category-post-title">
                <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
            </h3>

            <div class="item-meta-yt">
                <p class="post-date">
                    <?php echo get_the_date('M j, Y'); ?>
                    <?php if ($meta_right_content) : ?>
                        &bull; <?php echo $meta_right_content; ?>
                    <?php endif; ?>
                </p>
            </div>

            <p class="category-post-excerpt">
                <?php echo wp_trim_words(get_the_excerpt(), 18); // Shorter excerpt for grid layout 
                ?>
            </p>

            <a href="<?php the_permalink(); ?>" class="category-read-more">
                Read More →
            </a>
        </div>
    </article>
    <?php
}


/**
 * AJAX handler for infinite scroll - NOW MERGED TO HANDLE ALL POST TYPES
 */
function ezoix_infinite_scroll_posts()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ezoix_nonce')) {
        wp_die('Security check failed');
    }

    $page = intval($_POST['page']);
    $posts_per_page = 10;
    $offset = 15 + (($page - 1) * $posts_per_page); // Offset: 15 for initial load + posts from previous AJAX pages

    $featured_posts = ezoix_cache_featured_posts(2);
    $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

    $query = new WP_Query(array(
        'post_type' => array('post', 'mobile_device', 'laptop_device'), 
        'posts_per_page' => $posts_per_page,
        'offset' => $offset, // Use offset for proper pagination with exclusion
        'post__not_in' => $exclude_ids,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
    ));

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            ezoix_render_grid_card(); // Call the unified card function
        endwhile;
        wp_reset_postdata();
    else :
        echo 'no_more_posts';
    endif;

    wp_die();
}
add_action('wp_ajax_infinite_scroll_posts', 'ezoix_infinite_scroll_posts');
add_action('wp_ajax_nopriv_infinite_scroll_posts', 'ezoix_infinite_scroll_posts');
/**
 * Get similar mobile devices based on brand and category.
 *
 * @param int $post_id The current mobile device post ID.
 * @param int $number The number of posts to return.
 * @return WP_Query
 */
function ezoix_get_similar_mobile_devices($post_id, $number = 3)
{
    if (!$post_id || get_post_type($post_id) !== 'mobile_device') {
        return new WP_Query(array('paged' => -1));
    }

    $tax_query = array('relation' => 'OR');
    $current_brands = wp_get_post_terms($post_id, 'mobile_brand', array('fields' => 'slugs'));
    $current_categories = wp_get_post_terms($post_id, 'mobile_category', array('fields' => 'slugs'));

    // 1. Prioritize devices with the same brand(s)
    if (!empty($current_brands)) {
        $tax_query[] = array(
            'taxonomy' => 'mobile_brand',
            'field'    => 'slug',
            'terms'    => $current_brands,
        );
    }

    // 2. Also look for devices with matching categories
    if (!empty($current_categories)) {
        $tax_query[] = array(
            'taxonomy' => 'mobile_category',
            'field'    => 'slug',
            'terms'    => $current_categories,
        );
    }

    // If no matching terms exist, stop the query
    if (count($tax_query) === 1 && $tax_query['relation'] === 'OR') {
        return new WP_Query(array('paged' => -1));
    }


    $args = array(
        'post_type'      => 'mobile_device',
        'post_status'    => 'publish',
        'posts_per_page' => $number,
        'post__not_in'   => array($post_id),
        'orderby'        => 'rand',
        'tax_query'      => $tax_query,
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    return new WP_Query($args);
}
/**
 * Get similar laptop devices based on brand and category.
 */
function ezoix_get_similar_laptop_devices($post_id, $number = 3) // MODIFIED: ADDED LAPTOP FUNCTION
{
    if (!$post_id || get_post_type($post_id) !== 'laptop_device') {
        return new WP_Query(array('paged' => -1));
    }

    $tax_query = array('relation' => 'OR');
    $current_brands = wp_get_post_terms($post_id, 'laptop_brand', array('fields' => 'slugs'));
    $current_categories = wp_get_post_terms($post_id, 'laptop_category', array('fields' => 'slugs'));

    if (!empty($current_brands)) {
        $tax_query[] = array(
            'taxonomy' => 'laptop_brand',
            'field'    => 'slug',
            'terms'    => $current_brands,
        );
    }

    if (!empty($current_categories)) {
        $tax_query[] = array(
            'taxonomy' => 'laptop_category',
            'field'    => 'slug',
            'terms'    => $current_categories,
        );
    }

    if (count($tax_query) === 1 && $tax_query['relation'] === 'OR') {
        return new WP_Query(array('paged' => -1));
    }


    $args = array(
        'post_type'      => 'laptop_device',
        'post_status'    => 'publish',
        'posts_per_page' => $number,
        'post__not_in'   => array($post_id),
        'orderby'        => 'rand',
        'tax_query'      => $tax_query,
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    return new WP_Query($args);
}
/**
 * Add theme support for responsive embeds
 */
function ezoix_theme_support()
{
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'ezoix_theme_support');

/**
 * Add support for block styles
 */
function ezoix_block_styles()
{
    wp_enqueue_style('ezoix-block-styles', get_template_directory_uri() . '/block-styles.css', array(), wp_get_theme()->get('Version'));
}
add_action('enqueue_block_assets', 'ezoix_block_styles');

/**
 * Limit the number of revisions
 */
function ezoix_limit_revisions($num, $post) {}
add_filter('wp_revisions_to_keep', 'ezoix_limit_revisions', 10, 2);

/**
 * Disable self-pingbacks
 */
function ezoix_no_self_ping(&$links)
{
    $home = get_option('home');
    foreach ($links as $l => $link) {
        if (0 === strpos($link, $home)) {
            unset($links[$l]);
        }
    }
}
add_action('pre_ping', 'ezoix_no_self_ping');

/**
 * Add nofollow to external links in content
 */
function ezoix_nofollow_external_links($content)
{
    if (!is_single()) {
        return $content;
    }

    $pattern = '/<a (.*?)href="(.*?)"(.*?)>/i';
    $content = preg_replace_callback($pattern, function ($matches) {
        $link = $matches[0];
        $url = $matches[2];

        $site_url = site_url();
        if (strpos($url, $site_url) === false && strpos($url, 'http') === 0) {
            if (strpos($link, 'rel=') === false) {
                $link = str_replace('<a ', '<a rel="nofollow noopener external" ', $link);
            } else {
                $link = preg_replace('/rel="(.*?)"/i', 'rel="$1 nofollow noopener external"', $link);
            }
        }

        return $link;
    }, $content);

    return $content;
}
add_filter('the_content', 'ezoix_nofollow_external_links');

/**
 * Add schema markup for better SEO
 */
function ezoix_schema_markup()
{
    if (is_single()) {
        global $post;
    ?>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Article",
                "headline": "<?php echo esc_js(get_the_title()); ?>",
                "description": "<?php echo esc_js(wp_strip_all_tags(get_the_excerpt())); ?>",
                "image": "<?php echo esc_url(get_the_post_thumbnail_url($post->ID, 'full')); ?>",
                "author": {
                    "@type": "Person",
                    "name": "<?php the_author(); ?>"
                },
                "publisher": {
                    "@type": "Organization",
                    "name": "<?php bloginfo('name'); ?>",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "<?php echo esc_url(get_site_icon_url()); ?>"
                    }
                },
                "datePublished": "<?php echo get_the_date('c'); ?>",
                "dateModified": "<?php echo get_the_modified_date('c'); ?>",
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": "<?php the_permalink(); ?>"
                }
            }
        </script>
    <?php
    }
}
add_action('wp_head', 'ezoix_schema_markup');

/**
 * **NEW**: Add SEO Meta Description for Taxonomy Archives (Categories, Mobile Categories)
 * Uses the term description if available, and ensures it's stripped of HTML.
 */
function ezoix_add_taxonomy_meta_description()
{
    if (is_archive() && !is_post_type_archive()) {
        $term = get_queried_object();

        if ($term && !is_wp_error($term) && isset($term->description)) {
            $description = strip_tags(term_description($term->term_id, $term->taxonomy, false));
            $description = esc_attr(wp_trim_words($description, 30)); // Trim to ~150-160 characters

            if (!empty($description)) {
                // Ensure no SEO plugin has already added a meta description
                if (!did_action('wpseo_head') && !did_action('rank_math/head')) {
                    echo '<meta name="description" content="' . $description . '">' . "\n";
                }
            }
        }
    }
}
add_action('wp_head', 'ezoix_add_taxonomy_meta_description');

/**
 * Optimize database tables
 */
function ezoix_optimize_database()
{
    global $wpdb;

    $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%'");
    foreach ($tables as $table) {
        foreach ($table as $table_name) {
            $wpdb->query("OPTIMIZE TABLE $table_name");
        }
    }
}
if (!wp_next_scheduled('ezoix_weekly_optimization')) {
    wp_schedule_event(time(), 'weekly', 'ezoix_weekly_optimization');
}
add_action('ezoix_weekly_optimization', 'ezoix_optimize_database');

/**
 * ============================================================================
 * MOBILE SPECS ACF INTEGRATION & CUSTOM POST TYPE
 * ============================================================================
 */
function ezoix_register_mobile_device_cpt()
{
    $labels = array(
        'name'                  => _x('Mobile Devices', 'Post Type General Name', 'ezoix'),
        'singular_name'         => _x('Mobile Device', 'Post Type Singular Name', 'ezoix'),
        'menu_name'             => __('Mobile Devices', 'ezoix'),
        'name_admin_bar'        => __('Mobile Device', 'ezoix'),
        'archives'              => __('Device Archives', 'ezoix'),
        'attributes'            => __('Device Attributes', 'ezoix'),
        'parent_item_colon'     => __('Parent Device:', 'ezoix'),
        'all_items'             => __('All Mobile Devices', 'ezoix'),
        'add_new_item'          => __('Add New Device', 'ezoix'),
        'add_new'               => __('Add New', 'ezoix'),
        'new_item'              => __('New Device', 'ezoix'),
        'edit_item'             => __('Edit Device', 'ezoix'),
        'update_item'           => __('Update Device', 'ezoix'),
        'view_item'             => __('View Device', 'ezoix'),
        'view_items'            => __('View Devices', 'ezoix'),
        'search_items'          => __('Search Devices', 'ezoix'),
        'not_found'             => __('Not found', 'ezoix'),
        'not_found_in_trash'    => __('Not found in Trash', 'ezoix'),
        'featured_image'        => __('Device Image', 'ezoix'),
        'set_featured_image'    => __('Set device image', 'ezoix'),
        'remove_featured_image' => __('Remove device image', 'ezoix'),
        'use_featured_image'    => __('Use as device image', 'ezoix'),
        'insert_into_item'      => __('Insert into device', 'ezoix'),
        'uploaded_to_this_item' => __('Uploaded to this device', 'ezoix'),
        'items_list'            => __('Devices list', 'ezoix'),
        'items_list_navigation' => __('Devices list navigation', 'ezoix'),
        'filter_items_list'     => __('Filter devices list', 'ezoix'),
    );

    $args = array(
        'label'                 => __('Mobile Device', 'ezoix'),
        'description'           => __('Mobile device specifications', 'ezoix'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'post-tags'),
        'taxonomies'            => array('mobile_category', 'mobile_brand'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 25,
        'menu_icon'             => 'dashicons-smartphone',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array(
            'slug' => 'mobile-devices',
            'with_front' => false
        ),
    );


    register_post_type('mobile_device', $args);
}
// Removed redundant ezoix_mobile_device_template_redirect() function.

function ezoix_include_cpt_in_category_archive($query)
{
    // Only target the main query on the front-end
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Check if it's a standard WordPress category archive page
    if ($query->is_category()) {

        // Get the current post types being queried (default is 'post')
        $post_types = $query->get('post_type');

        // Ensure $post_types is an array and include 'post' by default if empty
        if (empty($post_types)) {
            $post_types = array('post');
        } elseif (!is_array($post_types)) {
            $post_types = array($post_types);
        }

        // Add the 'mobile_device' CPT to the list
        if (!in_array('mobile_device', $post_types)) {
            $post_types[] = 'mobile_device';
            $post_types[] = 'laptop_device'; 
            $query->set('post_type', $post_types);
        }
    }
}
add_action('pre_get_posts', 'ezoix_include_cpt_in_category_archive');
/**
 * Flush rewrite rules on theme activation/update
 */
function ezoix_force_rewrite_flush()
{
    if (get_option('ezoix_needs_rewrite_flush')) {
        flush_rewrite_rules(true);
        delete_option('ezoix_needs_rewrite_flush');
    }
}
add_action('init', 'ezoix_force_rewrite_flush', 999);

update_option('ezoix_needs_rewrite_flush', true);
/**
 * Register Mobile Categories Taxonomy
 */
function ezoix_register_mobile_taxonomies()
{
    $category_labels = array(
        'name'              => _x('Mobile Categories', 'taxonomy general name', 'ezoix'),
        'singular_name'     => _x('Mobile Category', 'taxonomy singular name', 'ezoix'),
        'search_items'      => __('Search Categories', 'ezoix'),
        'all_items'         => __('All Categories', 'ezoix'),
        'parent_item'       => __('Parent Category', 'ezoix'),
        'parent_item_colon' => __('Parent Category:', 'ezoix'),
        'edit_item'         => __('Edit Category', 'ezoix'),
        'update_item'       => __('Update Category', 'ezoix'),
        'add_new_item'      => __('Add New Category', 'ezoix'),
        'new_item_name'     => __('New Category Name', 'ezoix'),
        'menu_name'         => __('Categories', 'ezoix'),
    );

    $category_args = array(
        'hierarchical'      => true,
        'labels'            => $category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'mobile-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('mobile_category', array('mobile_device'), $category_args);

    $brand_labels = array(
        'name'              => _x('Mobile Brands', 'taxonomy general name', 'ezoix'),
        'singular_name'     => _x('Mobile Brand', 'taxonomy singular name', 'ezoix'),
        'search_items'      => __('Search Brands', 'ezoix'),
        'all_items'         => __('All Brands', 'ezoix'),
        'parent_item'       => __('Parent Brand', 'ezoix'),
        'parent_item_colon' => __('Parent Brand:', 'ezoix'),
        'edit_item'         => __('Edit Brand', 'ezoix'),
        'update_item'       => __('Update Brand', 'ezoix'),
        'add_new_item'      => __('Add New Brand', 'ezoix'),
        'new_item_name'     => __('New Brand Name', 'ezoix'),
        'menu_name'         => __('Brands', 'ezoix'),
    );

    $brand_args = array(
        'hierarchical'      => true,
        'labels'            => $brand_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'mobile-brand'),
        'show_in_rest'      => true,
    );

    register_taxonomy('mobile_brand', array('mobile_device'), $brand_args);
}

/**
 * ============================================================================
 * LAPTOP SPECS ACF INTEGRATION & CUSTOM POST TYPE
 * ============================================================================
 */
function ezoix_register_laptop_device_cpt() 
{
    $labels = array(
        'name'                  => _x('Laptop Devices', 'Post Type General Name', 'ezoix'),
        'singular_name'         => _x('Laptop Device', 'Post Type Singular Name', 'ezoix'),
        'menu_name'             => __('Laptop Devices', 'ezoix'),
        'name_admin_bar'        => __('Laptop Device', 'ezoix'),
        'archives'              => __('Laptop Archives', 'ezoix'),
        'attributes'            => __('Laptop Attributes', 'ezoix'),
        'parent_item_colon'     => __('Parent Laptop:', 'ezoix'),
        'all_items'             => __('All Laptop Devices', 'ezoix'),
        'add_new_item'          => __('Add New Laptop', 'ezoix'),
        'add_new'               => __('Add New', 'ezoix'),
        'new_item'              => __('New Laptop', 'ezoix'),
        'edit_item'             => __('Edit Laptop', 'ezoix'),
        'update_item'           => __('Update Laptop', 'ezoix'),
        'view_item'             => __('View Laptop', 'ezoix'),
        'view_items'            => __('View Laptops', 'ezoix'),
        'search_items'          => __('Search Laptops', 'ezoix'),
        'not_found'             => __('Not found', 'ezoix'),
        'not_found_in_trash'    => __('Not found in Trash', 'ezoix'),
        'featured_image'        => __('Laptop Image', 'ezoix'),
        'set_featured_image'    => __('Set laptop image', 'ezoix'),
        'remove_featured_image' => __('Remove laptop image', 'ezoix'),
        'use_featured_image'    => __('Use as laptop image', 'ezoix'),
        'insert_into_item'      => __('Insert into laptop', 'ezoix'),
        'uploaded_to_this_item' => __('Uploaded to this laptop', 'ezoix'),
        'items_list'            => __('Laptops list', 'ezoix'),
        'items_list_navigation' => __('Laptops list navigation', 'ezoix'),
        'filter_items_list'     => __('Filter laptops list', 'ezoix'),
    );

    $args = array(
        'label'                 => __('Laptop Device', 'ezoix'),
        'description'           => __('Laptop device specifications', 'ezoix'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'post-tags'),
        'taxonomies'            => array('laptop_category', 'laptop_brand'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 26,
        'menu_icon'             => 'dashicons-laptop',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array(
            'slug' => 'laptop', // The requested URL slug
            'with_front' => false
        ),
    );


    register_post_type('laptop_device', $args);
}

/**
 * Register Laptop Taxonomies
 */
function ezoix_register_laptop_taxonomies() 
{
    $category_labels = array(
        'name'              => _x('Laptop Categories', 'taxonomy general name', 'ezoix'),
        'singular_name'     => _x('Laptop Category', 'taxonomy singular name', 'ezoix'),
        'search_items'      => __('Search Categories', 'ezoix'),
        'all_items'         => __('All Categories', 'ezoix'),
        'menu_name'         => __('Categories', 'ezoix'),
    );

    $category_args = array(
        'hierarchical'      => true,
        'labels'            => $category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'laptop-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('laptop_category', array('laptop_device'), $category_args);

    $brand_labels = array(
        'name'              => _x('Laptop Brands', 'taxonomy general name', 'ezoix'),
        'singular_name'     => _x('Laptop Brand', 'taxonomy singular name', 'ezoix'),
        'search_items'      => __('Search Brands', 'ezoix'),
        'all_items'         => __('All Brands', 'ezoix'),
        'menu_name'         => __('Brands', 'ezoix'),
    );

    $brand_args = array(
        'hierarchical'      => true,
        'labels'            => $brand_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'laptop-brand'),
        'show_in_rest'      => true,
    );

    register_taxonomy('laptop_brand', array('laptop_device'), $brand_args);
}
// End of CPT & Taxonomy additions

// REMOVED: Redundant function ezoix_mobile_device_template_redirect() hook
// REMOVED: Redundant function ezoix_mobile_device_template_redirect() hook
// The template redirect logic is now handled in ezoix_mobile_archive_templates.

function ezoix_process_json_specs($json_content)
{
    $specs_data = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_parse_error', 'Invalid JSON format: ' . json_last_error_msg());
    }

    $structured_data = array(
        'device_name' => isset($specs_data['device_name']) ? sanitize_text_field($specs_data['device_name']) : '',
        'specifications' => array(),
        'affiliate_links' => array()
    );

    if (isset($specs_data['specifications']) && is_array($specs_data['specifications'])) {
        foreach ($specs_data['specifications'] as $category => $items) {
            if (is_array($items)) {
                $category_data = array();
                foreach ($items as $key => $value) {
                    if (is_array($value)) {
                        $category_data[$key] = is_array($value) ? implode(', ', array_map('sanitize_text_field', $value)) : sanitize_text_field($value);
                    } else {
                        $category_data[$key] = sanitize_text_field($value);
                    }
                }
                $structured_data['specifications'][$category] = $category_data;
            }
        }
    }

    if (isset($specs_data['affiliate_links']) && is_array($specs_data['affiliate_links'])) {
        foreach ($specs_data['affiliate_links'] as $platform => $url) {
            $structured_data['affiliate_links'][] = array(
                'platform' => sanitize_text_field($platform),
                'url' => esc_url_raw($url)
            );
        }
    }

    return $structured_data;
}

/**
 * Save JSON data as ACF fields and create Mobile Device post (Original code)
 */
function ezoix_save_json_as_acf($json_content, $create_post = true)
{
    $specs_data = ezoix_process_json_specs($json_content);

    if (is_wp_error($specs_data)) {
        return $specs_data;
    }

    $device_name = $specs_data['device_name'];

    if ($create_post) {
        // Simplified logic: assume all posts created here are 'mobile_device' or 'laptop_device' for simplicity of the original function's intent
        $post_type = isset($_POST['post_type']) && in_array($_POST['post_type'], ['mobile_device', 'laptop_device']) ? $_POST['post_type'] : 'mobile_device';

        $existing_post = get_page_by_title($device_name, OBJECT, $post_type);

        if ($existing_post) {
            $post_id = $existing_post->ID;
            $post_data = array(
                'ID' => $post_id,
                'post_title' => $device_name,
                'post_type' => $post_type,
                'post_status' => 'publish'
            );
            wp_update_post($post_data);
        } else {
            $post_data = array(
                'post_title' => $device_name,
                'post_type' => $post_type,
                'post_status' => 'publish',
                'post_content' => ''
            );
            $post_id = wp_insert_post($post_data);
        }

        if ($post_id && function_exists('update_field')) {
            update_field('device_name', $device_name, $post_id);

            if (!empty($specs_data['specifications'])) {
                $specs_repeater = array();
                foreach ($specs_data['specifications'] as $category => $items) {
                    $spec_items = array();
                    foreach ($items as $key => $value) {
                        $spec_items[] = array(
                            'spec_key' => $key,
                            'spec_value' => $value
                        );
                    }
                    $specs_repeater[] = array(
                        'category_name' => ucwords(str_replace('_', ' ', $category)),
                        'specifications' => $spec_items
                    );
                }
                update_field('specifications', $specs_repeater, $post_id);
            }

            if (!empty($specs_data['affiliate_links'])) {
                update_field('affiliate_links', $specs_data['affiliate_links'], $post_id);
            }

            $brand = ezoix_extract_brand_from_name($device_name);
            if ($brand) {
                $taxonomy = ($post_type === 'mobile_device') ? 'mobile_brand' : 'laptop_brand';
                wp_set_object_terms($post_id, $brand, $taxonomy, true);
            }

            return array(
                'success' => true,
                'post_id' => $post_id,
                'device_name' => $device_name,
                'edit_link' => admin_url('post.php?post=' . $post_id . '&action=edit'),
                'view_link' => get_permalink($post_id)
            );
        }
    }

    return $specs_data;
}

/**
 * Display mobile specifications from ACF fields (Original code)
 */
function ezoix_display_mobile_specs_acf($post_id = null)
{
    if (!$post_id) {
        global $post;
        if (!$post) return '';
        $post_id = $post->ID;
    }

    if (!function_exists('get_field')) {
        return '<p>ACF plugin is required to display specifications.</p>';
    }

    $device_name = get_field('device_name', $post_id);
    $specifications = get_field('specifications', $post_id);
    $affiliate_links = get_field('affiliate_links', $post_id);

    if (!$device_name && !$specifications) {
        return '<p>No specifications available for this device.</p>';
    }

    ob_start();
    ?>

    <div class="mobile-specs-container">

        <div class="device-header">
            <h1 class="device-name"><?php echo esc_html($device_name ? $device_name : get_the_title($post_id)); ?></h1>
        </div>

        <?php if ($specifications) : ?>
            <div class="specs-table-wrapper">
                <table class="mobile-specs-table">
                    <thead>
                        <tr>
                            <th colspan="2" class="specs-section-header">Specifications</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specifications as $category) :
                            if (!empty($category['category']) && !empty($category['items'])) :
                        ?>
                                <tr class="category-header">
                                    <td colspan="2">
                                        <h3 class="category-title"><?php echo esc_html($category['category']); ?></h3>
                                    </td>
                                </tr>

                                <?php foreach ($category['items'] as $spec) :
                                    if (!empty($spec['key'])) :
                                ?>
                                        <tr class="spec-row">
                                            <td class="spec-label">
                                                <?php echo esc_html($spec['key']); ?>
                                            </td>
                                            <td class="spec-value">
                                                <?php echo esc_html($spec['value']); ?>
                                            </td>
                                        </tr>
                                <?php
                                    endif;
                                endforeach; ?>

                                <tr class="separator">
                                    <td colspan="2"></td>
                                </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($affiliate_links) : ?>
            <div class="affiliate-links-section">
                <h3 class="section-title">Buy Now</h3>
                <div class="affiliate-buttons">
                    <?php foreach ($affiliate_links as $link) :
                        if (!empty($link['platform']) && !empty($link['url'])) :
                    ?>
                            <a href="<?php echo esc_url($link['url']); ?>"
                                class="affiliate-button"
                                target="_blank"
                                rel="noopener nofollow">
                                Buy on <?php echo esc_html(ucwords($link['platform'])); ?>
                            </a>
                    <?php
                        endif;
                    endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}

/**
 * Template for single mobile device (Original code)
 */
function ezoix_mobile_device_template($template)
{
    if (is_singular('mobile_device')) {
        $new_template = locate_template(array('single-mobile-device.php'));
        if (!empty($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'ezoix_mobile_device_template', 99);


/**
 * Shortcode to display mobile specs by ID (Original code)
 */
function ezoix_mobile_specs_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => 0,
        'device' => '',
    ), $atts, 'mobile_specs');

    $post_id = $atts['id'];

    if (!$post_id && $atts['device']) {
        $device_post = get_page_by_title($atts['device'], OBJECT, 'mobile_device');
        if ($device_post) {
            $post_id = $device_post->ID;
        }
    }

    if (!$post_id) {
        global $post;
        if ($post) {
            $post_id = $post->ID;
        }
    }

    // MODIFIED to check for both device types
    if (get_post_type($post_id) !== 'mobile_device' && get_post_type($post_id) !== 'laptop_device') {
        return '<p>This shortcode only works with Mobile or Laptop Device posts.</p>';
    }

    return ezoix_display_mobile_specs_acf($post_id);
}
add_shortcode('mobile_specs', 'ezoix_mobile_specs_shortcode');

/**
 * Flush rewrite rules on theme activation (Original code)
 */
function ezoix_flush_rewrite_rules_on_activation()
{
    ezoix_register_mobile_device_cpt();
    ezoix_register_mobile_taxonomies();
    ezoix_register_laptop_device_cpt(); 
    ezoix_register_laptop_taxonomies(); 
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ezoix_flush_rewrite_rules_on_activation');

/**
 * Initialize mobile specs system (Original code)
 */
function ezoix_init_mobile_specs()
{
    ezoix_register_mobile_device_cpt();
    ezoix_register_mobile_taxonomies();
    ezoix_register_laptop_device_cpt(); 
    ezoix_register_laptop_taxonomies(); 
}
add_action('init', 'ezoix_init_mobile_specs', 0);

/**
 * Fix for mobile device permalinks
 * MODIFIED: Reverted custom permalink logic to default to CPT structure.
 */
function ezoix_fix_mobile_device_permalink($post_link, $post)
{
    if ($post->post_type === 'mobile_device' && $post->post_status === 'publish') {
        return home_url("/device/{$post->post_name}/");
    }
    return $post_link;
}

/**
 * Check if a slug conflicts with existing pages/posts (Original code)
 */
function ezoix_slug_conflicts_with_other_post_types($slug)
{
    global $wpdb;

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type NOT IN ('mobile_device', 'laptop_device', 'revision', 'nav_menu_item') LIMIT 1", 
        $slug
    ));

    return $existing ? true : false;
}

/**
 * Add rewrite rules for mobile and laptop devices
 * FIX: Removed conflicting root-level rules. CPT slugs are now handled automatically by WP with the 'device' prefix.
 */
function ezoix_add_mobile_device_rewrite_rules()
{

    add_rewrite_rule(
        '^mobile-category/([^/]+)/?$',
        'index.php?mobile_category=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^mobile-brand/([^/]+)/?$',
        'index.php?mobile_brand=$matches[1]',
        'top'
    );

    // MODIFIED: ADDED LAPTOP REWRITE RULES
    add_rewrite_rule(
        '^laptop-category/([^/]+)/?$',
        'index.php?laptop_category=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^laptop-brand/([^/]+)/?$',
        'index.php?laptop_brand=$matches[1]',
        'top'
    );
}
add_action('init', 'ezoix_add_mobile_device_rewrite_rules', 20);

/**
 * Force flush rewrite rules on theme activation (Original code)
 */
function ezoix_force_flush_rewrite_rules()
{
    update_option('ezoix_flush_rewrite_rules', true);
}
register_activation_hook(__FILE__, 'ezoix_force_flush_rewrite_rules');

/**
 * Create a test mobile device for debugging (Original code)
 */
function ezoix_create_test_mobile_device()
{
    if (get_option('ezoix_test_device_created')) {
        return;
    }

    $test_device = array(
        'post_title' => 'Samsung Galaxy S25 Ultra Test',
        'post_name' => 'samsung-galaxy-s25-ultra-test',
        'post_type' => 'mobile_device',
        'post_status' => 'publish',
        'post_content' => 'Test device created for debugging.'
    );

    $post_id = wp_insert_post($test_device);
    
    // MODIFIED: ADDED LAPTOP TEST DEVICE
    $test_laptop = array(
        'post_title' => 'MacBook Pro 16 M4 Test',
        'post_name' => 'macbook-pro-16-m4-test',
        'post_type' => 'laptop_device',
        'post_status' => 'publish',
        'post_content' => 'Test laptop created for debugging.'
    );
    $laptop_id = wp_insert_post($test_laptop);

    if (($post_id && !is_wp_error($post_id)) || ($laptop_id && !is_wp_error($laptop_id))) { 
        update_option('ezoix_test_device_created', true);

        if (function_exists('update_field')) {
            if ($post_id && !is_wp_error($post_id)) {
                update_field('device_name', 'Samsung Galaxy S25 Ultra Test', $post_id);

                $specs = array(
                    array(
                        'category' => 'Display',
                        'items' => array(
                            array('key' => 'Type', 'value' => 'Dynamic AMOLED 2X'),
                            array('key' => 'Size', 'value' => '6.9 inches')
                        )
                    )
                );
                update_field('specifications', $specs, $post_id);
            }
            // MODIFIED: ADDED LAPTOP FIELD POPULATION
            if ($laptop_id && !is_wp_error($laptop_id)) {
                update_field('device_name', 'MacBook Pro 16 M4 Test', $laptop_id);
                $specs = array(
                    array(
                        'category' => 'Display',
                        'items' => array(
                            array('key' => 'Screen', 'value' => '16.2 inch Liquid Retina XDR'),
                            array('key' => 'Resolution', 'value' => '3456x2234')
                        )
                    )
                );
                update_field('specifications', $specs, $laptop_id);
            }
        }
    }
}
add_action('init', 'ezoix_create_test_mobile_device');

/**
 * Admin notice to flush rewrite rules (Original code)
 */
function ezoix_admin_notice_flush_rules()
{
    if (get_option('ezoix_flush_rewrite_rules')) {
    ?>
        <div class="notice notice-warning">
            <p><strong>Device System:</strong> Please visit <a href="<?php echo admin_url('options-permalink.php'); ?>">Permalinks Settings</a> and click "Save Changes" to flush rewrite rules.</p>
        </div>
    <?php
    }
}
add_action('admin_notices', 'ezoix_admin_notice_flush_rules');

function custom_404_redirect()
{
    if (is_404()) {
        global $wp;
        $current_url = home_url($wp->request);

        if (strpos($current_url, '/mobile-devices/') !== false) {
            wp_redirect(home_url('/mobile-devices/'));
            exit;
        }
        if (strpos($current_url, '/laptop/') !== false) { 
            wp_redirect(home_url('/laptop/'));
            exit;
        }
    }
}
add_action('template_redirect', 'custom_404_redirect');

add_action('after_setup_theme', 'ezoix_load_acf_fields');

function ezoix_load_acf_fields()
{
    if (file_exists(get_template_directory() . '/acf-mobile-fields.php')) {
        require_once get_template_directory() . '/acf-mobile-fields.php';
    }
}

/**
 * Improved JSON Processor with GSM Arena-like structure 
 * MODIFIED: Now extracts nested content fields, including image URLs.
 */
function ezoix_process_mobile_json($json_content)
{
    $specs_data = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_parse_error', 'Invalid JSON format: ' . json_last_error_msg());
    }

    $structured_data = array(
        'device_name' => isset($specs_data['device_name']) ? sanitize_text_field($specs_data['device_name']) : '',
        'device_model' => isset($specs_data['model']) ? sanitize_text_field($specs_data['model']) : '',
        'release_date' => isset($specs_data['release_date']) ? sanitize_text_field($specs_data['release_date']) : '',
        'device_price' => isset($specs_data['price']) ? sanitize_text_field($specs_data['price']) : '',
        'device_status' => isset($specs_data['status']) ? sanitize_text_field($specs_data['status']) : 'available',
        'device_rating' => isset($specs_data['rating']) ? floatval($specs_data['rating']) : 5,
        'specifications' => array(),
        'affiliate_links' => array(),
        'pros' => array(),
        'cons' => array(),
        
        'tags' => isset($specs_data['seo']['tags']) && is_array($specs_data['seo']['tags']) ? array_map('sanitize_text_field', $specs_data['seo']['tags']) : (isset($specs_data['tags']) ? (is_array($specs_data['tags']) ? array_map('sanitize_text_field', $specs_data['tags']) : sanitize_text_field($specs_data['tags'])) : array()),
        'meta_description' => isset($specs_data['seo']['meta_description']) ? sanitize_text_field($specs_data['seo']['meta_description']) : (isset($specs_data['meta_description']) ? sanitize_text_field($specs_data['meta_description']) : ''),
        'type' => isset($specs_data['type']) ? sanitize_text_field($specs_data['type']) : 'mobile_device', // MODIFIED
        
        // --- TEXT FIELDS EXTRACTION ---
        'review_introduction' => isset($specs_data['content']['introduction']['text']) ? sanitize_textarea_field($specs_data['content']['introduction']['text']) : '',
        'review_display' => isset($specs_data['content']['display_experience']['text']) ? sanitize_textarea_field($specs_data['content']['display_experience']['text']) : '',
        'review_performance' => isset($specs_data['content']['performance_and_gaming']['text']) ? sanitize_textarea_field($specs_data['content']['performance_and_gaming']['text']) : '',
        'review_camera' => isset($specs_data['content']['camera_performance']['text']) ? sanitize_textarea_field($specs_data['content']['camera_performance']['text']) : '',
        'review_battery' => isset($specs_data['content']['battery_and_charging']['text']) ? sanitize_textarea_field($specs_data['content']['battery_and_charging']['text']) : '',
        'review_verdict' => isset($specs_data['content']['final_verdict']['text']) ? sanitize_textarea_field($specs_data['content']['final_verdict']['text']) : '',
        
        // --- NEW IMAGE URL FIELDS EXTRACTION ---
        // Assuming the image URL is stored under an 'image_url' key in the nested content objects.
        'review_introduction_image' => isset($specs_data['content']['introduction']['image_url']) ? esc_url_raw($specs_data['content']['introduction']['image_url']) : '',
        'review_display_image' => isset($specs_data['content']['display_experience']['image_url']) ? esc_url_raw($specs_data['content']['display_experience']['image_url']) : '',
        'review_performance_image' => isset($specs_data['content']['performance_and_gaming']['image_url']) ? esc_url_raw($specs_data['content']['performance_and_gaming']['image_url']) : '',
        'review_camera_image' => isset($specs_data['content']['camera_performance']['image_url']) ? esc_url_raw($specs_data['content']['camera_performance']['image_url']) : '',
        'review_battery_image' => isset($specs_data['content']['battery_and_charging']['image_url']) ? esc_url_raw($specs_data['content']['battery_and_charging']['image_url']) : '',
        'review_verdict_image' => isset($specs_data['content']['final_verdict']['image_url']) ? esc_url_raw($specs_data['content']['final_verdict']['image_url']) : '',
        // --- END NEW IMAGE FIELDS ---
    );

    if (isset($specs_data['specifications']) && is_array($specs_data['specifications'])) {
        foreach ($specs_data['specifications'] as $category => $items) {
            if (is_array($items)) {
                $category_items = array();
                foreach ($items as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(', ', array_map('sanitize_text_field', $value));
                    } else {
                        $value = sanitize_text_field($value);
                    }

                    $formatted_key = ucwords(str_replace(['_', '-'], ' ', $key));

                    $category_items[] = array(
                        'key' => $formatted_key,
                        'value' => $value
                    );
                }

                if (!empty($category_items)) {
                    $structured_data['specifications'][] = array(
                        'category' => ucwords(str_replace(['_', '-'], ' ', $category)),
                        'items' => $category_items
                    );
                }
            }
        }
    }

    if (isset($specs_data['affiliate_links']) && is_array($specs_data['affiliate_links'])) {
        foreach ($specs_data['affiliate_links'] as $platform => $link_data) {
            if (is_array($link_data)) {
                $structured_data['affiliate_links'][] = array(
                    'platform' => sanitize_text_field($platform),
                    'url' => isset($link_data['url']) ? esc_url_raw($link_data['url']) : '',
                    'price' => isset($link_data['price']) ? sanitize_text_field($link_data['price']) : ''
                );
            } else {
                $structured_data['affiliate_links'][] = array(
                    'platform' => sanitize_text_field($platform),
                    'url' => esc_url_raw($link_data),
                    'price' => ''
                );
            }
        }
    }

    if (isset($specs_data['pros']) && is_array($specs_data['pros'])) {
        foreach ($specs_data['pros'] as $pro) {
            $structured_data['pros'][] = array('item' => sanitize_text_field($pro));
        }
    }

    if (isset($specs_data['cons']) && is_array($specs_data['cons'])) {
        foreach ($specs_data['cons'] as $con) {
            $structured_data['cons'][] = array('item' => sanitize_text_field($con));
        }
    }

    return $structured_data;
}
/**
 * Enhanced JSON Import with better error handling
 * MODIFIED: Sets main post_content and saves all new review fields (text and image URL).
 */
function ezoix_import_mobile_json($json_content, $create_post = true)
{
    $specs_data = ezoix_process_mobile_json($json_content);

    if (is_wp_error($specs_data)) {
        return $specs_data;
    }

    if (empty($specs_data['device_name'])) {
        return new WP_Error('missing_device_name', 'Device name is required in JSON');
    }

    if ($create_post) {
        // Determine post type. Use 'type' from JSON, or default to 'mobile_device' if not a valid CPT
        $post_type_hint = isset($specs_data['type']) && in_array($specs_data['type'], ['mobile_device', 'laptop_device']) ? $specs_data['type'] : 'mobile_device'; 

        $existing_post = get_page_by_title($specs_data['device_name'], OBJECT, $post_type_hint);

        // --- Determine main post content from the new review_introduction field ---
        $main_post_content = '';
        if (!empty($specs_data['description'])) {
            $main_post_content = $specs_data['description'];
        } elseif (!empty($specs_data['review_introduction'])) { 
            $main_post_content = $specs_data['review_introduction'];
        }
        // -----------------------------------------------------------------------

        if ($existing_post) {
            $post_id = $existing_post->ID;
            $post_data = array(
                'ID' => $post_id,
                'post_title' => $specs_data['device_name'],
                'post_type' => $post_type_hint,
                'post_status' => 'publish',
                'post_content' => $main_post_content
            );
            wp_update_post($post_data);
        } else {
            $post_data = array(
                'post_title' => $specs_data['device_name'],
                'post_type' => $post_type_hint,
                'post_status' => 'publish',
                'post_content' => $main_post_content
            );
            $post_id = wp_insert_post($post_data);
        }

        if ($post_id && !is_wp_error($post_id) && function_exists('update_field')) {
            
            // This loop handles all simple fields, including all new 'review_' fields (text and image URL)
            foreach ($specs_data as $field => $value) {
                // Skip complex/array fields and nested JSON objects
                if ($field !== 'specifications' && $field !== 'affiliate_links' && $field !== 'pros' && $field !== 'cons' && $field !== 'tags' && $field !== 'meta_description') {
                    update_field($field, $value, $post_id);
                }
            }

            if (!empty($specs_data['specifications'])) {
                update_field('specifications', $specs_data['specifications'], $post_id);
            }

            if (!empty($specs_data['affiliate_links'])) {
                update_field('affiliate_links', $specs_data['affiliate_links'], $post_id);
            }

            if (!empty($specs_data['pros']) || !empty($specs_data['cons'])) {
                update_field('pros_cons', array(
                    'pros' => $specs_data['pros'],
                    'cons' => $specs_data['cons']
                ), $post_id);
            }

            // ---------------------------
            // Moved the tags and meta description logic inside this block
            // ---------------------------

            if (!empty($specs_data['tags'])) {
                // Automatically set the tags
                wp_set_post_tags($post_id, $specs_data['tags'], false);
            }

            // --- NEW LOGIC FOR META DESCRIPTION ---
            $meta_desc_value = '';
            if (!empty($specs_data['meta_description'])) {
                // Use the description from JSON
                $meta_desc_value = $specs_data['meta_description'];
            } else {
                // Auto-generate a meta description if not provided in JSON
                $brand = ezoix_extract_brand_from_name($specs_data['device_name']);
                $price = $specs_data['device_price'];
                $model = $specs_data['device_model'];

                // Try to find a display spec for a better description
                $display_spec = '';
                if (isset($specs_data['specifications'])) {
                    foreach ($specs_data['specifications'] as $category_item) {
                        if (strtolower($category_item['category']) === 'display' && !empty($category_item['items'][0]['value'])) {
                            $display_spec = ' with a ' . $category_item['items'][0]['value'] . ' display';
                            break;
                        }
                    }
                }

                $device_type_label = ($post_type_hint === 'laptop_device') ? 'laptop' : 'flagship';

                $meta_desc_value = "Check out the full specifications and review for the " . esc_html($specs_data['device_name']) . " (" . esc_html($model) . "). Get details on the " . esc_html($brand) . " " . $device_type_label . $display_spec . ". Priced at " . esc_html($price) . ".";

                // Trim to standard meta description length (155-160 characters)
                $meta_desc_value = substr($meta_desc_value, 0, 160);
            }
            update_field('seo_meta_description', $meta_desc_value, $post_id);

            // 2. ALSO save to the post excerpt, which is often used as a fallback by SEO plugins
            if (!empty($meta_desc_value)) {
                wp_update_post(array(
                    'ID'           => $post_id,
                    'post_excerpt' => $meta_desc_value,
                ));
            }

            $brand = ezoix_extract_brand_from_name($specs_data['device_name']);
            if ($brand) {
                $taxonomy = ($post_type_hint === 'mobile_device') ? 'mobile_brand' : 'laptop_brand';
                wp_set_object_terms($post_id, $brand, $taxonomy, true);
            }

            return array(
                'success' => true,
                'post_id' => $post_id,
                'device_name' => $specs_data['device_name'],
                'edit_link' => admin_url('post.php?post=' . $post_id . '&action=edit'),
                'view_link' => get_permalink($post_id)
            );
        }
    }

    return $specs_data;
}

/**
 * Enhanced JSON Upload with better error handling
 * (Admin functionality is skipped for brevity, keeping only core logic)
 */


/**
 * Enhanced JSON Import with better error handling
 * (Admin functionality is skipped for brevity, keeping only core logic)
 */


/**
 * Enhanced Admin Page for JSON Upload (Original code)
 */
function ezoix_mobile_specs_admin_page_enhanced()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $message = '';
    $result = array();

    if (isset($_POST['submit_json'])) {
        if (!wp_verify_nonce($_POST['ezoix_specs_nonce'], 'ezoix_process_json')) {
            $message = '<div class="error"><p>Security check failed.</p></div>';
        } else {
            $json_content = '';

            if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === 0) {
                $json_content = file_get_contents($_FILES['json_file']['tmp_name']);
            } elseif (isset($_POST['json_content']) && !empty($_POST['json_content'])) {
                $json_content = stripslashes($_POST['json_content']);
            }

            if (!empty($json_content)) {
                $result = ezoix_import_mobile_json($json_content, true);

                if (is_wp_error($result)) {
                    $message = '<div class="error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
                } else {
                    $message = '<div class="updated"><p>✅ JSON imported successfully! Device: <strong>' . esc_html($result['device_name']) . '</strong></p>';
                    $message .= '<p><a href="' . esc_url($result['edit_link']) . '" class="button button-primary">Edit Device</a> ';
                    $message .= '<a href="' . esc_url($result['view_link']) . '" class="button" target="_blank">View Device</a></p></div>';
                }
            } else {
                $message = '<div class="error"><p>Please provide JSON content.</p></div>';
            }
        }
    }

    if (isset($_POST['submit_bulk']) && isset($_FILES['bulk_json_files'])) {
        if (!wp_verify_nonce($_POST['ezoix_bulk_nonce'], 'ezoix_bulk_import')) {
            $message = '<div class="error"><p>Security check failed for bulk import.</p></div>';
        } else {
            $files = $_FILES['bulk_json_files'];
            $success_count = 0;
            $error_count = 0;

            foreach ($files['tmp_name'] as $index => $tmp_name) {
                if ($files['error'][$index] === 0) {
                    $json_content = file_get_contents($tmp_name);
                    $result = ezoix_import_mobile_json($json_content, true);

                    if (is_wp_error($result)) {
                        $error_count++;
                    } else {
                        $success_count++;
                    }
                }
            }

            $message = '<div class="updated"><p>Bulk import completed!</p>';
            $message .= '<p>Successfully imported: ' . $success_count . ' devices</p>';
            if ($error_count > 0) {
                $message .= '<p>Failed: ' . $error_count . ' devices</p>';
            }
            $message .= '</div>';
        }
    }
    ?>

    <div class="wrap">
        <h1><span class="dashicons dashicons-smartphone"></span> Mobile & Laptop Specifications Importer</h1>

        <?php echo $message; ?>

        <div class="json-upload-section">
            <h2>Import Device Specifications</h2>
            <p>Upload a JSON file or paste JSON content to create a Mobile or Laptop Device post with all specifications. You can include ` "type": "laptop_device", ` in your JSON to specifically create a laptop.</p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('ezoix_process_json', 'ezoix_specs_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="json_file">Upload JSON File</label></th>
                        <td>
                            <input type="file" name="json_file" id="json_file" accept=".json"   >
                            <p class="description">Upload a JSON file containing mobile or laptop specifications</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>OR</label></th>
                        <td>
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="json_content">Paste JSON Content</label></th>
                        <td>
                            <textarea name="json_content" id="json_content" rows="20" class="large-text code" placeholder='Paste your JSON here...'><?php echo isset($_POST['json_content']) ? esc_textarea(stripslashes($_POST['json_content'])) : ''; ?></textarea>
                            <p class="description">Use the format shown in the example below</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Import JSON as Device', 'primary', 'submit_json'); ?>
            </form>
        </div>

        <div class="bulk-import-section">
            <h2>Bulk Import</h2>
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('ezoix_bulk_import', 'ezoix_bulk_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="bulk_json_files">Upload Multiple JSON Files</label></th>
                        <td>
                            <input type="file" name="bulk_json_files[]" id="bulk_json_files" accept=".json" multiple>
                            <p class="description">Select multiple JSON files to import (max 10 files at once)</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Bulk Import JSON Files', 'secondary', 'submit_bulk'); ?>
            </form>
        </div>

        <div class="json-example-section">
            <h2>JSON Format Example</h2>
            <p>Use this format for your JSON files. Add `"type": "laptop_device"` to create a laptop.</p>

            <pre><code>{
  "device_name": "Samsung Galaxy S25 Ultra",
  "model": "SM-S928",
  "release_date": "2024-01-15",
  "price": "$1299",
  "status": "available",
  "rating": 8.5,
  "description": "The latest flagship from Samsung with advanced features.",
  "type": "mobile_device", // Set to "laptop_device" for laptops
  
  "content": {
    "introduction": {
      "text": "The introductory text/overview...",
      "image_url": "https://example.com/images/s25-intro.jpg"
    },
    "display_experience": {
      "text": "Details about the display experience...",
      "image_url": "https://example.com/images/s25-display.jpg"
    }
    // ... other content sections
  },

  "specifications": {
    "display": {
      "size": "6.8 inches",
      "resolution": "1440 x 3200 pixels",
      "type": "Dynamic AMOLED 2X",
      "refresh_rate": "120Hz",
      "protection": "Corning Gorilla Glass Victus+"
    },
    "camera": {
      "main": "200 MP, f/1.7",
      "ultra_wide": "12 MP, f/2.2",
      "telephoto": "10 MP, f/2.4 (3x optical zoom)",
      "front": "40 MP, f/2.2"
    },
    "hardware": {
      "chipset": "Snapdragon 8 Gen 3",
      "cpu": "Octa-core",
      "gpu": "Adreno 750",
      "ram": "12GB",
      "storage": "256GB/512GB/1TB",
      "battery": "5000 mAh",
      "charging": "45W wired, 15W wireless"
    },
    "connectivity": {
      "network": "5G",
      "wifi": "Wi-Fi 6E",
      "bluetooth": "5.3",
      "usb": "USB Type-C 3.2"
    }
  },
  
  "affiliate_links": {
    "amazon": {
      "url": "https://amazon.com/samsung-s25-ultra",
      "price": "$1299"
    },
    "flipkart": {
      "url": "https://flipkart.com/samsung-s25-ultra",
      "price": "₹1,29,999"
    }
  },
  
  "pros": [
    "Excellent display quality",
    "Powerful performance",
    "Great camera system",
    "Long battery life"
  ],
  
  "cons": [
    "Expensive",
    "Heavy and bulky",
    "No charger in box"
  ]
}</code></pre>
        </div>

        <div class="quick-actions-section">
            <h2>Quick Actions</h2>
            <div class="quick-action-buttons">
                <a href="<?php echo admin_url('post-new.php?post_type=mobile_device'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus"></span> Add New Mobile Manually
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=laptop_device'); ?>" class="button button-primary">
                    <span class="dashicons dashicons-plus"></span> Add New Laptop Manually
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=mobile_device'); ?>" class="button">
                    <span class="dashicons dashicons-list-view"></span> View Mobile Devices
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=laptop_device'); ?>" class="button">
                    <span class="dashicons dashicons-list-view"></span> View Laptop Devices
                </a>
            </div>
        </div>

        <div class="stats-section">
            <h2>Statistics</h2>
            <?php
            $total_mobile_devices = wp_count_posts('mobile_device');
            $total_laptop_devices = wp_count_posts('laptop_device');
            $published_mobile = $total_mobile_devices->publish;
            $published_laptop = $total_laptop_devices->publish;

            $brands_mobile = get_terms(array(
                'taxonomy' => 'mobile_brand',
                'hide_empty' => true,
            ));
            $brands_laptop = get_terms(array(
                'taxonomy' => 'laptop_brand',
                'hide_empty' => true,
            ));
            ?>
            <ul>
                <li><strong>Total Mobile Devices:</strong> <?php echo $published_mobile; ?></li>
                <li><strong>Total Laptop Devices:</strong> <?php echo $published_laptop; ?></li>
                <li><strong>Mobile Brands:</strong> <?php echo count($brands_mobile); ?></li>
                <li><strong>Laptop Brands:</strong> <?php echo count($brands_laptop); ?></li>
                <li><strong>Last Import:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
            </ul>
        </div>
    </div>

    <style>
        .wrap {
            max-width: 1200px;
            margin: 20px auto;
        }

        .json-upload-section,
        .bulk-import-section,
        .json-example-section,
        .quick-actions-section,
        .stats-section {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .json-example-section pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            margin: 15px 0;
        }

        .json-example-section code {
            background: none;
            padding: 0;
        }

        .quick-action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .quick-action-buttons .button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .stats-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stats-section li {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0073aa;
        }

        @media (max-width: 768px) {
            .stats-section ul {
                grid-template-columns: 1fr;
            }

            .quick-action-buttons {
                flex-direction: column;
            }

            .quick-action-buttons .button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="json-example-section">
        <h2>JSON Format Example</h2>
        <p>Use this format for your JSON files:</p>

        <pre><code>{
  "device_name": "Samsung Galaxy S25 Ultra",
  "model": "SM-S928",
  "release_date": "2024-01-15",
  "price": "$1299",
  "status": "available",
  "rating": 8.5,
  "description": "The latest flagship from Samsung with advanced features.",
  
  "specifications": {
    "display": {
      "size": "6.8 inches",
      "resolution": "1440 x 3200 pixels",
      "type": "Dynamic AMOLED 2X",
      "refresh_rate": "120Hz",
      "protection": "Corning Gorilla Glass Victus+"
    },
    "camera": {
      "main": "200 MP, f/1.7",
      "ultra_wide": "12 MP, f/2.2",
      "telephoto": "10 MP, f/2.4 (3x optical zoom)",
      "front": "40 MP, f/2.2"
    },
    "hardware": {
      "chipset": "Snapdragon 8 Gen 3",
      "cpu": "Octa-core",
      "gpu": "Adreno 750",
      "ram": "12GB",
      "storage": "256GB/512GB/1TB",
      "battery": "5000 mAh",
      "charging": "45W wired, 15W wireless"
    },
    "connectivity": {
      "network": "5G",
      "wifi": "Wi-Fi 6E",
      "bluetooth": "5.3",
      "usb": "USB Type-C 3.2"
    }
  },
  
  "affiliate_links": {
    "amazon": {
      "url": "https://amazon.com/samsung-s25-ultra",
      "price": "$1299"
    },
    "flipkart": {
      "url": "https://flipkart.com/samsung-s25-ultra",
      "price": "₹1,29,999"
    }
  },
  
  "pros": [
    "Excellent display quality",
    "Powerful performance",
    "Great camera system",
    "Long battery life"
  ],
  "cons": [
    "Expensive",
    "Heavy and bulky",
    "No charger in box"
  ]
}</code></pre>
    </div>
    </div>

    <style>
        .wrap {
            max-width: 1200px;
            margin: 20px auto;
        }

        .json-upload-section,
        .bulk-import-section,
        .json-example-section,
        .quick-actions-section,
        .stats-section {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .json-example-section pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            margin: 15px 0;
        }

        .json-example-section code {
            background: none;
            padding: 0;
        }

        .quick-action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .quick-action-buttons .button {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .stats-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stats-section li {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #0073aa;
        }

        @media (max-width: 768px) {
            .stats-section ul {
                grid-template-columns: 1fr;
            }

            .quick-action-buttons {
                flex-direction: column;
            }

            .quick-action-buttons .button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
<?php
}

/**
 * Create JSON upload page (Original code)
 */
function ezoix_create_json_upload_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $page = get_page_by_path('upload-json-specs');

    if (!$page) {
        $new_page = array(
            'post_title'    => 'Upload JSON Specs',
            'post_name'     => 'upload-json-specs',
            'post_content'  => '[json_upload_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );

        wp_insert_post($new_page);
    }
}
add_action('init', 'ezoix_create_json_upload_page');

/**
 * JSON upload shortcode (Original code)
 */
function ezoix_json_upload_shortcode()
{
    if (!current_user_can('manage_options')) {
        return '<p>You need administrator privileges to access this page.</p>';
    }

    ob_start();
?>
    <div class="wrap">
        <h1>Upload Mobile Specifications JSON</h1>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('ezoix_json_upload', 'ezoix_json_nonce'); ?>

            <div style="margin: 20px 0;">
                <label for="json_file"><strong>Select JSON File:</strong></label><br>
                <input type="file" name="json_file" id="json_file" accept=".json" required>
                <p class="description">Upload a JSON file containing mobile specifications</p>
            </div>

            <div style="margin: 20px 0;">
                <label><strong>OR Paste JSON Content:</strong></label><br>
                <textarea name="json_content" rows="15" style="width: 100%;" placeholder='{"device_name": "Phone Name", "specifications": {...}}'></textarea>
            </div>

            <input type="submit" name="submit_json" class="button button-primary" value="Import JSON">
        </form>

        <?php
        if (isset($_POST['submit_json']) && wp_verify_nonce($_POST['ezoix_json_nonce'], 'ezoix_json_upload')) {
            $json_content = '';

            if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === 0) {
                $json_content = file_get_contents($_FILES['json_file']['tmp_name']);
            } elseif (isset($_POST['json_content']) && !empty($_POST['json_content'])) {
                $json_content = stripslashes($_POST['json_content']);
            }

            if (!empty($json_content)) {
                if (function_exists('ezoix_import_mobile_json')) {
                    $result = ezoix_import_mobile_json($json_content, true);

                    if (is_wp_error($result)) {
                        echo '<div class="error notice"><p>Error: ' . esc_html($result->get_error_message()) . '</p></div>';
                    } else {
                        echo '<div class="updated notice"><p>✅ Successfully imported: ' . esc_html($result['device_name']) . '</p>';
                        echo '<p><a href="' . esc_url($result['edit_link']) . '" class="button">Edit Device</a> ';
                        echo '<a href="' . esc_url($result['view_link']) . '" class="button" target="_blank">View Device</a></p></div>';
                    }
                } else {
                    echo '<div class="error notice"><p>Import function not available. Please check if ACF is active.</p></div>';
                }
            }
        }
        ?>

        <hr style="margin: 30px 0;">

        <h2>JSON Format Example:</h2>
        <pre style="background: #f5f5f5; padding: 20px; overflow: auto;">
{
    "device_name": "Samsung Galaxy S25 Ultra",
    "model": "SM-S928",
    "release_date": "2024-01-15",
    "price": "$1299",
    "tags": [
    "Samsung S25",
    "Snapdragon 8 Gen 3",
    "200MP Camera",
    "Flagship 2024"
  ],

  
    "specifications": {
        "display": {
            "size": "6.8 inches",
            "resolution": "1440 x 3200 pixels"
        }
    }
}
        </pre>
    </div>

    <style>
        .wrap {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
<?php

    return ob_get_clean();
}
add_shortcode('json_upload_form', 'ezoix_json_upload_shortcode');

/**
 * Simple JSON upload via Media Library (Original code)
 */
function ezoix_simple_json_upload()
{
?>
    <div class="wrap">
        <h1>Upload JSON via Media Library</h1>

        <ol>
            <li>Go to <a href="<?php echo admin_url('media-new.php'); ?>">Media → Add New</a></li>
            <li>Upload your JSON file</li>
            <li>Copy the file URL</li>
            <li>Paste it here:</li>
        </ol>

        <form method="post">
            <input type="url" name="json_url" placeholder="https://yoursite.com/wp-content/uploads/2025/01/specs.json" style="width: 100%; padding: 10px; margin: 10px 0;">
            <input type="submit" name="process_json" class="button button-primary" value="Process JSON">
        </form>

        <?php
        if (isset($_POST['process_json']) && !empty($_POST['json_url'])) {
            $json_content = file_get_contents($_POST['json_url']);

            if ($json_content && function_exists('ezoix_import_mobile_json')) {
                $result = ezoix_import_mobile_json($json_content, true);

                if (!is_wp_error($result)) {
                    echo '<div class="updated"><p>Success! Device imported.</p></div>';
                }
            }
        }
        ?>
    </div>
<?php
}

/**
 * Add admin menus (Original code)
 */
function ezoix_add_admin_menus()
{
    add_menu_page(
        'Device Specs',
        'Device Specs',
        'manage_options',
        'mobile-specs',
        'ezoix_mobile_specs_admin_page_enhanced',
        'dashicons-smartphone',
        30
    );

    add_submenu_page(
        'mobile-specs',
        'Simple JSON Upload',
        'Simple Upload',
        'manage_options',
        'json-upload-simple',
        'ezoix_simple_json_upload'
    );
}
add_action('admin_menu', 'ezoix_add_admin_menus');

/**
 * Allow JSON file uploads (Original code)
 */
function ezoix_allow_json_uploads($mimes)
{
    $mimes['json'] = 'application/json';
    return $mimes;
}
add_filter('upload_mimes', 'ezoix_allow_json_uploads');

/**
 * Enhanced JSON upload form shortcode (Original code)
 */
function ezoix_json_upload_form_shortcode()
{
    if (!current_user_can('manage_options')) {
        return '<p>Administrator access required.</p>';
    }

    ob_start();
?>

    <div class="json-upload-container">
        <h2>Import Mobile Specifications</h2>

        <form method="post" id="json-upload-form">
            <?php wp_nonce_field('ezoix_json_import', 'ezoix_nonce'); ?>

            <div class="upload-options">
                <div class="option active" data-option="paste">
                    <h3>📝 Paste JSON Content</h3>
                    <textarea name="json_content" id="json_content" rows="20" placeholder='{
  "device_name": "Samsung Galaxy S25 Ultra",
  "model": "SM-S928",
  "specifications": {
    "display": {
      "size": "6.8 inches",
      "resolution": "1440 x 3200 pixels"
    }
  }
}'></textarea>
                </div>

                <div class="option" data-option="url">
                    <h3>🔗 JSON File URL</h3>
                    <input type="url" name="json_url" placeholder="https://example.com/specs.json" style="width: 100%; padding: 10px;">
                    <p class="description">Enter direct URL to JSON file</p>
                </div>

                <div class="option" data-option="file">
                    <h3>📁 Upload JSON File</h3>
                    <p><em>Note: May require additional configuration for .json uploads</em></p>
                    <input type="file" id="json_file_input" accept=".json,.txt">
                    <textarea id="file_content" name="file_content" style="display:none;"></textarea>
                </div>
            </div>

            <div class="option-tabs">
                <button type="button" class="tab-btn active" data-target="paste">Paste</button>
                <button type="button" class="tab-btn" data-target="url">URL</button>
                <button type="button" class="tab-btn" data-target="file">File</button>
            </div>

            <div style="margin-top: 20px;">
                <input type="submit" name="submit_json" class="button button-primary button-large" value="Import Specifications">
            </div>
        </form>

        <div id="upload-result"></div>

        <?php
        if (isset($_POST['submit_json']) && wp_verify_nonce($_POST['ezoix_nonce'], 'ezoix_json_import')) {
            $json_content = '';

            if (!empty($_POST['json_content'])) {
                $json_content = stripslashes($_POST['json_content']);
            } elseif (!empty($_POST['json_url'])) {
                $json_content = file_get_contents($_POST['json_url']);
            } elseif (!empty($_POST['file_content'])) {
                $json_content = stripslashes($_POST['file_content']);
            }

            if (!empty($json_content) && function_exists('ezoix_import_mobile_json')) {
                $result = ezoix_import_mobile_json($json_content, true);

                if (is_wp_error($result)) {
                    echo '<div class="error notice"><p>❌ Error: ' . esc_html($result->get_error_message()) . '</p></div>';
                } else {
                    echo '<div class="success notice"><p>✅ Successfully imported: <strong>' . esc_html($result['device_name']) . '</strong></p>';
                    echo '<p><a href="' . esc_url($result['edit_link']) . '" class="button">Edit Device</a> ';
                    echo '<a href="' . esc_url($result['view_link']) . '" class="button" target="_blank">View Device</a></p></div>';
                }
            } elseif (empty($json_content)) {
                echo '<div class="error notice"><p>❌ Please provide JSON content</p></div>';
            }
        }
        ?>
    </div>

    <style>
        .json-upload-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .upload-options {
            margin: 20px 0;
        }

        .option {
            display: none;
        }

        .option.active {
            display: block;
        }

        .option textarea {
            width: 100%;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f8f9fa;
        }

        .option-tabs {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }

        .tab-btn {
            padding: 10px 20px;
            background: #f1f1f1;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .tab-btn.active {
            background: #2271b1;
            color: white;
        }

        .notice {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .notice.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .notice.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const options = document.querySelectorAll('.option');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.dataset.target;

                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    options.forEach(opt => {
                        opt.classList.remove('active');
                        if (opt.dataset.option === target) {
                            opt.classList.add('active');
                        }
                    });
                });
            });

            const fileInput = document.getElementById('json_file_input');
            const fileContent = document.getElementById('file_content');

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            fileContent.value = e.target.result;
                        };
                        reader.readAsText(file);
                    }
                });
            }
        });
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('json_upload', 'ezoix_json_upload_form_shortcode');

/**
 * Emergency JSON processor (Original code)
 */
add_action('init', function () {
    if (isset($_GET['process_json']) && current_user_can('manage_options')) {
        $json = isset($_POST['json']) ? stripslashes($_POST['json']) : '';

        if (!empty($json) && function_exists('ezoix_import_mobile_json')) {
            $result = ezoix_import_mobile_json($json, true);

            if (!is_wp_error($result)) {
                wp_redirect($result['edit_link']);
                exit;
            }
        }

        echo '<form method="post"><textarea name="json" rows="30" cols="100"></textarea><br><input type="submit"></form>';
        exit;
    }
});
/**
 * Debug function to check if mobile device CPT is working (Original code)
 */
function ezoix_debug_cpt_status()
{
    if (current_user_can('manage_options')) {
        echo '';
        echo '';
        echo '';

        $test_posts = get_posts(array(
            'post_type' => array('mobile_device', 'laptop_device'), 
            'posts_per_page' => 1
        ));
        echo '';

        global $wp_rewrite;
        echo '';
    }
}
add_action('wp_footer', 'ezoix_debug_cpt_status');

/**
 * Force rewrite rules flush on every admin page load (temporary) (Original code)
 */
function ezoix_force_flush_now()
{
    if (current_user_can('manage_options') && !get_option('ezoix_rewrite_flushed')) {
        flush_rewrite_rules(true);
        update_option('ezoix_rewrite_flushed', true);
        echo '<div class="notice notice-success"><p>Rewrite rules flushed!</p></div>';
    }
}
add_action('admin_notices', 'ezoix_force_flush_now');

/**
 * Helper function to set the brand taxonomy
 */
function ezoix_set_device_brand_taxonomy($post_id, $post_type, $taxonomy_slug) // MODIFIED
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $device_name = get_field('device_name', $post_id);
    if (!$device_name) $device_name = get_the_title($post_id);

    $brand_name = ezoix_extract_brand_from_name($device_name);

    if ($brand_name && $brand_name !== 'Other') {
        $brand_term = term_exists($brand_name, $taxonomy_slug);

        if (!$brand_term) {
            $brand_term = wp_insert_term(
                $brand_name,
                $taxonomy_slug,
                array(
                    'description' => sprintf('All %s %s', $brand_name, str_replace('_', ' ', $post_type)),
                    'slug' => sanitize_title($brand_name)
                )
            );
        }

        if (!is_wp_error($brand_term)) {
            $term_id = is_array($brand_term) ? $brand_term['term_id'] : $brand_term;
            $current_brands = wp_get_post_terms($post_id, $taxonomy_slug, array('fields' => 'ids'));

            if (!in_array($term_id, $current_brands)) {
                wp_set_post_terms($post_id, array($term_id), $taxonomy_slug, true);
            }
        }
    }
}

/**
 * Auto-create brand category when mobile or laptop device is saved
 */
function ezoix_auto_create_brand_category_for_devices($post_id, $post, $update) // MODIFIED
{
    if ($post->post_type === 'mobile_device') {
        ezoix_set_device_brand_taxonomy($post_id, 'mobile_device', 'mobile_brand');
    } elseif ($post->post_type === 'laptop_device') {
        ezoix_set_device_brand_taxonomy($post_id, 'laptop_device', 'laptop_brand');
    }
}
add_action('save_post_mobile_device', 'ezoix_auto_create_brand_category_for_devices', 20, 3);
add_action('save_post_laptop_device', 'ezoix_auto_create_brand_category_for_devices', 20, 3); 

/**
 * Enhanced brand extraction with more brands (Original code)
 */
function ezoix_extract_brand_from_name($device_name)
{
    $brands = array(
        'Samsung',
        'Apple',
        'iPhone',
        'Google',
        'Pixel',
        'OnePlus',
        'Xiaomi',
        'Redmi',
        'Realme',
        'Oppo',
        'Vivo',
        'Motorola',
        'Nokia',
        'Sony',
        'LG',
        'Huawei',
        'Honor',
        'Asus', 
        'Lenovo',
        'HTC',
        'BlackBerry',
        'Microsoft',
        'Alcatel',
        'Tecno',
        'Infinix',
        'Itel',
        'Micromax',
        'Lava',
        'Gionee',
        'Poco',
        'Nothing',
        'Fairphone',
        'ZTE',
        'Nubia',
        'Meizu',
        'Sharp',
        'Panasonic',
        'Kyocera',
        'Dell', 
        'HP',
        'Acer',
        'MSI',
        'Razer',
        'Alienware'
    );

    usort($brands, function ($a, $b) {
        return strlen($b) - strlen($a);
    });

    foreach ($brands as $brand) {
        if (stripos($device_name, $brand) !== false) {
            return $brand;
        }
    }

    $words = explode(' ', $device_name);
    if (!empty($words[0]) && strlen($words[0]) > 2) {
        return ucfirst($words[0]);
    }

    return 'Other';
}

/**
 * Add rewrite rules for mobile brand archives (Original code)
 */
function ezoix_fix_brand_archive_rewrites()
{
    add_rewrite_rule(
        '^mobile-brand/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?mobile_brand=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^mobile-brand/([^/]+)/?$',
        'index.php?mobile_brand=$matches[1]',
        'top'
    );

    // MODIFIED: ADDED LAPTOP BRAND REWRITE RULES
    add_rewrite_rule(
        '^laptop-brand/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?laptop_brand=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^laptop-brand/([^/]+)/?$',
        'index.php?laptop_brand=$matches[1]',
        'top'
    );
}
add_action('init', 'ezoix_fix_brand_archive_rewrites', 20);

// REMOVED: Redundant function ezoix_mobile_brand_template_redirect()


/**
 * Auto-populate brand field when creating mobile device (Original code)
 */
function ezoix_auto_populate_brand_field($post_id)
{
    if (get_post_type($post_id) !== 'mobile_device' && get_post_type($post_id) !== 'laptop_device' || wp_is_post_revision($post_id)) { 
        return;
    }

    $post_type = get_post_type($post_id);
    $device_name = get_the_title($post_id);
    $taxonomy = ($post_type === 'mobile_device') ? 'mobile_brand' : 'laptop_brand';

    $brand_name = ezoix_extract_brand_from_name($device_name);

    if ($brand_name && $brand_name !== 'Other') {
        if (function_exists('update_field')) {
            $current_model = get_field('device_model', $post_id);
            if (!$current_model) {
                $model = preg_replace('/\b' . preg_quote($brand_name, '/') . '\b/i', '', $device_name);
                $model = trim($model);
                update_field('device_model', $model, $post_id);
            }
        }

        $brand_term = term_exists($brand_name, $taxonomy);
        if (!$brand_term) {
            $brand_term = wp_insert_term(
                $brand_name,
                $taxonomy,
                array(
                    'description' => sprintf('All %s %s', $brand_name, str_replace('_', ' ', $post_type)),
                    'slug' => sanitize_title($brand_name)
                )
            );
        }

        if (!is_wp_error($brand_term)) {
            $term_id = is_array($brand_term) ? $brand_term['term_id'] : $brand_term;
            wp_set_post_terms($post_id, array($term_id), $taxonomy, false);
        }
    }
}
add_action('wp_insert_post', 'ezoix_auto_populate_brand_field', 20, 1);


/**
 * Load correct archive templates for mobile taxonomies
 */
function ezoix_mobile_archive_templates($template) // MODIFIED to include laptop templates
{
    if (is_tax('mobile_brand') || is_tax('laptop_brand')) {
        $new_template = locate_template('archive-mobile-device.php'); 
        if ($new_template) {
            return $new_template;
        }
    }

    if (is_tax('mobile_category') || is_tax('laptop_category')) {
        $new_template = locate_template('archive-mobile-category.php'); 
        if ($new_template) {
            return $new_template;
        }
    }

    if (is_post_type_archive('mobile_device')) {
        $new_template = locate_template('archive-mobile-device.php');
        if ($new_template) {
            return $new_template;
        }
    }
    
    if (is_post_type_archive('laptop_device')) { 
        $new_template = locate_template('archive-laptop_device.php');
        if ($new_template) {
            return $new_template;
        }
    }
    
    if (is_singular('laptop_device')) { 
        $new_template = locate_template('single-laptop_device.php');
        if (!empty($new_template)) {
            return $new_template;
        }
    }

    return $template;
}
add_filter('template_include', 'ezoix_mobile_archive_templates', 99);


/**
 * ============================================================================
 * NEW: Global Ad Slots (Requires ACF PRO)
 * ============================================================================
 */

// 1. Register the Options Page
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Theme Ad Settings',
        'menu_title' => 'Ad Settings',
        'menu_slug'  => 'theme-ad-settings',
        'capability' => 'manage_options',
        'redirect'   => false,
    ));
}

// 2. Register the Fields for the Ad Slots
function ezoix_register_ad_acf_fields()
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group(array(
        'key' => 'group_index_ad_slots',
        'title' => 'Index Page Ad Slots (CodeWithBard)',
        'fields' => array(
            // Mobile Top Ad (Small banner)
            array(
                'key' => 'field_ad_top_image',
                'label' => 'Mobile Top Ad Image',
                'name' => 'ad_top_image',
                'type' => 'image',
                'return_format' => 'url',
                'instructions' => 'Recommended size: 320x50 (max 100px height for small UI impact)',
            ),
            array(
                'key' => 'field_ad_top_url',
                'label' => 'Mobile Top Ad URL',
                'name' => 'ad_top_url',
                'type' => 'url',
            ),

            // Mobile Bottom Ad (Small banner)
            array(
                'key' => 'field_ad_bottom_image',
                'label' => 'Mobile Bottom Ad Image',
                'name' => 'ad_bottom_image',
                'type' => 'image',
                'return_format' => 'url',
                'instructions' => 'Recommended size: 320x50 (max 100px height for small UI impact)',
            ),
            array(
                'key' => 'field_ad_bottom_url',
                'label' => 'Mobile Bottom Ad URL',
                'name' => 'ad_bottom_url',
                'type' => 'url',
            ),

            // Desktop Left Ad (Skyscraper/Sidebar)
            array(
                'key' => 'field_ad_left_image',
                'label' => 'Desktop Left Ad Image',
                'name' => 'ad_left_image',
                'type' => 'image',
                'return_format' => 'url',
                'instructions' => 'Recommended size: 300x600 (Skyscraper)',
            ),
            array(
                'key' => 'field_ad_left_url',
                'label' => 'Desktop Left Ad URL',
                'name' => 'ad_left_url',
                'type' => 'url',
            ),

            // Desktop Right Ad (Skyscraper/Sidebar)
            array(
                'key' => 'field_ad_right_image',
                'label' => 'Desktop Right Ad Image',
                'name' => 'ad_right_image',
                'type' => 'image',
                'return_format' => 'url',
                'instructions' => 'Recommended size: 300x600 (Skyscraper)',
            ),
            array(
                'key' => 'field_ad_right_url',
                'label' => 'Desktop Right Ad URL',
                'name' => 'ad_right_url',
                'type' => 'url',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'theme-ad-settings',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => 'Settings for the dynamic image ad slots on the main index page.',
    ));
}
add_action('acf/init', 'ezoix_register_ad_acf_fields');
/**
 * Automatically set Focus Keywords for Yoast SEO or Rank Math using post tags.
 * This function is hooked to run every time a post is saved/updated.
 */
function ezoix_set_focus_keyword_from_tags($post_id)
{
    // Check if the current user has permission to edit the post.
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if it's an autosave or a revision, if so, exit.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Get the tags associated with the post.
    $tags = wp_get_post_tags($post_id);

    if (! empty($tags)) {
        $keywords = array();
        foreach ($tags as $tag) {
            $keywords[] = $tag->name;
        }

        // The Focus Keyword field usually accepts a comma-separated string.
        $keyword_string = implode(', ', $keywords);

        // ------------------------------------
        // YOAST SEO INTEGRATION (Primary Focus Keyword)
        // ------------------------------------
        // Yoast SEO meta key is '_yoast_wpseo_focuskw'.
        // We only set it if the user hasn't already defined a focus keyword.
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $existing_yoast_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);

            // Only update if the field is empty to respect manual input.
            if (empty($existing_yoast_kw)) {
                update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword_string);
            }
        }

        // ------------------------------------
        // RANK MATH INTEGRATION (Primary Focus Keyword)
        // ------------------------------------
        // Rank Math meta key is 'rank_math_focus_keyword'.
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            $existing_rankmath_kw = get_post_meta($post_id, 'rank_math_focus_keyword', true);

            // Rank Math can handle multiple keywords (tags), but we ensure the field is empty first.
            if (empty($existing_rankmath_kw)) {
                update_post_meta($post_id, 'rank_math_focus_keyword', $keyword_string);
            }
        }
    }
}
add_action('save_post', 'ezoix_set_focus_keyword_from_tags');
add_action('save_post_mobile_device', 'ezoix_set_focus_keyword_from_tags');
add_action('save_post_laptop_device', 'ezoix_set_focus_keyword_from_tags');

/**
 * Automatically fetches and displays a link to the immediately preceding published post.
 *
 * @param int $current_post_id The ID of the current post to exclude from the query.
 * @return string HTML output of the latest link or an empty string.
 */
function ezoix_get_most_recent_link($current_post_id) {
    // Get the current post object to find its publication date
    $current_post = get_post($current_post_id);

    if (!$current_post) {
        return '';
    }

    // Get the exact date and time of the current post for comparison
    $current_post_date = $current_post->post_date;

    // Query arguments to find the post immediately PRECEDING the current one in time
    $args = array(
        'post_type'      => array('post', 'mobile_device', 'laptop_device'), 
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        
        // Find the "newest" post that is still older than the current one
        'orderby'        => 'date',
        'order'          => 'DESC', 
        
        'post__not_in'   => array($current_post_id), // Exclude the current page
        'no_found_rows'  => true, 
        
        'date_query'     => array(
            array(
                'before'    => $current_post_date,
                'inclusive' => false,
                'column'    => 'post_date',
            ),
        ),
    );

    $recent_posts = new WP_Query($args);
    $output = '';

    if ($recent_posts->have_posts()) {
        $recent_posts->the_post();
        $permalink = get_permalink();
        $title = get_the_title();
        
        // Determine the type label for anchor text
        $post_type_obj = get_post_type_object(get_post_type());
        $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : 'Article';

        $output = '<section class="auto-latest-link sidebar-widget">';
        $output .= '<h3 class="widget-title">Previously Published</h3>'; // Updated title
        $output .= '<p>Read our previous ' . esc_html($post_type_label) . ': <br>';
        $output .= '<a href="' . esc_url($permalink) . '" rel="prev" class="cta-button">' . esc_html($title) . ' →</a></p>'; // Added rel="prev"
        $output .= '</section>';
        
        wp_reset_postdata();
    }

    return $output;
}