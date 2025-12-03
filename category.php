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
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="category-post-card">
                            <!-- Thumbnail with placeholder -->
                            <div class="category-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php
                                        the_post_thumbnail('thumbnail', array(
                                            'loading' => 'lazy',
                                            'alt' => get_the_title(),
                                            'class' => 'category-post-thumbnail'
                                        ));
                                        ?>
                                    <?php else : ?>
                                        <!-- Cool placeholder for missing image -->
                                        <div class="category-thumbnail-placeholder">
                                            <span class="placeholder-icon">📝</span>
                                            <span class="category-placeholder-cat"><?php 
                                                $categories = get_the_category();
                                                if (!empty($categories)) {
                                                    echo esc_html($categories[0]->name);
                                                }
                                            ?></span>
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
                                        <span class="meta-icon">⏱️</span>
                                        <?php
                                        $word_count = str_word_count(strip_tags(get_the_content()));
                                        $reading_time = ceil($word_count / 200);
                                        echo $reading_time . ' min';
                                        ?>
                                    </span>
                                </div>
                                
                                <p class="category-post-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </p>
                                
                                <a href="<?php the_permalink(); ?>" class="category-read-more">
                                    Read Article →
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
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