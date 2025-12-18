<?php
/**
 * Template Name: Laptop Devices Archive
 * * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            
            <header class="archive-header">
                <?php 
                $term = get_queried_object();
                $term_name = single_term_title('', false);
                $term_description = term_description();
                
                // Check if it's a taxonomy term or post type archive
                if (is_tax()) {
                    $term_name = single_term_title('', false);
                } elseif (is_post_type_archive()) {
                    $term_name = post_type_archive_title('', false);
                }
                ?>
                <h1 class="archive-title">
                    <?php 
                    if (is_tax('laptop_brand')) {
                        echo esc_html($term_name) . ' Laptop Devices';
                    } elseif (is_tax('laptop_category')) {
                        echo esc_html($term_name) . ' Laptops';
                    } elseif (is_post_type_archive()) {
                        echo 'Laptop Devices';
                    }
                    ?>
                </h1>
                
                <?php if ($term_description) : ?>
                    <div class="archive-description">
                        <?php echo wp_kses_post($term_description); ?>
                    </div>
                <?php endif; ?>
                
                <div class="archive-meta">
                    <span class="device-count">
                        <?php 
                        global $wp_query;
                        echo number_format($wp_query->found_posts) . ' ' . _n('laptop', 'laptops', $wp_query->found_posts, 'ezoix'); 
                        ?>
                    </span>
                </div>
            </header>
            
            <?php if (is_tax('laptop_brand')) : ?>
            <div class="mobile-filters">
                <div class="filter-section">
                    <h3>Filter by Category</h3>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'laptop_category',
                        'hide_empty' => true,
                    ));
                    
                    if ($categories && !is_wp_error($categories)) :
                        echo '<ul class="filter-list">';
                        echo '<li><a href="' . get_term_link($term) . '" class="active">All Categories</a></li>';
                        foreach ($categories as $category) {
                            $link = add_query_arg('category', $category->slug, get_term_link($term));
                            echo '<li><a href="' . esc_url($link) . '">' . esc_html($category->name) . '</a></li>';
                        }
                        echo '</ul>';
                    endif;
                    ?>
                </div>
                
                <div class="filter-section">
                    <h3>Sort by</h3>
                    <ul class="sort-list">
                        <li><a href="<?php echo add_query_arg('sort', 'date', get_term_link($term)); ?>">Newest</a></li>
                        <li><a href="<?php echo add_query_arg('sort', 'title', get_term_link($term)); ?>">Name (A-Z)</a></li>
                        <li><a href="<?php echo add_query_arg('sort', 'price_low', get_term_link($term)); ?>">Price: Low to High</a></li>
                        <li><a href="<?php echo add_query_arg('sort', 'price_high', get_term_link($term)); ?>">Price: High to Low</a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="grid-container">
                        <?php $counter = 0; ?>
                        <?php while (have_posts()) : the_post(); ?>
                            <?php $counter++; ?>
                            <?php
                            $device_price = get_field('device_price');
                            $device_status = get_field('device_status');
                            $device_rating = get_field('device_rating');
                            $specifications = get_field('specifications');
                            $author_name = get_the_author();
                            ?>
                            <article class="mobile-device-card">
                                <div class="device-image">
                                    <a href="<?php the_permalink(); ?>">
                                        <div class="thumbnail-aspect-ratio-box">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('grid-portrait', array('loading' => 'lazy', 'class' => 'category-post-thumbnail')); ?>
                                            <?php else : ?>
                                                <div class="placeholder-content laptop-device-icon">
                                                    <span class="placeholder-icon dashicons dashicons-laptop"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    
                                    <?php if ($device_status) : ?>
                                        <span class="device-status status-<?php echo esc_attr($device_status); ?>">
                                            <?php echo ucfirst($device_status); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="device-content">
                                    <h2 class="device-title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    
                                    <div class="item-meta-yt">
                                        <p class="author-name"><?php echo esc_html($author_name); ?></p>

                                        <p class="post-date">
                                        <?php if ($device_price) : ?>
                                            <span class="price-value"><?php echo esc_html($device_price); ?></span>
                                        <?php endif; ?>
                                        <?php if ($device_rating) : 
                                            $rating = floatval($device_rating) / 2;
                                            if ($device_price) echo ' &bull; ';
                                        ?>
                                            <span class="rating-value">⭐ <?php echo number_format($rating, 1); ?>/5</span>
                                        <?php endif; ?>
                                        </p>
                                    </div>
                                </div> </article>
                            <?php if ($counter % 5 === 0) : ?>
                                <div class="video-interstitial">
                                    <div class="youtube-facade" data-video-id="PKshhTHyoZU">
                                        <img src="https://img.youtube.com/vi/PKshhTHyoZU/maxresdefault.jpg" alt="Video thumbnail" loading="lazy">
                                        <div class="play-button">▶</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="pagination">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => __('← Previous', 'ezoix'),
                            'next_text' => __('Next →', 'ezoix'),
                        ));
                        ?>
                    </div>
                    
                <?php else : ?>
                    <div class="no-devices">
                        <p>
                            <?php 
                            if (is_tax()) {
                                echo 'No laptop devices found for ' . esc_html($term_name) . '.';
                            } elseif (is_post_type_archive()) {
                                echo 'No laptop devices found.';
                            }
                            ?>
                        </p>
                        <a href="<?php echo get_post_type_archive_link('laptop_device'); ?>" class="cta-button">
                            ← Browse All Laptops
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        </main>
        
        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">All Brands</h3>
                <ul class="brands-list">
                    <?php
                    $all_brands = get_terms(array(
                        'taxonomy' => 'laptop_brand',
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => true,
                    ));
                    
                    if ($all_brands && !is_wp_error($all_brands)) :
                        foreach ($all_brands as $brand) :
                            // Check if it's the active term (only for taxonomy archives)
                            $active_class = '';
                            if (is_tax('laptop_brand') && isset($term->term_id)) {
                                $active_class = ($brand->term_id == $term->term_id) ? ' class="active"' : '';
                            }
                    ?>
                        <li<?php echo $active_class; ?>>
                            <a href="<?php echo get_term_link($brand); ?>">
                                <?php echo esc_html($brand->name); ?>
                                <span class="count">(<?php echo esc_html($brand->count); ?>)</span>
                            </a>
                        </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>
            
            <div class="sidebar-widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="categories-list">
                    <?php
                    $all_categories = get_terms(array(
                        'taxonomy' => 'laptop_category',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'hide_empty' => true,
                    ));
                    
                    if ($all_categories && !is_wp_error($all_categories)) :
                        foreach ($all_categories as $category) :
                            // Check if it's the active term (only for category taxonomy archives)
                            $active_class = '';
                            if (is_tax('laptop_category') && isset($term->term_id)) {
                                $active_class = ($category->term_id == $term->term_id) ? ' class="active"' : '';
                            }
                    ?>
                        <li<?php echo $active_class; ?>>
                            <a href="<?php echo get_term_link($category); ?>">
                                <?php echo esc_html($category->name); ?>
                                <span class="count">(<?php echo esc_html($category->count); ?>)</span>
                            </a>
                        </li>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php get_footer(); ?>