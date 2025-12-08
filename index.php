<?php get_header(); ?>

<?php
/**
 * Helper function to render a unified feed item card.
 * This function is used both for the initial load and the AJAX load.
 */
function render_feed_item()
{
    // Shared details
    $post_id = get_the_ID();
    $post_type = get_post_type();
    $permalink = get_the_permalink();
    $title = get_the_title();
    $date = get_the_date('M j, Y');
    $excerpt = wp_trim_words(get_the_excerpt(), 25);
    $word_count = str_word_count(strip_tags(get_the_content()));
    $reading_time = ceil($word_count / 200);

    // --- Post Type Specific Data ---
    $meta_right = '';
    $item_type_class = '';
    $placeholder_icon = '📝'; // Default for articles
    $placeholder_text = 'Article';

    if ($post_type === 'mobile_device') {
        $item_type_class = ' device-item';
        $price = function_exists('get_field') ? get_field('device_price') : '';
        $rating = function_exists('get_field') ? get_field('device_rating') : '';
        $placeholder_icon = '📱';
        $placeholder_text = 'Mobile Device';

        // Prioritize Price/Rating for mobile devices
        if ($price) {
            $meta_right .= '<span class="meta-item price-item">💵 <span class="meta-text">' . esc_html($price) . '</span></span>';
        }
        if ($rating) {
            // Convert 10-point to 5-point for display
            $rating_value = number_format(floatval($rating) / 2, 1);
            $meta_right .= '<span class="meta-item rating-item">⭐ <span class="meta-text">' . esc_html($rating_value) . '/5</span></span>';
        }
    } else {
        $item_type_class = ' article-item';
        // Use reading time for standard posts
        $meta_right = '<span class="meta-item read-item"><span class="meta-icon">⏱️</span><span class="meta-text">' . esc_html($reading_time) . ' min</span></span>';
    }
?>
    <!-- Unified Feed Item for Initial Load -->
    <article class="feed-item<?php echo $item_type_class; ?>" data-type="<?php echo esc_attr($post_type); ?>" data-date="<?php echo get_the_date('Y-m-d H:i:s'); ?>">

        <!-- Thumbnail Section -->
        <div class="thumbnail">
            <?php if (has_post_thumbnail()) : ?>
                <div class="item-thumbnail">
                    <a href="<?php echo esc_url($permalink); ?>">
                        <?php the_post_thumbnail('feed-portrait', array(
                            'loading' => 'lazy',
                            'class' => 'feed-thumbnail'
                        )); ?>
                    </a>
                </div>
            <?php else : ?>
                <!-- Custom placeholder for missing images based on content type -->
                <div class="item-thumbnail placeholder-<?php echo esc_attr($post_type); ?>">
                    <a href="<?php echo esc_url($permalink); ?>">
                        <span class="placeholder-icon" style="font-size: 36px;"><?php echo $placeholder_icon; ?></span>
                        <span class="placeholder-text" style="font-size: 10px; color: white; opacity: 0.8;"><?php echo $placeholder_text; ?></span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content Section -->
        <div class="item-details">
            <h2 class="item-title">
                <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
            </h2>

            <p class="item-excerpt">
                <?php echo esc_html($excerpt); ?>
            </p>

            <!-- Footer inside details for better alignment -->
            <div class="item-footer">
                <div class="footer-left">
                    <span class="footer-text">
                        <span class="footer-icon">📅</span>
                        <?php echo esc_html($date); ?>
                    </span>
                </div>
                <div class="footer-right">
                    <div class="item-meta">
                        <?php echo $meta_right; ?>
                    </div>
                </div>
            </div>
        </div>
    </article>
<?php
}
?>

<div class="container container-index">

    <div class="feed-items" id="feed-container">
        <?php
        // 1. Get featured posts to exclude from the main list
        $featured_posts = ezoix_cache_featured_posts(2);
        $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

        // 2. Get posts for the initial feed (Page 1)
        $feed_items = new WP_Query(array(
            'post_type' => array('post', 'mobile_device'), // Query BOTH post types
            'posts_per_page' => 15,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'post__not_in' => $exclude_ids, // Exclude featured items
            'no_found_rows' => true,
        ));

        if ($feed_items->have_posts()) :
            while ($feed_items->have_posts()) : $feed_items->the_post();
                render_feed_item();
            endwhile;
            wp_reset_postdata();
        else :
            // Display message if no content is found
        ?>
            <div class="no-content">
                <div class="no-content-icon">📭</div>
                <h3>No articles or devices yet</h3>
                <p>Start publishing content to see them here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Infinite Scroll Loading/End Messages -->
    <div class="load-more-section" id="infinite-scroll-loading-section">
        <!-- The button is removed, leaving only the loading indicator placeholder -->
        <div id="feed-loading" class="feed-loading">
            <div class="loading-spinner"></div>
            Loading more content...
        </div>
    </div>
    <div id="feed-end-message" style="text-align: center; color: var(--text-light); margin-top: 20px; display: none;">
        That's all the content we have for now!
    </div>

</div>

<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
    ↑
</button>

<?php get_footer(); ?>