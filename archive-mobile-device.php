<?php
/**
 * Template Name: Mobile Brands Archive
 * 
 * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            
            <!-- Archive Header -->
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
                    if (is_tax()) {
                        echo esc_html($term_name) . ' Mobile Devices';
                    } elseif (is_post_type_archive()) {
                        echo 'Mobile Devices';
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
                        echo number_format($wp_query->found_posts) . ' ' . _n('device', 'devices', $wp_query->found_posts, 'ezoix'); 
                        ?>
                    </span>
                </div>
            </header>
            
            <!-- Filters (Only show on taxonomy pages) -->
            <?php if (is_tax('mobile_brand')) : ?>
            <div class="mobile-filters">
                <div class="filter-section">
                    <h3>Filter by Category</h3>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'mobile_category',
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
            
            <!-- Devices Grid -->
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="grid-container">
                        <?php while (have_posts()) : the_post(); 
                            $device_price = get_field('device_price');
                            $device_status = get_field('device_status');
                            $device_rating = get_field('device_rating');
                            $specifications = get_field('specifications');
                        ?>
                        <article class="mobile-device-card">
                            <div class="device-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                    </a>
                                <?php else : ?>
                                    <div class="no-image">
                                        <span class="dashicons dashicons-smartphone"></span>
                                    </div>
                                <?php endif; ?>
                                
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
                                
                                <?php if ($device_price) : ?>
                                <div class="device-price">
                                    <span class="price-label">Price:</span>
                                    <span class="price-value"><?php echo esc_html($device_price); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="device-meta">
                                    <?php
                                    $brands = get_the_terms(get_the_ID(), 'mobile_brand');
                                    if ($brands && !is_wp_error($brands)) :
                                        echo '<span class="device-brand">';
                                        foreach ($brands as $brand) {
                                            echo esc_html($brand->name);
                                            break;
                                        }
                                        echo '</span>';
                                    endif;
                                    ?>
                                    
                                    <?php
                                    $categories = get_the_terms(get_the_ID(), 'mobile_category');
                                    if ($categories && !is_wp_error($categories)) :
                                        echo '<span class="device-category">';
                                        $category_names = array();
                                        foreach ($categories as $category) {
                                            $category_names[] = esc_html($category->name);
                                        }
                                        echo implode(', ', $category_names);
                                        echo '</span>';
                                    endif;
                                    ?>
                                </div>
                                
                                <?php if ($device_rating) : ?>
                                <div class="device-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = floatval($device_rating) / 2; // Convert 10-point to 5-point
                                        $full_stars = floor($rating);
                                        $half_star = ($rating - $full_stars) >= 0.5;
                                        $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                        
                                        for ($i = 0; $i < $full_stars; $i++) {
                                            echo '<span class="star full">★</span>';
                                        }
                                        if ($half_star) {
                                            echo '<span class="star half">★</span>';
                                        }
                                        for ($i = 0; $i < $empty_stars; $i++) {
                                            echo '<span class="star empty">★</span>';
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-value"><?php echo number_format($rating, 1); ?>/5</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($specifications) : 
                                    $quick_spec = '';
                                    foreach ($specifications as $spec) {
                                        if (isset($spec['category']) && $spec['category'] === 'Display' && isset($spec['items'][0])) {
                                            $quick_spec = $spec['items'][0]['value'];
                                            break;
                                        }
                                    }
                                    
                                    if (!$quick_spec && isset($specifications[0]['items'][0])) {
                                        $quick_spec = $specifications[0]['items'][0]['value'];
                                    }
                                    
                                    if ($quick_spec) : ?>
                                    <div class="device-quick-spec">
                                        <span class="spec-label">Display:</span>
                                        <span class="spec-value"><?php echo esc_html($quick_spec); ?></span>
                                    </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="<?php the_permalink(); ?>" class="view-details">View Details →</a>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
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
                                echo 'No mobile devices found for ' . esc_html($term_name) . '.';
                            } elseif (is_post_type_archive()) {
                                echo 'No mobile devices found.';
                            }
                            ?>
                        </p>
                        <a href="<?php echo get_post_type_archive_link('mobile_device'); ?>" class="cta-button">
                            ← Browse All Devices
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        </main>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">All Brands</h3>
                <ul class="brands-list">
                    <?php
                    $all_brands = get_terms(array(
                        'taxonomy' => 'mobile_brand',
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => true,
                    ));
                    
                    if ($all_brands && !is_wp_error($all_brands)) :
                        foreach ($all_brands as $brand) :
                            // Check if it's the active term (only for taxonomy archives)
                            $active_class = '';
                            if (is_tax('mobile_brand') && isset($term->term_id)) {
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
                        'taxonomy' => 'mobile_category',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'hide_empty' => true,
                    ));
                    
                    if ($all_categories && !is_wp_error($all_categories)) :
                        foreach ($all_categories as $category) :
                            // Check if it's the active term (only for category taxonomy archives)
                            $active_class = '';
                            if (is_tax('mobile_category') && isset($term->term_id)) {
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