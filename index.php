<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            <!-- Featured Posts Section -->
            <?php
            $featured_posts = ezoix_cache_featured_posts(2);

            if ($featured_posts->have_posts()) : ?>
                <section class="featured-posts">
                    <div class="section-header">
                        <h2 class="section-title">Trending Now</h2>
                    </div>

                    <div class="post-grid">
                        <?php while ($featured_posts->have_posts()) : $featured_posts->the_post(); ?>
                            <article class="post-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php
                                            the_post_thumbnail('desktop-thumbnail', array(
                                                'loading' => 'eager',
                                                'alt' => get_the_title(),
                                                'class' => 'ezoix-critical'
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
                                    <p class="post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                                </div>
                            </article>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Latest News Section - Compact Layout with Infinite Scroll -->
            <section class="latest-news">
                <div class="section-header">
                    <h2 class="section-title">Latest News</h2>
                    <span class="post-count"><?php echo number_format(wp_count_posts()->publish); ?> Articles</span>
                </div>

                <div class="posts-column" id="posts-container">
                    <?php
                    // Get initial posts (first 10)
                    $posts_per_page = 10;
                    $paged = 1;
                    
                    // Get featured posts IDs to exclude
                    $featured_posts = ezoix_cache_featured_posts(2);
                    $exclude_ids = wp_list_pluck($featured_posts->posts, 'ID');
                    
                    $main_posts = new WP_Query(array(
                        'posts_per_page' => $posts_per_page,
                        'paged' => $paged,
                        'post__not_in' => $exclude_ids,
                        'post_status' => 'publish',
                        'no_found_rows' => true,
                        'update_post_meta_cache' => false,
                        'update_post_term_cache' => false,
                    ));

                    if ($main_posts->have_posts()) :
                        while ($main_posts->have_posts()) : $main_posts->the_post();
                    ?>
                            <article class="post-card-compact" data-post-id="<?php the_ID(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php
                                            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'desktop-thumbnail');
                                            ?>
                                            <img 
                                                data-src="<?php echo esc_url($thumbnail_url); ?>" 
                                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
                                                alt="<?php the_title_attribute(); ?>" 
                                                loading="lazy" 
                                                class="lazy"
                                            >
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
                    else :
                        echo '<p class="no-posts">No posts found.</p>';
                    endif;
                    ?>
                </div>

                <!-- Infinite Scroll Loading Indicator -->
                <div id="infinite-scroll-loading" class="infinite-scroll-loading" style="display: none;">
                    <span class="loading-spinner"></span> Loading more posts...
                </div>

                <!-- End of Posts Message -->
                <div id="infinite-scroll-end" class="infinite-scroll-end" style="display: none;">
                    You've reached the end! 🎉
                </div>

                <!-- Fallback Load More Button -->
                <div class="load-more-container">
                    <button id="load-more-posts" class="load-more-button" data-page="1">
                        Load More Posts
                    </button>
                </div>

                <?php wp_reset_postdata(); ?>
            </section>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Default sidebar widgets -->
            <div class="sidebar-widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="categories-list">
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => true
                    ));
                    foreach ($categories as $category) {
                        echo '<li><a href="' . get_category_link($category->term_id) . '">' . $category->name . ' <span class="category-count">' . $category->count . '</span></a></li>';
                    }
                    ?>
                </ul>
            </div>

            <div class="sidebar-widget">
                <h3 class="widget-title">Recent Posts</h3>
                <ul class="recent-posts-list">
                    <?php
                    $recent_posts = ezoix_cache_recent_posts(5);
                    foreach ($recent_posts as $post) {
                        echo '<li><a href="' . get_permalink($post['ID']) . '">' . $post['post_title'] . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
    ↑
</button>

<?php get_footer(); ?>