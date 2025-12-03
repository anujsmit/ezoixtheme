<?php get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            <?php while (have_posts()) : the_post(); ?>
            <article class="single-post">
                <header class="single-post-header">
                    <div class="breadcrumb">
                        <a href="<?php echo home_url(); ?>">Home</a> &raquo; 
                        <?php 
                        $categories = get_the_category();
                        if (!empty($categories)) {
                            echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a> &raquo; ';
                        }
                        ?>
                        <span><?php the_title(); ?></span>
                    </div>
                    
                    <h1 class="single-post-title"><?php the_title(); ?></h1>
                    
                    <div class="single-post-meta">
                        <span class="post-date"><?php echo get_the_date(); ?></span>
                        <span class="post-author">By <?php the_author(); ?></span>
                        <span class="post-categories"><?php the_category(', '); ?></span>
                    </div>
                </header>
                
                <?php if (has_post_thumbnail()) : ?>
                <div class="post-featured-image">
                    <?php 
                    the_post_thumbnail('featured-image', array(
                        'loading' => 'eager',
                        'alt' => get_the_title(),
                        'class' => 'ezoix-critical'
                    )); 
                    ?>
                </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php the_content(); ?>
                    
                    <!-- ACF Custom Fields (if used) -->
                    <?php if (function_exists('get_field')) : ?>
                        <?php if (get_field('specifications')) : ?>
                        <div class="acf-section">
                            <h3 class="acf-section-title">Specifications</h3>
                            <ul class="specs-list">
                                <?php 
                                $specs = get_field('specifications');
                                foreach ($specs as $spec) : ?>
                                <li><span class="spec-label"><?php echo esc_html($spec['label']); ?>:</span> <?php echo esc_html($spec['value']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (get_field('pros') || get_field('cons')) : ?>
                        <div class="pros-cons">
                            <?php if (get_field('pros')) : ?>
                            <div class="pros">
                                <h4 class="pros-title">Pros</h4>
                                <ul class="features-list">
                                    <?php 
                                    $pros = get_field('pros');
                                    foreach ($pros as $pro) : ?>
                                    <li><?php echo esc_html($pro['item']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (get_field('cons')) : ?>
                            <div class="cons">
                                <h4 class="cons-title">Cons</h4>
                                <ul class="features-list">
                                    <?php 
                                    $cons = get_field('cons');
                                    foreach ($cons as $con) : ?>
                                    <li><?php echo esc_html($con['item']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (get_field('rating')) : ?>
                        <div class="rating-section">
                            <div class="rating-stars">
                                <?php
                                $rating = get_field('rating');
                                $full_stars = floor($rating);
                                $half_stars = ceil($rating - $full_stars);
                                $empty_stars = 5 - $full_stars - $half_stars;
                                
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<span class="star">★</span>';
                                }
                                for ($i = 0; $i < $half_stars; $i++) {
                                    echo '<span class="star">☆</span>';
                                }
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<span class="star">☆</span>';
                                }
                                ?>
                            </div>
                            <div class="rating-value"><?php echo esc_html($rating); ?>/5</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (get_field('affiliate_link')) : ?>
                        <div class="cta-buttons">
                            <a href="<?php echo esc_url(get_field('affiliate_link')); ?>" class="cta-button" target="_blank" rel="noopener nofollow">Buy Now</a>
                            <a href="#specifications" class="cta-button secondary">More Info</a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </article>
            
            <!-- Related Posts -->
            <?php
            $categories = get_the_category();
            if ($categories) {
                $category_ids = array();
                foreach ($categories as $category) {
                    $category_ids[] = $category->term_id;
                }
                
                $related_posts = new WP_Query(array(
                    'category__in' => $category_ids,
                    'post__not_in' => array(get_the_ID()),
                    'posts_per_page' => 3,
                    'orderby' => 'rand',
                    'no_found_rows' => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                ));
                
                if ($related_posts->have_posts()) :
            ?>
            <section class="related-posts">
                <div class="section-header">
                    <h2 class="section-title">Related Posts</h2>
                </div>
                
                <div class="related-posts-grid">
                    <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
                    <article class="post-card">
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
                            </div>
                        </div>
                    </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
            <?php
                endif;
            }
            ?>
            
            <!-- Comments -->
            <?php comments_template(); ?>
            
            <?php endwhile; ?>
        </main>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>

<?php get_footer(); ?>