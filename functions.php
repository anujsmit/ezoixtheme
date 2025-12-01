<?php

/**
 * Ezoix Tech Blog Theme Functions - Optimized Version
 * 
 * @package Ezoix_Tech_Blog
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Theme Setup
 */
function ezoix_theme_setup()
{
    // Enable title tag support
    add_theme_support('title-tag');

    // Enable post thumbnails
    add_theme_support('post-thumbnails');

    // Enable custom logo
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Enable HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Enable custom background
    add_theme_support('custom-background');

    // Enable post formats
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

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'ezoix'),
        'footer'  => __('Footer Menu', 'ezoix'),
    ));

    // Add optimized image sizes
    add_image_size('mobile-thumbnail', 400, 200, true);
    add_image_size('tablet-thumbnail', 600, 300, true);
    add_image_size('desktop-thumbnail', 800, 400, true);
    add_image_size('featured-image', 1200, 600, true);
    add_image_size('hero-image', 1920, 800, true);

    // Load theme text domain
    load_theme_textdomain('ezoix', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'ezoix_theme_setup');

/**
 * Optimized Enqueue Styles and Scripts
 */
function ezoix_theme_scripts()
{
    // Main stylesheet
    wp_enqueue_style('ezoix-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));

    // Google Fonts with preconnect
    wp_enqueue_style('ezoix-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null);

    // Custom JavaScript
    wp_enqueue_script('ezoix-script', get_template_directory_uri() . '/js/script.js', array(), '1.0.0', true);

    // Add comment reply script on single posts
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    // Localize script for AJAX
    wp_localize_script('ezoix-script', 'ezoix_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ezoix_nonce')
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
 * Lazy load images
 */
function ezoix_lazy_load_images($content)
{
    if (is_admin() || is_feed()) return $content;

    // Don't lazy load above-the-fold images
    if (is_singular() && has_post_thumbnail()) {
        $content = preg_replace('/<img(.*?)class=\"(.*?wp-post-image.*?)\"(.*?)>/i', '<img$1class="$2 ezoix-critical"$3>', $content);
    }

    // Lazy load other images
    $content = preg_replace('/<img(.*?)src=/i', '<img$1loading="lazy" src=', $content);

    return $content;
}
add_filter('the_content', 'ezoix_lazy_load_images');

/**
 * Responsive images
 */
function ezoix_responsive_images($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    if (is_admin()) return $html;

    $src = wp_get_attachment_image_src($post_thumbnail_id, $size);
    $srcset = wp_get_attachment_image_srcset($post_thumbnail_id, $size);
    $sizes = wp_get_attachment_image_sizes($post_thumbnail_id, $size);

    // Determine loading strategy
    $loading = 'loading="lazy"';
    if (is_singular() && has_post_thumbnail() && get_post_thumbnail_id() == $post_thumbnail_id) {
        $loading = 'class="ezoix-critical"';
    }

    return sprintf(
        '<img src="%s" srcset="%s" sizes="%s" %s %s>',
        esc_url($src[0]),
        esc_attr($srcset),
        esc_attr($sizes),
        $loading,
        isset($attr['alt']) ? 'alt="' . esc_attr($attr['alt']) . '"' : ''
    );
}
add_filter('post_thumbnail_html', 'ezoix_responsive_images', 10, 5);

/**
 * Register Widget Areas
 */
function ezoix_widgets_init()
{
    // Main Sidebar
    register_sidebar(array(
        'name'          => __('Sidebar', 'ezoix'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'ezoix'),
        'before_widget' => '<div class="sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    // Footer Widgets
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
        $posts = new WP_Query(array(
            'posts_per_page' => $number,
            'meta_query'     => array(
                array(
                    'key'     => 'featured_post',
                    'value'   => '1',
                    'compare' => '='
                )
            ),
            'no_found_rows'  => true, // Improve performance
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
    return 20;
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
        'post',
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
    echo '<p class="description">' . __('Featured posts will appear in the "Trending Now" section.', 'ezoix') . '</p>';
}

function ezoix_save_featured_meta($post_id)
{
    // Check if nonce is set
    if (! isset($_POST['ezoix_featured_meta_nonce'])) {
        return;
    }

    // Verify nonce
    if (! wp_verify_nonce($_POST['ezoix_featured_meta_nonce'], 'ezoix_featured_meta')) {
        return;
    }

    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (isset($_POST['post_type']) && 'post' == $_POST['post_type']) {
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Save featured post meta
    $featured = isset($_POST['featured_post']) ? 1 : 0;
    update_post_meta($post_id, 'featured_post', $featured);

    // Clear featured posts cache
    delete_transient('ezoix_featured_posts_2');
}
add_action('save_post', 'ezoix_save_featured_meta');

/**
 * Optimize WordPress
 */
function ezoix_optimize_queries()
{
    if (! is_admin()) {
        // Remove unnecessary stuff
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Disable embeds
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
    // Hero Section
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

        // Clear cache when widget is updated
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
        echo '<!-- Generated in ' . timer_stop(0) . ' seconds -->';
        echo '<!-- ' . get_num_queries() . ' queries -->';
        echo '<!-- Memory used: ' . size_format(memory_get_usage(true), 2) . ' -->';
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

    // Clear various caches
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
        echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";

        // Preload above-the-fold images
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

// AJAX handler for load more posts
function ezoix_load_more_posts()
{
    $page = $_POST['page'] ?: 1;
    $posts_per_page = 10;

    $query = new WP_Query(array(
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish'
    ));

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
        ?>
            <article class="post-card post-card-column">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php
                            the_post_thumbnail('desktop-thumbnail', array(
                                'loading' => 'lazy',
                                'alt' => get_the_title()
                            ));
                            ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="post-content">
                    <span class="post-category"><?php the_category(', '); ?></span>
                    <h3 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="post-meta">
                        <span class="post-date"><?php echo get_the_date(); ?></span>
                        <span class="post-author">By <?php the_author(); ?></span>
                    </div>
                    <p class="post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 25); ?></p>
                    <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                </div>
            </article>
        <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '';
    endif;

    wp_die();
}
add_action('wp_ajax_load_more_posts', 'ezoix_load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'ezoix_load_more_posts');
/**
 * AJAX handler for infinite scroll
 */
function ezoix_infinite_scroll_posts()
{
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'ezoix_nonce')) {
        wp_die('Security check failed');
    }

    $page = intval($_POST['page']);
    $posts_per_page = 10;

    // Get featured posts IDs to exclude
    $featured_posts = ezoix_cache_featured_posts(2);
    $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

    $query = new WP_Query(array(
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post__not_in' => $exclude_ids,
        'post_status' => 'publish',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
        ?>
            <article class="post-card-compact" data-post-id="<?php the_ID(); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php
                            the_post_thumbnail('desktop-thumbnail', array(
                                'loading' => 'lazy',
                                'alt' => get_the_title()
                            ));
                            ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="post-content">
                    <span class="post-category"><?php the_category(', '); ?></span>
                    <h3 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <div class="post-meta">
                        <span class="post-date"><?php echo get_the_date(); ?></span>
                        <span class="post-author">By <?php the_author(); ?></span>
                    </div>
                    <p class="post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                </div>
            </article>
<?php
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
 * Get total pages count for infinite scroll
 */
function ezoix_get_total_pages()
{
    $posts_per_page = 10;
    $featured_posts = ezoix_cache_featured_posts(2);
    $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

    $total_posts = wp_count_posts();
    $published_posts = $total_posts->publish;

    // Subtract featured posts from total
    $remaining_posts = max(0, $published_posts - count($exclude_ids));

    return ceil($remaining_posts / $posts_per_page);
}
?>