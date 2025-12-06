<?php

/**
 * Template Name: Mobile Categories Archive - Unified to match category.php style
 * * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container container-menu">
    <div class="content-area category-page">
        <main class="main-content">
            <?php if (have_posts()) : ?>
                <div class="category-header">
                    <h1 class="category-title"><?php single_term_title(); // Displays the category/term name 
                                                ?></h1>
                    <?php if (term_description()) : ?>
                        <div class="category-description">
                            <?php echo term_description(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="category-posts-grid">
                    <?php while (have_posts()) : the_post();
                        $post_id = get_the_ID();
                        $post_type = get_post_type();

                        // --- Mobile Device Specific Data ---
                        $price = (function_exists('get_field') && $post_type === 'mobile_device') ? get_field('device_price', $post_id) : null;
                        $rating = (function_exists('get_field') && $post_type === 'mobile_device') ? get_field('device_rating', $post_id) : null;

                        // Calculate display rating (10-point scale to 5-point scale)
                        $display_rating = $rating ? number_format(floatval($rating) / 2, 1) : null;

                        // Set placeholder based on post type (used in category.php placeholder logic)
                        $placeholder_icon = ($post_type === 'mobile_device') ? '📱' : '📝';
                        $placeholder_cat_text = ($post_type === 'mobile_device') ? 'Mobile Device' : single_term_title('', false);

                        // Calculate reading time or primary spec
                        $meta_right_content = '';
                        if ($post_type === 'mobile_device') {
                            // Display Price if available
                            $meta_right_content = $price ? '💵 ' . esc_html($price) : '';
                        } else {
                            // Default blog post reading time
                            $word_count = str_word_count(strip_tags(get_the_content()));
                            $reading_time = ceil($word_count / 200);
                            $meta_right_content = '⏱️ ' . $reading_time . ' min';
                        }
                    ?>
                        <article class="category-post-card">
                            <!-- Thumbnail with placeholder -->
                            <div class="category-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php
                                        // Use 'medium' size for better quality in the grid, or define a custom 'grid-thumbnail' size if needed
                                        the_post_thumbnail('medium', array(
                                            'loading' => 'lazy',
                                            'alt' => get_the_title(),
                                            'class' => 'category-post-thumbnail'
                                        ));
                                        ?>
                                    <?php else : ?>
                                        <!-- Placeholder (similar logic to the one in category.php) -->
                                        <div class="category-thumbnail-placeholder">
                                            <span class="placeholder-icon"><?php echo esc_html($placeholder_icon); ?></span>
                                            <span class="category-placeholder-cat"><?php echo esc_html($placeholder_cat_text); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="category-post-content">
                                <h3 class="category-post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="category-post-meta">
                                    <span class="post-date">
                                        <span class="meta-icon">📅</span>
                                        <?php echo get_the_date('M j, Y'); ?>
                                    </span>
                                    <span class="post-read-time">
                                        <?php if ($meta_right_content) : ?>
                                            <?php echo $meta_right_content; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <p class="category-post-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </p>

                                <?php if ($display_rating) : ?>
                                    <div class="device-rating" style="margin-bottom: 15px;">
                                        <span style="font-weight: 600; color: var(--color-rating);">Rating:</span>
                                        <span style="font-weight: 700; color: var(--text-dark);"><?php echo $display_rating; ?>/5</span>
                                    </div>
                                <?php endif; ?>

                                <a href="<?php the_permalink(); ?>" class="category-read-more">
                                    View Details →
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination (using standard pagination links) -->
                <div class="pagination">
                    <?php
                    global $wp_query;
                    echo paginate_links(array(
                        'mid_size' => 2,
                        'prev_text' => __('&laquo; Previous', 'ezoix'),
                        'next_text' => __('Next &raquo;', 'ezoix'),
                        'type' => 'plain',
                        'total' => $wp_query->max_num_pages,
                        'current' => max(1, get_query_var('paged'))
                    ));
                    ?>
                </div>
            <?php else : ?>
                <div class="no-posts">
                    <div class="no-posts-icon">📭</div>
                    <h3>No items found in this category yet</h3>
                    <p>Check back soon for new content, or browse our main device archive.</p>
                    <a href="<?php echo home_url('/mobile-devices/'); ?>" class="cta-button">← Browse All Devices</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>