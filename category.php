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
                        $author_name = get_the_author();
                    ?>
                        <article class="category-post-card">
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
                                            <div class="placeholder-content">
                                                <span class="placeholder-icon">📺</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>

                            <div class="category-post-content">
                                <h3 class="category-post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="item-meta-yt">
                                    <p class="author-name"><?php echo esc_html($author_name); ?></p>
                                    <p class="post-date">
                                        <?php echo get_the_date('M j, Y'); ?>
                                        &bull;
                                        <?php
                                        $word_count = str_word_count(strip_tags(get_the_content()));
                                        $reading_time = ceil($word_count / 200);
                                        echo '⏱️ ' . $reading_time . ' min';
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </article>
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