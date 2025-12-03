<?php get_header(); ?>

<div class="container container-index">
    <div class="left">

        <div class="feed-items" id="feed-container">
            <?php
            // Get ONLY blog posts, sorted by date
            $feed_items = new WP_Query(array(
                'post_type' => 'post', // Only blog posts
                'posts_per_page' => 15,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_status' => 'publish',
                'no_found_rows' => true,
            ));

            if ($feed_items->have_posts()) :
                while ($feed_items->have_posts()) : $feed_items->the_post();
                    $categories = get_the_category();
            ?>
                    <article class="feed-item article-item" data-type="article" data-date="<?php echo get_the_date('Y-m-d H:i:s'); ?>">

                        <!-- Time Badge -->
                        <div class="item-time">
                            <span class="time-icon">🕒</span>
                            <time datetime="<?php echo get_the_date('c'); ?>">
                                <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                            </time>
                        </div>

                        <!-- Item Header -->
                        <div class="item-header">
                            <div class="item-type-badge article-badge">
                                <span class="badge-icon">📝</span>
                                <span class="badge-text">Article</span>
                            </div>
                            <?php if (!empty($categories)) : ?>
                                <span class="item-category">
                                    <?php echo esc_html($categories[0]->name); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Item Content -->
                        <div class="item-content">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="item-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="item-details">
                                <h2 class="item-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <div class="item-meta">
                                    <!-- Article Meta -->
                                    <span class="meta-item author-item">
                                        <span class="meta-icon">👤</span>
                                        <span class="meta-text">By <?php the_author(); ?></span>
                                    </span>
                                    <span class="meta-item read-item">
                                        <span class="meta-icon">⏱️</span>
                                        <span class="meta-text">
                                            <?php
                                            $word_count = str_word_count(strip_tags(get_the_content()));
                                            $reading_time = ceil($word_count / 200);
                                            echo $reading_time . ' min read';
                                            ?>
                                        </span>
                                    </span>
                                </div>

                                <p class="item-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 35); ?>
                                </p>

                                <div class="item-actions">
                                    <a href="<?php the_permalink(); ?>" class="action-button">
                                        Read Article →
                                    </a>
                                    <button class="action-button share-button" data-url="<?php the_permalink(); ?>" data-title="<?php the_title_attribute(); ?>">
                                        <span class="share-icon">📤</span> Share
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Item Footer -->
                        <div class="item-footer">
                            <div class="footer-left">
                                <span class="footer-text">
                                    <span class="footer-icon">📅</span>
                                    Published: <?php echo get_the_date(); ?>
                                </span>
                            </div>
                            <div class="footer-right">
                                <button class="footer-action save-item" data-id="<?php the_ID(); ?>">
                                    <span class="action-icon">💾</span>
                                    Save
                                </button>
                                <button class="footer-action bookmark-item" data-id="<?php the_ID(); ?>">
                                    <span class="action-icon">🔖</span>
                                    Bookmark
                                </button>
                            </div>
                        </div>
                    </article>
                <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="no-content">
                    <div class="no-content-icon">📭</div>
                    <h3>No articles yet</h3>
                    <p>Start publishing articles to see them here.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Load More -->
        <div class="load-more-section">
            <button id="load-more-feed" class="load-more-btn" data-page="1">
                <span class="btn-icon">⬇️</span>
                Load More Articles
            </button>
            <div id="feed-loading" class="feed-loading" style="display: none;">
                <div class="loading-spinner"></div>
                Loading more articles...
            </div>
        </div>
    </div>

    <div class="right">
        <!-- Sidebar -->
        <aside class="feed-sidebar">
            <div class="sidebar-ad">
                <h3 class="ad-title">🔥 Popular Articles</h3>
                <?php
                $popular_posts = new WP_Query(array(
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'meta_key' => 'post_views_count',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'post_status' => 'publish',
                    'no_found_rows' => true,
                ));

                if ($popular_posts->have_posts()) :
                    while ($popular_posts->have_posts()) : $popular_posts->the_post();
                        $categories = get_the_category();
                ?>
                        <div class="trending-device">
                            <a href="<?php the_permalink(); ?>" class="trending-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="trending-image">
                                        <?php the_post_thumbnail('thumbnail'); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="trending-info">
                                    <?php if (!empty($categories)) : ?>
                                        <span class="trending-brand"><?php echo esc_html($categories[0]->name); ?></span>
                                    <?php endif; ?>
                                    <h4 class="trending-title"><?php the_title(); ?></h4>
                                    <span class="trending-price">
                                        <?php
                                        $word_count = str_word_count(strip_tags(get_the_content()));
                                        $reading_time = ceil($word_count / 200);
                                        echo $reading_time . ' min read';
                                        ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                <a href="<?php echo get_post_type_archive_link('post'); ?>" class="sidebar-cta">
                    View All Articles →
                </a>
            </div>

            <div class="sidebar-ad">
                <h3 class="ad-title">📢 Advertisement</h3>
                <div class="ad-placeholder">
                    <p>Your ad could be here!</p>
                    <small>Contact us for advertising opportunities</small>
                </div>
            </div>

        </aside>
    </div>

</div>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
    ↑
</button>

<?php get_footer(); ?>