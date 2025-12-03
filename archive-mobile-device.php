<?php
/**
 * Template Name: Mobile Devices Archive
 * 
 * @package Ezoix_Tech_Blog
 */

get_header(); ?>

<div class="container">
    <div class="content-area">
        <main class="main-content">
            
            <!-- Archive Header -->
            <header class="archive-header">
                <h1 class="archive-title">Mobile Devices</h1>
                <p class="archive-description">Browse our collection of mobile device specifications and reviews.</p>
            </header>
            
            <!-- Filters -->
            <div class="mobile-filters">
                <div class="filter-brands">
                    <h3>Filter by Brand</h3>
                    <?php
                    $brands = get_terms(array(
                        'taxonomy' => 'mobile_brand',
                        'hide_empty' => true,
                    ));
                    
                    if ($brands && !is_wp_error($brands)) :
                        echo '<ul class="brand-list">';
                        echo '<li><a href="' . get_post_type_archive_link('mobile_device') . '" class="active">All Brands</a></li>';
                        foreach ($brands as $brand) {
                            echo '<li><a href="' . get_term_link($brand) . '">' . esc_html($brand->name) . '</a></li>';
                        }
                        echo '</ul>';
                    endif;
                    ?>
                </div>
                
                <div class="filter-categories">
                    <h3>Filter by Category</h3>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'mobile_category',
                        'hide_empty' => true,
                    ));
                    
                    if ($categories && !is_wp_error($categories)) :
                        echo '<ul class="category-list">';
                        echo '<li><a href="' . get_post_type_archive_link('mobile_device') . '" class="active">All Categories</a></li>';
                        foreach ($categories as $category) {
                            echo '<li><a href="' . get_term_link($category) . '">' . esc_html($category->name) . '</a></li>';
                        }
                        echo '</ul>';
                    endif;
                    ?>
                </div>
            </div>
            
            <!-- Devices Grid -->
            <div class="mobile-devices-grid">
                <?php if (have_posts()) : ?>
                    
                    <div class="grid-container">
                        <?php while (have_posts()) : the_post(); ?>
                        <article class="mobile-device-card">
                            <div class="device-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                <?php else : ?>
                                    <div class="no-image">
                                        <span class="dashicons dashicons-smartphone"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="device-content">
                                <h2 class="device-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
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
                                    $specs = get_field('specifications', get_the_ID());
                                    if ($specs && is_array($specs) && !empty($specs[0]['specifications'])) :
                                        $first_specs = $specs[0]['specifications'];
                                        if (!empty($first_specs[0]['spec_value'])) :
                                            echo '<span class="device-spec">' . esc_html($first_specs[0]['spec_value']) . '</span>';
                                        endif;
                                    endif;
                                    ?>
                                </div>
                                
                                <div class="device-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                                
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
                    <p class="no-devices">No mobile devices found.</p>
                <?php endif; ?>
            </div>
            
        </main>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">Import Mobile Specs</h3>
                <p>Have JSON specifications? Import them easily:</p>
                <a href="<?php echo admin_url('admin.php?page=mobile-specs'); ?>" class="button">Import JSON</a>
            </div>
            
            <div class="sidebar-widget">
                <h3 class="widget-title">Popular Brands</h3>
                <ul class="popular-brands">
                    <?php
                    $popular_brands = get_terms(array(
                        'taxonomy' => 'mobile_brand',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                        'hide_empty' => true,
                    ));
                    
                    if ($popular_brands && !is_wp_error($popular_brands)) :
                        foreach ($popular_brands as $brand) :
                    ?>
                        <li>
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
        </aside>
    </div>
</div>

<?php get_footer(); ?>