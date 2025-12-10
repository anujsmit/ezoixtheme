<?php get_header(); ?>

<!-- Add a wrapper div with the correct class -->
<div class="container container-index">
    
    <main class="main-content">
        <div class="category-posts-grid" id="feed-container">
            <?php
            // 1. Get featured posts to exclude from the main list
            $featured_posts = ezoix_cache_featured_posts(2);
            $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');

            // 2. Get posts for the initial feed (Page 1)
            $feed_items = new WP_Query(array(
                'post_type' => array('post', 'mobile_device'),
                'posts_per_page' => 15,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => 'publish',
                'post__not_in' => $exclude_ids,
                'no_found_rows' => true,
            ));

            if ($feed_items->have_posts()) :
                while ($feed_items->have_posts()) : $feed_items->the_post();
                    ezoix_render_grid_card();
                endwhile;
                wp_reset_postdata();
            else :
            ?>
                <div class="no-content">
                    <div class="no-content-icon">📭</div>
                    <h3>No articles or devices yet</h3>
                    <p>Start publishing content to see them here.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="load-more-section" id="infinite-scroll-loading-section">
            <div id="feed-loading" class="feed-loading">
                <div class="loading-spinner"></div>
                Loading more content...
            </div>
        </div>
        <div id="feed-end-message" style="text-align: center; color: var(--text-light); margin-top: 20px; display: none;">
            That's all the content we have for now!
        </div>
    </main>
    
</div>

<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
    ↑
</button>

<?php get_footer(); ?>