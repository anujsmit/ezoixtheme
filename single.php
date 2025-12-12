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

                    <div class="post-content">
                        <?php the_content(); ?>
                        <?php if (function_exists('get_field')) : ?>
                            <?php $specs_data = get_field('specifications'); ?>
                            <?php if ($specs_data) : ?>
                                <div class="acf-section specifications-section">
                                    <h3 class="acf-section-title">Specifications</h3>
                                    <ul class="specs-list">
                                        <?php
                                        // Loop through the main repeater (Categories)
                                        foreach ($specs_data as $category_group) :
                                            // Check if the inner repeater (Items) exists and is not empty
                                            if (!empty($category_group['items']) && is_array($category_group['items'])) :
                                        ?>
                                                <li class="spec-category-header">
                                                    <strong><?php echo esc_html($category_group['category']); ?></strong>
                                                </li>
                                                <?php
                                                // Loop through the inner repeater (Key/Value pairs)
                                                foreach ($category_group['items'] as $spec) :
                                                    // Use 'key' and 'value' (from acf-mobile-fields.php) and check if they exist
                                                    $spec_key = isset($spec['key']) ? $spec['key'] : '';
                                                    $spec_value = isset($spec['value']) ? $spec['value'] : '';
                                                ?>
                                                    <li><span class="spec-label"><?php echo esc_html($spec_key); ?>:</span> <?php echo esc_html($spec_value); ?></li>
                                                <?php endforeach; ?>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
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
                
                <?php echo ezoix_get_most_recent_link(get_the_ID()); ?>
                <?php endwhile; ?>
        </main>
    </div>
</div>

<?php get_footer(); ?>