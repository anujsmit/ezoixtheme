<?php get_header(); ?>

<div class="container container-menu">
    <div class="content-area category-page">
        <main class="main-content">
            <?php if (have_posts()) : ?>
                <div class="category-header">
                    <h1 class="category-title"><?php single_cat_title(); ?></h1>
                    <?php if (category_description()) : ?>
                        <div class="category-description">
                            <?php echo category_description(); ?>
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
                    <h3>No articles in this category yet</h3>
                    <p>Check back soon for new content.</p>
                    <a href="<?php echo home_url(); ?>" class="cta-button">← Back to Home</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>