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
                        ezoix_render_grid_card(); // Use the new unified card function
                    ?>
                    <?php endwhile; ?>
                </div>

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